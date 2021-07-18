<div class="variants-editor" id="variants_editor_{{ sanitizeId($product->id) }}" data-reload-url="{{ route('variants.show', $product->id) }}">
    <div class="row">
        <div class="col">
            @if($duplicate)
                @if($product->variants()->count() != 0)
                    {{ _i('Il prodotto duplicato avrà una copia delle varianti del prodotto originario. Potranno essere modificate dopo il salvataggio del duplicato.') }}
                @else
                    {{ _i('Non ci sono varianti da duplicare') }}
                @endif
            @else
                <x-larastrap::ambutton :label="_i('Crea Nuova Variante')" :data-modal-url="route('variants.create', ['product_id' => $product->id])" />

                @if($product->variantCombos->isEmpty() == false)
                    <x-larastrap::ambutton :label="_i('Modifica Matrice Varianti')" :data-modal-url="route('variants.matrix', $product->id)" />
                @endif
            @endif
        </div>
    </div>

    @if($product->variants->isEmpty() == false)
        <br>
        <table class="table">
            @foreach($product->variants as $variant)
                <tr data-variant-id="{{ $variant->id }}">
                    <td width="30%">{{ $variant->name }}</td>
                    <td width="50%">{{ $variant->printableValues() }}</td>

                    <td width="20%">
                        @if($duplicate == false)
                            <a class="btn btn-warning async-modal" data-modal-url="{{ route('variants.edit', $variant->id) }}"><i class="bi-pencil"></i></a>
                            <div class="btn btn-danger delete-variant"><i class="bi-x-lg"></i></div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
</div>
