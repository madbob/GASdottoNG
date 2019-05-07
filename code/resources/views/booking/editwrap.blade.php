<?php

$more_orders = ($aggregate->orders->count() > 1);
$grand_total = 0;
$has_shipping = $aggregate->canShip();

if (!isset($required_mode)) {
    $required_mode = $aggregate->isRunning() ? 'edit' : 'show';
}

?>

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs hidden-xs" role="tablist">
            <li role="presentation"><a href="#myself-{{ sanitizeId($user->id) }}-{{ $aggregate->id }}" role="tab" data-toggle="tab">{{ _i('La Mia Prenotazione') }}</a></li>

            @if($user->can('users.subusers'))
                <li role="presentation"><a href="#friends-{{ sanitizeId($user->id) }}-{{ $aggregate->id }}" role="tab" data-toggle="tab">{{ _i('Prenotazioni per gli Amici') }}</a></li>
            @endif

            @if($standalone == false && $has_shipping && $aggregate->isActive())
                <li role="presentation"><a href="#others-{{ $aggregate->id }}" role="tab" data-toggle="tab">{{ _i('Prenotazioni per Altri') }}</a></li>
            @endif

            @if($standalone == false && $has_shipping && $aggregate->status == 'closed')
                <li role="presentation"><a href="#add-others-{{ $aggregate->id }}" role="tab" data-toggle="tab">{{ _i('Aggiungi/Modifica Prenotazione') }}</a></li>
            @endif
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane" id="myself-{{ sanitizeId($user->id) }}-{{ $aggregate->id }}">
                @if($required_mode == 'edit')
                    @include('booking.edit', ['aggregate' => $aggregate, 'user' => $user])
                @else
                    @include('booking.show', ['aggregate' => $aggregate, 'user' => $user])
                @endif
            </div>

            @if($user->can('users.subusers'))
                <div role="tabpanel" class="tab-pane" id="friends-{{ sanitizeId($user->id) }}-{{ $aggregate->id }}">
                    <div class="row">
                        <div class="col-md-12">
                            @include('commons.loadablelist', [
                                'identifier' => 'list-friends-' . $user->id . '-' . $aggregate->id,
                                'items' => $user->friends,
                                'header_function' => function($friend) use ($aggregate) {
                                    return $friend->printableFriendHeader($aggregate);
                                },
                                'empty_message' => $user->id == $currentuser->id ? _i('Da qui potrai creare delle sotto-prenotazioni assegnate ai tuoi amici. Esse andranno a far parte della tua prenotazione globale, ma potrai comunque mantenere separate le informazioni. Popola la tua lista di amici dalla pagina del tuo profilo.') : _i('Non ci sono amici registrati per questo utente.'),
                                'url' => url('booking/' . $aggregate->id . '/user'),
                            ])
                        </div>
                    </div>
                </div>
            @endif

            @if($has_shipping && $aggregate->isActive())
                <div role="tabpanel" class="tab-pane fillable-booking-space" id="others-{{ $aggregate->id }}">
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
                </div>
            @endif

            @if($has_shipping && $aggregate->status == 'closed')
                <div role="tabpanel" class="tab-pane fillable-booking-space" id="add-others-{{ $aggregate->id }}">
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
                </div>
            @endif
        </div>
    </div>
</div>
