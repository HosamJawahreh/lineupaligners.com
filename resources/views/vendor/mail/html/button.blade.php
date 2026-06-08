@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
@php
    $brand = $lineupMail ?? \App\Support\LineUpMailBranding::data();
    $buttonColor = match ($color) {
        'success' => '#16a34a',
        'error' => '#dc2626',
        default => $brand['primaryColor'] ?? '#1a7fd4',
    };
@endphp
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td bgcolor="{{ $buttonColor }}" style="border-radius: 8px; background-color: {{ $buttonColor }};">
<a href="{{ $url }}"
   class="button button-{{ $color }}"
   target="_blank"
   rel="noopener"
   style="background-color: {{ $buttonColor }}; border: 1px solid {{ $buttonColor }}; border-radius: 8px; color: #ffffff !important; display: inline-block; font-size: 16px; font-weight: 600; line-height: 1.25; padding: 12px 28px; text-align: center; text-decoration: none; -webkit-text-size-adjust: none;">
{!! $slot !!}
</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
