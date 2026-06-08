@extends('layouts.app')

@php
    $isEdit = isset($patient);
    $caseTypes = config('patient-case-types', []);
    $fullName = old('name', $isEdit ? $patient->fullName() : '');
    $defaultDoctorId = old('doctor_id', $isEdit ? $patient->doctor_id : (auth()->user()->isDoctor() ? auth()->user()->doctor?->id : ''));
@endphp

@section('title', $isEdit ? 'Edit Case' : 'New Case')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/patient-form.css') }}?v=4">
@endpush

@section('content')
<section class="content patient-form-page patient-case-form">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>{{ $isEdit ? 'Edit' : 'Add' }} Patient Case Study
                <small class="text-muted">{{ config('app.name') }}</small>
                </h2>
            </div>
            <ul class="breadcrumb float-md-right">
                <li class="breadcrumb-item"><a href="{{ route('patients.index') }}">Patients</a></li>
                <li class="breadcrumb-item active">{{ $isEdit ? 'Edit' : 'Add' }}</li>
            </ul>
        </div>
    </div>

    <div class="container-fluid">
        <form method="POST"
              action="{{ $isEdit ? route('patients.update', $patient) : route('patients.store') }}"
              enctype="multipart/form-data"
              class="patient-case-form-inner"
              data-scan-upload>
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="card patient-case-card">
                <div class="patient-case-card-header">
                    <i class="zmdi zmdi-plus-circle-o"></i>
                    {{ $isEdit ? 'Edit' : 'Add' }} Patient Case Study
                </div>
                <div class="body">
                    <div class="row clearfix">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $fullName }}" placeholder="Patient full name" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Case Type <span class="text-danger">*</span></label>
                                <select name="case_type" class="form-control show-tick" required>
                                    <option value="">— Select case type —</option>
                                    @foreach($caseTypes as $value => $label)
                                        <option value="{{ $value }}" @selected(old('case_type', $patient->case_type ?? 'full_case') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row clearfix">
                        @if(auth()->user()->isAdmin() && isset($doctors) && $doctors->isNotEmpty())
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Doctor <span class="text-danger">*</span></label>
                                <select name="doctor_id" class="form-control show-tick" required>
                                    <option value="">— Select doctor —</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" @selected((string) $defaultDoctorId === (string) $doctor->id)>
                                            Dr. {{ $doctor->fullName() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @else
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Doctor</label>
                                <input type="text" class="form-control" value="Dr. {{ auth()->user()->doctor?->fullName() ?? auth()->user()->name }}" readonly>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Email <span class="text-success small">optional</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $patient->email ?? '') }}" placeholder="Email">
                            </div>
                        </div>
                    </div>

                    <div class="row clearfix">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Phone <span class="text-success small">optional</span></label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $patient->phone ?? '') }}" placeholder="Phone">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label>Date of Birth <span class="text-success small">optional</span></label>
                                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', isset($patient) && $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label>Gender <span class="text-success small">optional</span></label>
                                <select name="gender" class="form-control show-tick">
                                    <option value="">— Select —</option>
                                    @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('gender', $patient->gender ?? '') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row clearfix">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Photos <span class="text-success small">optional — multiple</span></label>
                                <div class="case-photos-dropzone" id="case-photos-dropzone">
                                    <i class="zmdi zmdi-camera"></i>
                                    <p class="m-b-5">Drop images here or click to browse</p>
                                    <small class="text-muted">Max 100MB each — PNG, JPG, JPEG, WebP</small>
                                    <input type="file" name="photos[]" id="case-photos-input" multiple accept="image/jpeg,image/png,image/webp">
                                </div>
                                <div id="case-photos-preview" class="case-photos-preview m-t-10"></div>
                                @if($isEdit && $patient->originalPhotos->isNotEmpty())
                                <div class="case-photos-existing m-t-15">
                                    <small class="text-muted d-block m-b-10">Current photos</small>
                                    <div class="row">
                                        @foreach($patient->originalPhotos as $photo)
                                        <div class="col-6 col-md-4 m-b-10">
                                            <div class="case-photo-thumb">
                                                <img src="{{ $photo->url() }}" alt="Case photo">
                                                <div class="checkbox m-t-5 m-b-0">
                                                    <input type="checkbox" name="remove_photos[]" id="remove-photo-{{ $photo->id }}" value="{{ $photo->id }}">
                                                    <label for="remove-photo-{{ $photo->id }}">Remove</label>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Notes <span class="text-success small">optional</span></label>
                                <textarea name="notes" rows="8" class="form-control no-resize" placeholder="Case notes, treatment plan, aligner stages…">{{ old('notes', $patient->notes ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row clearfix">
                        <div class="col-12">
                            <p class="text-muted m-b-15">
                                <i class="zmdi zmdi-rotate-3d m-r-5"></i>
                                3D scans are optional — upload upper, lower, both, or add them later when editing the case.
                            </p>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group scan-upload-box scan-upload-box--upper">
                                <label for="upper_jaw_scan">Upper 3D Model <span class="text-success small">optional</span></label>
                                <div class="scan-upload-box__visual" aria-hidden="true">
                                    <img src="{{ asset('assets/images/placeholders/uppper-paceholder.jpeg') }}" alt="" width="160" height="100">
                                </div>
                                <input type="file" name="upper_jaw_scan" id="upper_jaw_scan" accept=".stl,.obj,.ply,.zip,model/stl,model/obj,application/octet-stream,application/zip">
                                <small class="text-muted d-block m-t-5">STL, OBJ, PLY, or ZIP — max 100MB</small>
                                @if($isEdit && $patient->upper_jaw_scan)
                                <div class="scan-current-file">
                                    <a href="{{ $patient->upperJawScanDownloadUrl() }}" download>{{ $patient->scanDisplayName($patient->upper_jaw_scan, 'upper_jaw_scan') }}</a>
                                </div>
                                <div class="checkbox m-t-10 m-b-0">
                                    <input type="checkbox" name="remove_upper_jaw_scan" id="remove_upper_jaw_scan" value="1">
                                    <label for="remove_upper_jaw_scan">Remove current file</label>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group scan-upload-box scan-upload-box--lower">
                                <label for="lower_jaw_scan">Lower 3D Model <span class="text-success small">optional</span></label>
                                <div class="scan-upload-box__visual" aria-hidden="true">
                                    <img src="{{ asset('assets/images/placeholders/lower-paceholder.jpeg') }}" alt="" width="160" height="100">
                                </div>
                                <input type="file" name="lower_jaw_scan" id="lower_jaw_scan" accept=".stl,.obj,.ply,.zip,model/stl,model/obj,application/octet-stream,application/zip">
                                <small class="text-muted d-block m-t-5">STL, OBJ, PLY, or ZIP — max 100MB</small>
                                @if($isEdit && $patient->lower_jaw_scan)
                                <div class="scan-current-file">
                                    <a href="{{ $patient->lowerJawScanDownloadUrl() }}" download>{{ $patient->scanDisplayName($patient->lower_jaw_scan, 'lower_jaw_scan') }}</a>
                                </div>
                                <div class="checkbox m-t-10 m-b-0">
                                    <input type="checkbox" name="remove_lower_jaw_scan" id="remove_lower_jaw_scan" value="1">
                                    <label for="remove_lower_jaw_scan">Remove current file</label>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row clearfix">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group scan-upload-box">
                                <label for="case_data_zip">Case Data Archive <span class="text-success small">optional</span></label>
                                <input type="file" name="case_data_zip" id="case_data_zip" accept=".zip,application/zip">
                                <small class="text-muted d-block m-t-5">ZIP file — max 100MB</small>
                                @if($isEdit && $patient->hasCaseDataZip())
                                <div class="scan-current-file">
                                    <a href="{{ $patient->caseDataZipDownloadUrl() }}" download>{{ $patient->caseDataZipDisplayName() }}</a>
                                    @if($size = $patient->caseDataZipSizeLabel())
                                    <small class="text-muted d-block">{{ $size }}</small>
                                    @endif
                                </div>
                                <div class="checkbox m-t-10 m-b-0">
                                    <input type="checkbox" name="remove_case_data_zip" id="remove_case_data_zip" value="1">
                                    <label for="remove_case_data_zip">Remove current archive</label>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="text-center m-t-20">
                        <button type="submit" class="btn btn-primary btn-round btn-lg patient-case-submit">
                            <i class="zmdi zmdi-plus m-r-5"></i>{{ $isEdit ? 'Update Patient Case Study' : 'Create Patient Case Study' }}
                        </button>
                        <a href="{{ route('patients.index') }}" class="btn btn-default btn-round btn-simple m-l-10">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function () {
    var $zone = $('#case-photos-dropzone');
    var $input = $('#case-photos-input');
    var $preview = $('#case-photos-preview');

    $zone.on('click', function (e) {
        if (e.target === $input[0]) return;
        $input.trigger('click');
    });

    $zone.on('dragover', function (e) {
        e.preventDefault();
        $zone.addClass('dragover');
    }).on('dragleave drop', function (e) {
        e.preventDefault();
        $zone.removeClass('dragover');
    });

    $input.on('change', function () {
        $preview.empty();
        Array.from(this.files || []).forEach(function (file) {
            if (!file.type.match(/^image\//)) return;
            var reader = new FileReader();
            reader.onload = function (ev) {
                $preview.append(
                    '<div class="case-photo-preview-item"><img src="' + ev.target.result + '" alt=""><small>' + file.name + '</small></div>'
                );
            };
            reader.readAsDataURL(file);
        });
    });

    setTimeout(function () {
        if (typeof window.LineUpActivateSelectpickers === 'function') {
            window.LineUpActivateSelectpickers();
        }
    }, 100);
});
</script>
@endpush
