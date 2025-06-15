<x-larastrap::modal>
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
                            'label' => __('texts.generic.id'),
                            'field' => 'id',
                            'type' => 'hidden',
                        ],
                        [
                            'label' => __('texts.generic.name'),
                            'field' => 'name',
                            'type' => 'text',
                            'extra' => [
                                'mandatory' => true
                            ]
                        ],
                        [
                            'label' => __('texts.generic.measures.discrete'),
                            'field' => 'discrete',
                            'help' => __('texts.generic.help.discrete_measure'),
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
                            'label' => __('texts.products.list'),
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
