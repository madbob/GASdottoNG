<!DOCTYPE html>
<html lang="{{ htmlLang() }}">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

        <link rel="canonical" href="https://www.gasdotto.net/" />
        <meta name="description" content="Questa è una istanza di GASdotto, il gestionale per l'Economia Solidale" />

        <title>{{ currentAbsoluteGas()->name }} | GASdotto</title>
        <link rel="alternate" type="application/rss+xml" title="{{ __('orders.list_open') }}" href="{{ route('rss') }}"/>

        <link rel="stylesheet" type="text/css" href="{{ mix('/css/gasdotto.css') }}">

        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <meta name="absolute_url" content="{{ route('root') }}"/>
        <meta name="current_currency" content="{{ currentAbsoluteGas()->currency }}"/>

        @if(Auth::check())
            <meta name="needs_tour" content="{{ $currentuser->tour ? '0' : '1' }}"/>
        @endif
    </head>
    <body>
        <div id="preloader">
            <img src="{{ asset('images/loading.svg') }}" alt="{{ __('commons.loading') }}">
        </div>

        @if(isset($menu))
            <x-larastrap::navbar id="main-navbar" :options="$menu" :end_options="$end_menu" classes="fixed-top" />
        @endif

        @if(Auth::check())
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
        @endif

        <div class="container-fluid mb-5">
            <div class="row">
                <div class="col-md-12" id="main-contents">
                    @include('commons.flashing')
                    @yield('content')
                </div>
            </div>
        </div>

        <div id="postponed"></div>

        <x-larastrap::modal title="" id="service-modal">
        </x-larastrap::modal>

        @if(Auth::check())
            <x-larastrap::modal title="{{ __('commons.feedback') }}" id="feedback-modal">
                <div>
                    <p>
                        {{ __('commons.about.opensource') }}
                    </p>
                    <p>
                        {{ __('commons.about.contribute') }}
                    </p>
                    <ul>
                        <li>GitHub: <a href="https://github.com/madbob/GASdottoNG/" target="_blank">github.com/madbob/GASdottoNG</a></li>
                        <li>via mail: <a href="mailto:info@madbob.org">info@madbob.org</a></li>
                        <li>sulla mailing list degli utenti: <a href="https://groups.google.com/g/gasdotto-dev">groups.google.com/g/gasdotto-dev</a></li>
                    </ul>
                    <p>
                        {{ __('commons.about.donate') }}
                    </p>
                    <p>
                        <a href="https://paypal.me/m4db0b" target="_blank"><img src="https://www.gasdotto.net/images/paypal.png" border="0" alt="PayPal"></a>
                    </p>
                </div>
                <p>
                    {!! __('commons.about.link') !!}
                </p>
                <p>
                    {{ __('commons.about.local_contact') }}
                </p>
                <ul>
                    @foreach(everybodyCan('gas.permissions', $currentgas) as $admin)
                        <li>{{ $admin->printableName() }} - {{ join(', ', $admin->getContactsByType('email')) }}</li>
                    @endforeach
                </ul>

                <p>
                    {!! __('commons.about.translations') !!}
                </p>
            </x-larastrap::modal>
        @endif

        @if(Session::has('prompt_message'))
            <x-larastrap::modal title="{{ __('commons.warning') }}" id="prompt-message-modal">
                <p class="w-100 h-100 d-flex align-items-center justify-content-center">
                    {!! Session::get('prompt_message') !!}
                </p>
            </x-larastrap::modal>
        @endif

        @include('commons.passwordmodal')

        <script src="{{ mix('/js/gasdotto.js') }}"></script>
        <script src="{{ asset('/js/lang/bootstrap-datepicker.' . htmlLang() . '.min.js') }}"></script>
        <script src="{{ asset('/js/lang/' . htmlLang() . '.js') }}"></script>

        <!-- Piwik -->
        <script type="text/javascript">
            var _paq = _paq || [];
            _paq.push(["setDocumentTitle", document.domain + "/" + document.title]);
            _paq.push(['disableCookies']);
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function() {
                var u="//stats.madbob.org/";
                _paq.push(['setTrackerUrl', u+'piwik.php']);
                _paq.push(['setSiteId', '11']);
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
            })();
        </script>
        <noscript><p><img src="//stats.madbob.org/piwik.php?idsite=11&rec=1" style="border:0;" alt="" /></p></noscript>
        <!-- End Piwik Code -->
    </body>
</html>
