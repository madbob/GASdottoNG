<x-larastrap::mform :obj="$supplier" nosave nodelete>
    <x-larastrap::hidden name="id" />

    <div class="row">
        <div class="col-md-6">
            <x-larastrap::text name="name" :label="_i('Nome')" readonly disabled />
            <x-larastrap::text name="business_name" :label="_i('Ragione Sociale')" readonly disabled />

            <x-larastrap::field :label="_i('Descrizione')">
                <p class="form-control-plaintext">
                    {!! prettyFormatHtmlText($supplier->description) !!}
                </p>
            </x-larastrap::field>

            <x-larastrap::text name="taxcode" :label="_i('Codice Fiscale')" readonly disabled />
            <x-larastrap::text name="vat" :label="_i('Partita IVA')" readonly disabled />
            @include('commons.staticcontactswidget', ['obj' => $supplier])
        </div>
        <div class="col-md-6">
            @include('commons.permissionseditor', ['object' => $supplier, 'master_permission' => 'supplier.modify', 'editable' => $editable])
        </div>
    </div>
</x-larastrap::mform>
