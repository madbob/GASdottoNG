<x-larastrap::text name="name" tlabel="generic.name" :disabled="$modtype ? $modtype->system : false" />

<x-larastrap::checks name="classes" tlabel="generic.targets" :options="[
    'App\Product' => __('texts.products.list'),
    'App\Supplier' => __('texts.generic.suppliers_and_orders'),
    'App\Circle' => __('texts.generic.aggregations_and_groups')
]" />
