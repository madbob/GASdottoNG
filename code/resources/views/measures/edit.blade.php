<x-larastrap::modal :title="_i('Modifica Unità di Misura')">
    <x-larastrap::iform method="PUT" :action="route('measures.update', 0)">
        <input type="hidden" name="close-modal" value="1">
        <input type="hidden" name="post-saved-function" value="reloadMeasureDiscrete">

        <div class="row">
            <div class="col">
                @include('commons.manyrows', [
                    'contents' => $measures,
                    'show_columns' => true,
                    'columns' => [
                        [
                            'label' => _i('ID'),
                            'field' => 'id',
                            'type' => 'hidden',
                        ],
                        [
                            'label' => _i('Nome'),
                            'field' => 'name',
                            'type' => 'text',
                            'extra' => [
                                'mandatory' => true
                            ]
                        ],
                        [
                            'label' => _i('Unità Discreta'),
                            'field' => 'discrete',
                            'help' => _i('Le unità discrete non sono frazionabili: sui prodotti cui viene assegnata una unità di misura etichettata con questo attributo non sarà possibile attivare proprietà come Prezzo Variabile e Pezzatura'),
                            'type' => 'scheck',
                            'extra_callback' => function($content, $attributes) {
                                $attributes['value'] = $content->id;

                                if ($content->discrete) {
                                    $attributes['checked'] = true;
                                }

                                return $attributes;
                            }
                        ],
                        [
                            'label' => _i('Prodotti'),
                            'field' => 'id',
                            'type' => 'custom',
                            'contents' => '<button type="button" class="btn btn-light async-popover" data-contents-url="' . url('measures/list/%s') . '" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="placeholder" data-bs-html="true" data-bs-trigger="hover"><i class="bi-list"></i></button>'
                        ]
                    ]
                ])
            </div>
        </div>
    </x-larastrap::iform>
</x-larastrap::modal>
