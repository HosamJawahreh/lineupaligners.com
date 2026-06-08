@component('mail::message')
# Your aligner case was updated

Hello **{{ $patientName }}**,

Here is the latest update on your case **{{ $caseId }}** ({{ $caseType }}) from **{{ $clinicName }}**.

@component('mail::panel')
## {{ $eventTitle }}

@if(filled($eventSummary))
**{{ $eventSummary }}**
@endif

@if(filled($eventBody))
{{ $eventBody }}
@endif

@if(filled($eventDate))
**When:** {{ $eventDate }}
@endif

@if(filled($actorName))
**By:** {{ $actorName }}@if(filled($actorRole)) ({{ $actorRole }})@endif
@endif
@endcomponent

**Current status:** {{ $workflowStatus }}

@if(filled($senderName))
This message was sent on your behalf by **{{ $senderName }}** from the {{ $clinicName }} team.
@endif

If you have questions, reply to this email or contact your clinic directly.

Warm regards,<br>
**{{ $clinicName }}**
@endcomponent
