<!DOCTYPE html>
<html lang="{{ htmlLang() }}">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

        <link rel="canonical" href="https://www.gasdotto.net/" />
        <meta name="description" content="Questa è una istanza di GASdotto, il gestionale per l'Economia Solidale" />

        <title>{{ currentAbsoluteGas()->name }} | GASdotto</title>
        <link rel="alternate" type="application/rss+xml" title="{{ _i('Ordini Aperti') }}" href="{{ route('rss') }}"/>

        <link rel="stylesheet" type="text/css" href="{{ mix('/css/gasdotto.css') }}">

        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <meta name="absolute_url" content="{{ route('root') }}"/>
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
            <div>
                <p>
                    {{ _i('GASdotto è sviluppato con modello open source!') }}
                </p>
                <p>
                    {{ _i('Puoi contribuire mandando una segnalazione o una richiesta:') }}
                </p>
                <ul>
                    <li>su GitHub: <a href="https://github.com/madbob/GASdottoNG/" target="_blank">github.com/madbob/GASdottoNG</a></li>
                    <li>via mail: <a href="mailto:info@madbob.org">info@madbob.org</a></li>
                    <li>sulla mailing list degli utenti: <a href="https://groups.google.com/g/gasdotto-dev">groups.google.com/g/gasdotto-dev</a></li>
                </ul>
                <p>
                    {{ _i('O facendo una donazione:') }}
                </p>
                <p>
                    <a href="https://paypal.me/m4db0b" target="_blank"><img src="https://www.gasdotto.net/images/paypal.png" border="0"></a>
                </p>
            </div>
            <p>
                {!! _i('Puoi anche consultate <a href="https://gasdotto.net/" target="_blank">il sito di GASdotto</a> per dare una occhiata alla documentazione, o seguirci <a href="https://twitter.com/GASdottoNet" target="_blank">su Twitter</a> o <a href="https://sociale.network/@gasdottonet" target="_blank">su Mastodon</a> per aggiornamenti periodici.') !!}
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
