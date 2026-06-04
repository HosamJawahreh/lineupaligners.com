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

    <title>Sign In | {{ $projectName }}</title>
    <meta name="description" content="Sign in to {{ $projectName }} — aligner case management.">

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

                <p class="login-card__subtitle">Sign in to start your session</p>



                <form method="POST" action="{{ route('login') }}" novalidate>

                    @csrf



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

                            value="{{ old('email') }}"

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

                            placeholder="Password"

                            required

                            autocomplete="current-password"

                        >

                        <span class="login-field__icon" aria-hidden="true">

                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">

                                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/>

                            </svg>

                        </span>

                    </div>



                    <label class="login-remember" for="remember">

                        <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>

                        Remember Me

                    </label>



                    <button type="submit" class="login-submit">

                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">

                            <path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/>

                        </svg>

                        Log in

                    </button>



                    <a href="{{ route('pages.forgot-password') }}" class="login-forgot">Forgot password?</a>

                </form>

            </div>

        </div>

    </main>



    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25/dist/sweetalert2.all.min.js"></script>

    <script src="{{ asset('assets/js/alerts.js') }}"></script>

    @include('layouts.partials.flash-sweetalert')

</body>

</html>

