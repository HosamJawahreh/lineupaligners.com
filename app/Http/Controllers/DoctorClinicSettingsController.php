<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorClinicSettingsController extends Controller
{
    public function edit(): View
    {
        $doctor = $this->doctorProfile();

        return view('doctor.settings.clinic', [
            'doctor' => $doctor,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $doctor = $this->doctorProfile();

        $data = $request->validate([
            'clinic_name' => ['required', 'string', 'max:255'],
            'clinic_email' => ['nullable', 'email', 'max:255'],
            'clinic_phone' => ['nullable', 'string', 'max:50'],
            'clinic_address' => ['nullable', 'string', 'max:500'],
        ]);

        $doctor->update([
            'clinic_name' => $data['clinic_name'],
            'clinic_email' => $data['clinic_email'] ?? null,
            'clinic_phone' => $data['clinic_phone'] ?? null,
            'clinic_address' => $data['clinic_address'] ?? null,
        ]);

        return back()->with('success', 'Clinic information saved.');
    }

    protected function doctorProfile(): Doctor
    {
        $user = auth()->user();

        abort_unless($user->isDoctor(), 403);

        $doctor = $user->doctor
            ?? Doctor::where('email', $user->email)->whereNull('user_id')->first();

        abort_if(! $doctor, 403, 'Your doctor profile is not linked yet. Please contact an administrator.');

        return $doctor;
    }
}
