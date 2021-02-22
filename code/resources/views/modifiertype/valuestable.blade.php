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
            <th>Fornitore</th>
            <th>Utente</th>
            <th>Valore</th>
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
                    <td>{{ printablePriceCurrency($amount) }}</td>
                </tr>
            @endforeach
        </tbody>
        <thead>
            <tr>
                <th></th>
                <th></th>
                <th>{{ printablePriceCurrency($total) }}</th>
            </tr>
        </thead>
    @else
        <tbody>
            <tr>
                <td colspan="3">
                    <div class="alert alert-info">
                        Il modificatore non è stato applicato in questo intervallo di date.
                    </div>
                </td>
            </tr>
        </tbody>
    @endif
</table>
