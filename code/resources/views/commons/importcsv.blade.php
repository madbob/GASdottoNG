<?php

if (!isset($modal_extras)) {
    $modal_extras = [];
}

$importer = \App\Importers\CSV\CSVImporter::getImporter($import_target);
$explain_extras = $importer->extraInformations();

?>

<x-larastrap::mbutton classes="d-none d-md-inline-block" :label="_i('Importa CSV')" :triggers_modal="$modal_id" />

<x-larastrap::modal :title="_i('Importa CSV')" id="{{ $modal_id }}">
    <div class="wizard_page">
        <x-larastrap::form method="POST" :action="url('import/csv?type=' . $import_target . '&step=guess')" :buttons="[]">
            @foreach($modal_extras as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}" />
            @endforeach

			@if($explain_extras)
				<p>
					{!! $explain_extras !!}
				</p>

				<hr>
			@endif

            <p>
                {{ _i('Sono ammessi solo files in formato CSV. Si raccomanda di formattare la propria tabella in modo omogeneo, senza usare celle unite, celle vuote, intestazioni: ogni riga deve contenere tutte le informazioni relative al soggetto. Eventuali prezzi e somme vanno espresse senza includere il simbolo dell\'euro.') }}
            </p>

            <p class="text-center">
                <img src="{{ url('images/csv_explain.png') }}" />
            </p>
            <p>
                {{ _i('Una volta caricato il file sarà possibile specificare quale attributo rappresenta ogni colonna trovata nel documento. Non è necessario specificare tutte le colonne previste, tranne quelle obbligatorie.') }}
            </p>

            <hr/>

            <?php

            $data = (object)[];
            foreach($modal_extras as $name => $value) {
                $data->$name = $value;
            }

            ?>

            <x-larastrap::file name="file" :label="_i('File da Caricare')" classes="immediate-run" required :data-url="sprintf('import/csv?type=%s&step=guess', $import_target)" :data-form-data="json_encode($data)" />

            <hr />

            <div class="small">
                <p>
                    {{ _i('Le colonne ammesse per questo tipo di CSV sono:') }}
                </p>
                <ul>
                    @foreach($importer->fields() as $meta)
                        <li>
                            {{ $meta->label }}

                            @if(isset($meta->explain))
                                - {{ $meta->explain }}
                            @endif

                            @if(isset($meta->mandatory) && $meta->mandatory)
                                <span class="badge text-bg-danger">{{ _i('Obbligatorio') }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </x-larastrap::form>
    </div>
</x-larastrap::modal>
