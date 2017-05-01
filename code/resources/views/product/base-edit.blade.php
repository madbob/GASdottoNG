@include('commons.textfield', ['obj' => $product, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
@include('commons.decimalfield', ['obj' => $product, 'name' => 'price', 'label' => 'Prezzo Unitario', 'postlabel' => '€', 'mandatory' => true])
@include('commons.decimalfield', ['obj' => $product, 'name' => 'transport', 'label' => 'Prezzo Trasporto', 'postlabel' => '€'])
@include('commons.textfield', ['obj' => $product, 'name' => 'discount', 'label' => 'Sconto', 'postlabel' => '€ / %'])

@include('commons.selectobjfield', [
    'obj' => $product,
    'name' => 'category_id',
    'objects' => App\Category::orderBy('name', 'asc')->where('parent_id', '=', null)->get(),
    'triggering_modal' => $currentuser->can('categories.admin', $currentgas) ? 'createCategory' : false,
    'label' => 'Categoria'
])

@include('commons.selectobjfield', [
    'obj' => $product,
    'name' => 'measure_id',
    'objects' => App\Measure::orderBy('name', 'asc')->get(),
    'triggering_modal' => $currentuser->can('measures.admin', $currentgas) ? 'createMeasure' : false,
    'extra_class' => 'measure-selector',
    'label' => 'Unità di Misura',
    'datafields' => ['discrete']
])

@include('commons.textarea', ['obj' => $product, 'name' => 'description', 'label' => 'Descrizione'])
