<?php

if (!isset($startdate)) {
    $startdate = date('Y-m-d', strtotime('-1 months'));
}

if (!isset($enddate)) {
    $enddate = date('Y-m-d');
}

$modified_values = App\ModifiedValue::whereIn('modifier_id', $modifiers)->where(DB::raw('DATE(created_at)'), '>=', $startdate)->where(DB::raw('DATE(created_at)'), '<=', $enddate)->where('amount', '!=', 0)->orderBy('created_at', 'desc')->get();
$total = 0;

?>

<table class="table">
    <thead>
        <tr>
            <th scope="col">{{ __('orders.supplier') }}</th>
            <th scope="col">{{ __('user.name') }}</th>
            <th scope="col">{{ __('generic.date') }}</th>
            <th scope="col">{{ __('generic.value') }}</th>
        </tr>
    </thead>

    @if($modified_values->count() != 0)
        <tbody>
            @foreach($modified_values as $mod_value)
                <?php

                $mod_value_summary = $mod_value->getSummary();
                $amount = $mod_value->effective_amount;
                $total += $amount;

                ?>
                <tr>
                    <td>{{ $mod_value_summary->supplier->printableName() }}</td>
                    <td>{{ $mod_value_summary->user->printableName() }}</td>
                    <td>{{ printableDate($mod_value->created_at) }}</td>
                    <td>{{ printablePriceCurrency($amount) }}</td>
                </tr>
            @endforeach
        </tbody>
        <thead>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td class="fw-bold">{{ printablePriceCurrency($total) }}</td>
            </tr>
        </thead>
    @else
        <tbody>
            <tr>
                <td colspan="4">
                    <x-larastrap::suggestion>
                        {{ __('movements.help.modifier_not_applied_in_time_range') }}
                    </x-larastrap::suggestion>
                </td>
            </tr>
        </tbody>
    @endif
</table>
