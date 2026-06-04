<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(): View
    {
        return view('theme.pages.book-appointment');
    }

    public function create(): View
    {
        return view('theme.pages.book-appointment');
    }

    public function store(Request $request): RedirectResponse
    {
        Appointment::create($request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'scheduled_at' => ['required', 'date'],
            'status' => ['nullable', 'in:pending,confirmed,cancelled,completed'],
            'notes' => ['nullable', 'string'],
        ]));

        return redirect()->route('appointments.index')->with('success', 'Appointment booked.');
    }

    public function edit(Appointment $appointment): View
    {
        return view('theme.pages.book-appointment');
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment->update($request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'scheduled_at' => ['required', 'date'],
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
            'notes' => ['nullable', 'string'],
        ]));

        return redirect()->route('appointments.index')->with('success', 'Appointment updated.');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $appointment->delete();

        return redirect()->route('appointments.index')->with('success', 'Appointment cancelled.');
    }
}
