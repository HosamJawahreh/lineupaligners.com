@extends('website.smiliz.layout')

@section('smiliz-script-tier', 'core')

@section('title', $pageTitle ?? $projectName)

@section('meta_description', $pageDescription ?? '')

@if($hasBeforeAfter ?? false)
@section('needs-before-after', '1')
@endif

@section('smiliz-body')
@include('website.smiliz.partials.header')

{!! $pageHtml !!}

@include('website.smiliz.partials.footer')
@endsection
