@include('commons.textfield', ['obj' => $product, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
@include('commons.selectobjfield', ['obj' => $product, 'name' => 'category_id', 'objects' => App\Category::orderBy('name', 'asc')->get(), 'label' => 'Categoria'])
@include('commons.selectobjfield', ['obj' => $product, 'name' => 'measure_id', 'objects' => App\Measure::orderBy('name', 'asc')->get(), 'label' => 'Unità di Misura'])
@include('commons.textarea', ['obj' => $product, 'name' => 'description', 'label' => 'Descrizione'])
