@extends('layouts.app')

@section('title', 'Patient Profile')

@section('content')
<section class="content profile-page">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>Patient Profile
                <small class="text-muted">{{ config('app.name') }}</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">
                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-primary btn-icon btn-round d-none d-md-inline-block float-right m-l-10">
                    <i class="zmdi zmdi-edit"></i>
                </a>
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('patients.index') }}">Patients</a></li>
                    <li class="breadcrumb-item active">Patient Profile</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-4 col-md-12 col-sm-12">
                <div class="card member-card">
                    <div class="header l-coral">
                        <h4 class="m-t-10">{{ $patient->fullName() }}</h4>
                        <small>{{ $patient->display_patient_id }}</small>
                    </div>
                    <div class="member-img">
                        <img src="{{ $patient->photoUrl() }}" class="rounded-circle" alt="profile-image">
                    </div>
                    <div class="body">
                        <div class="col-12">
                            <span class="badge badge-success">{{ $patient->statusLabel() }}</span>
                            <span class="badge badge-info m-l-5">{{ $patient->caseTypeLabel() }}</span>
                        </div>
                        <hr>
                        <strong>Date of Birth</strong>
                        <p>{{ $patient->date_of_birth?->format('d M Y') ?? '—' }}</p>
                        <strong>Age</strong>
                        <p>{{ $patient->age ?? '—' }}</p>
                        <strong>Email ID</strong>
                        <p>{{ $patient->email ?? '—' }}</p>
                        <strong>Phone</strong>
                        <p>{{ $patient->phone ?? '—' }}</p>
                        <hr>
                        <strong>Address</strong>
                        <address>{{ $patient->address ?? '—' }}</address>
                        @if($patient->doctor)
                        <hr>
                        <strong>Doctor</strong>
                        <p>Dr. {{ $patient->doctor->fullName() }}</p>
                        @endif
                        @if($patient->photos->isNotEmpty())
                        <hr>
                        <strong>Case Photos</strong>
                        <div class="row m-t-10">
                            @foreach($patient->photos as $photo)
                            <div class="col-4 m-b-10">
                                <a href="{{ $photo->url() }}" target="_blank" rel="noopener">
                                    <img src="{{ $photo->url() }}" alt="Case photo" class="img-fluid rounded" style="max-height:80px;object-fit:cover;width:100%;">
                                </a>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        @if($patient->notes)
                        <hr>
                        <strong>Notes</strong>
                        <p class="m-b-0">{{ $patient->notes }}</p>
                        @endif
                        @if($patient->upper_jaw_scan || $patient->lower_jaw_scan)
                        <hr id="scans">
                        <strong>3D Jaw Scans</strong>
                        <ul class="list-unstyled m-b-0 m-t-10">
                            @if($patient->upper_jaw_scan)
                            <li class="m-b-5">
                                <i class="zmdi zmdi-layers m-r-5"></i>
                                <a href="{{ $patient->upperJawScanUrl() }}" download>{{ $patient->scanDisplayName($patient->upper_jaw_scan, 'upper_jaw_scan') }}</a>
                            </li>
                            @endif
                            @if($patient->lower_jaw_scan)
                            <li>
                                <i class="zmdi zmdi-layers m-r-5"></i>
                                <a href="{{ $patient->lowerJawScanUrl() }}" download>{{ $patient->scanDisplayName($patient->lower_jaw_scan, 'lower_jaw_scan') }}</a>
                            </li>
                            @endif
                        </ul>
                        @endif
                    </div>
                </div>
                <div class="card">
                    <div class="header">
                        <h2><strong>General</strong> Report</h2>                                
                    </div>
                    <div class="body">
                        <ul class="list-unstyled">
                            <li>
                                <div>Blood Pressure</div>
                                <div class="progress m-b-20">
                                    <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%"> <span class="sr-only">40% Complete (success)</span> </div>
                                </div>
                            </li>
                            <li>
                                <div>Heart Beat</div>
                                <div class="progress m-b-20">
                                    <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%"> <span class="sr-only">20% Complete</span> </div>
                                </div>
                            </li>
                            <li>
                                <div>Haemoglobin</div>
                                <div class="progress m-b-20">
                                    <div class="progress-bar progress-bar-warning progress-bar-striped" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%"> <span class="sr-only">60% Complete (warning)</span> </div>
                                </div>
                            </li>
                            <li>
                                <div>Sugar</div>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-danger progress-bar-striped" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 80%"> <span class="sr-only">80% Complete (danger)</span> </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div> 
            </div>
            <div class="col-lg-8 col-md-12 col-sm-12">
                <div class="card">
                    <div class="body">
                        <h5><strong>Case Study</strong></h5>
                        <p class="text-muted m-b-0">Patient case study module coming soon — this will be the core of {{ config('app.name') }}.</p>
                        @if($patient->notes)
                        <hr>
                        <strong>Notes</strong>
                        <p>{{ $patient->notes }}</p>
                        @endif
                    </div>
                </div>        
                <div class="card" id="timeline">
                    <div class="body">
                        <div class="timeline-body">
                            <div class="timeline m-border">
                                <div class="timeline-item">
                                    <div class="item-content">
                                        <div class="text-small">Just now</div>
                                        <p>Discharge.</p>
                                    </div>
                                </div>
                                <div class="timeline-item border-info">
                                    <div class="item-content">
                                        <div class="text-small">11:30</div>
                                        <p>Routine Checkup</p>
                                    </div>
                                </div>
                                <div class="timeline-item border-warning border-l">
                                    <div class="item-content">
                                        <div class="text-small">10:30</div>
                                        <p>Operation </p>
                                    </div>
                                </div>
                                <div class="timeline-item border-warning">
                                    <div class="item-content">
                                        <div class="text-small">3 days ago</div>
                                        <p>Routine Checkup</p>
                                    </div>
                                </div>
                                <div class="timeline-item border-danger">
                                    <div class="item-content">
                                        <div class="text--muted">Thu, 10 Mar</div>
                                        <p>Routine Checkup</p>
                                    </div>
                                </div>
                                <div class="timeline-item border-info">
                                    <div class="item-content">
                                        <div class="text-small">Sat, 5 Mar</div>
                                        <p>Routine Checkup</p>
                                    </div>
                                </div>
                                <div class="timeline-item border-danger">
                                    <div class="item-content">
                                        <div class="text-small">Sun, 11 Feb</div>
                                        <p>Blood checkup test</p>
                                    </div>
                                </div>
                                <div class="timeline-item border-info">
                                    <div class="item-content">
                                        <div class="text-small">Thu, 17 Jan</div>
                                        <p>Admit patient ward no. 21</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
