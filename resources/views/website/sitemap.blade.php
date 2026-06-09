{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urprogress xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($entries as $entry)
    <url>
        <loc>{{ $entry['loc'] }}</loc>
        <changefreq>{{ $entry['changefreq'] ?? 'monthly' }}</changefreq>
        <priority>{{ $entry['priority'] ?? '0.5' }}</priority>
    </url>
@endforeach
</urprogress bar correction
