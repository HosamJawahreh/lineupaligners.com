@extends('layouts.app')

@section('title', 'File Dashboard')

@section('content')
<section class="content file_manager">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>File Manager
                <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">
                <button class="btn btn-white btn-icon btn-round d-none d-md-inline-block float-right m-l-10" type="button">
                    <i class="zmdi zmdi-upload"></i>
                </button>
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-md-12 col-lg-12 col-xs-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>File</strong> Reports</h2>
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
                        <div id="m_bar_chart" class="xl-slategray"></div>
                        <div class="row clearfix">
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="body">                        
                                    <p>Storage</p>
                                    <h4 class="m-t-0">32GB</h4>                                
                                    <div class="progress m-t-10">
                                        <div class="progress-bar l-green" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%;"></div>
                                    </div>
                                    <small class="info">of 1Tb</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="body">
                                    <p>Documents</p>
                                    <h4 class="m-t-0">2GB</h4>
                                    <div class="progress m-t-10">
                                        <div class="progress-bar l-blush" role="progressbar" aria-valuenow="12" aria-valuemin="0" aria-valuemax="100" style="width: 12%;"></div>
                                    </div>
                                    <small class="info">of 1Tb</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="body">
                                    <p>Media</p>
                                    <h4 class="m-t-0">10GB</h4>
                                    <div class="progress m-t-10">
                                        <div class="progress-bar l-parpl" role="progressbar" aria-valuenow="39" aria-valuemin="0" aria-valuemax="100" style="width: 39%;"></div>
                                    </div>
                                    <small class="info">of 1Tb</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="body">
                                    <p>Images</p>
                                    <h4 class="m-t-0">20GB</h4>
                                    <div class="progress m-t-10">
                                        <div class="progress-bar l-blue" role="progressbar" aria-valuenow="89" aria-valuemin="0" aria-valuemax="100" style="width: 89%;"></div>
                                    </div>
                                    <small class="info">of 1Tb</small>
                                </div>
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>
        </div>        
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
                                <i class="zmdi zmdi-file-text"></i>
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">Document_2017.doc</p>
                                <small>Size: 42KB <span class="date text-muted">Nov 02, 2017</span></small>
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
                            <div class="image">
                                <img src="{{ asset('assets/images/image-gallery/1.jpg') }}" alt="img" class="img-fluid">
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">img21545ds.jpg</p>
                                <small>Size: 2MB <span class="date text-muted">Dec 11, 2017</span></small>
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
                                <i class="zmdi zmdi-playlist-audio"></i>
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">newsong.mp3</p>
                                <small>Size: 8MB <span class="date text-muted">Dec 11, 2017</span></small>
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
                                <i class="zmdi zmdi-collection-pdf"></i>
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">asdf  hhkj.pdf</p>
                                <small>Size: 3MB <span class="date text-muted">Aug 18, 2017</span></small>
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
                                <i class="zmdi zmdi-chart"></i>
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">Report2016.xls</p>
                                <small>Size: 68KB <span class="date text-muted">Dec 12, 2016</span></small>
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
                                <i class="zmdi zmdi-file-text"></i>
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">Document_2017.doc</p>
                                <small>Size: 89KB <span class="date text-muted">Dec 15, 2017</span></small>
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
                            <div class="image">
                                <img src="{{ asset('assets/images/image-gallery/3.jpg') }}" alt="img" class="img-fluid">
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">img21545ds.jpg</p>
                                <small>Size: 2MB <span class="date text-muted">Nov 11, 2017</span></small>
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
                                <small>Size: 720MB <span class="date text-muted">Feb 16, 2016</span></small>
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
                                <i class="zmdi zmdi-chart"></i>
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">Report2017.xls</p>
                                <small>Size: 103KB <span class="date text-muted">Jan 24, 2016</span></small>
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
                            <div class="image">
                                <img src="{{ asset('assets/images/image-gallery/2.jpg') }}" alt="img" class="img-fluid">
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">img21545ds.jpg</p>
                                <small>Size: 2MB <span class="date text-muted">Dec 11, 2017</span></small>
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
                                <i class="zmdi zmdi-playlist-audio"></i>
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">newsong.mp3</p>
                                <small>Size: 8MB <span class="date text-muted">Dec 11, 2017</span></small>
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
                            <div class="image">
                                <img src="{{ asset('assets/images/image-gallery/8.jpg') }}" alt="img" class="img-fluid">
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">img21545ds.jpg</p>
                                <small>Size: 2MB <span class="date text-muted">Dec 11, 2017</span></small>
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
                                <i class="zmdi zmdi-collection-pdf"></i>
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">asdf  hhkj.pdf</p>
                                <small>Size: 3MB <span class="date text-muted">Aug 18, 2017</span></small>
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
                                <i class="zmdi zmdi-chart"></i>
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">Report2016.xls</p>
                                <small>Size: 68KB <span class="date text-muted">Dec 12, 2016</span></small>
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
                                <i class="zmdi zmdi-file-text"></i>
                            </div>
                            <div class="file-name">
                                <p class="m-b-5 text-muted">Document_2017.doc</p>
                                <small>Size: 89KB <span class="date text-muted">Dec 15, 2017</span></small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts-before-main')
<script src="{{ asset('assets/bundles/morrisscripts.bundle.js') }}"></script>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/pages/file/filemanager.js') }}"></script>
@endpush
