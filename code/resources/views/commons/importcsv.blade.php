<?php

if (!isset($modal_extras))
    $modal_extras = [];

?>

<button type="button" class="btn btn-default" data-toggle="modal" data-target="#{{ $modal_id }}">{{ _i('Importa CSV') }}</button>
<div class="modal fade wizard" id="{{ $modal_id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ _i('Importa CSV') }}</h4>
            </div>
            <div class="wizard_page">
                <form class="form-horizontal" method="POST" action="{{ url('import/csv?type=' . $import_target . '&step=guess') }}" data-toggle="validator" enctype="multipart/form-data">
                    @foreach($modal_extras as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}" />
                    @endforeach

                    <div class="modal-body">
                        <p>
                            {{ _i('Sono ammessi solo files in formato CSV. Si raccomanda di formattare la propria tabella in modo omogeneo, senza usare celle unite, celle vuote, intestazioni: ogni riga deve contenere tutte le informazioni relative al soggetto. Eventuali prezzi e somme vanno espresse senza includere il simbolo dell\'euro.') }}
                        </p>
                        <p>
                            {{ _i('Una volta caricato il file sarà possibile specificare quale attributo rappresenta ogni colonna trovata nel documento.') }}
                        </p>
                        <p class="text-center">
                            <img src="{{ url('images/csv_explain.png') }}">
                        </p>

                        <hr/>

                        <?php

                        $data = (object)[];
                        foreach($modal_extras as $name => $value) {
                            $data->$name = $value;
                        }

                        ?>

                        @include('commons.filefield', [
                            'obj' => null,
                            'name' => 'file',
                            'label' => _i('File da Caricare'),
                            'mandatory' => true,
                            'extra_class' => 'immediate-run',
                            'extras' => [
                                'data-url' => 'import/csv?type=' . $import_target . '&step=guess',
                                'data-form-data' => json_encode($data),
                                'data-run-callback' => 'wizardLoadPage'
                            ]
                        ])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                        <button type="submit" class="btn btn-success">{{ _i('Avanti') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
