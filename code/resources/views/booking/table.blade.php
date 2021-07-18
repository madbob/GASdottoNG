@if($aggregate->isRunning() == false)
    @if(empty($aggregate->bookings))
        <div class="alert alert-info " role="alert">
            {{ _i('Non ci sono elementi da visualizzare.') }}
        </div>
    @else
        <?php

        $payments = App\MovementType::paymentsByType('booking-payment');
        $default_payment_method = App\MovementType::defaultPaymentByType('booking-payment');

        ?>

        <div class="row">
            <x-larastrap::form classes="inner-form" method="POST" :action="url('deliveries/' . $aggregate->id . '/fast')">
                <input type="hidden" name="post-saved-function" value="reloadCurrentLoadable">

                <div class="col-md-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="10%">
                                    <input type="checkbox" class="triggers-all-checkbox" data-target-class="booking-select" value="1">
                                </th>
                                <th width="30%"></th>
                                <th width="30%"></th>
                                <th width="30%">
                                    <div class="btn-group float-end triggers-all-radio" data-toggle="buttons">
                                        @foreach($payments as $method_id => $name)
                                            <label class="btn btn-light" data-target-class="method-select-{{ $method_id }}">
                                                <input type="radio" value="{{ $method_id }}" autocomplete="off"> {{ $name }}
                                            </label>
                                        @endforeach
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($aggregate->bookings as $booking)
                                <tr>
                                    <td>
                                        @if($booking->status != 'shipped')
                                            <input type="checkbox" class="booking-select" name="bookings[]" value="{{ $booking->id }}">
                                        @endif
                                    </td>
                                    <td>{{ $booking->user->printableName() }}</td>
                                    <td>{{ printablePriceCurrency($booking->total_value) }}</td>
                                    <td>
                                        @if($booking->status != 'shipped')
                                            <?php $payment_method = ($booking->user->payment_method_id != 'none' && ($booking->user->payment_method->valid_config)($booking->user)) ? $booking->user->payment_method_id : $default_payment_method ?>
                                            <div class="btn-group float-end" data-toggle="buttons">
                                                @foreach($payments as $method_id => $name)
                                                    <label class="btn btn-ight method-select-{{ $method_id }} {{ $method_id == $payment_method ? 'active' : '' }}">
                                                        <input type="radio" name="method-{{ $booking->id }}" value="{{ $method_id }}" autocomplete="off" {{ $method_id == $payment_method ? 'checked' : '' }}> {{ $name }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-larastrap::form>
        </div>
    @endif
@else
    <div class="alert alert-danger">
        {{ _i('Questo pannello sarà attivo quando le prenotazioni saranno chiuse') }}
    </div>
@endif
