@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof AppAlert === 'undefined') {
        return;
    }

    @if(session('patient_email_sent'))
    Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 6500,
        timerProgressBar: true,
        customClass: {
            popup: 'lineup-toast lineup-toast--email-sent',
            title: 'lineup-toast-title',
        },
    }).fire({
        icon: 'success',
        title: @json(session('success')),
    });
    @else
    AppAlert.success(@json(session('success')));
    @endif
});
</script>
@endif

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    AppAlert.error(@json(session('error')));
});
</script>
@endif

@if(isset($errors) && $errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    AppAlert.error(@json($errors->first()));
});
</script>
@endif
