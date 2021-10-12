<div class="variants-editor" id="variants_editor_{{ sanitizeId($product->id) }}" data-reload-url="{{ route('variants.show', $product->id) }}">
    <div class="row">
        <div class="col">
            <x-larastrap::ambutton :label="_i('Crea Nuova Variante')" :data-modal-url="route('variants.create', ['product_id' => $product->id])" />
            <x-larastrap::ambutton :label="_i('Modifica Matrice Varianti')" :data-modal-url="route('variants.matrix', $product->id)" />
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
                        <a class="btn btn-warning async-modal" data-modal-url="{{ route('variants.edit', $variant->id) }}"><i class="bi-pencil"></i></a>
                        <div class="btn btn-danger delete-variant"><i class="bi-x-lg"></i></div>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
</div>
