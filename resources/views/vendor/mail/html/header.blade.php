@props(['url'])
@php
    $brand = $lineupMail ?? \App\Support\LineUpMailBranding::data();
    $homeUrl = $url ?: ($brand['websiteUrl'] ?? config('app.url'));
@endphp
<tr>
<td class="header">
<a href="{{ $homeUrl }}" style="display: inline-block; text-decoration: none;">
@if (filled($brand['logoUrl'] ?? null))
<img src="{{ $brand['logoUrl'] }}" class="logo lineup-logo" alt="{{ $brand['clinicName'] }}">
@else
<span style="color: #ffffff; font-size: 22px; font-weight: 600; letter-spacing: 0.02em;">{{ $brand['clinicName'] }}</span>
@endif
</a>
@if (filled($brand['logoUrl'] ?? null))
<p class="lineup-header-tagline">{{ $brand['clinicName'] }}</p>
@endif
</td>
</tr>
