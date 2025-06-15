<?php

$data = [];

$payment_method = $supplier->payment_method;
if (!empty($payment_method)) {
    $data[] = $payment_method;
}

foreach (App\Currency::enabled() as $currency) {
    $data[] = __('texts.movements.current_balance_amount', [
        'amount' => printablePriceCurrency($supplier->currentBalanceAmount($currency), '.', $currency)
    ]);
}

echo join('<br>', $data);
