@php
    $brand = $lineupMail ?? \App\Support\LineUpMailBranding::data();
@endphp
@component('mail::message')
# {{ $title }}

Hello **{{ $name }}**,

{{ $body }}

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Open in {{ $brand['projectName'] }}
@endcomponent

If the button does not work, copy this link into your browser:

{{ $url }}

Thanks,<br>
**{{ $brand['clinicName'] }}**
@endcomponent
