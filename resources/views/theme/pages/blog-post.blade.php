@extends('layouts.app')

@section('title', 'Blog Post')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/dropzone/dropzone.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-select/css/bootstrap-select.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/blog.css') }}">
@endpush

@section('content')
<section class="content blog-page">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-5 col-sm-12">
                <h2>New Post
                    <small>Welcome to Oreo</small>
                </h2>
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">
                <ul class="breadcrumb float-md-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i> Oreo</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pages.blog-dashboard') }}">Blog</a></li>
                    <li class="breadcrumb-item active">New Post</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="body">
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Enter Blog title" />
                        </div>
                        <select class="form-control show-tick">
                            <option>Select Category --</option>
                            <option>Web Design</option>
                            <option>Photography</option>
                            <option>Technology</option>
                            <option>Lifestyle</option>
                            <option>Sports</option>
                        </select>
                        <form action="/" id="frmFileUpload" class="dropzone m-b-20 m-t-20" method="post" enctype="multipart/form-data">
                            <div class="dz-message">
                                <div class="drag-icon-cph"> <i class="material-icons">touch_app</i> </div>
                                <h3>Drop files here or click to upload.</h3>
                                <em>(This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)</em> </div>
                            <div class="fallback">
                                <input name="file" type="file" multiple />
                            </div>
                        </form>                        
                    </div>
                </div>
                <div class="card">
                    <div class="body">
                        <textarea id="ckeditor">
                            <h2>WYSIWYG Editor</h2>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam ullamcorper sapien non nisl facilisis bibendum in quis tellus. Duis in urna bibendum turpis pretium fringilla. Aenean neque velit, porta eget mattis ac, imperdiet quis nisi. Donec non dui et tortor vulputate luctus. Praesent consequat rhoncus velit, ut molestie arcu venenatis sodales.</p>
                            <h3>Lacinia</h3>
                            <ul>
                                <li>Suspendisse tincidunt urna ut velit ullamcorper fermentum.</li>
                                <li>Nullam mattis sodales lacus, in gravida sem auctor at.</li>
                                <li>Praesent non lacinia mi.</li>
                                <li>Mauris a ante neque.</li>
                                <li>Aenean ut magna lobortis nunc feugiat sagittis.</li>
                            </ul>
                            <h3>Pellentesque adipiscing</h3>
                            <p>Maecenas quis ante ante. Nunc adipiscing rhoncus rutrum. Pellentesque adipiscing urna mi, ut tempus lacus ultrices ac. Pellentesque sodales, libero et mollis interdum, dui odio vestibulum dolor, eu pellentesque nisl nibh quis nunc. Sed porttitor leo adipiscing venenatis vehicula. Aenean quis viverra enim. Praesent porttitor ut ipsum id ornare.</p>
                        </textarea>
                        <button type="button" class="btn btn-primary btn-round waves-effect m-t-20">Post</button>
                    </div>
                </div>
            </div>            
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/dropzone/dropzone.js') }}"></script>
<script src="{{ asset('assets/plugins/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('assets/js/pages/forms/editors.js') }}"></script>
@endpush
