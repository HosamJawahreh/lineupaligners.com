@extends('layouts.app')

@section('title', 'Search Result')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inbox.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/light-gallery/css/lightgallery.css') }}">
@endpush

@section('content')
<section class="content search_result_page">
   <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>Search Results
                <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                    <li class="breadcrumb-item active">Search Results</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="body">
                        <div class="input-group">
                            <input type="text" class="form-control" value="Bootstrap 4 admin" placeholder="Search...">
                            <span class="input-group-addon"><i class="zmdi zmdi-search"></i></span>
                        </div>
                        <ul class="nav nav-tabs p-l-0 p-r-0">
                            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#Web">Web</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#Images">Images</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#Videos">Videos</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#Oreo">Oreo</a></li>
                        </ul>                        
                        <p class="m-b-0">Search Result For "Bootstrap 4 admin"</p>
                        <strong> About 16,853 result ( 0.13 seconds)</strong>
                    </div>
                </div>
                <div class="tab-content">
                    <div class="tab-pane card active" id="Web">
                        <div class="body">
                            <h5 class="title"><a target="_blank" href="https://themeforest.net/item/falcon-bootstrap-admin-dashboard-template-ui-kit/20999984">Falcon - Bootstrap Admin Dashboard Template + UI Kit</a></h5>
                            <small>https://themeforest.net/item/falcon-bootstrap-admin-dashboard-template-ui-kit/20999984</small>
                            <p class="m-t-10">Falcon is premium admin dashboard powered with AngularJS. It’s builded on popular Twitter Bootstrap v3 framework. Falcon is fully based on HTML5 + CSS3 standards. Is fully responsive and clean on every device and every browser.</p>
                            <a class="m-r-20" target="_blank" href="https://themeforest.net/item/admincc-bootstrap-material-design-premium-admin-dashboard/19931304">AdminCC</a>
                            <a class="m-r-20" target="_blank" href="https://themeforest.net/item/swift-admin-ultimate-material-design-template-sass/19242451">Swift Admin</a>
                        </div>
                        <div class="body">
                            <h5 class="title"><a target="_blank" href="https://themeforest.net/item/swift-hospital-responsive-material-admin-for-doctors-hospitals/19453296">Swift Hospital - Bootstrap 4 Dashboard for Doctors & Hospitals </a></h5>
                            <small>https://themeforest.net/item/swift-hospital-responsive-material-admin-for-doctors-hospitals/19453296</small>
                            <p class="m-t-10">Swift Hospital Admin is Bootstrap 4 Material Design premium admin dashboard theme for Healthcare, Hospital and Medical industry.</p>
                            <a class="m-r-20" target="_blank" href="https://themeforest.net/item/oakleaf-admin-bootstrap-html5-with-sass/19154835">Oakleaf Admin</a>
                            <a class="m-r-20" target="_blank" href="https://themeforest.net/item/falcon-bootstrap-admin-dashboard-template-ui-kit/20999984">Falcon Admin</a>
                        </div>
                        <div class="body">
                            <h5 class="title"><a target="_blank" href="https://themeforest.net/item/swift-university-responsive-material-admin-template-for-school-colleges/19535863">Swift University - Bootstrap 4 Dashboard template for School & Colleges </a></h5>
                            <small>https://themeforest.net/item/swift-university-responsive-material-admin-template-for-school-colleges/19535863</small>
                            <p class="m-t-10">Swift University Admin is Bootstrap4 Material Design premium admin dashboard theme. The template is used for University, Collage and School.
                            It is really suitable template for Professors, Staff member, Student, Library Assets, Departments and Organization. It is purpose oriented design, responsive layout and wonderful features like Event Schedule, Professors profile, Student profile, Student invoice, Income Report, Sales Report, Payments and many more.</p>
                            <a class="m-r-20" target="_blank" href="https://themeforest.net/item/admincc-bootstrap-material-design-premium-admin-dashboard/19931304">AdminCC</a>
                            <a class="m-r-20" target="_blank" href="https://themeforest.net/item/oakleaf-admin-bootstrap-html5-with-sass/19154835">Oakleaf Admin</a>
                            <a class="m-r-20" target="_blank" href="https://themeforest.net/item/falcon-bootstrap-admin-dashboard-template-ui-kit/20999984">Falcon Admin</a>
                        </div>
                        <div class="body">
                            <h5 class="title"><a target="_blank" href="https://themeforest.net/item/adminx-the-ultimate-powerful-material-design-dashboard/20694893">adminX - Multipurpose Bootstrap4 Dashboard + Angular4 CLI Starter Kit</a></h5>
                            <small>https://themeforest.net/item/swift-hospital-responsive-material-admin-for-doctors-hospitals/19453296</small>
                            <p class="m-t-10">adminX Admin is Material Design premium admin dashboard theme. It’s builded on popular Twitter Bootstrap 4x framework. adminX is fully based on HTML5 + CSS3 Standards. Is fully responsive and clean on every device and every browser.</p>
                            <a class="m-r-20" target="_blank" href="https://themeforest.net/item/oakleaf-admin-bootstrap-html5-with-sass/19154835">Oakleaf Admin</a>
                            <a class="m-r-20" target="_blank" href="https://themeforest.net/item/falcon-bootstrap-admin-dashboard-template-ui-kit/20999984">Falcon Admin</a>
                            <a class="m-r-20" target="_blank" href="https://themeforest.net/item/admincc-bootstrap-material-design-premium-admin-dashboard/19931304">AdminCC</a>
                        </div>
                        <ul class="body pagination pagination-primary">
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">Next</a></li>
                        </ul>
                    </div>
                    <div class="tab-pane card" id="Images">
                        <div class="header">
                            <h2><strong>Images</strong> <small>All pictures taken from <a href="https://pexels.com/" target="_blank">pexels.com</a></small> </h2>
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
                            <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> <a href="{{ asset('assets/images/image-gallery/1.jpg') }}"> <img class="img-fluid img-thumbnail" src="{{ asset('assets/images/image-gallery/thumb/thumb-1.jpg') }}" alt=""> </a> </div>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> <a href="{{ asset('assets/images/image-gallery/2.jpg') }}" > <img class="img-fluid img-thumbnail" src="{{ asset('assets/images/image-gallery/thumb/thumb-2.jpg') }}" alt=""> </a> </div>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> <a href="{{ asset('assets/images/image-gallery/3.jpg') }}" > <img class="img-fluid img-thumbnail" src="{{ asset('assets/images/image-gallery/thumb/thumb-3.jpg') }}" alt=""> </a> </div>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> <a href="{{ asset('assets/images/image-gallery/4.jpg') }}" > <img class="img-fluid img-thumbnail" src="{{ asset('assets/images/image-gallery/thumb/thumb-4.jpg') }}" alt=""> </a> </div>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> <a href="{{ asset('assets/images/image-gallery/5.jpg') }}" > <img class="img-fluid img-thumbnail" src="{{ asset('assets/images/image-gallery/thumb/thumb-5.jpg') }}" alt=""> </a> </div>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> <a href="{{ asset('assets/images/image-gallery/6.jpg') }}" > <img class="img-fluid img-thumbnail" src="{{ asset('assets/images/image-gallery/thumb/thumb-6.jpg') }}" alt=""> </a> </div>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> <a href="{{ asset('assets/images/image-gallery/7.jpg') }}" > <img class="img-fluid img-thumbnail" src="{{ asset('assets/images/image-gallery/thumb/thumb-7.jpg') }}" alt=""> </a> </div>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> <a href="{{ asset('assets/images/image-gallery/8.jpg') }}" > <img class="img-fluid img-thumbnail" src="{{ asset('assets/images/image-gallery/thumb/thumb-8.jpg') }}" alt=""> </a> </div>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> <a href="{{ asset('assets/images/image-gallery/9.jpg') }}" > <img class="img-fluid img-thumbnail" src="{{ asset('assets/images/image-gallery/thumb/thumb-9.jpg') }}" alt=""> </a> </div>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> <a href="{{ asset('assets/images/image-gallery/10.jpg') }}" > <img class="img-fluid img-thumbnail" src="{{ asset('assets/images/image-gallery/thumb/thumb-10.jpg') }}" alt=""> </a> </div>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> <a href="{{ asset('assets/images/image-gallery/11.jpg') }}" > <img class="img-fluid img-thumbnail" src="{{ asset('assets/images/image-gallery/thumb/thumb-11.jpg') }}" alt=""> </a> </div>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> <a href="{{ asset('assets/images/image-gallery/12.jpg') }}" > <img class="img-fluid img-thumbnail" src="{{ asset('assets/images/image-gallery/thumb/thumb-12.jpg') }}" alt=""> </a> </div>                                
                            </div>
                        </div>
                        <ul class="body pagination pagination-primary">
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>                            
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">Next</a></li>
                        </ul>
                    </div>
                    <div class="tab-pane card" id="Videos">
                        <div class="body text-center">
                            <div class="not_found">
                                <i class="zmdi zmdi-mood-bad zmdi-hc-4x"></i>
                                <h4 class="m-b-0">Sorry No result found.</h4>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="Oreo">
                        <div class="card">
                            <div class="body">
                                <h6 class="m-t-0"><a href="javascript:void(0);">Timeline</a></h6>
                                <p>Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?</p>
                                <a href="javascript:void(0);">http://Oreo.com/lorem/timeline.html</a>
                                <div class="row">
                                    <div class="col-sm-4 m-t-10"> <a href="javascript:void(0);"><img src="{{ asset('assets/images/image2.jpg') }}" alt="" class="img-fluid img-thumbnail"></a> </div>
                                    <div class="col-sm-4 m-t-10"> <a href="javascript:void(0);"> <img src="{{ asset('assets/images/image3.jpg') }}" alt="" class="img-fluid img-thumbnail"></a> </div>
                                    <div class="col-sm-4 m-t-10"> <a href="javascript:void(0);"> <img src="{{ asset('assets/images/image4.jpg') }}" alt="" class="img-fluid img-thumbnail"> </a> </div>
                                </div>
                            </div>
                        </div>
                        <div class="card inbox">
                            <div class="cover">
                                <h6 class="m-t-20 m-l-20"><a href="javascript:void(0);">Inbox</a></h6>
                                <ul class="mail_list list-group list-unstyled">
                                    <li class="list-group-item">
                                        <div class="media">
                                            <div class="pull-left">
                                                <div class="controls">
                                                    <div class="checkbox">
                                                        <input type="checkbox" id="basic_checkbox_1">
                                                        <label for="basic_checkbox_1"></label>
                                                    </div>
                                                    <a href="javascript:void(0);" class="favourite text-muted d-none d-md-inline-block" data-toggle="active"><i class="zmdi zmdi-star-outline"></i></a>
                                                </div>
                                                <div class="thumb d-none d-md-inline-block m-r-20"> <img src="{{ asset('assets/images/xs/avatar1.jpg') }}" class="rounded-circle" alt=""> </div>
                                            </div>
                                            <div class="media-body">
                                                <div class="media-heading">
                                                    <a href="{{ route('pages.mail-single') }}" class="m-r-10">Velit a labore</a>
                                                    <span class="badge badge-info">Family</span>
                                                    <small class="float-right text-muted"><time class="d-none d-md-inline-block" datetime="2017">12:35 AM</time><i class="zmdi zmdi-attachment-alt"></i> </small>
                                                </div>
                                                <p class="msg">Lorem Ipsum is simply dumm dummy text of the printing and typesetting industry. </p>
                                            </div>
                                        </div>
                                    </li>                                
                                    <li class="list-group-item">
                                        <div class="media">
                                            <div class="pull-left">
                                                <div class="controls">
                                                    <div class="checkbox">
                                                        <input type="checkbox" id="basic_checkbox_3">
                                                        <label for="basic_checkbox_3"></label>
                                                    </div>
                                                    <a href="javascript:void(0);" class="favourite text-muted d-none d-md-inline-block" data-toggle="active"><i class="zmdi zmdi-star-outline"></i></a>
                                                </div>
                                                <div class="thumb d-none d-md-inline-block m-r-20"> <img src="{{ asset('assets/images/xs/avatar3.jpg') }}" class="rounded-circle" alt=""> </div>
                                            </div>
                                            <div class="media-body">
                                                <div class="media-heading">
                                                    <a href="{{ route('pages.mail-single') }}" class="m-r-10">Velit a labore</a>
                                                    <span class="badge badge-primary">Google</span>
                                                    <small class="float-right text-muted"><time class="d-none d-md-inline-block" datetime="2017">12:35 AM</time><i class="zmdi zmdi-attachment-alt"></i> </small>
                                                </div>
                                                <p class="msg"> If you are going to use a passage of Lorem Ipsum, you need to be sure</p>                                
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item unread">
                                        <div class="media">
                                            <div class="pull-left">
                                                <div class="controls">
                                                    <div class="checkbox">
                                                        <input type="checkbox" id="basic_checkbox_4">
                                                        <label for="basic_checkbox_4"></label>
                                                    </div>
                                                    <a href="javascript:void(0);" class="favourite text-muted d-none d-md-inline-block" data-toggle="active"><i class="zmdi zmdi-star-outline"></i></a>
                                                </div>
                                                <div class="thumb d-none d-md-inline-block m-r-20"> <img src="{{ asset('assets/images/xs/avatar4.jpg') }}" class="rounded-circle" alt=""> </div>
                                            </div>
                                            <div class="media-body">
                                                <div class="media-heading">
                                                    <a href="{{ route('pages.mail-single') }}" class="m-r-10">Variations of passages</a>
                                                    <span class="badge badge-success">Themeforest</span>
                                                    <small class="float-right text-muted"><time class="d-none d-md-inline-block" datetime="2017">12:35 AM</time><i class="zmdi zmdi-attachment-alt"></i> </small>
                                                </div>
                                                <p class="msg">There are many variations of passages of Lorem Ipsum available, but the majority </p>                                
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card">
                            <div class="body">
                                <h6 class="m-t-0"><a href="javascript:void(0);">Chat</a></h6>
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                <ul class="list-unstyled team-info">
                                    <li class="m-r-15"><small>Dr. Team</small></li>
                                    <li>
                                        <img src="{{ asset('assets/images/xs/avatar10.jpg') }}" alt="Avatar">
                                    </li>
                                    <li>
                                        <img src="{{ asset('assets/images/xs/avatar9.jpg') }}" alt="Avatar">
                                    </li>
                                    <li>
                                        <img src="{{ asset('assets/images/xs/avatar8.jpg') }}" alt="Avatar">
                                    </li>
                                    <li>
                                        <img src="{{ asset('assets/images/xs/avatar7.jpg') }}" alt="Avatar">
                                    </li>
                                    <li>
                                        <img src="{{ asset('assets/images/xs/avatar6.jpg') }}" alt="Avatar">
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <ul class="pagination pagination-primary">
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">Next</a></li>
                        </ul>
                    </div>
                </div>                
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/light-gallery/js/lightgallery-all.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/medias/image-gallery.js') }}"></script>
@endpush
