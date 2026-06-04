@extends('layouts.app')

@section('title', 'Colors')

@section('content')
<section class="content">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>Colors
                <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">UI</a></li>
                    <li class="breadcrumb-item active">Colors</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>Gradient</strong> Colors</h2>
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
                        <div class="clearfix row">
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 l-green">
                                    <div class="color-name">Green</div>                                    
                                    <div class="color-class-name">.l-green</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 l-pink">
                                    <div class="color-name">Pink</div>                                    
                                    <div class="color-class-name">.l-pink</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 l-coral">
                                    <div class="color-name">Coral</div>                                    
                                    <div class="color-class-name">.l-coral</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 l-amber">
                                    <div class="color-name">Amber</div>                                    
                                    <div class="color-class-name">.l-amber</div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix row">
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 l-turquoise">
                                    <div class="color-name">Turquoise</div>                                    
                                    <div class="color-class-name">.l-turquoise</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 l-khaki">
                                    <div class="color-name">Khaki</div>                                    
                                    <div class="color-class-name">.l-khaki</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 l-parpl">
                                    <div class="color-name">Parpl</div>                                    
                                    <div class="color-class-name">.l-parpl</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 l-slategray">
                                    <div class="color-name">Slategray</div>                                    
                                    <div class="color-class-name">.l-slategray</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>Material</strong> Design Colors <small> Taken by Google's Material Design Color page which link is <a href="https://material.google.com/style/color.html#color-color-palette" target="_blank" title="Google's Material Design Color">material.google.com/style/color.html#color-color-palette</a> </small> </h2>
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
                        <div class="clearfix row">
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-red">
                                    <div class="color-name">RED</div>
                                    <div class="color-code">#F44336</div>
                                    <div class="color-class-name">bg-red</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-pink">
                                    <div class="color-name">PINK</div>
                                    <div class="color-code">#E91E63</div>
                                    <div class="color-class-name">bg-pink</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-purple">
                                    <div class="color-name">PURPLE</div>
                                    <div class="color-code">#9C27B0</div>
                                    <div class="color-class-name">bg-purple</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-deep-purple">
                                    <div class="color-name">DEEP PURPLE</div>
                                    <div class="color-code">#673AB7</div>
                                    <div class="color-class-name">bg-deep-purple</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-indigo">
                                    <div class="color-name">INDIGO</div>
                                    <div class="color-code">#3F51B5</div>
                                    <div class="color-class-name">bg-indigo</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-blue">
                                    <div class="color-name">BLUE</div>
                                    <div class="color-code">#2196F3</div>
                                    <div class="color-class-name">bg-blue</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-light-blue">
                                    <div class="color-name">LIGHT BLUE</div>
                                    <div class="color-code">#03A9F4</div>
                                    <div class="color-class-name">bg-light-blue</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-cyan">
                                    <div class="color-name">CYAN</div>
                                    <div class="color-code">#00BCD4</div>
                                    <div class="color-class-name">bg-cyan</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-teal">
                                    <div class="color-name">TEAL</div>
                                    <div class="color-code">#009688</div>
                                    <div class="color-class-name">bg-teal</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-green">
                                    <div class="color-name">GREEN</div>
                                    <div class="color-code">#4CAF50</div>
                                    <div class="color-class-name">bg-green</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-light-green">
                                    <div class="color-name">LIGHT GREEN</div>
                                    <div class="color-code">#8BC34A</div>
                                    <div class="color-class-name">bg-light-green</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-lime">
                                    <div class="color-name">LIME</div>
                                    <div class="color-code">#CDDC39</div>
                                    <div class="color-class-name">bg-lime</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-yellow">
                                    <div class="color-name">YELLOW</div>
                                    <div class="color-code">#FFEB3B</div>
                                    <div class="color-class-name">bg-yellow</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-amber">
                                    <div class="color-name">AMBER</div>
                                    <div class="color-code">#FFC107</div>
                                    <div class="color-class-name">bg-amber</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-orange">
                                    <div class="color-name">ORANGE</div>
                                    <div class="color-code">#FF9800</div>
                                    <div class="color-class-name">bg-orange</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-deep-orange">
                                    <div class="color-name">DEEP ORANGE</div>
                                    <div class="color-code">#FF5722</div>
                                    <div class="color-class-name">bg-deep-orange</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-brown">
                                    <div class="color-name">BROWN</div>
                                    <div class="color-code">#795548</div>
                                    <div class="color-class-name">bg-brown</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-grey">
                                    <div class="color-name">GREY</div>
                                    <div class="color-code">#9E9E9E</div>
                                    <div class="color-class-name">bg-grey</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-blue-grey">
                                    <div class="color-name">BLUE GREY</div>
                                    <div class="color-code">#607D8B</div>
                                    <div class="color-class-name">bg-blue-grey</div>
                                </div>
                            </div>
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <div class="body m-b-10 bg-black">
                                    <div class="color-name">BLACK</div>
                                    <div class="color-code">#000000</div>
                                    <div class="color-class-name">bg-black</div>
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
