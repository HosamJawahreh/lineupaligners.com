@extends('layouts.app')

@section('title', 'Page')

@section('content')
<section class="content">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>Modals Popups
                <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">UI</a></li>
                    <li class="breadcrumb-item active">Modals</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <!-- Modal Size Example -->
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>Modal</strong> Size Example <small>Modals are streamlined, but flexible, dialog prompts with the minimum required functionality and smart defaults.</small> </h2>
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
                        <button type="button" class="btn btn-default waves-effect m-r-20" data-toggle="modal" data-target="#defaultModal">MODAL - DEFAULT SIZE</button>
                        <button type="button" class="btn btn-default waves-effect m-r-20" data-toggle="modal" data-target="#largeModal">MODAL - LARGE SIZE</button>
                        <button type="button" class="btn btn-default waves-effect" data-toggle="modal" data-target="#smallModal">MODAL - SMALL SIZE</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- #END# Modal Size Example --> 
        <!-- Material Design Colors -->
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>Material</strong> Design Colors <small>You can use material design colors which examples are <code>.modal-col-pink</code> class</small> </h2>
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
                        <div class="button-demo js-modal-buttons">
                            <button type="button" data-toggle="modal" data-target="#colorModal" data-color="red" class="btn bg-red waves-effect">RED</button>
                            <button type="button" data-color="pink" class="btn bg-pink waves-effect">PINK</button>
                            <button type="button" data-color="purple" class="btn bg-purple waves-effect">PURPLE</button>
                            <button type="button" data-color="deep-purple" class="btn bg-deep-purple waves-effect">DEEP PURPLE</button>
                            <button type="button" data-color="indigo" class="btn bg-indigo waves-effect">INDIGO</button>
                            <button type="button" data-color="blue" class="btn bg-blue waves-effect">BLUE</button>
                            <button type="button" data-color="light-blue" class="btn bg-light-blue waves-effect">LIGHT BLUE</button>
                            <button type="button" data-color="cyan" class="btn bg-cyan waves-effect">CYAN</button>
                            <button type="button" data-color="teal" class="btn bg-teal waves-effect">TEAL</button>
                            <button type="button" data-color="green" class="btn bg-green waves-effect">GREEN</button>
                            <button type="button" data-color="light-green" class="btn bg-light-green waves-effect">LIGHT GREEN</button>
                            <button type="button" data-color="lime" class="btn bg-lime waves-effect">LIME</button>
                            <button type="button" data-color="yellow" class="btn bg-yellow waves-effect">YELLOW</button>
                            <button type="button" data-color="amber" class="btn bg-amber waves-effect">AMBER</button>
                            <button type="button" data-color="orange" class="btn bg-orange waves-effect">ORANGE</button>
                            <button type="button" data-color="deep-orange" class="btn bg-deep-orange waves-effect">DEEP ORANGE</button>
                            <button type="button" data-color="brown" class="btn bg-brown waves-effect">BROWN</button>
                            <button type="button" data-color="grey" class="btn bg-grey waves-effect">GREY</button>
                            <button type="button" data-color="blue-grey" class="btn bg-blue-grey waves-effect">BLUE GREY</button>
                            <button type="button" data-color="black" class="btn bg-black waves-effect waves-light">BLACK</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- #END# Material Design Colors -->         
    </div>
</section>
@endsection
