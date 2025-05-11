<x-larastrap::form :obj="$product" :buttons="[]">
    <div class="row">
        <div class="col-md-6">
            <x-larastrap::price name="price" :label="_i('Prezzo Unitario')" readonly disabled />
            @include('commons.staticobjfield', ['obj' => $product, 'name' => 'category', 'label' => 'Categoria'])
            @include('commons.staticobjfield', ['obj' => $product, 'name' => 'measure', 'label' => 'Unità di Misura'])

            <x-larastrap::field :label="_i('Descrizione')">
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
                    @include('commons.staticimagefield', ['obj' => $product, 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-larastrap::text name="portion_quantity" :label="_i('Pezzatura')" readonly disabled />
					<x-larastrap::text name="package_size" :label="_i('Confezione')" readonly disabled />
					<x-larastrap::text name="min_quantity" :label="_i('Minimo')" readonly disabled />
					<x-larastrap::text name="max_available" :label="_i('Disponibile')" readonly disabled />
					<x-larastrap::text name="weight" :label="_i('Peso')" readonly disabled />
                </div>
                <div class="col-md-6">
					<x-larastrap::text name="multiple" :label="_i('Multiplo')" readonly disabled />
					<x-larastrap::text name="max_quantity" :label="_i('Massimo')" readonly disabled />
					<x-larastrap::text name="global_min" :label="_i('Minimo Complessivo')" readonly disabled />
                </div>
            </div>

            @include('variant.viewer', ['product' => $product])
        </div>
    </div>
</x-larastrap::form>
