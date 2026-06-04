@component('mail::message')
# {{ $title }}

Hello {{ $name }},

{{ $body }}

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Open in LineUp
@endcomponent

If the button does not work, copy this link into your browser:

{{ $url }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
