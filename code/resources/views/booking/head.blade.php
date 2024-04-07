<?php

$count_products = 0;
$selected_circles = null;

foreach($aggregate->orders as $order) {
    $o = $order->userBooking($user->id);
    $count_products += $o->products()->count();

    if ($count_products == 0) {
        foreach($o->friends_bookings as $sub_o) {
            $count_products += $sub_o->products()->count();
        }
    }

    if ($count_products > 0) {
        $selected_circles = $o->circles;
        break;
    }
}

if ($user->isFriend() == false) {
    $circles = $aggregate->orders->first()->circlesByGroup();

    $display_circles = array_filter($circles, fn($c) => $c->group->context == 'order');

    $select_circles = array_filter($circles, fn($c) => $c->group->context == 'booking');
    if (is_null($selected_circles) || $selected_circles->isEmpty()) {
        /*
            Questo è per forzare sempre un default, ed evitare che esistano
            prenotazioni cui non venga assegnato nessuno dei Circle richiesti
        */
        foreach($select_circles as $meta) {
            $default = array_filter($meta->circles, fn($c) => $c->is_default);
            if (empty($default) == false) {
                $selected_circles[] = $default[0];
            }
            else {
                $selected_circles[] = $meta->circles[0];
            }
        }
    }
}

?>

<div class="row booking-header">
    <div class="col-12 col-md-8">
        @if($user->isFriend() == false)
            @foreach($select_circles as $meta)
                <x-larastrap::radios-model :label="$meta->group->name" name="circles[]" :options="$meta->circles" :value="$selected_circles" :readonly="$editable == false" />
            @endforeach

            @foreach($display_circles as $meta)
                <x-larastrap::field :label="$meta->group->name">
                    <ul class="list-unstyled">
                        @foreach($meta->circles as $circle)
                            <li class="form-control-plaintext">{{ $circle->name }}</li>
                        @endforeach
                    </ul>
                </x-larastrap::field>
            @endforeach
        @endif
    </div>
    <div class="col-12 col-md-4">
        @if($count_products != 0)
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
        @endif
    </div>
</div>

<hr>
