@extends('layouts.app')

@section('title', 'Alerts')

@section('content')
<section class="content">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>Alerts
                <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">                
                <button class="btn btn-white btn-icon btn-round d-none d-md-inline-block float-right m-l-10" type="button">
                    <i class="zmdi zmdi-plus"></i>
                </button>
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">UI</a></li>
                    <li class="breadcrumb-item active"> Alerts</li>
                </ul>                
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>Basic Bootstrap</strong> Alerts
                            <small>Wrap any text and an optional dismiss button in <code>.alert</code> and one of the four contextual classes (e.g., <code>.alert-success</code>) for basic alert messages.</small>
                        </h2>
                    </div>
                    <div class="body">
                        <div class="alert alert-success">
                            <strong>Well done!</strong> You successfully read this important alert message.
                        </div>
                        <div class="alert alert-info">
                            <strong>Heads up!</strong> This alert needs your attention, but it's not super important.
                        </div>
                        <div class="alert alert-warning">
                            <strong>Warning!</strong> Better check yourself, you're not looking too good.
                        </div>
                        <div class="alert alert-danger">
                            <strong>Oh snap!</strong> Change a few things up and try submitting again.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>Links</strong> in Alerts
                            <small>Use the <code>.alert-link</code> utility class to quickly provide matching colored links within any alert.</small>
                        </h2>
                    </div>
                    <div class="body">
                        <div class="alert alert-success">
                            <strong>Well done!</strong> You successfully read <a href="javascript:void(0);" class="alert-link">this important alert message</a>.
                        </div>
                        <div class="alert alert-info">
                            <strong>Heads up!</strong> This <a href="javascript:void(0);" class="alert-link">alert needs your attention</a>, but it's not super important.
                        </div>
                        <div class="alert alert-warning">
                            <strong>Warning!</strong> Better check yourself, you're <a href="javascript:void(0);" class="alert-link">not looking too good</a>.
                        </div>
                        <div class="alert alert-danger">
                            <strong>Oh snap!</strong> <a href="javascript:void(0);" class="alert-link">Change a few things up</a> and try submitting again.
                        </div>                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
