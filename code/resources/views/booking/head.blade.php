@php

$selected_circles = new Illuminate\Support\Collection();

foreach($aggregate->orders as $order) {
    $o = $order->userBooking($user->id);
    $selected_circles = $selected_circles->merge($o->circles);
}

$selected_circles = $selected_circles->unique('id');

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
                $selected_circles[] = array_shift($default);
            }
            else {
                $selected_circles[] = $meta->circles[array_key_first($meta->circles)];
            }
        }
    }
}

@endphp

<div class="row booking-header">
    <div class="col-12 col-md-8">
        @if($user->isFriend() == false)
            @if($editable)
                @foreach($select_circles as $meta)
                    <x-larastrap::radios-model :label="$meta->group->name" name="circles[]" :options="$meta->circles" :value="$selected_circles" />
                @endforeach
            @else
                @foreach($select_circles as $meta)
                    <x-larastrap::text readonly disabled :label="$meta->group->name" :value="$selected_circles->filter(fn($c) => $c->group->id == $meta->group->id)->map(fn($c) => $c->name)->join(', ')" />
                @endforeach
            @endif

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
        <x-larastrap::card header="generic.download">
            <div class="list-group">
                <a href="{{ url('booking/' . $aggregate->id . '/user/' . $user->id . '/document') }}" class="list-group-item list-group-item-action">
                    {{ __('texts.orders.files.order.shipping') }}
                    <i class="bi-download float-end"></i>
                </a>

                @if($currentgas->hasFeature('extra_invoicing'))
                    @foreach(App\Receipt::retrieveByAggregateUser($aggregate, $user) as $receipt)
                        <a href="{{ route('receipts.download', $receipt->id) }}" class="list-group-item list-group-item-action">
                            {{ __('texts.generic.invoice') }}
                            <i class="bi-download float-end"></i>
                        </a>
                    @endforeach
                @endif
            </div>
        </x-larastrap::card>

        @include('attachment.partials.downloadable', [
            'obj' => $aggregate,
        ])
    </div>
</div>

<hr>
