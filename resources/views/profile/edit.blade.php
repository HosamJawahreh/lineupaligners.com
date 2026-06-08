@extends('layouts.app')

@section('title', 'Profile')
@section('body-class', 'doctor-profile-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/profile-page.css') }}?v=6">
@endpush

@section('content')
@php
    $displayName = $doctor
        ? trim(($doctor->first_name ?? '').' '.($doctor->last_name ?? ''))
        : ($user->name ?? 'Doctor');
    $metaLine = collect([
        $doctor?->specialty,
        $doctor?->doctorRole?->name,
    ])->filter()->implode(' · ');
@endphp

<section class="content doctor-profile-page">
    <div class="doctor-profile__wrap">
        <header class="doctor-profile__head">
            <h1>My Profile</h1>
            <p>Account, personal, and professional details</p>
        </header>

        @if(!$doctor)
            <div class="doctor-profile__empty">
                <div class="alert alert-warning m-b-0">
                    Your doctor profile is not linked yet. Please contact an administrator to complete your account setup.
                </div>
            </div>
        @else
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="doctor-profile__form">
            @csrf
            @method('PUT')

            <div class="doctor-profile__panel">
                <div class="doctor-profile__identity">
                    <img src="{{ $user->photoUrl() }}" id="profile-photo-preview" class="doctor-profile__avatar" alt="">
                    <div class="doctor-profile__identity-info">
                        <h2 class="doctor-profile__name">{{ $displayName ?: 'Doctor' }}</h2>
                        @if($metaLine)
                            <span class="doctor-profile__meta">{{ $metaLine }}</span>
                        @else
                            <span class="doctor-profile__meta">{{ $user->email }}</span>
                        @endif
                        @if($doctor->doctorRole && !$doctor->specialty)
                            <span class="doctor-profile__role">{{ $doctor->doctorRole->name }}</span>
                        @endif
                    </div>
                    <div class="doctor-profile__identity-actions">
                        <label class="doctor-profile__upload-btn">
                            <i class="zmdi zmdi-camera" aria-hidden="true"></i>
                            Change photo
                            <input type="file" name="photo" id="profile-photo-input" accept="image/jpeg,image/png,image/webp">
                        </label>
                        @if($user->photo || $doctor->photo)
                        <div class="doctor-profile__remove-photo checkbox">
                            <input type="checkbox" name="remove_photo" id="remove_photo" value="1">
                            <label for="remove_photo">Remove photo</label>
                        </div>
                        @endif
                    </div>
                </div>

                <section class="doctor-profile__section">
                    <h3 class="doctor-profile__section-title">Personal</h3>
                    <div class="doctor-profile__grid doctor-profile__grid--personal">
                        <div class="doctor-profile__field">
                            <label for="first_name">First name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name', $doctor->first_name) }}" required>
                        </div>
                        <div class="doctor-profile__field">
                            <label for="last_name">Last name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name', $doctor->last_name) }}" required>
                        </div>
                        <div class="doctor-profile__field">
                            <label for="gender">Gender</label>
                            <select name="gender" id="gender" class="form-control">
                                <option value="">—</option>
                                <option value="male" @selected(old('gender', $doctor->gender) === 'male')>Male</option>
                                <option value="female" @selected(old('gender', $doctor->gender) === 'female')>Female</option>
                                <option value="other" @selected(old('gender', $doctor->gender) === 'other')>Other</option>
                            </select>
                        </div>
                        <div class="doctor-profile__field">
                            <label for="date_of_birth">Date of birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" value="{{ old('date_of_birth', $doctor->date_of_birth?->format('Y-m-d')) }}">
                        </div>
                    </div>
                </section>

                <hr class="doctor-profile__divider">

                <section class="doctor-profile__section">
                    <h3 class="doctor-profile__section-title">Professional</h3>
                    <div class="doctor-profile__grid">
                        <div class="doctor-profile__field">
                            <label for="specialty">Specialty</label>
                            <input type="text" name="specialty" id="specialty" class="form-control" value="{{ old('specialty', $doctor->specialty) }}" placeholder="e.g. Orthodontist">
                        </div>
                        <div class="doctor-profile__field">
                            <label for="experience_years">Years of experience</label>
                            <input type="number" name="experience_years" id="experience_years" class="form-control" min="0" max="80" value="{{ old('experience_years', $doctor->experience_years) }}">
                        </div>
                        <div class="doctor-profile__field doctor-profile__field--full">
                            <label for="website">Website</label>
                            <input type="url" name="website" id="website" class="form-control" value="{{ old('website', $doctor->website) }}" placeholder="https://">
                        </div>
                        <div class="doctor-profile__field doctor-profile__field--full">
                            <label for="bio">Bio</label>
                            <textarea name="bio" id="bio" class="form-control" rows="3" placeholder="Short professional bio">{{ old('bio', $doctor->bio) }}</textarea>
                        </div>
                    </div>
                </section>

                <hr class="doctor-profile__divider">

                <section class="doctor-profile__section">
                    <h3 class="doctor-profile__section-title">Account &amp; security</h3>
                    <div class="doctor-profile__grid doctor-profile__grid--account">
                        <div class="doctor-profile__field">
                            <label for="phone">Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $doctor->phone ?? $user->phone) }}">
                        </div>
                        <div class="doctor-profile__field">
                            <label for="email">Login email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $doctor->email ?? $user->email) }}" required>
                        </div>
                        <div class="doctor-profile__field">
                            <label for="password">New password</label>
                            <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" placeholder="{{ $doctor->user_id ? 'Leave blank to keep' : 'Required' }}" {{ $doctor->user_id ? '' : 'required' }}>
                        </div>
                        <div class="doctor-profile__field">
                            <label for="password_confirmation">Confirm password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password" placeholder="Confirm">
                        </div>
                    </div>
                    @if($doctor->doctorRole)
                    <p class="doctor-profile__hint m-t-10">Role: {{ $doctor->doctorRole->name }} (assigned by administrator)</p>
                    @endif
                </section>

                <footer class="doctor-profile__footer">
                    <button type="submit" class="btn btn-primary btn-round">
                        <i class="zmdi zmdi-check m-r-5"></i> Save changes
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-default btn-round btn-simple">Cancel</a>
                </footer>
            </div>
        </form>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function () {
    var defaultAvatar = @json(asset('assets/images/profile_av.jpg'));

    $('#profile-photo-input').on('change', function () {
        var file = this.files && this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#profile-photo-preview').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
        $('#remove_photo').prop('checked', false);
    });

    $('#remove_photo').on('change', function () {
        if (this.checked) {
            $('#profile-photo-preview').attr('src', defaultAvatar);
            $('#profile-photo-input').val('');
        }
    });
});
</script>
@endpush
