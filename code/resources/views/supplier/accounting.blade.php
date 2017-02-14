@if($currentgas->userCan('movements.view|movements.admin'))
    <div class="row">
        <div class="col-md-12">
            <p class="lead">Saldo Corrente: <span id="balance-supplier-{{ $supplier->id }}" data-fetch-url="{{ url('suppliers/' . $supplier->id . '/plain_balance') }}">{{ $supplier->balance }}</span> €</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?php $orders = $supplier->orders ?>

            @if($orders->isEmpty())
                <div class="alert alert-info" role="alert">
                    Non ci sono elementi da visualizzare.
                </div>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ordine</th>
                            <th>Totale</th>
                            <th>Pagamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supplier->orders as $order)
                            <?php $summary = $order->calculateSummary() ?>

                            <tr>
                                <td>
                                    {{ $order->printableName() }}<br/>
                                    {{ $order->printableDates() }}
                                </td>

                                <td>
                                    {{ $summary->price_delivered }} €
                                </td>

                                <td>
                                    <?php

                                    $obj = $order->payment;
                                    $rand = rand();

                                    ?>

                                    <label class="static-label text-muted" data-updatable-name="movement-date-{{ $rand }}" data-updatable-field="printable_text">
                                        @if (!$obj || empty($obj->registration_date) || strstr($obj->registration_date, '0000-00-00') !== false)
                                            Mai
                                        @else
                                            {{ $obj->printableDate('registration_date') }} <span class="glyphicon {{ $obj->payment_icon }}" aria-hidden="true"></span>
                                        @endif
                                    </label>

                                    @if($order->status != 'open' && $currentgas->userCan('movements.admin'))
                                        <input type="hidden" name="payment_id" value="{{ $obj ? $obj->id : '' }}" data-updatable-name="movement-id-{{ $rand }}" data-updatable-field="id">
                                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#editMovement-{{ $rand }}">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>

                                        @include('movement.modal', [
                                            'obj' => $obj,
                                            'default' => App\Movement::generate('order-payment', $currentgas, $order, $summary->price_delivered),
                                            'dom_id' => $rand,
                                            'extra' => [
                                                'post-saved-refetch' => '#balance-supplier-' . $supplier->id
                                            ]
                                        ])
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endif
