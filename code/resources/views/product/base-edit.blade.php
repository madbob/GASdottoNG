@include('commons.textfield', ['obj' => $product, 'name' => 'name', 'label' => _i('Nome'), 'mandatory' => true])
@include('commons.decimalfield', ['obj' => $product, 'name' => 'price', 'label' => _i('Prezzo Unitario'), 'is_price' => true, 'mandatory' => true])
@include('commons.decimalfield', ['obj' => $product, 'name' => 'transport', 'label' => _i('Prezzo Trasporto'), 'is_price' => true])
@include('commons.percentagefield', ['obj' => $product, 'name' => 'discount', 'label' => _i('Sconto')])

@include('commons.selectobjfield', [
    'obj' => $product,
    'name' => 'category_id',
    'objects' => App\Category::orderBy('name', 'asc')->where('parent_id', '=', null)->get(),
    'label' => _i('Categoria'),
    'required' => ($product == null)
])

@include('commons.selectobjfield', [
    'obj' => $product,
    'name' => 'measure_id',
    'objects' => App\Measure::orderBy('name', 'asc')->get(),
    'extra_class' => 'measure-selector',
    'label' => _i('Unità di Misura'),
    'datafields' => ['discrete'],
    'enforced_default' => 'non-specificato'
])

@include('commons.textarea', ['obj' => $product, 'name' => 'description', 'label' => _i('Descrizione')])

@include('commons.selectobjfield', [
    'obj' => $product,
    'name' => 'vat_rate_id',
    'objects' => App\VatRate::orderBy('name', 'asc')->get(),
    'label' => _i('Aliquota IVA'),
    'extra_selection' => [
        '0' => _i('Nessuna')
    ]
])
