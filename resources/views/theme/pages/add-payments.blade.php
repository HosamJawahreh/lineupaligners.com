@extends('layouts.app')

@section('title', 'Add Payments')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-select/css/bootstrap-select.css') }}">
@endpush

@section('content')
<section class="content">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>Add Payment
                <small class="text-muted">Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">
                <button class="btn btn-primary btn-icon btn-round d-none d-md-inline-block float-right m-l-10" type="button">
                    <i class="zmdi zmdi-plus"></i>
                </button>
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Payment</a></li>
                    <li class="breadcrumb-item active">Add Payment</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12 ">
                <div class="card">
                    <div class="header">
                        <h2><strong>Patients</strong> Information <small>Description text here...</small> </h2>
                        <ul class="header-dropdown">
                            <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more"></i> </a>
                                <ul class="dropdown-menu dropdown-menu-right slideUp float-right">
                                    <li><a href="javascript:void(0);">Edit</a></li>
                                    <li><a href="javascript:void(0);">Delete</a></li>
                                    <li><a href="javascript:void(0);">Report</a></li>
                                </ul>
                            </li>
                            <li class="remove">
                                <a role="button" class="boxs-close"><i class="zmdi zmdi-close"></i></a>
                            </li>
                        </ul>
                    </div>
                    <div class="body">
                        <div class="row clearfix">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <input type="number" class="form-control" placeholder="Payment Number">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Patient Name">
                                </div>
                            </div>
                            <div class="col-sm-12 ">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Doctor Name">
                                </div>
                            </div>                            
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="header">
                        <h2><strong>Payment</strong> Information <small>Description text here...</small> </h2>
                        <ul class="header-dropdown">
                            <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more"></i> </a>
                                <ul class="dropdown-menu dropdown-menu-right slideUp float-right">
                                    <li><a href="javascript:void(0);">Edit</a></li>
                                    <li><a href="javascript:void(0);">Delete</a></li>
                                    <li><a href="javascript:void(0);">Report</a></li>
                                </ul>
                            </li>
                            <li class="remove">
                                <a role="button" class="boxs-close"><i class="zmdi zmdi-close"></i></a>
                            </li>
                        </ul>
                    </div>
                    <div class="body">
                        <div class="row clearfix">                                                   
                            <div class="col-sm-12 ">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Payment Date">
                                </div>
                            </div>
                            <div class="col-sm-12 ">
                                <div class="form-group">
                                    <input type="number" class="form-control" placeholder="Total Amount">
                                </div>
                            </div>
                            <div class="col-sm-12 ">
                                <div class="form-group">
                                    <input type="number" class="form-control" placeholder="Discount">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                <select class="form-control show-tick" data-live-search="true">
                                    <option value="">-- Payment Method --</option>
                                    <option>Cash</option>
                                    <option>Cheque</option>
                                    <option>Credit Card</option>
                                    <option>Debit Card</option>
                                    <option>Netbanking</option>
                                    <option>Insurance</option>
                                </select>
                                </div>
                            </div>                            
                            <div class="col-sm-12">
                                <div class="form-group">
                                <select class="form-control show-tick">
                                    <option value="">-- Payment Method --</option>
                                    <option>Cash</option>
                                    <option>Cheque</option>
                                    <option>Credit Card</option>
                                    <option>Debit Card</option>
                                    <option>Netbanking</option>
                                    <option>Insurance</option>
                                </select>
                                </div>
                            </div>                            
                            <div class="col-sm-6">
                                <div class="form-group">
                                <select class="form-control show-tick">
                                    <option value="">-- Payment Method --</option>
                                    <option>Cash</option>
                                    <option>Cheque</option>
                                    <option>Credit Card</option>
                                    <option>Debit Card</option>
                                    <option>Netbanking</option>
                                    <option>Insurance</option>
                                </select>
                                </div>
                            </div>                            
                            <div class="col-sm-6">
                                <div class="form-group">
                                <select class="form-control show-tick">
                                    <option value="">-- Payment Status --</option>
                                    <option>Select Status</option>
                                    <option>Complete</option>
                                    <option>Pending</option>
                                    <option>Partial</option>
                                </select>
                                </div>
                            </div>
                            <div class="col-sm-12 m-t-30">
                                <button type="submit" class="btn btn-primary btn-round">Submit</button>
                                <button type="submit" class="btn btn-default btn-round btn-simple">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
