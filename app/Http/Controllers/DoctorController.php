<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Doctor::class);

        $doctors = Doctor::with(['user', 'doctorRole'])->orderBy('first_name')->get();

        return view('theme.pages.doctors', compact('doctors'));
    }

    public function create(): View
    {
        $this->authorize('create', Doctor::class);

        return view('theme.pages.add-doctor', [
            'doctorRoles' => DoctorRole::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Doctor::class);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'doctor_role_id' => ['nullable', 'exists:doctor_roles,id'],
        ]);

        $user = User::create([
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_DOCTOR,
            'phone' => $data['phone'] ?? null,
        ]);

        Doctor::create([
            'user_id' => $user->id,
            'doctor_role_id' => $data['doctor_role_id'] ?? null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'specialty' => $data['specialty'] ?? 'Orthodontist',
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'is_active' => true,
        ]);

        return redirect()->route('doctors.index')->with('success', 'Doctor account created successfully.');
    }

    public function show(Doctor $doctor): View
    {
        $this->authorize('view', $doctor);

        return view('theme.pages.profile', compact('doctor'));
    }

    public function edit(Doctor $doctor): View
    {
        $this->authorize('update', $doctor);

        return view('theme.pages.add-doctor', [
            'doctor' => $doctor,
            'doctorRoles' => DoctorRole::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $this->authorize('update', $doctor);

        $linkedUserId = $doctor->user_id
            ?? User::where('email', $request->input('email'))->value('id');

        $needsNewLogin = $doctor->user_id === null && $linkedUserId === null;

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($linkedUserId),
            ],
            'password' => [
                Rule::requiredIf($needsNewLogin),
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'is_active' => ['sometimes', 'boolean'],
            'doctor_role_id' => ['nullable', 'exists:doctor_roles,id'],
        ]);

        $doctor->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'specialty' => $data['specialty'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'doctor_role_id' => $data['doctor_role_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->syncDoctorUser($doctor, $data);

        return redirect()->route('doctors.index')->with('success', 'Doctor updated successfully.');
    }

    /**
     * Keep the linked users row in sync (create or link when missing).
     */
    private function syncDoctorUser(Doctor $doctor, array $data): void
    {
        $name = trim($data['first_name'].' '.$data['last_name']);

        $user = $doctor->user;

        if (! $user) {
            $user = User::where('email', $data['email'])->first();
        }

        if ($user && $user->role !== User::ROLE_DOCTOR) {
            throw ValidationException::withMessages([
                'email' => 'This email belongs to a non-doctor account and cannot be linked.',
            ]);
        }

        $userAttributes = [
            'name' => $name,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => User::ROLE_DOCTOR,
        ];

        if (! empty($data['password'])) {
            $userAttributes['password'] = $data['password'];
        }

        if ($user) {
            $user->update($userAttributes);

            if ($doctor->user_id !== $user->id) {
                $doctor->update(['user_id' => $user->id]);
            }

            return;
        }

        if (empty($data['password'])) {
            throw ValidationException::withMessages([
                'password' => 'A password is required to create the doctor login account.',
            ]);
        }

        $user = User::create($userAttributes);

        $doctor->update(['user_id' => $user->id]);
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        $this->authorize('delete', $doctor);

        if ($doctor->user) {
            $doctor->user->delete();
        }

        $doctor->delete();

        return redirect()->route('doctors.index')->with('success', 'Doctor removed successfully.');
    }
}
