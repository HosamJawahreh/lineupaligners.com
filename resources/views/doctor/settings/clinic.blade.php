@extends('layouts.app')

@section('title', 'Clinic Settings')
@section('meta_description', 'Manage your clinic name, contact details, and address for LineUp cases.')
@section('body-class', 'doctor-clinic-settings-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/doctor-clinic-settings.css') }}">
@endpush

@section('content')
<section class="content doctor-clinic-settings-page">
    <div class="doctor-clinic-settings__wrap">
        <header class="doctor-clinic-settings__head">
            <h1>Clinic settings</h1>
            <p>Your practice details shown on cases and exports</p>
        </header>

        <form method="POST" action="{{ route('doctor.clinic-settings.update') }}" class="doctor-clinic-settings__form">
            @csrf
            @method('PUT')

            <div class="doctor-clinic-settings__panel">
                <section class="doctor-clinic-settings__section">
                    <h2 class="doctor-clinic-settings__section-title">Clinic information</h2>
                    <div class="doctor-clinic-settings__grid">
                        <div class="doctor-clinic-settings__field doctor-clinic-settings__field--full">
                            <label for="clinic_name">Clinic name</label>
                            <input type="text" name="clinic_name" id="clinic_name" class="form-control"
                                   value="{{ old('clinic_name', $doctor->clinic_name) }}" required
                                   placeholder="e.g. Smile Dental Clinic">
                        </div>
                        <div class="doctor-clinic-settings__field">
                            <label for="clinic_email">Contact email</label>
                            <input type="email" name="clinic_email" id="clinic_email" class="form-control"
                                   value="{{ old('clinic_email', $doctor->clinic_email) }}"
                                   placeholder="clinic@example.com">
                        </div>
                        <div class="doctor-clinic-settings__field">
                            <label for="clinic_phone">Phone</label>
                            <input type="text" name="clinic_phone" id="clinic_phone" class="form-control"
                                   value="{{ old('clinic_phone', $doctor->clinic_phone) }}"
                                   placeholder="+1 555 0000">
                        </div>
                        <div class="doctor-clinic-settings__field doctor-clinic-settings__field--full">
                            <label for="clinic_address">Address</label>
                            <textarea name="clinic_address" id="clinic_address" class="form-control" rows="3"
                                      placeholder="Street, city, country">{{ old('clinic_address', $doctor->clinic_address) }}</textarea>
                        </div>
                    </div>
                    <p class="doctor-clinic-settings__hint">Leave fields empty to use the default clinic name from LineUp until you save your own.</p>
                </section>

                <footer class="doctor-clinic-settings__footer">
                    <button type="submit" class="btn btn-primary btn-round">
                        <i class="zmdi zmdi-check m-r-5"></i> Save clinic settings
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-default btn-round btn-simple">Cancel</a>
                </footer>
            </div>
        </form>
    </div>
</section>
@endsection
