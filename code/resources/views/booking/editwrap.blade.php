<?php

$more_orders = ($aggregate->orders->count() > 1);
$grand_total = 0;

$has_shipping = false;

foreach ($aggregate->orders as $order) {
    if ($currentuser->can('supplier.shippings', $order->supplier)) {
        $has_shipping = true;
        break;
    }
}

?>

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs hidden-xs" role="tablist">
            <li role="presentation"><a href="#myself-{{ $aggregate->id }}" role="tab" data-toggle="tab">La Mia Prenotazione</a></li>

            @if($has_shipping && $aggregate->isActive())
                <li role="presentation"><a href="#others-{{ $aggregate->id }}" role="tab" data-toggle="tab">Prenotazioni per Altri</a></li>
            @endif

            @if($has_shipping && $aggregate->status == 'closed')
                <li role="presentation"><a href="#add-others-{{ $aggregate->id }}" role="tab" data-toggle="tab">Aggiungi/Modifica Prenotazione</a></li>
            @endif
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane" id="myself-{{ $aggregate->id }}">
                    @if($aggregate->isRunning())
                        @include('booking.edit', ['aggregate' => $aggregate, 'user' => $user])
                    @else
                        @include('booking.show', ['aggregate' => $aggregate, 'user' => $user])
                    @endif
            </div>

            @if($has_shipping && $aggregate->isActive())
                <div role="tabpanel" class="tab-pane fillable-booking-space" id="others-{{ $aggregate->id }}">
                    <div class="row">
                        <div class="col-md-12">
                            <input data-aggregate="{{ $aggregate->id }}" class="form-control bookingSearch" placeholder="Cerca Utente" />
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
                        Attenzione: questo ordine è stato chiuso, prima di aggiungere o modificare una prenotazione accertati che i quantitativi totali desiderati non siano già stati comunicati al fornitore o che possano comunque essere modificati.
                    </div>
                    <br/>
                    <div class="row">
                        <div class="col-md-12">
                            <input data-aggregate="{{ $aggregate->id }}" class="form-control bookingSearch" data-enforce-booking-mode="edit" placeholder="Cerca Utente" />
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
