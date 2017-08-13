@extends($theme_layout)

@section('content')

<div class="row">
    <div class="col-md-12">
        @can('users.admin', $currentgas)
            @include('commons.addingbutton', [
                'template' => 'user.base-edit',
                'typename' => 'user',
                'typename_readable' => 'Utente',
                'targeturl' => 'users'
            ])

            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#importCSVusers">Importa CSV</button>

            <div class="modal fade wizard" id="importCSVusers" tabindex="-1" role="dialog" aria-labelledby="importCSVusers">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Importa CSV</h4>
                        </div>
                        <div class="wizard_page">
                            <form class="form-horizontal" method="POST" action="{{ url('import/csv?type=users&step=guess') }}" data-toggle="validator" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <p>
                                        Sono ammessi solo files in formato CSV. Si raccomanda di formattare la propria tabella in modo omogeneo, senza usare celle unite, celle vuote, intestazioni: ogni riga deve contenere tutte le informazioni relative ad un utente.
                                    </p>
                                    <p>
                                        Una volta caricato il file sarà possibile specificare quale attributo rappresenta ogni colonna trovata nel documento.
                                    </p>
                                    <p class="text-center">
                                        <img src="{{ url('images/csv_explain.png') }}">
                                    </p>

                                    <hr/>

                                    @include('commons.filefield', [
                                        'obj' => null,
                                        'name' => 'file',
                                        'label' => 'File da Caricare',
                                        'mandatory' => true,
                                        'extra_class' => 'immediate-run',
                                        'extras' => [
                                            'data-url' => 'import/csv?type=users&step=guess',
                                            'data-run-callback' => 'wizardLoadPage'
                                        ]
                                    ])
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                                    <button type="submit" class="btn btn-success">Avanti</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </div>
</div>

<div class="clearfix"></div>
<hr/>

<div class="row">
    <div class="col-md-12">
        @include('commons.loadablelist', [
            'identifier' => 'user-list',
            'items' => $users,
            'legend' => (object)[
                'class' => 'User'
            ],
            'filters' => [
                'deleted_at' => (object)[
                    'icon' => 'inbox',
                    'label' => 'Eliminati',
                    'value' => null
                ]
            ]
        ])
    </div>
</div>

@endsection
