<form class="form-horizontal main-form" method="PUT" action="{{ route('products.update', $product->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('commons.staticpricefield', ['obj' => $product, 'name' => 'price', 'label' => 'Prezzo Unitario', 'mandatory' => true])
            @include('commons.staticobjfield', ['obj' => $product, 'name' => 'category', 'label' => 'Categoria'])
            @include('commons.staticobjfield', ['obj' => $product, 'name' => 'measure', 'label' => 'Unità di Misura'])
            @include('commons.staticstringfield', ['obj' => $product, 'name' => 'description', 'label' => 'Descrizione', 'callable' => 'htmlize'])
            @include('commons.staticstringfield', ['obj' => $product, 'name' => 'supplier_code', 'label' => 'Codice Fornitore'])
            @include('commons.staticboolfield', ['obj' => $product, 'name' => 'active', 'label' => 'Ordinabile'])
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-6">
                    @include('commons.staticimagefield', ['obj' => $product, 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                </div>
                <div class="col-md-6">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    @include('commons.staticstringfield', ['obj' => $product, 'name' => 'portion_quantity', 'label' => 'Pezzatura'])
                </div>
                <div class="col-md-6">
                    @include('commons.staticboolfield', ['obj' => $product, 'name' => 'variable', 'label' => 'Variabile'])
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    @include('commons.staticstringfield', ['obj' => $product, 'name' => 'package_size', 'label' => 'Confezione'])
                </div>
                <div class="col-md-6">
                    @include('commons.staticstringfield', ['obj' => $product, 'name' => 'multiple', 'label' => 'Multiplo'])
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    @include('commons.staticstringfield', ['obj' => $product, 'name' => 'min_quantity', 'label' => 'Minimo'])
                </div>
                <div class="col-md-6">
                    @include('commons.staticstringfield', ['obj' => $product, 'name' => 'max_quantity', 'label' => 'Massimo'])
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    @include('commons.staticstringfield', ['obj' => $product, 'name' => 'max_available', 'label' => 'Disponibile'])
                </div>
            </div>

            @include('variant.viewer', ['product' => $product])
        </div>
    </div>
</form>
