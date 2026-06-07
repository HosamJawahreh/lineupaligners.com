@if($content['sections']['process'] ?? true)

@php
    $isHomepageTwo = ($content['template'] ?? '') === 'smiliz-homepage-2';
    $processSteps = $content['process']['steps'] ?? [];
@endphp

<section @class([
    'lineup-process-section',
    'section-lg pbmit-bg-color-light bottom-radius position-relative' => ! $isHomepageTwo,
    'pricing-section-two section-lg bottom-radius pbmit-bg-color-light' => $isHomepageTwo,
]) id="process">
    @include('website.smiliz.partials.section-grid-atmosphere')

    <div class="lineup-process-showcase" data-step-count="{{ count($processSteps) }}">
        <div class="lineup-process-showcase__pin-spacer">
            <div class="lineup-process-showcase__pin-sticky">
                <div class="container lineup-process-section__container position-relative">
                    @if($isHomepageTwo)
                    <div class="pbmit-heading-subheading row animation-style2 lineup-process-section__intro" data-aos="fade-up" data-aos-duration="700">
                        <div class="col-md-4 full-width-1200">
                            <span class="lineup-process-section__pill">{{ $content['process']['subtitle'] }}</span>
                        </div>
                        <div class="col-md-8 full-width-1200">
                            <h2 class="pbmit-title lineup-process-section__title">{!! nl2br(e($content['process']['title'])) !!}</h2>
                        </div>
                    </div>
                    @else
                    <div class="pbmit-heading-subheading text-center animation-style2 lineup-process-section__intro lineup-process-section__intro--center" data-aos="fade-up" data-aos-duration="700">
                        <span class="lineup-process-section__pill">{{ $content['process']['subtitle'] }}</span>
                        <h2 class="pbmit-title lineup-process-section__title">{!! nl2br(e($content['process']['title'])) !!}</h2>
                    </div>
                    @endif

                    {{-- Desktop / tablet: scroll-driven interactive tour --}}
                    <div class="lineup-process-showcase__tour">
                        <nav class="lineup-process-showcase__nav" aria-label="{{ $content['process']['subtitle'] ?? __('website.process_step_label', ['num' => '01']) }}">
                            <div class="lineup-process-showcase__rail" aria-hidden="true">
                                <span class="lineup-process-showcase__rail-track"></span>
                                <span class="lineup-process-showcase__rail-fill"></span>
                            </div>
                            @foreach($processSteps as $step)
                            @php $stepNum = str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT); @endphp
                            <button type="button"
                                    class="lineup-process-showcase__tab{{ $loop->first ? ' is-active' : '' }}"
                                    data-step-index="{{ $loop->index }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                <span class="lineup-process-showcase__tab-num">{{ $stepNum }}</span>
                                <span class="lineup-process-showcase__tab-body">
                                    <span class="lineup-process-showcase__tab-title">{{ $step['title'] }}</span>
                                    <span class="lineup-process-showcase__tab-desc">{{ $step['description'] }}</span>
                                </span>
                            </button>
                            @endforeach
                        </nav>

                        <div class="lineup-process-showcase__stage">
                            <div class="lineup-process-showcase__stage-glow" aria-hidden="true"></div>
                            @foreach($processSteps as $step)
                            <figure class="lineup-process-showcase__panel{{ $loop->first ? ' is-active' : '' }}"
                                    data-step-index="{{ $loop->index }}"
                                    aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                                <div class="lineup-process-showcase__device">
                                    <div class="lineup-process-showcase__device-bar">
                                        <span class="lineup-process-showcase__device-dots" aria-hidden="true"><span></span><span></span><span></span></span>
                                        <span class="lineup-process-showcase__device-url">
                                            <svg viewBox="0 0 24 24" width="11" height="11" aria-hidden="true"><path fill="currentColor" d="M12 1a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V11a2 2 0 0 0-2-2h-1V6a5 5 0 0 0-5-5zm-3 8V6a3 3 0 1 1 6 0v3H9z"/></svg>
                                            {{ __('website.process_dashboard_url') }}
                                        </span>
                                    </div>
                                    <div class="lineup-process-showcase__device-screen">
                                        <img src="{{ $websiteContent->processStepImageUrl($step, $loop->index) }}"
                                             alt="{{ $step['title'] }}"
                                             loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                             decoding="async">
                                    </div>
                                </div>
                            </figure>
                            @endforeach
                        </div>
                    </div>

                    {{-- Mobile: stacked journey cards --}}
                    <ol class="lineup-process-showcase__stack">
                        @foreach($processSteps as $step)
                        @php $stepNum = str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT); @endphp
                        <li class="lineup-process-showcase__stack-item"
                            data-aos="fade-up"
                            data-aos-duration="600"
                            data-aos-delay="{{ min($loop->index * 80, 320) }}">
                            <span class="lineup-process-showcase__stack-num">{{ $stepNum }}</span>
                            <div class="lineup-process-showcase__stack-copy">
                                <h3>{{ $step['title'] }}</h3>
                                <p>{{ $step['description'] }}</p>
                            </div>
                            <div class="lineup-process-showcase__stack-device">
                                <img src="{{ $websiteContent->processStepImageUrl($step, $loop->index) }}"
                                     alt="{{ $step['title'] }}"
                                     loading="lazy"
                                     decoding="async">
                            </div>
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>

@once
@push('scripts')
<script src="{{ asset('assets/js/lineup-process-section.js') }}?v=15" defer></script>
@endpush
@endonce

@endif
