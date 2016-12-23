<div class="well variants-editor">
    @foreach ($product->variants as $variant)
        <div class="row">
            <input type="hidden" name="variant_id" value="{{ $variant->id }}">
            <input type="hidden" name="variant_offset" value="{{ $variant->has_offset }}">

            <div class="col-md-3 variant_name control-label">
                <span class="variant_name">{{ $variant->name }}</span>
            </div>
            <div class="col-md-6 control-label">
                <span>{{ $variant->printableValues() }}</span>
            </div>
            <div class="col-md-3 text-right">
                <div class="btn btn-warning edit-variant"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></div>
                <div class="btn btn-danger delete-variant"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></div>
            </div>

            <div class="hidden exploded_values">
                @include('commons.manyrows', [
                    'contents' => $variant->values,
                    'columns' => [
                        [
                            'label' => 'Valore',
                            'field' => 'value',
                            'type' => 'text'
                        ],
                        [
                            'label' => 'Differenza Prezzo',
                            'field' => 'price_offset',
                            'type' => 'decimal'
                        ]
                    ]
                ])
            </div>
        </div>
    @endforeach

    <div class="row">
        <div class="col-md-12">
            <button type="button" class="btn btn-default add-variant">Crea Nuova Variante</button>
        </div>
    </div>
</div>
