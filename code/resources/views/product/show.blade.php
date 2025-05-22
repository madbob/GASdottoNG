<x-larastrap::form :obj="$product" :buttons="[]">
    <div class="row">
        <div class="col-md-6">
            <x-larastrap::price name="price" tlabel="products.prices.unit" readonly disabled />
            @include('commons.staticobjfield', ['obj' => $product, 'name' => 'category', 'label' => __('generic.category')])
            @include('commons.staticobjfield', ['obj' => $product, 'name' => 'measure', 'label' => __('generic.measure')])

            <x-larastrap::field tlabel="generic.description">
                <p class="form-control-plaintext">
                    {!! prettyFormatHtmlText($product->description) !!}
                </p>
            </x-larastrap::field>

            @if(filled($product->supplier_code))
                <x-larastrap::text name="supplier_code" tlabel="products.code" readonly disabled />
            @endif

            <x-larastrap::check name="active" tlabel="products.bookable" readonly disabled />
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    @include('commons.staticimagefield', ['obj' => $product, 'label' => __('generic.photo'), 'valuefrom' => 'picture_url'])
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-larastrap::text name="portion_quantity" tlabel="products.portion_quantity" readonly disabled />
					<x-larastrap::text name="package_size" tlabel="products.package_size" readonly disabled />
					<x-larastrap::text name="min_quantity" tlabel="products.min_quantity" readonly disabled />
					<x-larastrap::text name="max_available" tlabel="products.available" readonly disabled />
					<x-larastrap::text name="weight" tlabel="generic.weight" readonly disabled />
                </div>
                <div class="col-md-6">
					<x-larastrap::text name="multiple" tlabel="products.multiple" readonly disabled />
					<x-larastrap::text name="max_quantity" tlabel="products.max_quantity" readonly disabled />
					<x-larastrap::text name="global_min" tlabel="products.global_min" readonly disabled />
                </div>
            </div>

            @include('variant.viewer', ['product' => $product])
        </div>
    </div>
</x-larastrap::form>
