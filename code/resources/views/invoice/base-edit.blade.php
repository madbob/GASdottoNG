<x-larastrap::select-model name="supplier_id" :label="_i('Fornitore')" :options="$currentuser->targetsByAction('supplier.invoices')" required />
<x-larastrap::text name="number" :label="_i('Numero')" required />
<x-larastrap::datepicker name="date" :label="_i('Data')" required defaults_now />
<x-larastrap::file name="file" :label="_i('Allegato')" />
<x-larastrap::price name="total" :label="_i('Totale Imponibile')" required />
<x-larastrap::price name="total_vat" :label="_i('Totale IVA')" required />
