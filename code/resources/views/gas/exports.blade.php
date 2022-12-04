<x-larastrap::accordionitem :label="_i('Importa/Esporta')">
    <div class="row">
        <div class="col">
            <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
                <input type="hidden" name="group" value="import">

                <div class="col">
                    @if(env('HUB_URL'))
                        <x-larastrap::check name="es_integration" :label="_i('Integrazione Hub Economia Solidale')" :pophelp="_i('Abilita alcune funzioni (sperimentali!) di integrazione con hub.economiasolidale.net, tra cui l\'aggiornamento automatico dei listini e l\'aggregazione degli ordini con altri GAS.')" />
                    @endif

                    <?php $csv_separator_help = _i('Il carattere da usare per separare i campi in tutti i CSV esportati. La scelta di questo valore dipende soprattutto dall\'applicazione che si utilizza per aprire questi files. Si consiglia l\'uso di <a target="_blank" href="http://it.libreoffice.org/">LibreOffice</a>.') ?>
                    <x-larastrap::text name="csv_separator" :label="_i('Separatore Files CSV Esportati')" :pophelp="$csv_separator_help" />
                </div>
            </x-larastrap::form>

            <hr>

            <x-larastrap::field :label="_i('Importazione')" :pophelp="_i('Da qui è possibile importare un file GDXP generato da un\'altra istanza di GASdotto o da qualsiasi altra piattaforma che supporta il formato')">
                <x-larastrap::mbutton :label="_i('Importa GDXP')" triggers_modal="#importGDXP" />
                @push('postponed')
                    <x-larastrap::modal :title="_i('Importa GDXP')" id="importGDXP" classes="wizard">
                        <div class="wizard_page">
                            <x-larastrap::form method="POST" :action="url('import/gdxp?step=read')">
                                <p>
                                    {{ _i("GDXP è un formato interoperabile per scambiare listini e ordini tra diversi gestionali. Da qui puoi importare un file in tale formato.") }}
                                </p>

                                <hr/>

                                <x-larastrap::file name="file" :label="_i('File da Caricare')" classes="immediate-run" required :data-url="url('import/gdxp?step=read')" />
                            </x-larastrap::form>
                        </div>
                    </x-larastrap::modal>
                @endpush
            </x-larastrap::field>

            <x-larastrap::field :label="_i('Esporta database')">
                <a href="{{ route('gas.dumpdb') }}" class="btn btn-light">{{ _i('Download') }} <i class="bi-download"></i></a>
            </x-larastrap::field>
        </div>
    </div>
</x-larastrap::accordionitem>
