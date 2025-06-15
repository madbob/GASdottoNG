<x-larastrap::text name="name" tlabel="generic.name" required />
<x-larastrap::price name="price" tlabel="products.prices.unit" required tpophelp="products.help.unit_price" />
<x-larastrap::selectobj name="category_id" tlabel="generic.category" :options="App\Category::with(['children'])->orderBy('name', 'asc')->where('parent_id', '=', null)->get()" required />
<x-larastrap::select-model name="measure_id" tlabel="generic.measure" classes="measure-selector" :options="App\Measure::orderBy('name', 'asc')->get()" required thelp="products.help.discrete_measure_selected_notice" tpophelp="products.help.measure" />
<x-larastrap::textarea name="description" tlabel="generic.description" />
<x-larastrap::select-model name="vat_rate_id" tlabel="products.vat_rate" :options="App\VatRate::orderBy('name', 'asc')->get()" tpophelp="products.help.vat_rate" :extra_options="[0 => __('texts.generic.none')]" />
