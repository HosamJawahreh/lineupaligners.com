@component('mail::message')
# New website inquiry

You received a new message from the public website.

@component('mail::panel')
**Name:** {{ $inquirerName }}

**Email:** [{{ $inquirerEmail }}](mailto:{{ $inquirerEmail }})

@if(filled($inquirerPhone))
**Phone:** {{ $inquirerPhone }}
@endif

@if(filled($inquirySubject))
**Subject:** {{ $inquirySubject }}
@endif

**Form:** {{ $formLabel }}
@endcomponent

## Message

{{ $inquiryMessage }}

---

Reply directly to this email to respond to **{{ $inquirerName }}**.

<small>Locale: {{ $locale }} · IP: {{ $ipAddress }}</small>

@endcomponent
