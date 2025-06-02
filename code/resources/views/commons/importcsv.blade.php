@php

if (!isset($modal_extras)) {
    $modal_extras = [];
}

$importer = \App\Importers\CSV\CSVImporter::getImporter($import_target);
$explain_extras = $importer->extraInformations();

@endphp

<x-larastrap::mbutton classes="d-none d-md-inline-block" tlabel="export.import.csv" :triggers_modal="$modal_id" />

<x-larastrap::modal id="{{ $modal_id }}">
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

            <p>{{ __('export.help.csv_instructions') }}</p>

            <p class="text-center">
                <img src="{{ url('images/csv_explain.png') }}" alt="{{ __('export.help.img_csv_instructions') }}" />
            </p>

            <p>{{ __('export.help.selection_instructions') }}</p>

            <hr/>

            <?php

            $data = (object)[];
            foreach($modal_extras as $name => $value) {
                $data->$name = $value;
            }

            ?>

            <x-larastrap::file name="file" tlabel="generic.file" classes="immediate-run" required :data-url="sprintf('import/csv?type=%s&step=guess', $import_target)" :data-form-data="json_encode($data)" />

            <hr />

            <div class="small">
                <p>{{ __('export.accepted_columns') }}</p>

                <ul>
                    @foreach($importer->fields() as $meta)
                        <li>
                            {{ $meta->label }}

                            @if(isset($meta->explain))
                                - {{ $meta->explain }}
                            @endif

                            @if(isset($meta->mandatory) && $meta->mandatory)
                                <span class="badge text-bg-danger">{{ __('generic.mandatory') }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </x-larastrap::form>
    </div>
</x-larastrap::modal>
