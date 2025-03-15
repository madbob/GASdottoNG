<div class="variants-editor" id="variants_editor_{{ sanitizeId($product->id) }}" data-reload-url="{{ route('variants.show', $product->id) }}">
    <div class="row">
        <div class="col">
            <x-larastrap::ambutton :label="_i('Crea Nuova Variante')" :data-modal-url="route('variants.create', ['product_id' => $product->id])" />

            @if($product->variants->count() > 1)
                <x-larastrap::ambutton :label="_i('Modifica Matrice Varianti')" :data-modal-url="route('variants.matrix', $product->id)" />
            @endif
        </div>
    </div>

    @if($product->variants->isEmpty() == false)
        <br>
        @foreach($product->variants as $variant)
            <div class="row mb-1" data-variant-id="{{ $variant->id }}">
                <div class="col-3">{{ $variant->name }}</div>
                <div class="col-6">{{ $variant->printableValues() }}</div>

                <div class="col-3 text-end">
                    <a class="btn btn-warning async-modal" data-modal-url="{{ route('variants.edit', $variant->id) }}"><i class="bi-pencil"></i></a>
                    <div class="btn btn-danger delete-variant {{ $variant->hasBookings() ? 'disabled' : '' }}"><i class="bi-x-lg"></i></div>
                </div>
            </div>
        @endforeach
    @endif
</div>
