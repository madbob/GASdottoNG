<div class="well variants-editor">
    <div class="row">
        <div class="col-md-12">
            @if($duplicate)
                @if($product->variants()->count() != 0)
                    {{ _i('Il prodotto duplicato avrà una copia delle varianti del prodotto originario. Potranno essere modificate dopo il salvataggio del duplicato.') }}
                @else
                    {{ _i('Non ci sono varianti da duplicare') }}
                @endif
            @else
                <button type="button" class="btn btn-warning add-variant pull-right">{{ _i('Crea Nuova Variante') }}</button>
            @endif
        </div>
    </div>

    @foreach ($product->variants as $variant)
        <div class="row variant-descr">
            <input type="hidden" name="variant_id" value="{{ $variant->id }}">
            <input type="hidden" name="variant_offset" value="{{ $variant->has_offset }}">

            <div class="col-md-3 variant_name control-label">
                <span class="variant_name">{{ $variant->name }}</span>
            </div>
            <div class="col-md-6 control-label">
                <span>{{ $variant->printableValues() }}</span>
            </div>
            <div class="col-md-3 text-right">
                @if($duplicate == false)
                    <div class="btn btn-warning edit-variant"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></div>
                    <div class="btn btn-danger delete-variant"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></div>
                @endif
            </div>

            <div class="hidden exploded_values">
                @include('commons.manyrows', [
                    'contents' => $variant->values,
                    'columns' => [
                        [
                            'label' => _i('Valore'),
                            'field' => 'value',
                            'type' => 'text'
                        ],
                        [
                            'label' => _i('Differenza Prezzo'),
                            'field' => 'price_offset',
                            'type' => 'decimal'
                        ]
                    ]
                ])
            </div>
        </div>
    @endforeach
</div>
