<x-larastrap::text name="name" tlabel="generic.name" :disabled="$modtype ? $modtype->system : false" />
<x-larastrap::checks name="classes" :label="_i('Oggetti')" :options="['App\Product' => __('products.list'), 'App\Supplier' => _i('Fornitori/Ordini'), 'App\Circle' => _i('Aggregazioni/Gruppi')]" />
