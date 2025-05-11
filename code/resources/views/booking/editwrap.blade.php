<?php

$orders = $aggregate->orders()->with(['products', 'bookings', 'modifiers'])->get();
$aggregate->setRelation('orders', $orders);

$rand = Illuminate\Support\Str::random(10);
$more_orders = ($orders->count() > 1);
$grand_total = 0;
$has_shipping = $aggregate->canShip();

if (!isset($required_mode)) {
    $required_mode = $aggregate->isRunning() ? 'edit' : 'show';
    $enforced = false;
}
else {
    $enforced = true;
}

?>

<div>
    <div class="row">
        <div class="col-md-12">
            @if($required_mode == 'edit' && $user->canBook() == false)
                <div class="alert alert-danger">
                    {{ __('orders.help.insufficient_credit_notice') }}
                </div>
                <br>
            @endif

            <x-larastrap::tabs>
                <x-larastrap::tabpane tlabel="orders.booking.nav.mine" active="true" icon="bi-person" :id="sprintf('bookings-mine-%s-%s', sanitizeId($user->id), sanitizeId($aggregate->id))">
                    @if($required_mode == 'edit')
                        @include('booking.edit', ['aggregate' => $aggregate, 'user' => $user, 'enforced' => $enforced])
                    @else
                        @include('booking.show', ['aggregate' => $aggregate, 'user' => $user])
                    @endif
                </x-larastrap::tabpane>

                @if($user->can('users.subusers', $user->gas))
                    <x-larastrap::tabpane tlabel="orders.booking.nav.friends" icon="bi-person-add" :id="sprintf('bookings-friends-%s-%s', sanitizeId($user->id), sanitizeId($aggregate->id))">
                        <div class="row">
                            <div class="col-md-12">
                                @include('commons.loadablelist', [
                                    'identifier' => 'list-friends-' . sanitizeId($user->id) . '-' . $aggregate->id,
                                    'items' => $user->friends,
                                    'header_function' => function($friend) use ($aggregate) {
                                        return $friend->printableFriendHeader($aggregate);
                                    },
                                    'empty_message' => $user->id == $currentuser->id ? __('orders.help.friends_bookings_notice') : __('orders.help.no_friends'),
                                    'url' => url('booking/' . $aggregate->id . '/user'),
                                ])
                            </div>
                        </div>
                    </x-larastrap::tabpane>
                @endif

                @if($standalone == false && $has_shipping && $aggregate->isActive())
                    <x-larastrap::tabpane tlabel="orders.booking.nav.others" classes="fillable-booking-space" icon="bi-people" :id="sprintf('bookings-other-%s-%s', sanitizeId($user->id), sanitizeId($aggregate->id))">
                        <div class="row">
                            <div class="col-md-12">
                                <input data-aggregate="{{ $aggregate->id }}" class="form-control bookingSearch" placeholder="{{ __('generic.search.users') }}" />
                            </div>
                            <p>&nbsp;</p>
                        </div>

                        <div class="row">
                            <div class="col-md-12 other-booking">
                            </div>
                        </div>
                    </x-larastrap::tabpane>
                @endif

                @if($standalone == false && $has_shipping && $aggregate->orders()->where('status', 'closed')->count() > 0)
                    <x-larastrap::tabpane tlabel="orders.booking.nav.add" classes="fillable-booking-space" icon="bi-person-check" :id="sprintf('bookings-more-%s-%s', sanitizeId($user->id), sanitizeId($aggregate->id))">
                        <div class="alert alert-danger">
                            {{ __('orders.help.closed_order_alert_new_booking') }}
                        </div>
                        <br/>
                        <div class="row">
                            <div class="col-md-12">
                                <input data-aggregate="{{ $aggregate->id }}" class="form-control bookingSearch" data-enforce-booking-mode="edit" placeholder="{{ __('generic.search.users') }}" />
                            </div>
                            <p>&nbsp;</p>
                        </div>

                        <div class="row">
                            <div class="col-md-12 other-booking">
                            </div>
                        </div>
                    </x-larastrap::tabpane>
                @endif
            </x-larastrap::tabs>
        </div>
    </div>
</div>
