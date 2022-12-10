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
                    {{ _i('Attenzione: il tuo credito è insuffiente per effettuare nuove prenotazioni.') }}
                </div>
                <br>
            @endif

            <x-larastrap::tabs>
                <x-larastrap::tabpane :label="_i('La Mia Prenotazione')" active="true" icon="bi-person">
                    @if($required_mode == 'edit')
                        @include('booking.edit', ['aggregate' => $aggregate, 'user' => $user, 'enforced' => $enforced])
                    @else
                        @include('booking.show', ['aggregate' => $aggregate, 'user' => $user])
                    @endif
                </x-larastrap::tabpane>

                @if($user->can('users.subusers'))
                    <x-larastrap::tabpane :label="_i('Prenotazioni per gli Amici')" icon="bi-person-add">
                        <div class="row">
                            <div class="col-md-12">
                                @include('commons.loadablelist', [
                                    'identifier' => 'list-friends-' . sanitizeId($user->id) . '-' . $aggregate->id,
                                    'items' => $user->friends,
                                    'header_function' => function($friend) use ($aggregate) {
                                        return $friend->printableFriendHeader($aggregate);
                                    },
                                    'empty_message' => $user->id == $currentuser->id ? _i('Da qui potrai creare delle sotto-prenotazioni assegnate ai tuoi amici. Esse andranno a far parte della tua prenotazione globale, ma potrai comunque mantenere separate le informazioni. Popola la tua lista di amici dalla pagina del tuo profilo.') : _i('Non ci sono amici registrati per questo utente.'),
                                    'url' => url('booking/' . $aggregate->id . '/user'),
                                ])
                            </div>
                        </div>
                    </x-larastrap::tabpane>
                @endif

                @if($standalone == false && $has_shipping && $aggregate->isActive())
                    <x-larastrap::tabpane :label="_i('Prenotazioni per Altri')" classes="fillable-booking-space" icon="bi-people">
                        <div class="row">
                            <div class="col-md-12">
                                <input data-aggregate="{{ $aggregate->id }}" class="form-control bookingSearch" placeholder="{{ _i('Cerca Utente') }}" />
                            </div>
                            <p>&nbsp;</p>
                        </div>

                        <div class="row">
                            <div class="col-md-12 other-booking">
                            </div>
                        </div>
                    </x-larastrap::tabpane>
                @endif

                @if($standalone == false && $has_shipping && $aggregate->status == 'closed')
                    <x-larastrap::tabpane :label="_i('Aggiungi/Modifica Prenotazione')" classes="fillable-booking-space" icon="bi-person-check">
                        <div class="alert alert-danger">
                            {{ _i('Attenzione: questo ordine è stato chiuso, prima di aggiungere o modificare una prenotazione accertati che i quantitativi totali desiderati non siano già stati comunicati al fornitore o che possano comunque essere modificati.') }}
                        </div>
                        <br/>
                        <div class="row">
                            <div class="col-md-12">
                                <input data-aggregate="{{ $aggregate->id }}" class="form-control bookingSearch" data-enforce-booking-mode="edit" placeholder="{{ _i('Cerca Utente') }}" />
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
