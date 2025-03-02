<?php

if ($booking->order->status == 'closed') {
    /*
        Il valore di $key deve essere generato con lo stesso criterio usato per
        la sua valutazione, in BookingsService::handlePreProcess()
    */
    if ($combo) {
        $base_combo = App\VariantCombo::byValues($combo->values->pluck('id'));
        $now_price = $combo->getPrice();
        $then_price = $base_combo->getPrice();
        $key = sprintf('apply_price_%s_%s', $product->product->id, $combo->id);
    }
    else {
        $base_prod = App\Product::tFind($product->product->id);
        $now_price = $product->product->getPrice();
        $then_price = $base_prod->getPrice();
        $key = sprintf('apply_price_%s', $product->product->id);
    }

    /*
        Devo badare a selezionare il prezzo che eventualmente è già stato
        utilizzato in fase di consegna, nel caso in cui la consegna stessa venga
        riaperta successivamente per essere ulteriormente modificata
    */
    $assigned_price = $product->getFinalUnitPrice();
    $actual_price = closestNumber([$now_price, $then_price], $assigned_price);
}
else {
    if ($combo) {
        $now_price = $then_price = $actual_price = $combo->getPrice();
    }
    else {
        $now_price = $then_price = $actual_price = $product->product->getPrice();
    }
}

?>

@if($then_price != $now_price)
    <?php

    $price_options = [
        (string) $then_price => printablePriceCurrency($then_price),
        (string) $now_price => printablePriceCurrency($now_price),
    ];

    ?>
    <x-larastrap::radios :name="$key" :options="$price_options" classes="alt_price_selector" :value="$actual_price" squeeze />
@else
    {{ printablePriceCurrency($now_price) }}
@endif
