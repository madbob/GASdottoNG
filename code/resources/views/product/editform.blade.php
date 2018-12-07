<?php

if (!isset($duplicate))
    $duplicate = false;

?>

<div class="row">
    <div class="col-md-6">
        @include('product.base-edit', ['product' => $product])
        @include('commons.textfield', ['obj' => $product, 'name' => 'supplier_code', 'label' => _i('Codice Fornitore')])
        @include('commons.boolfield', ['obj' => $product, 'name' => 'active', 'label' => _i('Ordinabile')])
    </div>
    <div class="col-md-6">
        @include('commons.imagefield', ['obj' => $product, 'name' => 'picture', 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
        @include('commons.decimalfield', ['obj' => $product, 'name' => 'portion_quantity', 'label' => _i('Pezzatura'), 'decimals' => 3])
        @include('commons.boolfield', ['obj' => $product, 'name' => 'variable', 'label' => _i('Variabile')])
        @include('commons.decimalfield', ['obj' => $product, 'name' => 'package_size', 'label' => _i('Confezione'), 'decimals' => 3])
        @include('commons.decimalfield', ['obj' => $product, 'name' => 'multiple', 'label' => _i('Multiplo'), 'decimals' => 3])
        @include('commons.decimalfield', ['obj' => $product, 'name' => 'min_quantity', 'label' => _i('Minimo'), 'decimals' => 3])
        @include('commons.decimalfield', ['obj' => $product, 'name' => 'max_quantity', 'label' => _i('Massimo Consigliato'), 'decimals' => 3])
        @include('commons.decimalfield', ['obj' => $product, 'name' => 'max_available', 'label' => _i('Disponibile'), 'decimals' => 3])
        @include('product.variantseditor', ['product' => $product, 'duplicate' => $duplicate])
    </div>
</div>
