<?php $currencies = App\Currency::enabled() ?>
@if($currencies->count() > 1)
    <x-larastrap::price name="amount" tlabel="generic.value" required />
    <x-larastrap::select-model name="currency_id" tlabel="generic.currency" :options="$currencies" />
@else
    <x-larastrap::price name="amount" tlabel="generic.value" required :attributes="['data-allow-negative' => $allow_negative ? '1' : '0']" />
@endif
