@extends('layouts.app')

@section('title', 'Page')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/ion-rangeslider/css/ion.rangeSlider.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/ion-rangeslider/css/ion.rangeSlider.skinFlat.css') }}">
@endpush

@section('content')
<section class="content">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>Rang Sliders
                <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">UI</a></li>
                    <li class="breadcrumb-item active">Rang Sliders</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>Examples</strong> <small>Taken by <a href="http://ionden.com/a/plugins/ion.rangeSlider/en.html" target="_blank">ionden.com/a/plugins/ion.rangeSlider/en.html</a></small> </h2>
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
                        <div class="irs-demo m-b-30"> <b>Start without params</b>
                            <input type="text" id="range_01" value="" />
                        </div>
                        <div class="irs-demo m-b-30"> <b>Set min value, max value and start point</b>
                            <input type="text" id="range_02" value="" />
                        </div>
                        <div class="irs-demo m-b-30"> <b>Set type to double and specify range, also showing grid and adding prefix "$"</b>
                            <input type="text" id="range_03" value="" />
                        </div>
                        <div class="irs-demo m-b-30"> <b>Set up range with negative values</b>
                            <input type="text" id="range_04" value="" />
                        </div>
                        <div class="irs-demo m-b-30"> <b>Using step 250</b>
                            <input type="text" id="range_05" value="" />
                        </div>
                        <div class="irs-demo m-b-30"> <b>Set up range with fractional values, using fractional step</b>
                            <input type="text" id="range_06" value="" />
                        </div>
                        <div class="irs-demo m-b-30"> <b>Set up you own numbers</b>
                            <input type="text" id="range_07" value="" />
                        </div>
                        <div class="irs-demo m-b-30"> <b>Using any strings as your values</b>
                            <input type="text" id="range_08" value="" />
                        </div>
                        <div class="irs-demo m-b-30"> <b>One more example with strings</b>
                            <input type="text" id="range_09" value="" />
                        </div>
                        <div class="irs-demo m-b-30"> <b>No prettify. Big numbers are ugly and unreadable</b>
                            <input type="text" id="range_10" value="" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/ion-rangeslider/js/ion.rangeSlider.js') }}"></script>
<script src="{{ asset('assets/js/pages/ui/range-sliders.js') }}"></script>
@endpush
