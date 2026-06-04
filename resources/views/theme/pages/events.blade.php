@extends('layouts.app')

@section('title', 'Events')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/fullcalendar/fullcalendar.min.css') }}">
@endpush

@section('content')
<section class="content page-calendar">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>Calendar
                <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">App</a></li>
                    <li class="breadcrumb-item active">Calendar</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container-fluid">        
        <div class="row">
            <div class="col-md-12 col-lg-4 col-xl-4">
                <div class="card">
                    <div class="body">
                        <button type="button" class="btn btn-sm btn-round btn-success waves-effect" data-toggle="modal" data-target="#addevent">Add Events</button>
                        <button class="btn btn-simple btn-sm btn-primary btn-round d-xl-none m-t-0 float-right" data-toggle="collapse" data-target="#open-events" aria-expanded="false" aria-controls="collapseExample"><i class="zmdi zmdi-chevron-down"></i></button>                        
                    </div>
                </div>
                <div class="collapse-xs collapse-sm collapse" id="open-events">
                    <div class="card">
                        <div class="header">
                            <h2><strong>Repeating</strong> Event</h2>
                            <ul class="header-dropdown">
                                <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more"></i> </a>
                                    <ul class="dropdown-menu dropdown-menu-right slideUp">
                                        <li><a href="javascript:void(0);">Action</a></li>
                                        <li><a href="javascript:void(0);">Another action</a></li>
                                        <li><a href="javascript:void(0);">Something else</a></li>
                                    </ul>
                                </li>
                                <li class="remove">
                                    <a role="button" class="boxs-close"><i class="zmdi zmdi-close"></i></a>
                                </li>
                            </ul>                        
                        </div>
                        <div class="body">
                            <div class="event-name b-lightred row">
                                <div class="col-2 text-center">
                                    <h4>09<span>Dec</span><span>2017</span></h4>
                                </div>
                                <div class="col-10">
                                    <h6>Repeating Event</h6>
                                    <p>It is a long established fact that a reader will be distracted</p>
                                    <address><i class="zmdi zmdi-pin"></i> 123 6th St. Melbourne, FL 32904</address>
                                </div>
                            </div>
                            <div class="event-name b-greensea row">
                                <div class="col-2 text-center">
                                    <h4>16<span>Dec</span><span>2017</span></h4>
                                </div>
                                <div class="col-10">
                                    <h6>Repeating Event</h6>
                                    <p>It is a long established fact that a reader will be distracted</p>
                                    <address><i class="zmdi zmdi-pin"></i> 123 6th St. Melbourne, FL 32904</address>
                                </div>
                            </div>
                            <div class="event-name b-primary row">
                                <div class="col-2 text-center">
                                    <h4>11<span>Dec</span><span>2017</span></h4>
                                </div>
                                <div class="col-10">
                                    <h6>Conference</h6>
                                    <p>It is a long established fact that a reader will be distracted</p>
                                    <address><i class="zmdi zmdi-pin"></i> 123 6th St. Melbourne, FL 32904</address>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="header">
                            <h2><strong>Activity</strong></h2>
                            <ul class="header-dropdown">
                                <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more"></i> </a>
                                    <ul class="dropdown-menu dropdown-menu-right slideUp">
                                        <li><a href="javascript:void(0);">Action</a></li>
                                        <li><a href="javascript:void(0);">Another action</a></li>
                                        <li><a href="javascript:void(0);">Something else</a></li>
                                    </ul>
                                </li>
                                <li class="remove">
                                    <a role="button" class="boxs-close"><i class="zmdi zmdi-close"></i></a>
                                </li>
                            </ul>                        
                        </div>
                        <div class="body">                                                    
                            <div class="event-name b-primary row">
                                <div class="col-2 text-center">
                                    <h4>13<span>Dec</span><span>2017</span></h4>
                                </div>
                                <div class="col-10">
                                    <h6>Birthday</h6>
                                    <p>It is a long established fact that a reader will be distracted</p>
                                    <address><i class="zmdi zmdi-pin"></i> 123 6th St. Melbourne, FL 32904</address>
                                </div>
                            </div>
                        </div>
                    </div>                
                </div>
            </div>
            <div class="col-md-12 col-lg-8 col-xl-8">
                <div class="card">
                    <div class="body">
                        <button class="btn btn-primary btn-sm btn-round waves-effect" id="change-view-today">today</button>
                        <button class="btn btn-default btn-sm btn-simple btn-round waves-effect" id="change-view-day" >Day</button>
                        <button class="btn btn-default btn-sm btn-simple btn-round waves-effect" id="change-view-week">Week</button>
                        <button class="btn btn-default btn-sm btn-simple btn-round waves-effect" id="change-view-month">Month</button>                        
                    </div>
                </div>
                <div class="card">
                    <div class="body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</section>
@endsection

@push('scripts-before-main')
<script src="{{ asset('assets/bundles/fullcalendarscripts.bundle.js') }}"></script>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/pages/calendar/calendar.js') }}"></script>
@endpush
