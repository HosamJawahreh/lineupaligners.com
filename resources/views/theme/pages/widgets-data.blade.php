@extends('layouts.app')

@section('title', 'Page')

@section('content')
<section class="content home">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>Data Widgets
                <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">                
                <button class="btn btn-white btn-icon btn-round d-none d-md-inline-block float-right m-l-10" type="button">
                    <i class="zmdi zmdi-plus"></i>
                </button>
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item active">Widgets</li>
                    <li class="breadcrumb-item active">Data</li>
                </ul>                
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-sm-12">
                <div class="card">
                    <div class="body">
                        <div class="row clearfix">
                            <div class="col-lg-4 col-md-4 col-sm-12 text-center">
                                <div class="body">
                                    <h2 class="number count-to m-t-0 m-b-5" data-from="0" data-to="501" data-speed="1000" data-fresh-interval="700">501</h2>
                                    <p class="text-muted">Heart Transplant</p>
                                    <span id="linecustom1">1,4,2,6,5,2,3,8,5,2</span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 text-center">
                                <div class="body">
                                    <h2 class="number count-to m-t-0 m-b-5" data-from="0" data-to="2501" data-speed="2000" data-fresh-interval="700">2501</h2>
                                    <p class="text-muted ">New Clients</p>
                                    <span id="linecustom2">2,9,5,5,8,5,4,2,6</span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 text-center">
                                <div class="body">
                                    <h2 class="number count-to m-t-0 m-b-5" data-from="0" data-to="6051" data-speed="2000" data-fresh-interval="700">6051</h2>
                                    <p class="text-muted">Total Profit</p>
                                    <span id="linecustom3">1,5,3,6,6,3,6,8,4,2</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="body">
                        <div class="row">
                            <div class="col-7">
                                <h5 class="m-t-0">Traffic</h5>
                                <small class="text-small">4% higher than last month</small>
                            </div>
                            <div class="col-5 text-right">
                                <h2 class="m-b-0">20</h2>
                                <small class="info">of 1Tb</small>
                            </div>
                            <div class="col-12">
                                <div class="progress m-t-20" value="20" type="success">
                                <div class="progress-bar l-green" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="body">
                        <div class="row">
                            <div class="col-7">
                                <h5 class="m-t-0">Server</h5>
                                <small class="text-small">6% higher than last month</small>
                            </div>
                            <div class="col-5 text-right">
                                <h2 class="m-b-0">12%</h2>
                                <small class="info">of 1Tb</small>
                            </div>
                            <div class="col-12">
                                <div class="progress m-t-20" value="12">
                                <div class="progress-bar l-blush" role="progressbar" aria-valuenow="12" aria-valuemin="0" aria-valuemax="100" style="width: 12%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="body">
                        <div class="row">
                            <div class="col-7">
                                <h5 class="m-t-0">Email</h5>
                                <small class="text-small">Total Registered email</small>
                            </div>
                            <div class="col-5 text-right">
                                <h2 class="m-b-0">39</h2>
                                <small class="info">of 100</small>
                            </div>
                            <div class="col-12">
                                <div class="progress m-t-20" value="39">
                                <div class="progress-bar l-parpl" role="progressbar" aria-valuenow="39" aria-valuemin="0" aria-valuemax="100" style="width: 39%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="body">
                        <div class="row">
                            <div class="col-7">
                                <h5 class="m-t-0">Domians</h5>
                                <small class="text-small">Total registered Domain</small>
                            </div>
                            <div class="col-5 text-right">
                                <h2 class="m-b-0">8</h2>
                                <small class="info">of 10</small>
                            </div>
                            <div class="col-12">
                                <div class="progress m-t-20" value="89" type="success">
                                <div class="progress-bar l-blue" role="progressbar" aria-valuenow="89" aria-valuemin="0" aria-valuemax="100" style="width: 89%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-lg-4 col-md-12">
                <div class="card tasks_report">
                    <div class="header">
                        <h2><strong>Total</strong> Revenue</h2>                        
                        <ul class="header-dropdown">
                            <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more"></i> </a>
                                <ul class="dropdown-menu dropdown-menu-right slideUp">
                                    <li><a href="javascript:void(0);">2017 Year</a></li>
                                    <li><a href="javascript:void(0);">2016 Year</a></li>
                                    <li><a href="javascript:void(0);">2015 Year</a></li>
                                </ul>
                            </li>
                            <li class="remove">
                                <a role="button" class="boxs-close"><i class="zmdi zmdi-close"></i></a>
                            </li>
                        </ul>
                    </div>
                    <div class="body text-center">
                        <h4 class="m-t-0">Total Sale</h4>
                        <h6 class="m-b-20">2,45,124</h6>
                        <input type="text" class="knob dial1" value="66" data-width="140" data-height="140" data-thickness="0.1" data-fgColor="#000" readonly>
                        <h6 class="m-t-30">Satisfaction Rate</h6>
                        <small class="displayblock">47% Average <i class="zmdi zmdi-trending-up"></i></small>
                        <div class="sparkline m-t-20" data-type="bar" data-width="97%" data-height="45px" data-bar-Width="2" data-bar-Spacing="8" data-bar-Color="#000">3,2,6,5,9,8,7,8,4,5,1,2,9,5,1,3,5,7,4,6</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card">
                    <div class="header">
                        <h2><strong>Total</strong> Income</h2>                        
                    </div>
                    <div class="body">
                        <h3 class="m-b-0">47,012</h3>
                        <small class="displayblock">23% Average <i class="zmdi zmdi-trending-up"></i></small>
                        <div class="sparkline" data-type="line" data-spot-Radius="1" data-highlight-Spot-Color="rgb(63, 81, 181)" data-highlight-Line-Color="#222"
                        data-min-Spot-Color="rgb(233, 30, 99)" data-max-Spot-Color="rgb(63, 81, 181)" data-spot-Color="rgb(63, 81, 181, 0.7)"
                        data-offset="90" data-width="100%" data-height="60px" data-line-Width="1" data-line-Color="rgb(63, 81, 181, 0.7)"
                        data-fill-Color="rgba(221,94,137, 0.2)"> 1,2,3,1,4,3,6,4,4,1 </div>                        
                    </div>
                </div>
                <div class="card">
                    <div class="header">
                        <h2><strong>Total</strong> Orders</h2>                        
                    </div>
                    <div class="body">
                        <h3 class="m-b-0">512</h3>
                        <small class="displayblock">18% Average <i class="zmdi zmdi-trending-down"></i></small>
                        <div class="sparkline" data-type="line" data-spot-Radius="1" data-highlight-Spot-Color="rgb(63, 81, 181)" data-highlight-Line-Color="#222"
                        data-min-Spot-Color="rgb(233, 30, 99)" data-max-Spot-Color="rgb(120, 184, 62)" data-spot-Color="rgb(63, 81, 181, 0.7)"
                        data-offset="90" data-width="100%" data-height="60px" data-line-Width="1" data-line-Color="rgb(63, 81, 181, 0.7)"
                        data-fill-Color="rgba(128,133,233, 0.2)"> 4,5,2,8,4,8,7,4,8,5</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card">
                    <div class="header">
                        <h2><strong>Total</strong> Visitor</h2>                        
                    </div>
                    <div class="body">
                        <h3 class="m-b-0">1,612</h3>
                        <small class="displayblock">13% Average <i class="zmdi zmdi-trending-up"></i></small>
                        <div class="sparkline" data-type="line" data-spot-Radius="1" data-highlight-Spot-Color="rgb(63, 81, 181)" data-highlight-Line-Color="#222"
                        data-min-Spot-Color="rgb(233, 30, 99)" data-max-Spot-Color="rgb(120, 184, 62)" data-spot-Color="rgb(63, 81, 181, 0.7)"
                        data-offset="90" data-width="100%" data-height="60px" data-line-Width="1" data-line-Color="rgb(63, 81, 181, 0.7)"
                        data-fill-Color="rgba(44,168,255, 0.2)">1,5,9,3,5,7,8,5,2,3,5,7</div>
                    </div>
                </div>
                <div class="card">
                    <div class="header">
                        <h2><strong>Total</strong> Active Users</h2>                        
                    </div>
                    <div class="body">
                        <h3 class="m-b-0">721</h3>
                        <small class="displayblock">17% Average <i class="zmdi zmdi-trending-up"></i></small>
                        <div class="sparkline" data-type="line" data-spot-Radius="1" data-highlight-Spot-Color="rgb(63, 81, 181)" data-highlight-Line-Color="#222"
                        data-min-Spot-Color="rgb(233, 30, 99)" data-max-Spot-Color="rgb(120, 184, 62)" data-spot-Color="rgb(63, 81, 181, 0.7)"
                        data-offset="90" data-width="100%" data-height="60px" data-line-Width="1" data-line-Color="rgb(63, 81, 181, 0.7)"
                        data-fill-Color="rgba(0,0,0, 0.2)">8,6,4,2,3,6,5,7,9,8,5,2</div>
                    </div>
                </div>
            </div>                
        </div>        
        <div class="row clearfix">
            <div class="col-lg-2 col-md-4 col-sm-6 col-6 text-center">
                <div class="card">
                    <div class="body">
                        <p>Page View</p>
                        <input type="text" class="knob" value="42" data-linecap="round" data-width="80" data-height="80" data-thickness="0.15" data-fgColor="#00adef"
                        readonly>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-6 text-center">
                <div class="card">
                    <div class="body">
                        <p>Storage</p>
                        <input type="text" class="knob" value="81" data-linecap="round" data-width="80" data-height="80" data-thickness="0.15" data-fgColor="#49cdd0"
                        readonly>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-6 text-center">
                <div class="card">
                    <div class="body">
                        <p>New User</p>
                        <input type="text" class="knob" value="62" data-linecap="round" data-width="80" data-height="80" data-thickness="0.15" data-fgColor="#8f78db"
                        readonly>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-6 text-center">
                <div class="card">
                    <div class="body">
                        <p>Total Visit</p>
                        <input type="text" class="knob" value="38" data-linecap="round" data-width="80" data-height="80" data-thickness="0.15" data-fgColor="#f67a82"
                        readonly>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-6 text-center">
                <div class="card">
                    <div class="body">
                        <p>Subscribers</p>
                        <input type="text" class="knob" value="87" data-linecap="round" data-width="80" data-height="80" data-thickness="0.15" data-fgColor="#40b988"
                        readonly>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-6 text-center">
                <div class="card">
                    <div class="body">
                        <p>Bounce Rate</p>
                        <input type="text" class="knob" value="64" data-linecap="round" data-width="80" data-height="80" data-thickness="0.15" data-fgColor="#f7bb97"
                        readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card info-box-2 l-seagreen">
                    <div class="body">
                        <div class="icon col-12">
                            <div class="chart chart-pie">30, 35, 25, 8</div>
                        </div>
                        <div class="content col-12">
                            <div class="text">USAGE</div>
                            <div class="number">98%</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card info-box-2 l-parpl">
                    <div class="body">
                        <div class="icon col-12 m-t-10">
                            <div class="chart chart-bar">6,4,8,6,8,10,5,6,7,9,5</div>
                        </div>
                        <div class="content col-12">
                            <div class="text">IMPRESSIONS</div>
                            <div class="number">457 512</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card info-box-2 l-blue">
                    <div class="body">
                        <div class="icon col-12 m-t-5">
                            <span class="chart chart-line">9,4,6,5,6,4,7,3</span>
                        </div>
                        <div class="content col-12">
                            <div class="text">TOTAL SALES</div>
                            <div class="number">$125 543</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card info-box-2 l-coral">
                    <div class="body">
                        <div class="icon col-12 m-t-10">
                            <div class="chart chart-bar">4,6,-3,-1,2,-2,4,3,6,7,-2,3</div>
                        </div>
                        <div class="content col-12">
                            <div class="text">CURRENCY</div>
                            <div class="number">$1 063</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-lg-3 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <div class="body">
                        <input type="text" class="knob" data-skin="tron" value="66" data-width="90" data-height="90" data-thickness="0.1" data-fgColor="#26dad2" readonly>                        
                        <h6 class="m-t-20">Satisfaction Rate</h6>
                        <p class="displayblock m-b-0">47% Average <i class="zmdi zmdi-trending-up"></i></p>                        
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <div class="body">
                        <input type="text" class="knob dial2" value="26" data-width="90" data-height="90" data-thickness="0.1" data-fgColor="#7b69ec" readonly>
                        <h6 class="m-t-20">Project Panding</h6>
                        <p class="displayblock m-b-0">13% Average <i class="zmdi zmdi-trending-down"></i></p>
                        
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <div class="body">
                        <input type="text" class="knob dial3" value="76" data-width="90" data-height="90" data-thickness="0.1" data-fgColor="#f9bd53" readonly>
                        <h6 class="m-t-20">Productivity Goal</h6>
                        <p class="displayblock m-b-0">75% Average <i class="zmdi zmdi-trending-up"></i></p>
                        
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <div class="body">
                        <input type="text" class="knob dial4" value="88" data-width="90" data-height="90" data-thickness="0.1" data-fgColor="#00adef" readonly>
                        <h6 class="m-t-20">Total Revenue</h6>
                        <p class="displayblock m-b-0">54% Average <i class="zmdi zmdi-trending-up"></i></p>
                        
                    </div>
                </div>
            </div>            
        </div>
        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <ul class="row profile_state list-unstyled">
                        <li class="col-lg-3 col-md-3 col-6">
                            <div class="body">
                                <i class="zmdi zmdi-eye col-amber"></i>
                                <h4>2,365</h4>
                                <span>Post View</span>
                            </div>
                        </li>
                        <li class="col-lg-3 col-md-3 col-6">
                            <div class="body">
                                <i class="zmdi zmdi-thumb-up col-blue"></i>
                                <h4>365</h4>
                                <span>Likes</span>
                            </div>
                        </li>
                        <li class="col-lg-3 col-md-3 col-6">
                            <div class="body">
                                <i class="zmdi zmdi-comment-text col-red"></i>
                                <h4>65</h4>
                                <span>Comments</span>
                            </div>
                        </li>
                        <li class="col-lg-3 col-md-3 col-6">
                            <div class="body">
                                <i class="zmdi zmdi-account text-success"></i>
                                <h4>2,055</h4>
                                <span>Profile Views</span>
                            </div>
                        </li>                      
                    </ul>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-lg-3 col-md-6">
                <div class="card l-blue">
                    <div class="body">
                        <h4 class="m-t-0 m-b-0">2,048</h4>
                        <p class="m-b-0">Total Leads</p>
                    </div>
                    <div class="sparkline" data-type="line" data-spot-Radius="1" data-highlight-Spot-Color="rgb(233, 30, 99)" data-highlight-Line-Color="#222"
                    data-min-Spot-Color="rgb(233, 30, 99)" data-max-Spot-Color="rgb(0, 150, 136)" data-spot-Color="rgb(0, 188, 212)"
                    data-offset="90" data-width="100%" data-height="40px" data-line-Width="2" data-line-Color="rgba(255, 255, 255, 0.2)"
                    data-fill-Color="rgba(255, 255, 255, 0.3)"> 7,6,7,8,5,9,8,6,7 </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card l-parpl">
                    <div class="body">
                        <h4 class="m-t-0 m-b-0">521</h4>
                        <p class="m-b-0 ">Total Connections</p>
                    </div>
                    <div class="sparkline" data-type="line" data-spot-Radius="1" data-highlight-Spot-Color="rgb(233, 30, 99)" data-highlight-Line-Color="#222"
                    data-min-Spot-Color="rgb(233, 30, 99)" data-max-Spot-Color="rgb(0, 150, 136)" data-spot-Color="rgb(0, 188, 212)"
                    data-offset="90" data-width="100%" data-height="42px" data-line-Width="2" data-line-Color="rgba(255, 255, 255, 0.2)"
                    data-fill-Color="rgba(255, 255, 255, 0.3)"> 6,5,7,4,5,3,8,6,5 </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card l-seagreen">
                    <div class="body">
                        <h4 class="m-t-0 m-b-0">73</h4>
                        <p class="m-b-0 ">Articles</p>
                    </div>
                    <div class="sparkline" data-type="line" data-spot-Radius="1" data-highlight-Spot-Color="rgb(233, 30, 99)" data-highlight-Line-Color="#222"
                    data-min-Spot-Color="rgb(233, 30, 99)" data-max-Spot-Color="rgb(0, 150, 136)" data-spot-Color="rgb(0, 188, 212)"
                    data-offset="90" data-width="100%" data-height="45px" data-line-Width="2" data-line-Color="rgba(255, 255, 255, 0.2)"
                    data-fill-Color="rgba(255, 255, 255, 0.3)"> 8,7,7,5,5,4,8,7,5 </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card l-pink">
                    <div class="body">
                        <h4 class="m-t-0 m-b-0">15</h4>
                        <p class="m-b-0">Categories</p>
                    </div>
                    <div class="sparkline" data-type="line" data-spot-Radius="1" data-highlight-Spot-Color="rgb(233, 30, 99)" data-highlight-Line-Color="#222"
                    data-min-Spot-Color="rgb(233, 30, 99)" data-max-Spot-Color="rgb(0, 150, 136)" data-spot-Color="rgb(0, 188, 212)"
                    data-offset="90" data-width="100%" data-height="45px" data-line-Width="2" data-line-Color="rgba(255, 255, 255, 0.2)"
                    data-fill-Color="rgba(255, 255, 255, 0.3)"> 7,6,7,8,5,9,8,6,7 </div>
                </div>
            </div>
        </div>        
        <div class="row clearfix">
            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>USA</strong> New Patients</h2>
                    </div>
                    <div class="body">
                        <div class="row">
                            <div class="col-sm-4 col-4 m-b-10">
                                <small class="text-muted">Today</small>
                                <h5 class="m-b-0">256</h5>                                
                            </div>
                            <div class="col-sm-4 col-4 m-b-10">
                                <small class="text-muted">This Week</small>
                                <h5 class="m-b-0">621</h5>                                
                            </div>
                            <div class="col-sm-4 col-4 m-b-10">
                                <small class="text-muted">This Month</small>
                                <h5 class="m-b-0">981</h5>                                
                            </div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar l-turquoise" role="progressbar" aria-valuenow="87" aria-valuemin="0" aria-valuemax="100" style="width: 87%;"></div>
                        </div>
                    </div>                    
                </div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>India</strong> New Patients</h2>
                    </div>
                    <div class="body">
                        <div class="row">
                            <div class="col-sm-4 col-4 m-b-10">
                                <small class="text-muted">Today</small>
                                <h5 class="m-b-0">195</h5>                                
                            </div>
                            <div class="col-sm-4 col-4 m-b-10">
                                <small class="text-muted">This Week</small>
                                <h5 class="m-b-0">235</h5>                                
                            </div>
                            <div class="col-sm-4 col-4 m-b-10">
                                <small class="text-muted">This Month</small>
                                <h5 class="m-b-0">312</h5>                                
                            </div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar l-coral" role="progressbar" aria-valuenow="58" aria-valuemin="0" aria-valuemax="100" style="width: 58%;"></div>
                        </div>
                    </div>                    
                </div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>Europe</strong> New Patients</h2>
                    </div>
                    <div class="body">
                        <div class="row">
                            <div class="col-sm-4 col-4 m-b-10">
                                <small class="text-muted">Today</small>
                                <h5 class="m-b-0">210</h5>                                
                            </div>
                            <div class="col-sm-4 col-4 m-b-10">
                                <small class="text-muted">This Week</small>
                                <h5 class="m-b-0">462</h5>                                
                            </div>
                            <div class="col-sm-4 col-4 m-b-10">
                                <small class="text-muted">This Month</small>
                                <h5 class="m-b-0">574</h5>                                
                            </div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar l-parpl" role="progressbar" aria-valuenow="71" aria-valuemin="0" aria-valuemax="100" style="width: 71%;"></div>
                        </div>
                    </div>                    
                </div>                
            </div>
        </div>       
    </div>
</section>
@endsection

@push('scripts-before-main')
<script src="{{ asset('assets/bundles/knob.bundle.js') }}"></script>
<script src="{{ asset('assets/js/pages/charts/jquery-knob.js') }}"></script>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/pages/widgets/infobox/infobox-1.js') }}"></script>
<script src="{{ asset('assets/js/pages/cards/basic.js') }}"></script>
@endpush
