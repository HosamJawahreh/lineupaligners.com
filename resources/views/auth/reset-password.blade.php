@php
    use App\Models\Setting;
    $projectName = Setting::projectName();
    $logoUrl = Setting::logoUrl();
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Choose New Password | {{ $projectName }}</title>
    <meta name="description" content="Set a new password for your {{ $projectName }} account.">
    @php $brandName = $projectName; @endphp
    @include('layouts.partials.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/login-page.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/sweetalert-toast.css') }}">
</head>
<body class="login-page">
    <div class="login-page__bg" aria-hidden="true"></div>

    <main class="login-page__main">
        <div class="login-card">
            <div class="login-card__accent" aria-hidden="true"></div>
            <div class="login-card__body">
                <div class="login-card__logo">
                    <img src="{{ $logoUrl }}" alt="{{ $projectName }}">
                </div>
                <p class="login-card__subtitle">Choose a new password</p>
                <p class="login-card__hint">Enter your email and a new password below. The reset link expires after a short time for your security.</p>

                <form method="POST" action="{{ route('password.update') }}" novalidate>
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    @if ($errors->any())
                        <div class="login-alert" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="login-field">
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="login-field__input"
                            placeholder="Email"
                            value="{{ old('email', $email) }}"
                            required
                            autofocus
                            autocomplete="email"
                        >
                        <span class="login-field__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/>
                            </svg>
                        </span>
                    </div>

                    <div class="login-field">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="login-field__input"
                            placeholder="New password"
                            required
                            autocomplete="new-password"
                        >
                        <span class="login-field__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/>
                            </svg>
                        </span>
                    </div>

                    <div class="login-field">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="login-field__input"
                            placeholder="Confirm new password"
                            required
                            autocomplete="new-password"
                        >
                        <span class="login-field__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/>
                            </svg>
                        </span>
                    </div>

                    <button type="submit" class="login-submit">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                        Update password
                    </button>

                    <a href="{{ route('login') }}" class="login-forgot">Back to sign in</a>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('assets/js/alerts.js') }}"></script>
    @include('layouts.partials.flash-sweetalert')
</body>
</html>
