<x-larastrap::text name="name" tlabel="generic.name" :disabled="$modtype ? $modtype->system : false" />

<x-larastrap::checks name="classes" tlabel="generic.targets" :options="[
    'App\Product' => __('products.list'),
    'App\Supplier' => __('generic.suppliers_and_orders'),
    'App\Circle' => __('generic.aggregations_and_groups')
]" />
