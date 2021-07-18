<?php

$count_products = 0;

foreach($aggregate->orders as $order) {
    $o = $order->userBooking($user->id);
    $count_products += $o->products()->count();

    foreach($o->friends_bookings as $sub_o) {
        $count_products += $sub_o->products()->count();
    }
}

?>

@if($count_products != 0)
    <div class="row">
        <div class="col-12 col-md-4 offset-md-8">
            <div class="list-group">
                <a href="{{ url('booking/' . $aggregate->id . '/user/' . $user->id . '/document') }}" class="list-group-item">
                    {{ _i('Dettaglio Consegne') }} <i class="bi-download"></i>
                </a>

                @if($currentgas->hasFeature('extra_invoicing'))
                    @foreach(App\Receipt::retrieveByAggregateUser($aggregate, $user) as $receipt)
                        <a href="{{ route('receipts.download', $receipt->id) }}" class="list-group-item">
                            {{ _i('Fattura') }} <i class="bi-download"></i>
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <hr>
@endif
