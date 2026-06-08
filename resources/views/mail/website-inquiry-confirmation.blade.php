@component('mail::message')
# Thank you, {{ $inquirerName }}

We received your message and our team will get back to you soon.

@component('mail::panel')
**Your message**

{{ $inquiryMessage }}
@endcomponent

If you need to add anything, simply reply to this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
