@php
    use App\Models\Setting;

    $supportPhoneDisplay = trim($appSettings['clinic_phone'] ?? Setting::get('clinic_phone', ''));
    $supportPhoneTel = $supportPhoneDisplay !== ''
        ? preg_replace('/[^\d+]/', '', $supportPhoneDisplay)
        : '';
@endphp
@if($supportPhoneTel !== '')
<a href="tel:{{ $supportPhoneTel }}"
   class="lineup-call-support"
   title="Call LineUp Admin — {{ $supportPhoneDisplay }}"
   aria-label="Call Support — LineUp Admin at {{ $supportPhoneDisplay }}">
    <i class="zmdi zmdi-phone-in-talk" aria-hidden="true"></i>
</a>
@endif
