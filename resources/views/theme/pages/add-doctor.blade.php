@extends('layouts.app')

@section('title', isset($doctor) ? 'Edit Doctor' : 'Add Doctor')

@section('content')
<section class="content doctor-form-page">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>{{ isset($doctor) ? 'Edit' : 'Add' }} Doctor
                <small class="text-muted">{{ config('app.name') }}</small>
                </h2>
            </div>
            <ul class="breadcrumb float-md-right">
                <li class="breadcrumb-item"><a href="{{ route('doctors.index') }}">Doctors</a></li>
                <li class="breadcrumb-item active">{{ isset($doctor) ? 'Edit' : 'Add' }}</li>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <form method="POST" action="{{ isset($doctor) ? route('doctors.update', $doctor) : route('doctors.store') }}">
            @csrf
            @if(isset($doctor)) @method('PUT') @endif
            <div class="row clearfix">
                <div class="col-md-12">
                    <div class="card">
                        <div class="header"><h2><strong>Doctor</strong> Profile</h2></div>
                        <div class="body">
                            <div class="row clearfix">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <input type="text" name="first_name" class="form-control" placeholder="First Name" value="{{ old('first_name', $doctor->first_name ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <input type="text" name="last_name" class="form-control" placeholder="Last Name" value="{{ old('last_name', $doctor->last_name ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <input type="text" name="specialty" class="form-control" placeholder="Specialty" value="{{ old('specialty', $doctor->specialty ?? 'Orthodontist') }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <input type="text" name="phone" class="form-control" placeholder="Phone" value="{{ old('phone', $doctor->phone ?? '') }}">
                                    </div>
                                </div>
                                @if(!empty($doctorRoles) && $doctorRoles->isNotEmpty())
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Doctor Role</label>
                                        <select name="doctor_role_id" class="form-control show-tick">
                                            <option value="">— No role —</option>
                                            @foreach($doctorRoles as $role)
                                                <option value="{{ $role->id }}" @selected(old('doctor_role_id', $doctor->doctor_role_id ?? '') == $role->id)>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Controls what this doctor can do in the app</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row clearfix">
                <div class="col-md-12">
                    <div class="card">
                        <div class="header"><h2><strong>Doctor's</strong> Account Information</h2></div>
                        <div class="body">
                            <div class="row clearfix">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="email" name="email" class="form-control" placeholder="Login Email" value="{{ old('email', $doctor->email ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <input type="password" name="password" class="form-control" placeholder="{{ isset($doctor) ? (empty($doctor->user_id) ? 'Password (required — no login linked)' : 'New Password (optional)') : 'Password' }}" {{ isset($doctor) && empty($doctor->user_id) ? 'required' : (isset($doctor) ? '' : 'required') }}>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" {{ isset($doctor) ? '' : 'required' }}>
                                    </div>
                                </div>
                                @if(isset($doctor))
                                <div class="col-sm-12">
                                    <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $doctor->is_active ?? true))> Active</label>
                                </div>
                                @endif
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary btn-round">Submit</button>
                                    <a href="{{ route('doctors.index') }}" class="btn btn-default btn-round btn-simple">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection
