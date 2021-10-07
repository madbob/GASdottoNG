<?php

$data = [];

$payment_method = $supplier->payment_method;
if (!empty($payment_method)) {
    $data[] = $payment_method;
}

foreach (App\Currency::enabled() as $currency) {
    $data[] = _i('Saldo Attuale: %s', printablePriceCurrency($supplier->currentBalanceAmount($currency), '.', $currency));
}

echo join('<br>', $data);
