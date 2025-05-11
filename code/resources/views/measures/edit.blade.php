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
                            'label' => __('generic.id'),
                            'field' => 'id',
                            'type' => 'hidden',
                        ],
                        [
                            'label' => __('generic.name'),
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
                            'label' => __('products.list'),
                            'field' => 'id',
                            'type' => 't',
                            'extra_callback' => function($content, $attributes) {
                                $count = $content->products()->count();
                                if ($count) {
                                    $attributes['value'] = $count;
                                }
                                else {
                                    $attributes['value'] = '-';
                                }

                                return $attributes;
                            }
                        ]
                    ]
                ])
            </div>
        </div>
    </x-larastrap::iform>
</x-larastrap::modal>
