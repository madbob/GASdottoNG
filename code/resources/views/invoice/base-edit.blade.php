<?php

if($invoice && $invoice->orders->count() > 0) {
    $total_taxable = 0;
    $total_tax = 0;

    foreach($invoice->orders as $o) {
        $summary = $o->calculateInvoicingSummary();
        $total_taxable += $summary->total_taxable;
        $total_tax += $summary->total_tax;
    }

    $help_taxable = _i('Ordini Coinvolti: %s', printablePriceCurrency($total_taxable));
    $help_tax = _i('Ordini Coinvolti: %s', printablePriceCurrency($total_tax));
}
else {
    $help_taxable = null;
    $help_tax = null;
}

?>

@include('commons.selectobjfield', [
    'obj' => $invoice,
    'name' => 'supplier_id',
    'label' => _i('Fornitore'),
    'mandatory' => true,
    'objects' => App\Supplier::orderBy('name', 'asc')->get(),
    'disabled' => ($invoice && $invoice->status == 'payed')
])

@include('commons.textfield', [
    'obj' => $invoice,
    'name' => 'number',
    'label' => _i('Numero'),
    'mandatory' => true,
    'disabled' => ($invoice && $invoice->status == 'payed')
])

@include('commons.datefield', [
    'obj' => $invoice,
    'name' => 'date',
    'label' => _i('Data'),
    'mandatory' => true,
    'defaults_now' => true,
    'disabled' => ($invoice && $invoice->status == 'payed')
])

@include('commons.decimalfield', [
    'obj' => $invoice,
    'name' => 'total',
    'label' => _i('Totale Imponibile'),
    'mandatory' => true,
    'is_price' => true,
    'help_text' => $help_taxable,
    'disabled' => ($invoice && $invoice->status == 'payed')
])

@include('commons.decimalfield', [
    'obj' => $invoice,
    'name' => 'total_vat',
    'label' => _i('Totale IVA'),
    'mandatory' => true,
    'is_price' => true,
    'help_text' => $help_tax,
    'disabled' => ($invoice && $invoice->status == 'payed')
])
