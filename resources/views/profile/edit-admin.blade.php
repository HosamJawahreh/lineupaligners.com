@extends('layouts.app')

@section('title', 'Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/settings-layout.css') }}">
@endpush

@section('content')
<section class="content settings-page">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-sm-12">
                <h2>My Profile<small>Your administrator account</small></h2>
            </div>
            <ul class="breadcrumb float-md-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i></a></li>
                <li class="breadcrumb-item active">Profile</li>
            </ul>
        </div>
    </div>

    <div class="container-fluid">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card settings-card">
                <div class="header"><h2><strong>Profile</strong> Picture</h2></div>
                <div class="body">
                    <div class="row clearfix align-items-center">
                        <div class="col-md-3 text-center m-b-20 m-md-b-0">
                            <img src="{{ $user->photoUrl() }}" id="profile-photo-preview" class="settings-profile-avatar" alt="">
                            <p class="m-t-10 m-b-0"><strong>{{ $user->name }}</strong></p>
                            <small class="text-muted">Administrator</small>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group form-file-field">
                                <label>Upload photo</label>
                                <input type="file" name="photo" id="profile-photo-input" accept="image/jpeg,image/png,image/webp">
                                <small class="text-muted d-block m-t-5">PNG, JPG or WebP — max 2MB</small>
                            </div>
                            @if($user->photo)
                            <div class="checkbox">
                                <input type="checkbox" name="remove_photo" id="remove_photo" value="1">
                                <label for="remove_photo">Remove profile picture</label>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card settings-card">
                <div class="header"><h2><strong>Account</strong> Details</h2></div>
                <div class="body">
                    <div class="row clearfix">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>New password</label>
                                <input type="password" name="password" class="form-control" autocomplete="new-password" placeholder="Leave blank to keep current">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Confirm password</label>
                                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                            </div>
                        </div>
                    </div>
                    <p class="text-muted m-b-0">
                        Clinic branding, appearance, and system options are in
                        <a href="{{ route('settings.index') }}">Settings</a>.
                    </p>
                </div>
            </div>

            <div class="text-right m-b-30">
                <button type="submit" class="btn btn-primary btn-round waves-effect">
                    <i class="zmdi zmdi-check m-r-5"></i> Save profile
                </button>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function () {
    $('#profile-photo-input').on('change', function () {
        var file = this.files && this.files[0];
        if (!file) {
            return;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#profile-photo-preview').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush
