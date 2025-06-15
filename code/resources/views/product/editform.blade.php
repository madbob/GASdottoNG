@php

if (!isset($duplicate)) {
    $duplicate = false;
}

@endphp

<input type="hidden" name="post-saved-function" value="afterProductChange" class="skip-on-submit">

<div class="row">
    <div class="col-md-6">
        @include('product.base-edit', ['product' => $product])

        <x-larastrap::text name="supplier_code" tlabel="products.code" />
        <x-larastrap::check name="active" tlabel="products.bookable" tpophelp="products.help.bookable" />

        @if($duplicate == false)
            @include('commons.modifications', ['obj' => $product, 'duplicate' => $duplicate])
        @endif
    </div>
    <div class="col-md-6">
        @include('commons.imagefield', [
            'obj' => $product,
            'name' => 'picture',
            'label' => __('texts.generic.photo'),
            'valuefrom' => 'picture_url'
        ])

        <x-larastrap::decimal name="portion_quantity" tlabel="products.portion_quantity" decimals="3" tpophelp="products.help.portion_quantity" />
        <x-larastrap::integer name="package_size" tlabel="products.package_size" tpophelp="products.help.package_size" />
        <x-larastrap::decimal name="weight" tlabel="generic.weight" decimals="3" textappend="Kg" />
        <x-larastrap::integer name="multiple" tlabel="products.multiple" tpophelp="products.help.multiple" />
        <x-larastrap::decimal name="min_quantity" tlabel="products.min_quantity" decimals="3" tpophelp="products.help.min_quantity" />
        <x-larastrap::decimal name="max_quantity" tlabel="products.max_quantity" decimals="3" tpophelp="products.help.max_quantity" />
        <x-larastrap::decimal name="max_available" tlabel="products.available" decimals="3" tpophelp="products.help.available" />
        <x-larastrap::decimal name="global_min" tlabel="products.global_min" decimals="3" tpophelp="products.help.global_min" />

        @if($duplicate == false)
            <x-larastrap::field tlabel="products.variants" tpophelp="products.help.variants">
                @include('variant.editor', ['product' => $product])
            </x-larastrap::field>
        @endif
    </div>

    @if($duplicate)
        <div class="col-12">
            <x-larastrap::suggestion>
                {{ __('texts.products.help.duplicate_notice') }}
            </x-larastrap::suggestion>
        </div>
    @endif
</div>
