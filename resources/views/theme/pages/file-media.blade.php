@extends('layouts.app')

@section('title', 'Page')

@section('content')
<section class="content file_manager">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>Media
                <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">
                <button class="btn btn-white btn-icon btn-round d-none d-md-inline-block float-right m-l-10" type="button">
                    <i class="zmdi zmdi-upload"></i>
                </button>
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pages.file-dashboard') }}">File Manager</a></li>
                    <li class="breadcrumb-item active">Media</li>                    
                </ul>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#2017">2017</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#2016">2016</a></li>                        
                    </ul>
                </div>
                <div class="tab-content">
                    <div class="tab-pane active" id="2017">
                        <div class="row clearfix">
                            <div class="col-lg-3 col-md-4 col-sm-12">
                                <div class="card">
                                    <div class="file">
                                        <a href="javascript:void(0);">
                                            <div class="hover">
                                                <button type="button" class="btn btn-icon btn-icon-mini btn-round btn-danger">
                                                    <i class="zmdi zmdi-delete"></i>
                                                </button>
                                            </div>
                                            <div class="icon">
                                                <i class="zmdi zmdi-collection-folder-image"></i>
                                            </div>
                                            <div class="file-name">
                                                <p class="m-b-5 text-muted">hellonew.mkv</p>
                                                <small>Size: 720MB <span class="date text-muted">Dec 08, 2017</span></small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-12">
                                <div class="card">
                                    <div class="file">
                                        <a href="javascript:void(0);">
                                            <div class="hover">
                                                <button type="button" class="btn btn-icon btn-icon-mini btn-round btn-danger">
                                                    <i class="zmdi zmdi-delete"></i>
                                                </button>
                                            </div>
                                            <div class="icon">
                                                <i class="zmdi zmdi-collection-video"></i>
                                            </div>
                                            <div class="file-name">
                                                <p class="m-b-5 text-muted">Jee Le Zara Song.mpg4</p>
                                                <small>Size: 32MB <span class="date text-muted">Oct 11, 2016</span></small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-12">
                                <div class="card">
                                    <div class="file">
                                        <a href="javascript:void(0);">
                                            <div class="hover">
                                                <button type="button" class="btn btn-icon btn-icon-mini btn-round btn-danger">
                                                    <i class="zmdi zmdi-delete"></i>
                                                </button>
                                            </div>
                                            <div class="icon">
                                                <i class="zmdi zmdi-collection-folder-image"></i>
                                            </div>
                                            <div class="file-name">
                                                <p class="m-b-5 text-muted">hellonew.mkv</p>
                                                <small>Size: 720MB <span class="date text-muted">Dec 08, 2017</span></small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-12">
                                <div class="card">
                                    <div class="file">
                                        <a href="javascript:void(0);">
                                            <div class="hover">
                                                <button type="button" class="btn btn-icon btn-icon-mini btn-round btn-danger">
                                                    <i class="zmdi zmdi-delete"></i>
                                                </button>
                                            </div>
                                            <div class="icon">
                                                <i class="zmdi zmdi-collection-video"></i>
                                            </div>
                                            <div class="file-name">
                                                <p class="m-b-5 text-muted">Jee Le Zara Song.mpg4</p>
                                                <small>Size: 32MB <span class="date text-muted">Oct 11, 2016</span></small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-12">
                                <div class="card">
                                    <div class="file">
                                        <a href="javascript:void(0);">
                                            <div class="hover">
                                                <button type="button" class="btn btn-icon btn-icon-mini btn-round btn-danger">
                                                    <i class="zmdi zmdi-delete"></i>
                                                </button>
                                            </div>
                                            <div class="icon">
                                                <i class="zmdi zmdi-collection-folder-image"></i>
                                            </div>
                                            <div class="file-name">
                                                <p class="m-b-5 text-muted">hellonew.mkv</p>
                                                <small>Size: 720MB <span class="date text-muted">Dec 08, 2017</span></small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-12">
                                <div class="card">
                                    <div class="file">
                                        <a href="javascript:void(0);">
                                            <div class="hover">
                                                <button type="button" class="btn btn-icon btn-icon-mini btn-round btn-danger">
                                                    <i class="zmdi zmdi-delete"></i>
                                                </button>
                                            </div>
                                            <div class="icon">
                                                <i class="zmdi zmdi-collection-video"></i>
                                            </div>
                                            <div class="file-name">
                                                <p class="m-b-5 text-muted">Jee Le Zara Song.mpg4</p>
                                                <small>Size: 32MB <span class="date text-muted">Oct 11, 2016</span></small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-12">
                                <div class="card">
                                    <div class="file">
                                        <a href="javascript:void(0);">
                                            <div class="hover">
                                                <button type="button" class="btn btn-icon btn-icon-mini btn-round btn-danger">
                                                    <i class="zmdi zmdi-delete"></i>
                                                </button>
                                            </div>
                                            <div class="icon">
                                                <i class="zmdi zmdi-collection-folder-image"></i>
                                            </div>
                                            <div class="file-name">
                                                <p class="m-b-5 text-muted">hellonew.mkv</p>
                                                <small>Size: 720MB <span class="date text-muted">Dec 08, 2017</span></small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-12">
                                <div class="card">
                                    <div class="file">
                                        <a href="javascript:void(0);">
                                            <div class="hover">
                                                <button type="button" class="btn btn-icon btn-icon-mini btn-round btn-danger">
                                                    <i class="zmdi zmdi-delete"></i>
                                                </button>
                                            </div>
                                            <div class="icon">
                                                <i class="zmdi zmdi-collection-video"></i>
                                            </div>
                                            <div class="file-name">
                                                <p class="m-b-5 text-muted">Jee Le Zara Song.mpg4</p>
                                                <small>Size: 32MB <span class="date text-muted">Oct 11, 2016</span></small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="2016">
                        <div class="row clearfix">
                            
                        </div>
                    </div>                   
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
