@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    AppAlert.success(@json(session('success')));
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
