<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $content['seo']['meta_title'] ?? $projectName }}</title>
    <meta name="description" content="{{ $content['seo']['meta_description'] ?? '' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material-design-iconic-font/css/material-design-iconic-font.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lineup-public-website.css') }}?v=1">
    @include('layouts.partials.favicon')
</head>
<body class="lineup-public @if(!empty($isPreview)) lineup-public--preview @endif">
    @if(!empty($isPreview))
    <div class="lineup-public-preview-bar">
        <i class="zmdi zmdi-eye"></i> Admin preview — this is how your public site will look.
        <a href="{{ route('admin.website.index') }}">Back to Manage Website</a>
    </div>
    @endif
    @yield('website-body')
    <script>
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
    </script>
</body>
</html>
