<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return view('profile.edit-admin', [
                'user' => $user,
            ]);
        }

        $doctor = $user->doctor()->with('doctorRole')->first()
            ?? Doctor::with('doctorRole')->where('email', $user->email)->whereNull('user_id')->first();

        return view('profile.edit', [
            'user' => $user,
            'doctor' => $doctor,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->updateAdmin($request, $user);
        }

        $doctor = $user->doctor
            ?? Doctor::where('email', $user->email)->whereNull('user_id')->first();

        if (! $doctor) {
            throw ValidationException::withMessages([
                'email' => 'Your doctor profile is not set up yet. Please contact an administrator.',
            ]);
        }

        $linkedUserId = $doctor->user_id ?? $user->id;

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
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'website' => ['nullable', 'url', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_photo' => ['sometimes', 'boolean'],
        ]);

        $doctor->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'specialty' => $data['specialty'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'bio' => $data['bio'] ?? null,
            'website' => $data['website'] ?? null,
            'experience_years' => $data['experience_years'] ?? 0,
        ]);

        $userAttributes = [
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => User::ROLE_DOCTOR,
        ];

        if (! empty($data['password'])) {
            $userAttributes['password'] = $data['password'];
        }

        if ($doctor->user_id && (int) $doctor->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'email' => 'This doctor profile is linked to another account. Please contact an administrator.',
            ]);
        }

        if ($doctor->user_id) {
            $user->update($userAttributes);
        } else {
            $existing = User::where('email', $data['email'])
                ->where('id', '!=', $user->id)
                ->first();

            if ($existing) {
                if ($existing->role !== User::ROLE_DOCTOR) {
                    throw ValidationException::withMessages([
                        'email' => 'This email belongs to another account type and cannot be used.',
                    ]);
                }

                throw ValidationException::withMessages([
                    'email' => 'This email is already used by another doctor account.',
                ]);
            }

            $user->update($userAttributes);
            $doctor->update(['user_id' => $user->id]);
        }

        if ($request->hasFile('photo')) {
            $this->deleteStoredFile($user->photo);
            $this->deleteStoredFile($doctor->photo);
            $path = $request->file('photo')->store('profiles', 'public');
            $user->update(['photo' => $path]);
            $doctor->update(['photo' => $path]);
        } elseif ($request->boolean('remove_photo')) {
            $this->deleteStoredFile($user->photo);
            $this->deleteStoredFile($doctor->photo);
            $user->update(['photo' => null]);
            $doctor->update(['photo' => null]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    protected function updateAdmin(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_photo' => ['sometimes', 'boolean'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => $data['password']]);
        }

        if ($request->hasFile('photo')) {
            $this->deleteStoredFile($user->photo);
            $path = $request->file('photo')->store('profiles', 'public');
            $user->update(['photo' => $path]);
        } elseif ($request->boolean('remove_photo')) {
            $this->deleteStoredFile($user->photo);
            $user->update(['photo' => null]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
