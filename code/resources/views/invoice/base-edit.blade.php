<x-larastrap::select-model name="supplier_id" tlabel="orders.supplier" :options="$currentuser->targetsByAction('supplier.invoices')" required />
<x-larastrap::text name="number" tlabel="generic.number" required />
<x-larastrap::datepicker name="date" tlabel="generic.date" required defaults_now />
<x-larastrap::file name="file" tlabel="generic.attachment" />
<x-larastrap::price name="total" tlabel="orders.totals.taxable" required />
<x-larastrap::price name="total_vat" tlabel="orders.totals.vat" required />
