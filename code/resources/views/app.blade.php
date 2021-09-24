<!DOCTYPE html>
<html lang="{{ htmlLang() }}">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

        <title>{{ currentAbsoluteGas()->name }} | GASdotto</title>
        <link rel="alternate" type="application/rss+xml" title="{{ _i('Ordini Aperti') }}" href="{{ route('rss') }}"/>

        <link rel="stylesheet" type="text/css" href="{{ mix('/css/gasdotto.css') }}">

        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <meta name="absolute_url" content="{{ url('/') }}"/>
        <meta name="current_currency" content="{{ currentAbsoluteGas()->currency }}"/>
    </head>
    <body>
        <div id="preloader">
            <img src="{{ asset('images/loading.svg') }}" alt="{{ _i('Caricamento in corso') }}">
        </div>

        @if(isset($menu))
            <x-larastrap::navbar :options="$menu" :end_options="$end_menu" classes="fixed-top" />
        @endif

        @if(Auth::check())
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
        @endif

        <div class="container-fluid">
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

        <x-larastrap::modal title="{{ _i('Feedback') }}" id="feedback-modal">
            <p>
                {{ _i('GASdotto è sviluppato con modello open source! Puoi contribuire mandando una segnalazione o una richiesta:') }}
            </p>
            <p>
                <a href="https://github.com/madbob/GASdottoNG/" target="_blank">https://github.com/madbob/GASdottoNG/</a><br>
                <a href="mailto:info@gasdotto.net">info@gasdotto.net</a>
            </p>
            <p>
                {{ _i('o facendo una donazione:') }}
            </p>
            <p>
                <a href="https://paypal.me/m4db0b" target="_blank"><img src="https://www.gasdotto.net/images/paypal.png" border="0"></a>
            </p>
            <p>
                {{ _i('Attenzione: per problemi sui contenuti di questo sito (fornitori, ordini, prenotazioni...) fai riferimento agli amministrazioni del tuo GAS.') }}
            </p>

            @if(currentLang() != 'it_IT')
                <p>
                    {!! _i('Se vuoi contribuire alla traduzione nella tua lingua, visita <a href="https://hosted.weblate.org/projects/gasdottong/translations/">questa pagina</a>.') !!}
                </p>
            @endif
        </x-larastrap::modal>

        @if(Session::has('prompt_message'))
            <x-larastrap::modal title="{{ _i('Attenzione') }}" id="prompt-message-modal">
                <p>
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
