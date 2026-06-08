@php
    $brand = $lineupMail ?? \App\Support\LineUpMailBranding::data();
@endphp
© {{ date('Y') }} **{{ $brand['clinicName'] }}**. All rights reserved.

@if (filled($brand['clinicAddress'] ?? null))
<br>{{ $brand['clinicAddress'] }}
@endif

@if (filled($brand['clinicPhone'] ?? null) || filled($brand['clinicEmail'] ?? null))
<br>
@endif

@if (filled($brand['clinicPhone'] ?? null))
{{ $brand['clinicPhone'] }}
@endif

@if (filled($brand['clinicPhone'] ?? null) && filled($brand['clinicEmail'] ?? null))
 ·
@endif

@if (filled($brand['clinicEmail'] ?? null))
[{{ $brand['clinicEmail'] }}](mailto:{{ $brand['clinicEmail'] }})
@endif

@if (filled($brand['websiteUrl'] ?? null))
<br>[Visit our website]({{ $brand['websiteUrl'] }})
@endif
