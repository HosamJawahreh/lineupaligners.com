@extends('website.layout')

@section('website-body')
<header class="lineup-public-nav">
    <div class="lineup-public-container lineup-public-nav__inner">
        <a href="#top" class="lineup-public-brand">
            <img src="{{ $logoUrl }}" alt="{{ $projectName }}" width="40" height="40">
            <span>{{ $projectName }}</span>
        </a>
        <nav class="lineup-public-nav__links" aria-label="Primary">
            <a href="#platform">Platform</a>
            <a href="#cases">Results</a>
            <a href="#about">About</a>
            <a href="{{ $loginUrl }}" class="lineup-public-btn lineup-public-btn--sm">Doctor Portal</a>
        </nav>
    </div>
</header>

<main id="top">
    <section class="lineup-public-hero">
        <div class="lineup-public-container lineup-public-hero__grid">
            <div class="lineup-public-hero__copy">
                <span class="lineup-public-eyebrow">{{ $content['hero']['eyebrow'] }}</span>
                <h1>{{ $content['hero']['title'] }}</h1>
                <p>{{ $content['hero']['subtitle'] }}</p>
                <div class="lineup-public-hero__cta">
                    <a href="{{ $loginUrl }}" class="lineup-public-btn">{{ $content['hero']['cta_label'] }}</a>
                    <a href="#platform" class="lineup-public-btn lineup-public-btn--ghost">Explore platform</a>
                </div>
            </div>
            <div class="lineup-public-hero__visual">
                @if($heroImageUrl)
                <img src="{{ $heroImageUrl }}" alt="" class="lineup-public-hero__image">
                @else
                <div class="lineup-public-hero__mockup" aria-hidden="true">
                    <div class="lineup-public-hero__mockup-bar"></div>
                    <div class="lineup-public-hero__mockup-body">
                        <div class="lineup-public-hero__mockup-card"></div>
                        <div class="lineup-public-hero__mockup-card lineup-public-hero__mockup-card--accent"></div>
                        <div class="lineup-public-hero__mockup-card"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>

    @if(count($content['stats']) > 0)
    <section class="lineup-public-stats">
        <div class="lineup-public-container">
            <ul class="lineup-public-stats__list">
                @foreach($content['stats'] as $stat)
                <li>
                    <strong>{{ $stat['value'] }}</strong>
                    <span>{{ $stat['label'] }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </section>
    @endif

    <section class="lineup-public-section" id="about">
        <div class="lineup-public-container lineup-public-section__split">
            <div>
                <span class="lineup-public-eyebrow">About us</span>
                <h2>{{ $content['about']['title'] }}</h2>
                <p class="lineup-public-lead">{{ $content['about']['body'] }}</p>
            </div>
            <div class="lineup-public-about-cards">
                <div class="lineup-public-about-card">
                    <i class="zmdi zmdi-assignment-check"></i>
                    <h3>Treatment planning</h3>
                    <p>Staged aligner plans with doctor review and approval built in.</p>
                </div>
                <div class="lineup-public-about-card">
                    <i class="zmdi zmdi-factory"></i>
                    <h3>Manufacturing</h3>
                    <p>Track production from plan approval through delivery.</p>
                </div>
                <div class="lineup-public-about-card">
                    <i class="zmdi zmdi-shield-check"></i>
                    <h3>Secure collaboration</h3>
                    <p>Private messaging between your clinic and LineUp specialists.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="lineup-public-section lineup-public-section--alt" id="platform">
        <div class="lineup-public-container">
            <div class="lineup-public-section__head">
                <span class="lineup-public-eyebrow">For doctors & clinics</span>
                <h2>{{ $content['platform']['title'] }}</h2>
                <p class="lineup-public-lead">{{ $content['platform']['intro'] }}</p>
            </div>
            <div class="lineup-public-features">
                @foreach($content['features'] as $feature)
                <article class="lineup-public-feature">
                    <span class="lineup-public-feature__icon"><i class="zmdi {{ $feature['icon'] }}"></i></span>
                    <h3>{{ $feature['title'] }}</h3>
                    <p>{{ $feature['description'] }}</p>
                </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="lineup-public-section" id="cases">
        <div class="lineup-public-container">
            <div class="lineup-public-section__head">
                <span class="lineup-public-eyebrow">Clinical outcomes</span>
                <h2>{{ $content['treatments']['title'] }}</h2>
                <p class="lineup-public-lead">{{ $content['treatments']['intro'] }}</p>
            </div>

            @if($showcases->isEmpty())
            <p class="lineup-public-empty-cases">Case studies coming soon.</p>
            @else
            <div class="lineup-public-cases">
                @foreach($showcases as $case)
                <article class="lineup-public-case">
                    <div class="lineup-public-case__compare">
                        <figure>
                            <figcaption>Before</figcaption>
                            @if($case->beforeImageUrl())
                            <img src="{{ $case->beforeImageUrl() }}" alt="Before — {{ $case->title }}" loading="lazy">
                            @else
                            <div class="lineup-public-case__placeholder">Photo coming soon</div>
                            @endif
                        </figure>
                        <figure>
                            <figcaption>After</figcaption>
                            @if($case->afterImageUrl())
                            <img src="{{ $case->afterImageUrl() }}" alt="After — {{ $case->title }}" loading="lazy">
                            @else
                            <div class="lineup-public-case__placeholder">Photo coming soon</div>
                            @endif
                        </figure>
                    </div>
                    <div class="lineup-public-case__body">
                        <div class="lineup-public-case__meta">
                            <span class="lineup-public-case__type">{{ $case->caseTypeLabel() }}</span>
                            @if($case->treatment_months)
                            <span>{{ $case->treatment_months }} months</span>
                            @endif
                        </div>
                        <h3>{{ $case->title }}</h3>
                        @if($case->patient_label)
                        <p class="lineup-public-case__label">{{ $case->patient_label }}</p>
                        @endif
                        @if($case->summary)
                        <p><strong>Challenge:</strong> {{ $case->summary }}</p>
                        @endif
                        @if($case->outcome)
                        <p><strong>Outcome:</strong> {{ $case->outcome }}</p>
                        @endif
                    </div>
                </article>
                @endforeach
            </div>
            @endif
        </div>
    </section>

    <section class="lineup-public-cta-band">
        <div class="lineup-public-container lineup-public-cta-band__inner">
            <div>
                <h2>Ready to partner with {{ $projectName }}?</h2>
                <p>Submit cases, review plans, and manufacture aligners through one dedicated workflow.</p>
            </div>
            <a href="{{ $loginUrl }}" class="lineup-public-btn lineup-public-btn--light">Access doctor dashboard</a>
        </div>
    </section>
</main>

<footer class="lineup-public-footer">
    <div class="lineup-public-container lineup-public-footer__inner">
        <div class="lineup-public-footer__brand">
            <img src="{{ $logoUrl }}" alt="" width="32" height="32">
            <span>{{ $projectName }}</span>
        </div>
        <p>{{ $content['contact']['tagline'] }}</p>
        <div class="lineup-public-footer__contact">
            @if($content['contact']['email'])
            <a href="mailto:{{ $content['contact']['email'] }}">{{ $content['contact']['email'] }}</a>
            @endif
            @if($content['contact']['phone'])
            <span>{{ $content['contact']['phone'] }}</span>
            @endif
        </div>
        <p class="lineup-public-footer__copy">&copy; {{ date('Y') }} {{ $projectName }}. All rights reserved.</p>
    </div>
</footer>
@endsection
