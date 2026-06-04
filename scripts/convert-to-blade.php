<?php

/**
 * Converts all Oreo Hospital HTML templates to Blade views.
 */

$base = dirname(__DIR__);
$legacyDir = $base.'/template/legacy';
$pagesDir = $base.'/resources/views/theme/pages';
$partialsDir = $base.'/resources/views/layouts/partials';
$authDir = $base.'/resources/views/auth';

foreach ([$pagesDir, $partialsDir, $authDir] as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

$routeMap = [
    'index.html' => ['name' => 'dashboard', 'route' => 'dashboard'],
    'sign-in.html' => ['name' => 'login', 'route' => 'login'],
    'sign-up.html' => ['name' => 'sign-up', 'route' => 'pages.sign-up'],
    'forgot-password.html' => ['name' => 'forgot-password', 'route' => 'pages.forgot-password'],
    '404.html' => ['name' => '404', 'route' => 'pages.404'],
    '500.html' => ['name' => '500', 'route' => 'pages.500'],
    'page-offline.html' => ['name' => 'page-offline', 'route' => 'pages.page-offline'],
    'locked.html' => ['name' => 'locked', 'route' => 'pages.locked'],
    'doctors.html' => ['name' => 'doctors', 'route' => 'doctors.index'],
    'add-doctor.html' => ['name' => 'add-doctor', 'route' => 'doctors.create'],
    'patients.html' => ['name' => 'patients', 'route' => 'patients.index'],
    'add-patient.html' => ['name' => 'add-patient', 'route' => 'patients.create'],
    'patient-profile.html' => ['name' => 'patient-profile', 'route' => 'pages.patient-profile'],
    'patient-invoice.html' => ['name' => 'patient-invoice', 'route' => 'pages.patient-invoice'],
    'book-appointment.html' => ['name' => 'book-appointment', 'route' => 'appointments.create'],
    'all-Departments.html' => ['name' => 'all-departments', 'route' => 'departments.index'],
    'add-departments.html' => ['name' => 'add-departments', 'route' => 'departments.create'],
    'more-Departments.html' => ['name' => 'more-departments', 'route' => 'pages.more-departments'],
    'payments.html' => ['name' => 'payments', 'route' => 'pages.payments'],
    'add-payments.html' => ['name' => 'add-payments', 'route' => 'pages.add-payments'],
    'invoice.html' => ['name' => 'invoice', 'route' => 'pages.invoice'],
    'profile.html' => ['name' => 'profile', 'route' => 'pages.profile'],
    'events.html' => ['name' => 'events', 'route' => 'pages.events'],
    'contact.html' => ['name' => 'contact', 'route' => 'pages.contact'],
    'chat.html' => ['name' => 'chat', 'route' => 'pages.chat'],
    'mail-inbox.html' => ['name' => 'mail-inbox', 'route' => 'pages.mail-inbox'],
    'mail-compose.html' => ['name' => 'mail-compose', 'route' => 'pages.mail-compose'],
    'mail-single.html' => ['name' => 'mail-single', 'route' => 'pages.mail-single'],
    'file-dashboard.html' => ['name' => 'file-dashboard', 'route' => 'pages.file-dashboard'],
    'file-documents.html' => ['name' => 'file-documents', 'route' => 'pages.file-documents'],
    'file-media.html' => ['name' => 'file-media', 'route' => 'pages.file-media'],
    'file-images.html' => ['name' => 'file-images', 'route' => 'pages.file-images'],
    'blog-dashboard.html' => ['name' => 'blog-dashboard', 'route' => 'pages.blog-dashboard'],
    'blog-list.html' => ['name' => 'blog-list', 'route' => 'pages.blog-list'],
    'blog-grid.html' => ['name' => 'blog-grid', 'route' => 'pages.blog-grid'],
    'blog-post.html' => ['name' => 'blog-post', 'route' => 'pages.blog-post'],
    'blog-details.html' => ['name' => 'blog-details', 'route' => 'pages.blog-details'],
    'widgets-app.html' => ['name' => 'widgets-app', 'route' => 'pages.widgets-app'],
    'widgets-data.html' => ['name' => 'widgets-data', 'route' => 'pages.widgets-data'],
    'ui_kit.html' => ['name' => 'ui-kit', 'route' => 'pages.ui-kit'],
    'alerts.html' => ['name' => 'alerts', 'route' => 'pages.alerts'],
    'collapse.html' => ['name' => 'collapse', 'route' => 'pages.collapse'],
    'colors.html' => ['name' => 'colors', 'route' => 'pages.colors'],
    'dialogs.html' => ['name' => 'dialogs', 'route' => 'pages.dialogs'],
    'icons.html' => ['name' => 'icons', 'route' => 'pages.icons'],
    'list-group.html' => ['name' => 'list-group', 'route' => 'pages.list-group'],
    'media-object.html' => ['name' => 'media-object', 'route' => 'pages.media-object'],
    'modals.html' => ['name' => 'modals', 'route' => 'pages.modals'],
    'notifications.html' => ['name' => 'notifications', 'route' => 'pages.notifications'],
    'progressbars.html' => ['name' => 'progressbars', 'route' => 'pages.progressbars'],
    'range-sliders.html' => ['name' => 'range-sliders', 'route' => 'pages.range-sliders'],
    'sortable-nestable.html' => ['name' => 'sortable-nestable', 'route' => 'pages.sortable-nestable'],
    'tabs.html' => ['name' => 'tabs', 'route' => 'pages.tabs'],
    'waves.html' => ['name' => 'waves', 'route' => 'pages.waves'],
    'blank.html' => ['name' => 'blank', 'route' => 'pages.blank'],
    'demo.html' => ['name' => 'demo', 'route' => 'pages.demo'],
    'demo2.html' => ['name' => 'demo2', 'route' => 'pages.demo2'],
    'timeline.html' => ['name' => 'timeline', 'route' => 'pages.timeline'],
    'search-results.html' => ['name' => 'search-results', 'route' => 'pages.search-results'],
    'image-gallery.html' => ['name' => 'image-gallery', 'route' => 'pages.image-gallery'],
    'dashboard-rtl.html' => ['name' => 'dashboard-rtl', 'route' => 'pages.dashboard-rtl'],
];

function convertPaths(string $html): string
{
    $html = preg_replace(
        '#style="background-image:url\(\.\./assets/([^"\)]+)\)"#',
        'style="background-image:url({{ asset(\'assets/$1\') }})"',
        $html
    );

    $html = preg_replace('#(href|src)="\.\./assets/([^"]+)"#', '$1="{{ asset(\'assets/$2\') }}"', $html);
    $html = preg_replace('#(href|src)="assets/([^"]+)"#', '$1="{{ asset(\'assets/$2\') }}"', $html);

    return $html;
}

function convertHtmlLinks(string $html, array $routeMap): string
{
    global $base;

    $html = str_replace(
        'href="sign-in.html" class="mega-menu"',
        'href="{{ route(\'logout\') }}" onclick="event.preventDefault();document.getElementById(\'logout-form\').submit();" class="mega-menu"',
        $html
    );

    foreach ($routeMap as $file => $meta) {
        $route = $meta['route'];
        if ($route === 'dashboard') {
            $replacement = "{{ route('dashboard') }}";
        } elseif (str_contains($route, '.')) {
            $replacement = "{{ route('$route') }}";
        } else {
            $replacement = "{{ route('$route') }}";
        }
        $html = str_replace('href="'.$file.'"', 'href="'.$replacement.'"', $html);
    }

    // Remaining .html links -> pages route by slug
    $html = preg_replace_callback('#href="([a-zA-Z0-9_-]+)\.html"#', function ($m) use ($routeMap) {
        $file = $m[1].'.html';
        if (isset($routeMap[$file])) {
            $route = $routeMap[$file]['route'];

            return 'href="{{ route(\''.$route.'\') }}"';
        }
        $slug = strtolower(str_replace('_', '-', $m[1]));

        return 'href="{{ route(\'pages.show\', \''.$slug.'\') }}"';
    }, $html);

    return $html;
}

function extractTitle(string $html): string
{
    if (preg_match('#<title>:: Oreo Hospital :: (.+?)</title>#', $html, $m)) {
        return trim($m[1]);
    }

    return 'Page';
}

function extractBodyClass(string $html): string
{
    if (preg_match('#<body class="([^"]+)"#', $html, $m)) {
        $class = trim(str_replace(['theme-cyan', '  '], ['', ' '], $m[1]));

        return trim($class);
    }

    return '';
}

function extractExtraStyles(string $html): array
{
    $styles = [];
    if (preg_match_all('#<link rel="stylesheet" href="(\{\{ asset\([^}]+\) \}\})"#', $html, $matches)) {
        foreach ($matches[1] as $href) {
            if (str_contains($href, 'main.css') || str_contains($href, 'color_skins.css') || str_contains($href, 'bootstrap.min.css') || str_contains($href, 'jvectormap') || str_contains($href, 'morris')) {
                continue;
            }
            $styles[] = $href;
        }
    }

    return array_unique($styles);
}

function extractExtraScripts(string $html): array
{
    $scripts = [];
    if (preg_match_all('#<script src="(\{\{ asset\([^}]+\) \}\})"></script>#', $html, $matches)) {
        foreach ($matches[1] as $src) {
            if (str_contains($src, 'libscripts') || str_contains($src, 'vendorscripts') || str_contains($src, 'mainscripts')) {
                continue;
            }
            $scripts[] = $src;
        }
    }

    return array_unique($scripts);
}

function extractAppContent(string $html): ?string
{
    if (! preg_match('#<section class="content[^"]*">#', $html, $m, PREG_OFFSET_CAPTURE)) {
        return null;
    }
    $start = $m[0][1];
    $scriptPos = strpos($html, '<!-- Jquery Core Js -->', $start);
    if ($scriptPos === false) {
        $scriptPos = strpos($html, '<script src="{{ asset(\'assets/bundles/libscripts.bundle.js\') }}"></script>', $start);
    }
    if ($scriptPos === false) {
        return null;
    }
    $chunk = substr($html, $start, $scriptPos - $start);
    $lastSection = strrpos($chunk, '</section>');
    if ($lastSection === false) {
        return null;
    }

    return substr($chunk, 0, $lastSection + strlen('</section>'));
}

function buildAppBlade(string $title, string $bodyClass, array $styles, string $content, array $scriptsBefore, array $scriptsAfter): string
{
    $blade = "@extends('layouts.app')\n\n";
    $blade .= "@section('title', ".var_export($title, true).")\n";
    if ($bodyClass !== '' && $bodyClass !== 'theme-cyan') {
        $blade .= "@section('body-class', ".var_export($bodyClass, true).")\n";
    }
    if ($styles) {
        $blade .= "\n@push('styles')\n";
        foreach ($styles as $style) {
            $blade .= '<link rel="stylesheet" href="'.$style.'">'."\n";
        }
        $blade .= "@endpush\n";
    }
    $blade .= "\n@section('content')\n".$content."\n@endsection\n";
    if ($scriptsBefore) {
        $blade .= "\n@push('scripts-before-main')\n";
        foreach ($scriptsBefore as $script) {
            if (str_contains($script, 'morris') || str_contains($script, 'jvectormap') || str_contains($script, 'knob') || str_contains($script, 'chart') || str_contains($script, 'flot') || str_contains($script, 'datatable') || str_contains($script, 'footable') || str_contains($script, 'fullcalendar')) {
                $blade .= '<script src="'.$script.'"></script>'."\n";
            }
        }
        $blade .= "@endpush\n";
    }
    if ($scriptsAfter) {
        $blade .= "\n@push('scripts')\n";
        foreach ($scriptsAfter as $script) {
            $blade .= '<script src="'.$script.'"></script>'."\n";
        }
        $blade .= "@endpush\n";
    }

    return $blade;
}

function buildAuthBlade(string $title, string $bodyHtml): string
{
    $head = <<<'BLADE'
<!doctype html>
<html class="no-js " lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<meta name="description" content="Oreo Hospital">
<title>
BLADE;
    $head .= $title.' :: {{ config(\'app.name\') }}</title>'."\n";
    $head .= <<<'BLADE'
<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/authentication.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/color_skins.css') }}">
</head>
BLADE;

    return $head."\n".$bodyHtml."\n";
}

function applyLoginFormFixes(string $html): string
{
    $html = str_replace(
        '<form class="form" method="" action="">',
        '<form class="form" method="POST" action="{{ route(\'login\') }}">@csrf',
        $html
    );
    $html = str_replace(
        '<input type="text" class="form-control" placeholder="Enter User Name">',
        '<input type="email" name="email" class="form-control" placeholder="Enter Email" value="{{ old(\'email\') }}" required autofocus>',
        $html
    );
    $html = str_replace(
        '<input type="password" placeholder="Password" class="form-control" />',
        '<input type="password" name="password" placeholder="Password" class="form-control" required />',
        $html
    );
    $html = preg_replace(
        '#<a href="\{\{ route\(\'dashboard\'\) \}\}" class="btn btn-primary btn-round btn-lg btn-block[^"]*">\s*SIGN IN\s*</a>#',
        '<button type="submit" class="btn btn-primary btn-round btn-lg btn-block">SIGN IN</button>',
        $html
    );

    return $html;
}

// Generate shared theme shell from index.html
$indexHtml = convertHtmlLinks(convertPaths(file_get_contents($legacyDir.'/index.html')), $routeMap);
$shellStart = '<!-- Top Bar -->';
$shellEnd = '<!-- Main Content -->';
$shellStartPos = strpos($indexHtml, $shellStart);
$shellEndPos = strpos($indexHtml, $shellEnd);
if ($shellStartPos !== false && $shellEndPos !== false) {
    file_put_contents($partialsDir.'/theme-shell.blade.php', substr($indexHtml, $shellStartPos, $shellEndPos - $shellStartPos)."\n");
}

$pageRegistry = [];
$converted = 0;

foreach (glob($legacyDir.'/*.html') as $filePath) {
    $fileName = basename($filePath);
    $html = convertHtmlLinks(convertPaths(file_get_contents($filePath)), $routeMap);
    $title = extractTitle($html);
    $meta = $routeMap[$fileName] ?? ['name' => slugify($fileName), 'route' => 'pages.show'];
    $slug = $meta['name'] ?? slugify($fileName);

    if ($fileName === 'sign-in.html') {
        $body = extractBetween($html, '<body class="theme-cyan authentication sidebar-collapse">', '</body>');
        $body = applyLoginFormFixes($body);
        file_put_contents($authDir.'/login.blade.php', buildAuthBlade('Sign In', $body));
        $pageRegistry[$slug] = ['file' => $fileName, 'route' => 'login', 'title' => $title, 'type' => 'auth'];
        $converted++;
        continue;
    }

    if (str_contains($html, 'authentication sidebar-collapse') && $fileName !== 'sign-in.html') {
        $body = extractBetween($html, '<body class="theme-cyan authentication sidebar-collapse">', '</body>');
        if ($fileName === '404.html') {
            $body = str_replace(
                '<a href="{{ route(\'dashboard\') }}" class="btn btn-primary btn-round btn-lg btn-block waves-effect waves-light">GO TO HOMEPAGE</a>',
                '<a href="{{ route(\'dashboard\') }}" class="btn btn-primary btn-round btn-lg btn-block waves-effect waves-light">GO TO HOMEPAGE</a>',
                $body
            );
        }
        file_put_contents($pagesDir.'/'.$slug.'.blade.php', buildAuthBlade($title, $body));
        $pageRegistry[$slug] = ['file' => $fileName, 'route' => $meta['route'] ?? 'pages.'.$slug, 'title' => $title, 'type' => 'auth'];
        $converted++;
        continue;
    }

    $content = extractAppContent($html);
    if ($content === null) {
        echo "SKIP (no content section): $fileName\n";
        continue;
    }

    $bodyClass = extractBodyClass($html);
    $styles = extractExtraStyles($html);
    $allScripts = extractExtraScripts($html);
    $scriptsBefore = [];
    $scriptsAfter = [];
    foreach ($allScripts as $script) {
        if (str_contains($script, 'morris') || str_contains($script, 'jvectormap') || str_contains($script, 'knob') || str_contains($script, 'chartscripts') || str_contains($script, 'flotscripts') || str_contains($script, 'datatablescripts') || str_contains($script, 'footable') || str_contains($script, 'fullcalendar')) {
            $scriptsBefore[] = $script;
        } else {
            $scriptsAfter[] = $script;
        }
    }

    $blade = buildAppBlade($title, $bodyClass, $styles, $content, $scriptsBefore, $scriptsAfter);

    if ($fileName === 'index.html') {
        file_put_contents($base.'/resources/views/dashboard/index.blade.php', $blade);
    } else {
        file_put_contents($pagesDir.'/'.$slug.'.blade.php', $blade);
    }

    $pageRegistry[$slug] = [
        'file' => $fileName,
        'route' => $meta['route'] ?? 'pages.'.$slug,
        'title' => $title,
        'type' => 'app',
    ];
    $converted++;
}

file_put_contents($base.'/config/theme-pages.php', "<?php\n\nreturn ".var_export($pageRegistry, true).";\n");

echo "Converted $converted pages.\n";
echo "Registry written to config/theme-pages.php\n";

function extractBetween(string $html, string $start, string $end): string
{
    $startPos = strpos($html, $start);
    $endPos = strpos($html, $end, $startPos);
    if ($startPos === false || $endPos === false) {
        return '';
    }

    return substr($html, $startPos, $endPos - $startPos);
}

function slugify(string $fileName): string
{
    $name = pathinfo($fileName, PATHINFO_FILENAME);

    return strtolower(str_replace('_', '-', $name));
}
