@props(['url'])
@php
    $brand = $lineupMail ?? \App\Support\LineUpMailBranding::data();
    $homeUrl = $url ?: ($brand['websiteUrl'] ?? config('app.url'));
    $brandLabel = $brand['mailBrandLabel'] ?? $brand['clinicName'];
    $primaryColor = $brand['primaryColor'] ?? '#1a7fd4';
@endphp
<tr>
<td class="header" style="text-align: center;">
<a href="{{ $homeUrl }}" style="display: inline-block; text-decoration: none;">
@if (filled($brand['logoUrl'] ?? null))
<img src="{{ $brand['logoUrl'] }}"
     class="logo lineup-logo"
     alt="{{ $brandLabel }}"
     style="border-radius: 12px; display: block; height: auto; margin: 0 auto 10px; max-height: 84px; max-width: 260px; width: auto;">
@else
<span style="color: #ffffff; font-size: 22px; font-weight: 600; letter-spacing: 0.02em;">{{ $brandLabel }}</span>
@endif
</a>
@if (filled($brand['logoUrl'] ?? null))
<p class="lineup-header-tagline"
   style="color: {{ $primaryColor }}; font-size: 14px; font-weight: 600; letter-spacing: 0.04em; margin: 0; text-align: center; text-transform: none;">
    {{ $brandLabel }}
</p>
@endif
</td>
</tr>
