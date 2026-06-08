@component('mail::message')
# Reset your password

Hello **{{ $userName }}**,

We received a request to reset the password for your **{{ $clinicName }}** account. Use the button below to choose a new password.

@component('mail::button', ['url' => $resetUrl, 'color' => 'primary'])
Reset password
@endcomponent

@component('mail::panel')
**This link expires in {{ $expiresMinutes }} minutes** and can only be used once.

If you did not request a password reset, you can safely ignore this email. Your password will remain unchanged.
@endcomponent

For your security, do not share this link with anyone.

@if (filled($lineupMail['clinicEmail'] ?? null))
If you need assistance, contact us at [{{ $lineupMail['clinicEmail'] }}](mailto:{{ $lineupMail['clinicEmail'] }}).
@endif

Regards,<br>
**{{ $clinicName }}**

@slot('subcopy')
If you have trouble using the button above, copy and paste this URL into your browser:

{{ $resetUrl }}
@endslot
@endcomponent
