@php

use App\Formatters\User as UserFormatter;

$user_columns = UserFormatter::formattableColumns('all');
$user_columns = array_intersect_key($user_columns, array_flip(['fullname', 'email', 'phone', 'mobile', 'payment_method', 'credit', 'last_booking']));
$user_columns_names = array_keys($user_columns);

$bookings = $aggregate->bookings;
usort($bookings, function($a, $b) {
    return $a->user->printableName() <=> $b->user->printableName();
});

$table_identifier = sprintf('static-bookings-%s', Illuminate\Support\Str::random(10));

@endphp

<div class="alert alert-danger mb-3">
    {{ __('texts.orders.help.waiting_closing_for_deliveries') }}
</div>

<div class="row d-none d-md-flex mb-1">
    <div class="col flowbox">
        <div class="form-group mainflow d-none d-xl-block">
            <input type="text" class="form-control table-text-filter" data-table-target="#{{ $table_identifier }}" tplaceholder="generic.do_filter">
        </div>

        @include('commons.columns', [
            'columns' => ['fullname'],
            'display_columns' => $user_columns,
            'target' => $table_identifier,
        ])
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="table-responsive">
            <table class="table" id="{{ $table_identifier }}">
                <thead>
                    <tr>
                        @foreach($user_columns as $index => $uc)
                            <th class="order-cell-{{ $index }}" scope="col">{{ $uc->name }}</th>
                        @endforeach

                        <th scope="col">{{ __('texts.generic.created_at') }}</th>
                        <th scope="col">{{ __('texts.generic.updated_at') }}</th>
                        <th scope="col">{{ __('texts.orders.totals.booked') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            @php
                            $formatted = UserFormatter::format($booking->user, $user_columns_names, $booking->aggregate);
                            @endphp

                            @foreach($formatted as $index => $format)
                                <td class="text-filterable-cell order-cell-{{ $user_columns_names[$index] }}">{{ $format }}</td>
                            @endforeach

                            <td>{{ printableDate($booking->created_at) }}</td>
                            <td>{{ printableDate($booking->updated_at) }}</td>
                            <td>{{ printablePriceCurrency($booking->getValue('booked', true)) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
