<x-larastrap::modal>
    <x-larastrap::form :obj="$variant" classes="inner-form" method="POST" :action="route('variants.store')">
        <input type="hidden" name="pre-saved-function" value="checkVariantsValues">
        <input type="hidden" name="reload-portion" value="#variants_editor_{{ sanitizeId($product->id) }}">
        <input type="hidden" name="close-modal" value="1">

        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="variant_id" value="{{ $variant ? $variant->id : '' }}">

        <x-larastrap::text name="name" tlabel="generic.name" required />

        <x-larastrap::field tlabel="generic.values">
            @include('commons.manyrows', [
                'contents' => $variant ? $variant->values : [],
                'removable_check' => fn($v) => $v->hasBookings(),
                'columns' => [
                    [
                        'label' => __('generic.id'),
                        'field' => 'id',
                        'type' => 'hidden',
                    ],
                    [
                        'label' => __('generic.value'),
                        'field' => 'value',
                        'type' => 'text',
                    ],
                ],
            ])
        </x-larastrap::field>
    </x-larastrap::form>
</x-larastrap::modal>
