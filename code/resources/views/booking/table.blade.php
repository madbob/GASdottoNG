@if($aggregate->isRunning() == false)
    @if(empty($aggregate->bookings))
        <x-larastrap::suggestion>
            {{ _i('Non ci sono elementi da visualizzare.') }}
        </x-larastrap::suggestion>
    @else
        <?php

        $payments = paymentsByType('booking-payment');
        $default_payment_method = defaultPaymentByType('booking-payment');

        ?>

        <div class="row">
            <x-larastrap::form classes="inner-form" method="POST" :action="url('deliveries/' . $aggregate->id . '/fast')">
                <input type="hidden" name="post-saved-function" value="reloadCurrentLoadable">

                <div class="col-md-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="5%">
                                    <x-larastrap::check classes="triggers-all-checkbox skip-on-submit" data-target-class="booking-select" squeeze switch="false" checked="true" />
                                </th>
                                <th width="35%"></th>
                                <th width="20%"></th>
                                <th width="20%">
                                    <x-larastrap::datepicker :value="date('Y-m-d')" squeeze classes="toggleall" />
                                </th>
                                <th width="20%">
                                    <div class="btn-group float-end" data-toggle="buttons">
                                        @foreach($payments as $method_id => $name)
                                            <label class="btn btn-light">
                                                <input type="radio" name="method-for-all" class="toggleall" value="{{ $method_id }}" autocomplete="off"> {{ $name }}
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
                                            <x-larastrap::check name="bookings[]" classes="booking-select" squeeze :value="$booking->id" switch="false" checked="true" />
                                        @endif
                                    </td>
                                    <td>{{ $booking->user->printableName() }}</td>
                                    <td>{{ printablePriceCurrency($booking->getValue('effective', true)) }}</td>
                                    <td>
                                        @if($booking->status != 'shipped')
                                            <x-larastrap::datepicker name="date-{{ $booking->id }}" :value="date('Y-m-d')" squeeze />
                                        @else
                                            <x-larastrap::text :value="printableDate($booking->delivery)" readonly disabled squeeze />
                                        @endif
                                    </td>
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
                                        @else
                                            <x-larastrap::text :value="$booking->payment ? $booking->payment->printablePayment() : '?'" classes="text-end" readonly disabled squeeze />
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
