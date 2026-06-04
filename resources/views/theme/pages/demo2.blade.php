@extends('layouts.app')

@section('title', '500')

@section('content')
<section class="content">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>Book Appointment
                <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Appointment</a></li>
                    <li class="breadcrumb-item active">Book Appointment</li>
                </ul>
            </div>
        </div>
    </div>    
    <div class="container-fluid">        
        <div class="row clearfix">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="card">
					<div class="header">
						<h2><strong>Book</strong> Appointment<small>Description text here...</small> </h2>
						<ul class="header-dropdown">                            
                            <li class="remove">
                                <a role="button" class="boxs-close"><i class="zmdi zmdi-close"></i></a>
                            </li>
                        </ul>
					</div>
					<div class="body">
                        <div class="row clearfix">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="First Name">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Last Name">
                                </div>
                            </div>
                        </div>
                        <div class="row clearfix">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <input type="text" id="dob" class="form-control" placeholder="Date of Birth">
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <select class="form-control show-tick">
                                    <option value="">- Gender -</option>
                                    <option value="10">Male</option>
                                    <option value="20">Female</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <select class="form-control show-tick" data-live-search="true">
                                    <option value="">- Service -</option>
                                    <option>Select Service</option>
                                    <option>Dental Checkup</option>
                                    <option>Full Body Checkup</option>
                                    <option>ENT Checkup</option>
                                    <option>Heart Checkup</option>
                                </select>                                
                            </div>
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="zmdi zmdi-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control datetimepicker" placeholder="Please choose date & time...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
					<div class="header">
						<h2><strong>Book</strong> Appointment<small>Description text here...</small> </h2>
						<ul class="header-dropdown">                            
                            <li class="remove">
                                <a role="button" class="boxs-close"><i class="zmdi zmdi-close"></i></a>
                            </li>
                        </ul>
					</div>
					<div class="body">
                        <div class="row clearfix">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="First Name">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Last Name">
                                </div>
                            </div>
                        </div>
                    </div>
				</div>
            </div>
            <div class="col-sm-12">
                <div class="card">
                    <div class="body">
                        <p> <b>With Search Bar</b> </p>
                        <select class="form-control z-index show-tick" data-live-search="true">
                            <option>Hot Dog, Fries and a Soda</option>
                            <option>Burger, Shake and a Smile</option>
                            <option>Sugar, Spice and all things nice</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-round">Submit</button>
                        <button type="submit" class="btn btn-default btn-round btn-simple">Cancel</button>
                    </div>
                </div>
            </div>
		</div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/momentjs/moment.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}"></script>
@endpush
