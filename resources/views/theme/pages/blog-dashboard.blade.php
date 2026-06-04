@extends('layouts.app')

@section('title', 'Blog Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/blog.css') }}">
@endpush

@section('content')
<section class="content blog-page">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-5 col-md-5 col-sm-12">
                <h2>Blog Dashboard
                    <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-7 col-md-7 col-sm-12 text-right">
                <div class="inlineblock text-center m-r-15 m-l-15 d-none d-lg-inline-block">
                    <div class="sparkline" data-type="bar" data-width="97%" data-height="25px" data-bar-Width="2" data-bar-Spacing="5" data-bar-Color="#fff">3,2,6,5,9,8,7,9,5,1,3,5,7,4,6</div>
                    <small class="col-white">Visitors</small>
                </div>
                <div class="inlineblock text-center m-r-15 m-l-15 d-none d-lg-inline-block">
                    <div class="sparkline" data-type="bar" data-width="97%" data-height="25px" data-bar-Width="2" data-bar-Spacing="5" data-bar-Color="#fff">1,3,5,7,4,6,3,2,6,5,9,8,7,9,5</div>
                    <small class="col-white">Bounce Rate</small>
                </div>
                <button class="btn btn-white btn-icon btn-round d-none d-md-inline-block float-right m-l-10" type="button">
                    <i class="zmdi zmdi-plus"></i>
                </button>
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item">Blog</li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="body text-center">
                        <div class="sparkline m-b-20" data-type="bar" data-width="97%" data-height="40px" data-bar-Width="3" data-bar-Spacing="5" data-bar-Color="#00ced1">1,2,2,3,3,4,4,5,5,6,6,5,5,4,4,3,3,2,2,1</div>
                        <h3 class="m-b-0">457 512</h3>
                        <small class="displayblock">47% Average <i class="zmdi zmdi-trending-up"></i></small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="body text-center">
                        <div class="sparkline m-b-20" data-type="bar" data-width="97%" data-height="40px" data-bar-Width="3" data-bar-Spacing="5" data-bar-Color="#e4d354">1,2,2,3,3,4,4,5,5,6,6,5,5,4,4,3,3,2,2,1</div>
                        <h3 class="m-b-0">236 512</h3>
                        <small class="displayblock">13% Average <i class="zmdi zmdi-trending-down"></i></small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="body text-center">
                        <div class="sparkline m-b-20" data-type="bar" data-width="97%" data-height="40px" data-bar-Width="3" data-bar-Spacing="5" data-bar-Color="#7cb5ec">1,2,3,4,5,4,3,2,1,2,3,4,5,6,7,8,7,6,5,4</div>
                        <h3 class="m-b-0">236 512</h3>
                        <small class="displayblock">47% Average <i class="zmdi zmdi-trending-up"></i></small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="body text-center">
                        <div class="sparkline m-b-20" data-type="bar" data-width="97%" data-height="40px" data-bar-Width="3" data-bar-Spacing="5" data-bar-Color="#f15c80">8,7,6,5,4,3,2,2,3,4,5,6,7,8,7,6,5,4</div>
                        <h3 class="m-b-0">236 512</h3>
                        <small class="displayblock">47% Average <i class="zmdi zmdi-trending-up"></i></small>
                    </div>
                </div>
            </div>
        </div>             
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12">                
                <div class="card">
                    <div class="header">
                        <h2><strong>Popular</strong> Categories</h2>
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
                        <div id="line_chart" class="graph"></div>
                        <div class="card xl-slategray">
                            <ul class="row profile_state list-unstyled">
                                <li class="col-lg-3 col-md-3 col-6">
                                    <div class="body">
                                        <i class="zmdi zmdi-eye col-amber"></i>
                                        <h4 class="number count-to" data-from="0" data-to="2365" data-speed="1000" data-fresh-interval="700">2365</h4>
                                        <span>Post View</span>
                                    </div>
                                </li>
                                <li class="col-lg-3 col-md-3 col-6">
                                    <div class="body">
                                        <i class="zmdi zmdi-thumb-up col-blue"></i>
                                        <h4 class="number count-to" data-from="0" data-to="365" data-speed="1000" data-fresh-interval="700">365</h4>
                                        <span>Likes</span>
                                    </div>
                                </li>
                                <li class="col-lg-3 col-md-3 col-6">
                                    <div class="body">
                                        <i class="zmdi zmdi-comment-text col-red"></i>
                                        <h4 class="number count-to" data-from="0" data-to="65" data-speed="1000" data-fresh-interval="700">65</h4>
                                        <span>Comments</span>
                                    </div>
                                </li>
                                <li class="col-lg-3 col-md-3 col-6">
                                    <div class="body">
                                        <i class="zmdi zmdi-account text-success"></i>
                                        <h4 class="number count-to" data-from="0" data-to="2055" data-speed="1000" data-fresh-interval="700">2055</h4>
                                        <span>Profile Views</span>
                                    </div>
                                </li>                      
                            </ul>
                        </div>                        
                    </div>
                </div>
            </div>
        </div>       
        <div class="row clearfix">
            <div class="col-lg-7 col-md-12">
                <div class="card visitors-map">
                    <div class="header">
                        <h2><strong>Visitors</strong> Statistics <small>Description text here...</small></h2>
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
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-md-12">
                                <div id="world-map-markers" class="jvector-map m-b-15"></div>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12">
                                <div class="table-responsive">                                    
                                    <table class="table table-hover m-b-0">
                                        <thead>
                                            <tr>
                                                <th>Contrary</th>
                                                <th>2016</th>
                                                <th>2017</th>
                                                <th>Change</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>USA</td>
                                                <td>2,009</td>
                                                <td>3,591</td>
                                                <td>7.01% <i class="zmdi zmdi-trending-up text-success"></i></td>
                                            </tr>
                                            <tr>
                                                <td>India</td>
                                                <td>1,129</td>
                                                <td>1,361</td>
                                                <td>3.01% <i class="zmdi zmdi-trending-up text-success"></i></td>
                                            </tr>
                                            <tr>
                                                <td>Canada</td>
                                                <td>2,009</td>
                                                <td>2,901</td>
                                                <td>9.01% <i class="zmdi zmdi-trending-up text-success"></i></td>
                                            </tr>
                                            <tr>
                                                <td>Australia</td>
                                                <td>954</td>
                                                <td>901</td>
                                                <td>5.71% <i class="zmdi zmdi-trending-down text-warning"></i></td>
                                            </tr>
                                            <tr>
                                                <td>Other</td>
                                                <td>4,236</td>
                                                <td>4,591</td>
                                                <td>9.15% <i class="zmdi zmdi-trending-up text-success"></i></td>
                                            </tr>                                            											
                                        </tbody>
                                    </table>                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>Browser</strong> Usage</h2>
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
                        <div id="donut_chart" class="dashboard-donut-chart"></div>
                        <table class="table m-b-0">
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Chrome</td>
                                    <td>6985</td>
                                    <td><i class="zmdi zmdi-caret-up text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Other</td>
                                    <td>2697</td>
                                    <td><i class="zmdi zmdi-caret-up text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Safari</td>
                                    <td>3597</td>
                                    <td><i class="zmdi zmdi-caret-down text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>Firefox</td>
                                    <td>2145</td>
                                    <td><i class="zmdi zmdi-caret-up text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>Opera</td>
                                    <td>1854</td>
                                    <td><i class="zmdi zmdi-caret-down text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>IE</td>
                                    <td>54</td>
                                    <td><i class="zmdi zmdi-caret-down text-danger"></i></td>
                                </tr>                               
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <div class="body">
                        <h4 class="m-t-0 m-b-0">2,048</h4>
                        <p class="m-b-0">Total Leads</p>
                    </div>
                    <div class="sparkline" data-type="line" data-spot-Radius="1" data-highlight-Spot-Color="rgb(233, 30, 99)" data-highlight-Line-Color="#222"
                    data-min-Spot-Color="rgb(233, 30, 99)" data-max-Spot-Color="rgb(0, 150, 136)" data-spot-Color="rgb(0, 188, 212)"
                    data-offset="90" data-width="100%" data-height="40px" data-line-Width="2" data-line-Color="rgba(255, 255, 255, 0.2)"
                    data-fill-Color="rgba(0, 0, 0, 0.3)"> 7,6,7,8,5,9,8,6,7 </div>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-sm-12">
                    <div class="card">
                    <div class="header">
                        <h2><strong>Social</strong> Media</h2>
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
                        <div class="table-responsive social_media_table">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Media</th>
                                        <th>Name</th>
                                        <th>Like</th>                                                                                
                                        <th>Comments</th>
                                        <th>Share</th>
                                        <th>Members</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="social_icon linkedin"><i class="zmdi zmdi-linkedin"></i></span>
                                        </td>
                                        <td><span class="list-name">Linked In</span>
                                            <span class="text-muted">Florida, United States</span>
                                        </td>
                                        <td>19K</td>
                                        <td>14K</td>
                                        <td>10K</td>
                                        <td>
                                            <span class="badge badge-success">2341</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="social_icon twitter-table"><i class="zmdi zmdi-twitter"></i></span>
                                        </td>
                                        <td><span class="list-name">Twitter</span>
                                            <span class="text-muted">Arkansas, United States</span>
                                        </td>
                                        <td>7K</td>
                                        <td>11K</td>
                                        <td>21K</td>
                                        <td>
                                            <span class="badge badge-success">952</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="social_icon facebook"><i class="zmdi zmdi-facebook"></i></span>
                                        </td>
                                        <td><span class="list-name">Facebook</span>
                                            <span class="text-muted">Illunois, United States</span>
                                        </td>
                                        <td>15K</td>
                                        <td>18K</td>
                                        <td>8K</td>
                                        <td>
                                            <span class="badge badge-success">6127</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="social_icon google"><i class="zmdi zmdi-google-plus"></i></span>
                                        </td>
                                        <td><span class="list-name">Google Plus</span>
                                            <span class="text-muted">Arizona, United States</span>
                                        </td>
                                        <td>15K</td>
                                        <td>18K</td>
                                        <td>154</td>
                                        <td>
                                            <span class="badge badge-success">325</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="social_icon youtube"><i class="zmdi zmdi-youtube-play"></i></span>
                                        </td>
                                        <td><span class="list-name">YouTube</span>
                                            <span class="text-muted">Alaska, United States</span>
                                        </td>
                                        <td>15K</td>
                                        <td>18K</td>
                                        <td>200</td>
                                        <td>
                                            <span class="badge badge-success">160</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>            
        </div>
        <div class="row clearfix">
            <div class="col-lg-7 col-md-12">
                <div class="card ">
                   <div class="header">
                       <h2><strong>Latest</strong> Comments</h2>
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
                       <ul class="inbox-widget list-unstyled clearfix">
                            <li class="inbox-inner"><a href="#">
                               <div class="inbox-item">
                                   <div class="inbox-img"> <img src="{{ asset('assets/images/sm/avatar1.jpg') }}" class="rounded" alt=""> </div>
                                   <div class="inbox-item-info">
                                       <p class="author">John Doe</p>
                                       <p class="inbox-message">Lorem Ipsum is simply dummy text of the been the industry's</p>
                                       <p class="inbox-date">12:34 PM</p>
                                   </div>
                                   <div class="hover_action">
                                        <a href="#"><i class="zmdi zmdi-favorite"></i></a>
                                        <a href="#"><i class="zmdi zmdi-edit"></i></a>
                                        <a href="#"><i class="zmdi zmdi-check-circle"></i></a>
                                   </div>
                               </div>
                               </a>
                            </li>
                            <li class="inbox-inner"> <a href="#">
                                <div class="inbox-item">
                                   <div class="inbox-img"> <img src="{{ asset('assets/images/sm/avatar2.jpg') }}" class="rounded" alt=""> </div>
                                   <div class="inbox-item-info">
                                       <p class="author">Maryam Amiri</p>
                                       <p class="inbox-message">Lorem Ipsum is simply dummyLorem Ipsum has been the industry's</p>
                                       <p class="inbox-date">10:34 AM</p>
                                   </div>
                                   <div class="hover_action">
                                        <a href="#"><i class="zmdi zmdi-favorite"></i></a>
                                        <a href="#"><i class="zmdi zmdi-edit"></i></a>
                                        <a href="#"><i class="zmdi zmdi-check-circle"></i></a>
                                   </div>
                                </div>
                                </a>
                            </li>
                            <li class="inbox-inner"> <a href="#">
                                <div class="inbox-item">
                                    <div class="inbox-img"> <img src="{{ asset('assets/images/sm/avatar3.jpg') }}" class="rounded" alt=""> </div>
                                    <div class="inbox-item-info">
                                        <p class="author">Fidel Tonn</p>
                                        <p class="inbox-message">text of the printing and  has been the industry's</p>
                                        <p class="inbox-date">15:34 PM</p>
                                    </div>
                                    <div class="hover_action">
                                        <a href="#"><i class="zmdi zmdi-favorite"></i></a>
                                        <a href="#"><i class="zmdi zmdi-edit"></i></a>
                                        <a href="#"><i class="zmdi zmdi-check-circle"></i></a>
                                   </div>
                                </div>
                                </a> </li>
                            <li class="inbox-inner"> <a href="#">
                                <div class="inbox-item">
                                    <div class="inbox-img"> <img src="{{ asset('assets/images/sm/avatar4.jpg') }}" class="rounded" alt=""> </div>
                                    <div class="inbox-item-info">
                                        <p class="author">Gary Camara</p>
                                        <p class="inbox-message">simply dummy text of the printing and typesetting industry's</p>
                                        <p class="inbox-date">11:10 AM</p>
                                    </div>
                                    <div class="hover_action">
                                        <a href="#"><i class="zmdi zmdi-favorite"></i></a>
                                        <a href="#"><i class="zmdi zmdi-edit"></i></a>
                                        <a href="#"><i class="zmdi zmdi-check-circle"></i></a>
                                   </div>
                                </div>
                                </a> </li>
                            <li class="inbox-inner"> <a href="#">
                                <div class="inbox-item">
                                    <div class="inbox-img"><img src="{{ asset('assets/images/sm/avatar5.jpg') }}" class="rounded" alt=""> </div>
                                    <div class="inbox-item-info">
                                        <p class="author">Tim Hank</p>
                                        <p class="inbox-message">text of the industry. Lorem Ipsum has been the industry's</p>
                                        <p class="inbox-date">18:45 PM</p>
                                    </div>
                                    <div class="hover_action">
                                        <a href="#"><i class="zmdi zmdi-favorite"></i></a>
                                        <a href="#"><i class="zmdi zmdi-edit"></i></a>
                                        <a href="#"><i class="zmdi zmdi-check-circle"></i></a>
                                   </div>
                                </div>
                                </a>
                            </li>
                            <li class="inbox-inner"> <a href="#">
                                <div class="inbox-item">
                                    <div class="inbox-img"> <img src="{{ asset('assets/images/sm/avatar6.jpg') }}" class="rounded" alt=""> </div>
                                    <div class="inbox-item-info">
                                        <p class="author">Hossein Shams </p>
                                        <p class="inbox-message">text of the printing and  has been the industry's</p>
                                        <p class="inbox-date">15:34 PM</p>
                                    </div>
                                    <div class="hover_action">
                                        <a href="#"><i class="zmdi zmdi-favorite"></i></a>
                                        <a href="#"><i class="zmdi zmdi-edit"></i></a>
                                        <a href="#"><i class="zmdi zmdi-check-circle"></i></a>
                                   </div>
                                </div>
                                </a>
                            </li>
                       </ul>
                   </div>
               </div>
            </div>
            <div class="col-lg-5 col-md-12">
                <div class="card single_post">
                    <div class="header">
                        <h2><strong>Latest</strong> Post</h2>
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
                        <h3 class="m-t-0 m-b-5"><a href="{{ route('pages.blog-details') }}">Apple Introduces Search Ads Basic</a></h3>
                        <ul class="meta">
                            <li><a href="#"><i class="zmdi zmdi-account col-blue"></i>Posted By: John Smith</a></li>
                            <li><a href="#"><i class="zmdi zmdi-label col-amber"></i>Technology</a></li>
                            <li><a href="#"><i class="zmdi zmdi-comment-text col-blue"></i>Comments: 3</a></li>
                        </ul>
                    </div>                    
                    <div class="body">
                        <div class="img-post m-b-15">
                            <img src="{{ asset('assets/images/blog/blog-page-4.jpg') }}" alt="Awesome Image">
                            <div class="social_share">                            
                                <button class="btn btn-primary btn-icon btn-icon-mini btn-round"><i class="zmdi zmdi-facebook"></i></button>
                                <button class="btn btn-primary btn-icon btn-icon-mini btn-round"><i class="zmdi zmdi-twitter"></i></button>
                                <button class="btn btn-primary btn-icon btn-icon-mini btn-round"><i class="zmdi zmdi-instagram"></i></button>
                            </div>
                        </div>
                        <p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal</p>
                        <a href="{{ route('pages.blog-details') }}" title="read more" class="btn btn-round btn-info">Read More</a>                        
                    </div>
                </div>                
            </div> 
        </div>
    </div>   
</section>
@endsection

@push('scripts-before-main')
<script src="{{ asset('assets/bundles/jvectormap.bundle.js') }}"></script>
<script src="{{ asset('assets/plugins/jvectormap/jquery-jvectormap-us-aea-en.js') }}"></script>
<script src="{{ asset('assets/bundles/morrisscripts.bundle.js') }}"></script>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/pages/blog/blog.js') }}"></script>
@endpush
