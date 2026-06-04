@extends('layouts.app')

@section('title', 'All Departments')

@section('content')
<section class="content">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>All Departments
                <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">                
                <button class="btn btn-white btn-icon btn-round d-none d-md-inline-block float-right m-l-10" type="button">
                    <i class="zmdi zmdi-plus"></i>
                </button>
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Departments</a></li>
                    <li class="breadcrumb-item active">Alle</li>
                </ul>                
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card project_widget">
                    <div class="pw_img">
                        <img class="img-fluid" src="{{ asset('assets/images/image1.jpg') }}" alt="About the image">
                    </div>
                    <div class="pw_content">
                        <div class="pw_header">
                            <h6>Cardiology</h6>
                            <ul class="list-unstyled team-info margin-0 p-t-15">
                                <li class="m-r-15"><small>Team</small></li>
                                <li><img src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar3.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar4.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar6.jpg') }}" alt="Avatar"></li>
                                <li><a href="javascript:void(0);" title="Add Member"><i class="zmdi zmdi-plus-circle"></i></a></li>
                            </ul>
                        </div>
                        <div class="pw_meta">
                            <p>How all this mistaken al idea of denouncing pleasure praisings pain was complete[...]</p>
                            <a href="{{ route('pages.more-departments') }}" class="btn btn-primary btn-simple btn-round">More</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card project_widget">
                    <div class="pw_img">
                        <img class="img-fluid" src="{{ asset('assets/images/image2.jpg') }}" alt="About the image">
                    </div>
                    <div class="pw_content">
                        <div class="pw_header">
                            <h6>Pulmonology</h6>
                            <ul class="list-unstyled team-info margin-0 p-t-15">
                                <li class="m-r-15"><small>Team</small></li>
                                <li><img src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar3.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar4.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar6.jpg') }}" alt="Avatar"></li>
                                <li><a href="javascript:void(0);" title="Add Member"><i class="zmdi zmdi-plus-circle"></i></a></li>
                            </ul>
                        </div>
                        <div class="pw_meta">
                            <p>Who chooses to enjoy a pleasure that has consquences, or one who avoids a pain.[...]</p>
                            <button class="btn btn-primary btn-simple btn-round">More</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card project_widget">
                    <div class="pw_img">
                        <img class="img-fluid" src="{{ asset('assets/images/image3.jpg') }}" alt="About the image">
                    </div>
                    <div class="pw_content">
                        <div class="pw_header">
                            <h6>Gynecology</h6>
                            <ul class="list-unstyled team-info margin-0 p-t-15">
                                <li class="m-r-15"><small>Team</small></li>
                                <li><img src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar3.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar4.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar6.jpg') }}" alt="Avatar"></li>
                                <li><a href="javascript:void(0);" title="Add Member"><i class="zmdi zmdi-plus-circle"></i></a></li>
                            </ul>
                        </div>
                        <div class="pw_meta">
                            <p>Who chooses to enjoy a pleasure that has annoying, or one who avoids a pain.[...]</p>
                            <button class="btn btn-primary btn-simple btn-round">More</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card project_widget">
                    <div class="pw_img">
                        <img class="img-fluid" src="{{ asset('assets/images/image4.jpg') }}" alt="About the image">
                    </div>
                    <div class="pw_content">
                        <div class="pw_header">
                            <h6>Neurology</h6>
                            <ul class="list-unstyled team-info margin-0 p-t-15">
                                <li class="m-r-15"><small>Team</small></li>
                                <li><img src="{{ asset('assets/images/xs/avatar9.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar10.jpg') }}" alt="Avatar"></li>
                                <li><a href="javascript:void(0);" title="Add Member"><i class="zmdi zmdi-plus-circle"></i></a></li>
                            </ul>
                        </div>
                        <div class="pw_meta">
                            <p>How all this mistaken al idea of denouncing pleasure praisings pain was complete[...]</p>
                            <button class="btn btn-primary btn-simple btn-round">More</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card project_widget">
                    <div class="pw_img">
                        <img class="img-fluid" src="{{ asset('assets/images/image5.jpg') }}" alt="About the image">
                    </div>
                    <div class="pw_content">
                        <div class="pw_header">
                            <h6>Urology</h6>
                            <ul class="list-unstyled team-info margin-0 p-t-15">
                                <li class="m-r-15"><small>Team</small></li>
                                <li><img src="{{ asset('assets/images/xs/avatar5.jpg') }}" alt="Avatar"></li>                                
                                <li><img src="{{ asset('assets/images/xs/avatar7.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar8.jpg') }}" alt="Avatar"></li>
                                <li><a href="javascript:void(0);" title="Add Member"><i class="zmdi zmdi-plus-circle"></i></a></li>
                            </ul>
                        </div>
                        <div class="pw_meta">
                            <p>Who chooses to enjoy a pleasure that has consquences, or one who avoids a pain.[...]</p>
                            <button class="btn btn-primary btn-simple btn-round">More</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card project_widget">
                    <div class="pw_img">
                        <img class="img-fluid" src="{{ asset('assets/images/image6.jpg') }}" alt="About the image">
                    </div>
                    <div class="pw_content">
                        <div class="pw_header">
                            <h6>Gastrology</h6>
                            <ul class="list-unstyled team-info margin-0 p-t-15">
                                <li class="m-r-15"><small>Team</small></li>
                                <li><img src="{{ asset('assets/images/xs/avatar1.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar8.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar6.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar10.jpg') }}" alt="Avatar"></li>
                                <li><a href="javascript:void(0);" title="Add Member"><i class="zmdi zmdi-plus-circle"></i></a></li>
                            </ul>
                        </div>
                        <div class="pw_meta">
                            <p>Who chooses to enjoy a pleasure that has annoying, or one who avoids a pain.[...]</p>
                            <button class="btn btn-primary btn-simple btn-round">More</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card project_widget">
                    <div class="pw_img">
                        <img class="img-fluid" src="{{ asset('assets/images/image7.jpg') }}" alt="About the image">
                    </div>
                    <div class="pw_content">
                        <div class="pw_header">
                            <h6>Pediatrician</h6>
                            <ul class="list-unstyled team-info margin-0 p-t-15">
                                <li class="m-r-15"><small>Team</small></li>
                                <li><img src="{{ asset('assets/images/xs/avatar9.jpg') }}" alt="Avatar"></li>                                
                                <li><a href="javascript:void(0);" title="Add Member"><i class="zmdi zmdi-plus-circle"></i></a></li>
                            </ul>
                        </div>
                        <div class="pw_meta">
                            <p>There anyone loves pursue or desires to pain sed of itself because pain occasionally[...]</p>
                            <button class="btn btn-primary btn-simple btn-round">More</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card project_widget">
                    <div class="pw_img">
                        <img class="img-fluid" src="{{ asset('assets/images/image1.jpg') }}" alt="About the image">
                    </div>
                    <div class="pw_content">
                        <div class="pw_header">
                            <h6>Laboratory</h6>
                            <ul class="list-unstyled team-info margin-0 p-t-15">
                                <li class="m-r-15"><small>Team</small></li>
                                <li><img src="{{ asset('assets/images/xs/avatar5.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar8.jpg') }}" alt="Avatar"></li>
                                <li><a href="javascript:void(0);" title="Add Member"><i class="zmdi zmdi-plus-circle"></i></a></li>
                            </ul>
                        </div>
                        <div class="pw_meta">
                            <p>Who chooses to enjoy a pleasure that has consquences, or one who avoids a pain.[...]</p>
                            <button class="btn btn-primary btn-simple btn-round">More</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card project_widget">
                    <div class="pw_img">
                        <img class="img-fluid" src="{{ asset('assets/images/image8.jpg') }}" alt="About the image">
                    </div>
                    <div class="pw_content">
                        <div class="pw_header">
                            <h6>Gastrology</h6>
                            <ul class="list-unstyled team-info margin-0 p-t-15">
                                <li class="m-r-15"><small>Team</small></li>
                                <li><img src="{{ asset('assets/images/xs/avatar1.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="Avatar"></li>
                                <li><img src="{{ asset('assets/images/xs/avatar8.jpg') }}" alt="Avatar"></li>                                
                                <li><img src="{{ asset('assets/images/xs/avatar10.jpg') }}" alt="Avatar"></li>
                                <li><a href="javascript:void(0);" title="Add Member"><i class="zmdi zmdi-plus-circle"></i></a></li>
                            </ul>
                        </div>
                        <div class="pw_meta">
                            <p>Take a trivial example, which of ever undertake laborous physically exercise some[...]</p>
                            <button class="btn btn-primary btn-simple btn-round">More</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
