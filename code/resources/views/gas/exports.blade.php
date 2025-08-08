<x-larastrap::accordionitem :label_html="formatAccordionLabel('gas.import_export', 'cloud-plus')">
    <div class="row">
        <div class="col">
            <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
                <input type="hidden" name="group" value="import">

                <div class="col">
                    @if(env('HUB_URL'))
                        <x-larastrap::check name="es_integration" tlabel="gas.enable_hub" tpophelp="gas.help.enable_hub" />
                    @endif

                    <x-larastrap::text name="csv_separator" tlabel="gas.csv_separator" tpophelp="gas.help.csv_separator" />
                </div>
            </x-larastrap::form>

            <hr>

            <x-larastrap::field tlabel="gas.import" tpophelp="gas.help.import">
                <x-larastrap::mbutton tlabel="export.import.gdxp" triggers_modal="#importGDXP" />
                @push('postponed')
                    <x-larastrap::modal id="importGDXP" classes="wizard">
                        <div class="wizard_page">
                            <x-larastrap::form method="POST" :action="url('import/gdxp?step=read')">
                                <p>
                                    {{ __('texts.gas.help.gdxp_explain') }}
                                </p>

                                <hr/>

                                <x-larastrap::file name="file" tlabel="generic.file" classes="immediate-run" required :data-url="url('import/gdxp?step=read')" />
                            </x-larastrap::form>
                        </div>
                    </x-larastrap::modal>
                @endpush
            </x-larastrap::field>

            <x-larastrap::field tlabel="export.export.database">
                <a href="{{ route('gas.dumpdb') }}" class="btn btn-light">{{ __('texts.generic.download') }} <i class="bi-download"></i></a>
            </x-larastrap::field>
        </div>
    </div>
</x-larastrap::accordionitem>
