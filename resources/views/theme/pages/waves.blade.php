@extends('layouts.app')

@section('title', 'Waves')

@section('content')
<section class="content">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>Waves
                <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">UI</a></li>
                    <li class="breadcrumb-item active">Waves</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>Color</strong> Variations</h2>
                        <ul class="header-dropdown">
                            <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more"></i> </a>
                                <ul class="dropdown-menu dropdown-menu-right">
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
                        <ul class="row list-unstyled text-right">
                            <li class="col-lg-3 col-md-6"> Default <a href="javascript:void(0);" class="btn btn-default waves-effect ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-light</code> <a href="javascript:void(0);" class="btn bg-blue waves-effect waves-light ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-red</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-red ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-pink</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-pink ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-purple</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-purple ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-deep-purple</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-deep-purple ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-indigo</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-indigo ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-blue</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-blue ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-light-blue</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-light-blue ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-cyan</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-cyan ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-teal</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-teal ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-green</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-green ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-light-green</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-light-green ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-lime</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-lime ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-yellow</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-yellow ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-amber</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-amber ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-orange</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-orange ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-deep-orange</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-deep-orange ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-brown</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-brown ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-grey</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-grey ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-blue-grey</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-blue-grey ">CLICK ME</a> </li>
                            <li class="col-lg-3 col-md-6"> <code>waves-black</code> <a href="javascript:void(0);" class="btn btn-default waves-effect waves-black ">CLICK ME</a> </li>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
