@include('commons.textfield', ['obj' => $product, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
@include('commons.decimalfield', ['obj' => $product, 'name' => 'price', 'label' => 'Prezzo Unitario', 'mandatory' => true])
@include('commons.decimalfield', ['obj' => $product, 'name' => 'transport', 'label' => 'Prezzo Trasporto'])

@include('commons.selectobjfield', [
	'obj' => $product,
	'name' => 'category_id',
	'objects' => App\Category::orderBy('name', 'asc')->where('parent_id', '=', null)->get(),
	'triggering_modal' => $currentgas->userCan('categories.admin') ? 'createCategory' : false,
	'label' => 'Categoria'
])

@include('commons.selectobjfield', [
	'obj' => $product,
	'name' => 'measure_id',
	'objects' => App\Measure::orderBy('name', 'asc')->get(),
	'triggering_modal' => $currentgas->userCan('measures.admin') ? 'createMeasure' : false,
	'extra_class' => 'measure-selector',
	'label' => 'Unità di Misura',
	'datafields' => ['discrete']
])

@include('commons.textarea', ['obj' => $product, 'name' => 'description', 'label' => 'Descrizione'])
