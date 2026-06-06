@php

    $nav = $content['navigation'] ?? [];

    $contact = $content['contact'] ?? [];

    $utility = $nav['footer_utility'] ?? [];

    $footerColumns = $nav['footer_columns'] ?? [];

    $servicesColumn = $nav['services_column'] ?? [];

    $newsletter = $nav['newsletter'] ?? [];

    $bottomLinks = $nav['bottom_links'] ?? [];

    $socialLinks = collect($nav['social_links'] ?? [])->filter(fn (array $row) => filled($row['url'] ?? ''));

    $companyColumn = $footerColumns[0] ?? ['title' => __('website.our_company'), 'links' => []];

    $newsletterTitle = filled($newsletter['title'] ?? '') ? $newsletter['title'] : __('website.newsletter_title');

    $newsletterBlurb = str_replace('{project}', $projectName, $newsletter['blurb'] ?? '');

@endphp

<footer class="site-footer pbmit-bg-color-white">

    <div class="pbmit-footer-big-area-wrapper">

        <div class="footer-wrap pbmit-footer-big-area">

            <div class="container">

                <div class="row align-items-center justify-content-between">

                    <div class="col-md-12">

                        <div class="pbmit-footer-info-inner">

                            @foreach($utility as $box)

                            @php

                                $source = $box['source'] ?? 'phone';

                                $boxTitle = $box['label'] ?? '';

                                $value = match ($source) {

                                    'phone' => $contact['phone'] ?? '',

                                    'email' => $contact['email'] ?? '',

                                    'address' => $contact['address'] ?? '',

                                    'chat' => $box['chat_label'] ?? __('website.chat_with_us'),

                                    default => '',

                                };

                                $linkUrl = $websiteContent->utilityLinkUrl($source, $contact, $websiteLocale ?? null);

                                $boxClass = 'pbmit-footer-box-'.($loop->iteration);

                            @endphp

                            <div class="pbmit-footer-box {{ $boxClass }}">

                                <div class="pbmit-footer-box-icon">

                                    <div class="pbmit-icon-type-icon">

                                        <div class="pbmit-footer-icon-wrap">

                                            @if($source === 'phone')

                                            <svg height="512" viewBox="0 0 512 512" width="512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><g><path d="m256 30a226.06 226.06 0 0 1 88 434.25 226.06 226.06 0 0 1 -176-416.5 224.5 224.5 0 0 1 88-17.75m0-30c-141.38 0-256 114.62-256 256s114.62 256 256 256 256-114.62 256-256-114.62-256-256-256z"></path><path d="m330.69 393.87c-14.87-1-35.83-6.13-56.29-13.45-72.14-25.82-142.53-94.61-157.49-190.83-2.66-17.13.14-32.78 13.12-45.52 4.35-4.26 8.22-9 12.47-13.36 16-16.47 39.38-16.89 55.95-1.07 5.25 5 10.59 9.93 15.71 15.09a38.07 38.07 0 0 1 1.37 52.79c-4 4.44-8.2 8.66-12.42 12.87-4.61 4.6-10.34 7.24-16.49 9.16-7.59 2.38-9 5.56-5.55 12.81q32.7 68.49 102.37 98.63c6.21 2.68 9.08 1.47 11.58-4.69 5.48-13.51 15.53-23.36 27.08-31.32 13.07-9 31.79-7 44.17 3.64a263.23 263.23 0 0 1 19.43 18.5 38.22 38.22 0 0 1 -.05 52.25c-1.93 2.1-3.92 4.15-5.77 6.31-11.14 12.95-25.27 19.01-49.19 18.19z"></path></g></svg>

                                            @elseif($source === 'email')

                                            <svg enable-background="new 0 0 54 54" viewBox="0 0 54 54" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="m50.726223 27.4150085c-1.0500488-2.0599976-3.5400391-2.960022-5.6700439-2.0599976l-1.5299072.6600342v-14.460022c0-1.3225851-1.1295853-3.0100098-3.0100098-3.0100098h-23.33008c-1.8339577 0-2.9799805 1.6915035-2.9799805 2.6400146-.058075.3480978-.0195894-.5360966-.0300293 15.4099731l-3.130127 2.0499878c-.8031416-1.1687469-2.377079-1.4796925-3.5498042-.8399658l-3.0500488 1.6499634c-1.2700198.6800537-1.8000491 2.25-1.1899416 3.5600586l4.9699704 10.839966c.6825027 1.4510994 2.3978977 2.0105019 3.7900391 1.2800293l3.0898438-1.6500244c1.25-.6599731 1.7900391-2.1799927 1.2299805-3.4899902 1.0600586-.2600098 2.1601563-.2900391 3.2299805-.1099854l8.7900391 1.5c.5.0799561 1 .1199951 1.4902344.1199951 1.6298828 0 3.239748-.4500122 4.6499043-1.3099976l15.3099365-9.4900513c1.1153257-.6840095 1.5054281-2.1188125.9200437-3.289978zm-15.7094727 2.2598267h-.140625c-.1098633-.5299683-.3198242-1.0299072-.6398926-1.4797974-.5900879-.8500366-1.4799805-1.4100342-2.5000019-1.5800171l-9.1799316-1.5700073 3.6298828-3.0299683.4499512.3699951c1.2803364 1.0800095 3.1598816 1.0799828 4.4401855 0l.4399414-.3699951 7.2500019 6.0499878zm-17.8305683-19.6298218h23.33008c.7999268 0 1.3926582.6493101 1.4799805 1.2600098-.1021347.0853729-12.6704578 10.5907593-11.8801289 9.9299927-.7299805.6099854-1.7897949.6099854-2.5197754 0-2.0226154-1.6886101-1.1271725-.9406013-11.9001465-9.9299927.1410209-.7042437.7288838-1.2600098 1.4899903-1.2600098zm-5.2099609 31.8099976-.4699707.2600098c-.3383923.184391-.8013306.0851898-1.0200195-.289978-.1999512-.3700562-.0700684-.8200073.2900391-1.0200195l.4699707-.2600098c.3598633-.2000122.8198242-.0800171 1.0200195.289978.1999511.3600463.0698242.8099975-.2900391 1.0200195zm37.040041-12.4199829-15.3100586 9.4799805c-1.5200195.9400024-3.33008 1.289978-5.0900898.9899902l-8.7897949-1.5c-1.3701172-.2399902-2.7802734-.1699829-4.130127.2000122l-3.9699707-8.5999756c.1002312-.0658245 4.6606369-3.0607891 4.3701172-2.8699951 2.1714191-1.5694237 4.3639069-.8882732 4.5598145-.8900146l10.8200684 1.8499756c1.2160397.2123356 2.1571217 1.319706 1.9399433 2.7300415-.2099609 1.25-1.4199238 2.1499634-2.7299824 1.9299927l-6.1000977-1.0500488c-.4099121-.0599976-.7998047.210022-.869873.6100464-.0700684.4099731.1999512.7999878.6101074.8699951l6.1098633 1.0499878c1.9325237.351223 3.8841839-.8572121 4.3999043-2.8499756 12.1256409-5.227169 1.6889191-.7266579 10.8100586-4.6500244 1.4100342-.5900269 3.0500488 0 3.7399902 1.3499756.249813.5348834.0477141 1.0854949-.3698731 1.3500365z"></path></svg>

                                            @elseif($source === 'address')

                                            <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="m43.7 24.66c0-6.44-5.25-11.69-11.7-11.69s-11.7 5.25-11.7 11.69 5.25 11.7 11.7 11.7 11.7-5.25 11.7-11.7z"></path><path d="m31.33 61.75c.19.17.43.25.67.25s.48-.08.67-.25c.89-.81 21.99-19.88 21.99-37.09 0-12.49-10.16-22.66-22.66-22.66s-22.66 10.17-22.66 22.66c0 17.21 21.1 36.28 21.99 37.09zm.67-57.75c11.39 0 20.66 9.27 20.66 20.66 0 14.66-17.05 31.56-20.66 34.98-3.62-3.42-20.66-20.31-20.66-34.98 0-11.39 9.27-20.66 20.66-20.66z"></path></svg>

                                            @else

                                            <svg enable-background="new 0 0 55 55" viewBox="0 0 55 55" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="m21.5697021 34.1262817h-10.8833007c-.4140625 0-.75.3359375-.75.75s.3359375.75.75.75h10.8833008c.4140625 0 .75-.3359375.75-.75s-.3359376-.75-.7500001-.75z"></path><path d="m35.8450928 5.949584c-7.5899658 0-13.9299316 4.5800171-15.3999023 10.6500254v.0003052h-13.2402344c-2.0678711 0-3.75 1.6826172-3.75 3.75v18.5996094c0 2.0673828 1.6821289 3.75 3.75 3.75h1.8500977v4.5898438c0 1.5756874 1.9022541 2.3462677 2.9897461 1.2412109l5.840332-5.8310547h16.9897461c2.0678711 0 3.75-1.6826172 3.75-3.75v-6.5498657c.1015434-.0175934.7044411-.071701 1.880249-.3800659.5100098-.1300049 1.0699463-.0100098 1.4899902.3200073l3.1700439 2.4799805c1.1029778.8540573 2.7600098.1029053 2.7600098-1.3499756v-4.9899902c0-.4300537.1499023-.8300171.4099121-1.1200562 2.1000977-2.3299561 3.2099609-5.1199951 3.2099609-8.0799561 0-7.3500376-7.0499267-13.3300181-15.6999511-13.3300181zm-.75 20.0100107v-.289978c0-.4100342.3300781-.75.75-.75.4100342 0 .75.3399658.75.75v.289978c0 .4099731-.3399658.75-.75.75-.4199219 0-.75-.3400268-.75-.75zm1.5-5.4600219v2.2999878c0 .4200439-.3399658.75-.75.75-.4199219 0-.75-.3299561-.75-.75v-2.9899902c0-.4099731.3300781-.75.75-.75 1.5700684 0 2.8500977-1.2799683 2.8500977-2.8499756s-1.2800293-2.8500376-2.8500977-2.8500376-2.8499756 1.2800293-2.8499756 2.8500376c0 .4099731-.3399658.75-.75.75-.4200439 0-.75-.3400269-.75-.75 0-2.4000254 1.9500732-4.3500376 4.3499756-4.3500376 2.4000244 0 4.3500977 1.9500122 4.3500977 4.3500376-.0000001 2.1400146-1.5600587 3.9299926-3.6000977 4.289978zm-1.7202149 20.6999511h-17.2998047c-.1987305 0-.3891602.0791016-.5297852.21875l-6.0649414 6.0556641c-.1568232.1576195-.425293.0514565-.425293-.1845703v-5.3398438c0-.4140625-.3359375-.75-.75-.75h-2.6000975c-1.2407227 0-2.25-1.0097656-2.25-2.25v-18.5996093c0-1.2402344 1.0092773-2.25 2.25-2.25h12.9802246c-.1881084 1.8314857.0571899 3.7361622.8199463 5.5697021h-10.3199463c-.4101563 0-.75.3399658-.75.75 0 .4099731.3398438.75.75.75h11.0599365c.8099365 1.3999634 1.9100342 2.6699829 3.2299805 3.7299805.0001221.000061.0001221.000061.0001221.0001221h-14.2902833c-.4140625 0-.75.3359375-.75.75s.3359375.75.75.75h16.4904785c3.1530914 1.7924309 6.9823875 2.4523354 9.9494629 2.1698608v6.3799438c0 1.2402344-1.0092773 2.25-2.25 2.25z"></path></svg>

                                            @endif

                                        </div>

                                    </div>

                                </div>

                                <div class="pbmit-footer-content-wrap">

                                    <span class="pbmit-footer-box-title">{{ $boxTitle }}</span>

                                    <span class="pbmit-footer-box-content">

                                        @if(in_array($source, ['phone', 'email', 'chat'], true) && filled($contact[$source === 'chat' ? 'email' : $source] ?? ''))

                                        <a href="{{ $linkUrl }}">{{ $source === 'chat' ? $value : $contact[$source] }}</a>

                                        @elseif($source === 'address')

                                        @if(filled($contact['address'] ?? ''))
                                        <a href="{{ $linkUrl }}">{{ $contact['address'] }}</a>
                                        @else
                                        {{ __('website.add_address_hint') }}
                                        @endif

                                        @else

                                        {{ $value ?: __('website.add_'.$source.'_hint') }}

                                        @endif

                                    </span>

                                </div>

                            </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="footer-wrap pbmit-footer-widget-area">

        <div class="container">

            <div class="row">

                <div class="col-md-6 col-lg-3 pbmit-footer-widget-col-1">

                    <aside class="widget">

                        <div class="textwidget">

                            <div class="pbmit-footer-logo">

                                <a href="{{ $websiteHomeUrl ?? route('website.home') }}">
                                    @include('website.smiliz.partials.logo-img', ['width' => 160, 'height' => 64])
                                </a>

                            </div>

                            <div>{{ $contact['tagline'] ?? '' }}</div>

                            @if($socialLinks->isNotEmpty())

                            <ul class="pbmit-social-links">

                                @foreach($socialLinks as $social)

                                @php

                                    $network = $social['network'] ?? 'link';

                                    $iconClass = match ($network) {

                                        'facebook' => 'pbmit-base-icon-facebook-logo',

                                        'twitter' => 'pbmit-base-icon-twitter-2',

                                        'instagram' => 'pbmit-base-icon-instagram',

                                        'youtube' => 'pbmit-base-icon-youtube-play',

                                        default => 'pbmit-base-icon-link',

                                    };

                                @endphp

                                <li class="pbmit-social-li pbmit-social-{{ $network }}">

                                    <a title="{{ $social['title'] ?? ucfirst($network) }}" href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">

                                        <span><i class="{{ $iconClass }}"></i></span>

                                    </a>

                                </li>

                                @endforeach

                            </ul>

                            @endif

                        </div>

                    </aside>

                </div>

                @if(filled($companyColumn['title'] ?? '') && ! empty($companyColumn['links']))

                <div class="col-md-6 col-lg-3 pbmit-footer-widget-col-2">

                    <aside class="widget pbmit-two-column-menu">

                        <h2 class="widget-title">{{ $companyColumn['title'] }}</h2>

                        <ul class="menu">

                            @foreach($companyColumn['links'] as $link)

                            <li><a href="{{ $websiteContent->resolveNavLink($link, $websiteLocale ?? null) }}">{{ $link['label'] }}</a></li>

                            @endforeach

                        </ul>

                    </aside>

                </div>

                @endif

                @if(! empty($servicesColumn['enabled']))

                <div class="col-md-6 col-lg-3 pbmit-footer-widget-col-3">

                    <aside class="widget">

                        <h2 class="widget-title">{{ $servicesColumn['title'] ?? __('website.our_services') }}</h2>

                        <ul class="menu">

                            @if(! empty($servicesColumn['use_features']))

                                @foreach(array_slice($content['features'] ?? [], 0, (int) ($servicesColumn['feature_limit'] ?? 5)) as $feature)

                                <li><a href="{{ $websiteContent->serviceLinkUrl($feature, $servicesColumn['feature_link'] ?? '#services') }}">{{ $feature['title'] }}</a></li>

                                @endforeach

                            @endif

                        </ul>

                    </aside>

                </div>

                @endif

                @if(! empty($newsletter['enabled']))

                <div class="col-md-6 col-lg-3 pbmit-footer-widget-col-4">

                    <aside class="widget">

                        <h2 class="widget-title">{{ $newsletterTitle }}</h2>

                        @if(filled($newsletterBlurb))

                        <div class="textwidget">{{ $newsletterBlurb }}</div>

                        @endif

                    </aside>

                    <aside class="widget pbmit-mailchip-spacing">

                        <div class="textwidget">

                            <form action="{{ route(app(\App\Services\WebsiteLocale::class)->routeName('website.inquiry.store')) }}" method="post" data-lineup-newsletter="1" novalidate>
                                @csrf
                                <div class="lineup-honeypot" aria-hidden="true"><input type="text" name="website_hp" tabindex="-1" autocomplete="off" value=""></div>
                                <div class="pbmit-footer-newsletter">

                                    <input type="email" class="form-control" name="email" placeholder="{{ __('website.newsletter_placeholder') }}" required>

                                    <button class="pbmit-form-btn" type="submit" value="{{ __('website.newsletter_button') }}" aria-label="{{ __('website.newsletter_aria') }}">

                                        <span class="pbmit-button-inner">

                                            <span class="pbmit-button-icon">

                                                <i class="pbmit-base-icon-mail-1"></i>

                                            </span>

                                        </span>

                                    </button>

                                </div>

                            </form>

                        </div>

                    </aside>

                </div>

                @endif

            </div>

        </div>

    </div>

    <div class="pbmit-footer-text-area">

        <div class="container">

            <div class="pbmit-footer-text-inner">

                <div class="row">

                    <div class="col-md-6">

                        <div class="pbmit-footer-copyright-text-area">

                            Copyright © {{ date('Y') }} <a href="{{ $websiteHomeUrl ?? route('website.home') }}">{{ $projectName }}</a>, {{ __('website.copyright') }}

                        </div>

                    </div>

                    @if(! empty($bottomLinks))

                    <div class="col-md-6">

                        <div class="pbmit-footer-menu-area">

                            <ul class="pbmit-footer-menu">

                                @foreach($bottomLinks as $link)

                                <li class="menu-item">

                                    <a href="{{ $websiteContent->resolveNavLink($link, $websiteLocale ?? null) }}">{{ $link['label'] }}</a>

                                </li>

                                @endforeach

                            </ul>

                        </div>

                    </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</footer>

