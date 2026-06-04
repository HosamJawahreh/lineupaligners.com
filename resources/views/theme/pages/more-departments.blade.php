@extends('layouts.app')

@section('title', 'More Departments')

@section('content')
<section class="content">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>Cardiology
                <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">                
                <button class="btn btn-white btn-icon btn-round d-none d-md-inline-block float-right m-l-10" type="button">
                    <i class="zmdi zmdi-plus"></i>
                </button>
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
                    <li class="breadcrumb-item active">More</li>
                </ul>                
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-4 col-md-12 profile-page">
                <div class="card profile-header">
                    <div class="body text-center">
                        <div class="profile-image"> <img src="{{ asset('assets/images/profile_av.jpg') }}" alt=""> </div>
                        <div>
                            <h4 class="m-b-0"><strong>Dr. Charlotte</strong> Deo</h4>
                            <span class="job_post">Neurologist</span>
                            <p>795 Folsom Ave, Suite 600<br> San Francisco, CADGE 94107</p>
                        </div>
                        <div>
                            <button class="btn btn-primary btn-round">Follow</button>
                            <button class="btn btn-primary btn-round btn-simple">Message</button>
                        </div>
                        <p class="social-icon m-t-5 m-b-0">
                            <a title="Twitter" href="javascript:void(0);"><i class="zmdi zmdi-twitter"></i></a>
                            <a title="Facebook" href="javascript:void(0);"><i class="zmdi zmdi-facebook"></i></a>
                            <a title="Google-plus" href="javascript:void(0);"><i class="zmdi zmdi-twitter"></i></a>
                            <a title="Behance" href="javascript:void(0);"><i class="zmdi zmdi-behance"></i></a>
                            <a title="Instagram" href="javascript:void(0);"><i class="zmdi zmdi-instagram "></i></a>
                        </p>
                    </div>                    
                </div>                               
                <div class="card">
                    <div class="body">
                        <div class="workingtime">
                            <h6>Working Time</h6>
                            <small class="text-muted">Tuesday</small>
                            <p>06:00 AM - 07:00 AM</p>
                            <hr>
                            <small class="text-muted">Thursday</small>
                            <p>06:00 AM - 07:00 AM</p>
                            <hr>
                        </div>
                        <div class="reviews">
                            <h6>Reviews</h6>
                            <small class="text-muted">Staff</small>
                            <p>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star-outline"></i>
                                <i class="zmdi zmdi-star-outline"></i>
                            </p>
                            <hr>
                            <small class="text-muted">Punctuality</small>
                            <p>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star-outline"></i>
                            </p>
                            <hr>
                            <small class="text-muted">Helpfulness</small>
                            <p>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star"></i>
                            </p>
                            <hr>
                            <small class="text-muted">Knowledge</small>
                            <p>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star-outline"></i>
                                <i class="zmdi zmdi-star-outline"></i>
                            </p>
                            <hr>
                            <small class="text-muted">Cost</small>
                            <p>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star"></i>
                                <i class="zmdi zmdi-star-outline"></i>
                                <i class="zmdi zmdi-star-outline"></i>
                                <i class="zmdi zmdi-star-outline"></i>
                            </p>
                            <hr>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="body">
                        <div id="demo2" class="carousel slide" data-ride="carousel">
                            <!-- Indicators -->
                            <ul class="carousel-indicators">
                                <li data-target="#demo2" data-slide-to="0" class=""></li>
                                <li data-target="#demo2" data-slide-to="1" class="active"></li>
                                <li data-target="#demo2" data-slide-to="2" class=""></li>
                            </ul>
            
                            <!-- Wrapper for slides -->
                            <div class="carousel-inner">
                                <div class="carousel-item">
                                    <img src="{{ asset('assets/images/image-gallery/5.jpg') }}" class="img-fluid" alt="">
                                    <div class="carousel-caption">
                                    <h3>Chicago</h3>
                                    <p>Thank you, Chicago!</p>
                                    </div>
                                </div>
                                <div class="carousel-item active">
                                    <img src="{{ asset('assets/images/image-gallery/6.jpg') }}" class="img-fluid" alt="">
                                    <div class="carousel-caption">
                                    <h3>New York</h3>
                                    <p>We love the Big Apple!</p>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <img src="{{ asset('assets/images/image-gallery/12.jpg') }}" class="img-fluid" alt="">
                                    <div class="carousel-caption">
                                    <h3>Los Angeles</h3>
                                    <p>We had such a great time in LA!</p>
                                    </div>
                                </div>
                            </div>
            
                            <!-- Controls -->
                            <!-- Left and right controls -->
                            <a class="carousel-control-prev" href="#demo2" data-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                            </a>
                            <a class="carousel-control-next" href="#demo2" data-slide="next">
                            <span class="carousel-control-next-icon"></span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="body">                        
                        <p>Explain to you how all this mistaken idea of denouncing pleasure and praising pain was born wewill give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness one rejects, dislikes, or avoids pleasure itself, because it is pleasure, but because those who do not know how to pursue pleasure sed rationally encounter consequences that are extremely painful.</p>
                        <blockquote>
                            <p class="blockquote blockquote-primary">
                                "I will be the leader of a company that ends up being worth billions of dollars, because I got the answers. I understand culture. I am the nucleus. I think that’s a responsibility that I have, to push possibilities, to show people, this is the level that things could be at."
                                <br>
                                <br>
                                <small>
                                    -- Dr. Charlotte
                                </small>
                            </p>
                        </blockquote>
                        <p>Denouncing pleasure and praising pain was born wewill give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness rationally.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="card">
                            <div class="header">
                                <h2><strong>Investigation</strong></h2>
                                <ul class="header-dropdown m-r--5">                                    
                                    <li class="remove">
                                        <a role="button" class="boxs-close"><i class="zmdi zmdi-close"></i></a>
                                    </li>
                                </ul>
                            </div>
                            <div class="body">
                                <div class="table-responsive">
                                    <table class="table table-hover m-b-0">
                                        <tbody>
                                            <tr>
                                                <td>Gastroscopy</td>
                                                <td>$120</td>
                                            </tr>
                                            <tr>
                                                <td>Colonoscopy</td>
                                                <td>$122</td>
                                            </tr>
                                            <tr>
                                                <td>Allergy Testing</td>
                                                <td>$234</td>
                                            </tr>
                                            <tr>
                                                <td>Bronshoscopy</td>
                                                <td>$245</td>
                                            </tr>
                                            <tr>
                                                <td>Paratyroid Scan</td>
                                                <td>$78</td>
                                            </tr>                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="card">
                            <div class="header">
                                <h2><strong>Treatments</strong></h2>
                                <ul class="header-dropdown m-r--5">                                    
                                    <li class="remove">
                                        <a role="button" class="boxs-close"><i class="zmdi zmdi-close"></i></a>
                                    </li>
                                </ul>
                            </div>
                            <div class="body">
                                <div class="table-responsive">
                                    <table class="table table-hover m-b-0">
                                        <tbody>
                                            <tr>
                                                <td>Colonoscopy</td>
                                                <td>$120</td>
                                            </tr>
                                            <tr>
                                                <td>Gastroscopy</td>
                                                <td>$122</td>
                                            </tr>
                                            <tr>
                                                <td>Paratyroid Scan</td>
                                                <td>$234</td>
                                            </tr>
                                            <tr>
                                                <td>Bronshoscopy</td>
                                                <td>$245</td>
                                            </tr>
                                            <tr>
                                                <td>Paratyroid Scan</td>
                                                <td>$78</td>
                                            </tr>                                            
                                        </tbody>
                                    </table>
                                </div>                                
                            </div>
                        </div>   
                    </div>
                </div>
            </div>            
        </div>
    </div>
</section>
@endsection
