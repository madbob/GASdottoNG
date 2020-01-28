<?php

$data = [];

$payment_method = $supplier->payment_method;
if (!empty($payment_method)) {
    $data[] = $payment_method;
}

$data[] = _i('Saldo Attuale: %s', printablePriceCurrency($supplier->current_balance_amount));

echo join('<br>', $data);
