<x-larastrap::text name="name" :label="_i('Nome')" :disabled="$modtype ? $modtype->system : false" />
<x-larastrap::checks name="classes" :label="_i('Oggetti')" :options="['App\Product' => _i('Prodotti'), 'App\Supplier' => _i('Fornitori/Ordini'), 'App\Delivery' => _i('Luoghi di Consegna')]" />
