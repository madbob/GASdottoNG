@if($aggregate->isRunning() == false)
    @if(empty($aggregate->bookings))
        <x-larastrap::suggestion>
            {{ __('texts.generic.empty_list') }}
        </x-larastrap::suggestion>
    @else
        <?php

        $payments = paymentsByType('booking-payment');
        $default_payment_method = defaultPaymentByType('booking-payment');
        $table_identifier = 'fast-shipping-' . sanitizeId($aggregate->id);

        ?>

        <div class="row">
            <div class="col">
                @include('commons.iconslegend', [
                    'class' => App\AggregateBooking::class,
                    'target' => '#' . $table_identifier,
                    'table_filter' => true,
                    'contents' => $aggregate->bookings,
                ])
            </div>
        </div>

        <hr>

        <div class="row">
            <x-larastrap::form classes="inner-form" method="POST" :action="url('deliveries/' . $aggregate->id . '/fast')">
                <input type="hidden" name="post-saved-function" value="reloadCurrentLoadable">

                <div class="col-md-12">
                    <table class="table" id="{{ $table_identifier }}">
                        <thead>
                            <tr>
                                <th scope="col" width="5%">
                                    <x-larastrap::check classes="triggers-all-checkbox skip-on-submit" data-target-class="booking-select" squeeze switch="false" checked="true" />
                                </th>
                                <th scope="col" width="35%"></th>
                                <th scope="col" width="20%"></th>
                                <th scope="col" width="20%">
                                    <x-larastrap::datepicker :value="date('Y-m-d')" squeeze classes="toggleall" />
                                </th>
                                <th scope="col" width="20%">
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
                                @php
                                $internal_id = Illuminate\Support\Str::random(10);
                                $row_id = sprintf('%s||%s', $booking->user->id, $internal_id);
                                @endphp

                                <tr>
                                    <td>
                                        @if($booking->status != 'shipped')
                                            <x-larastrap::check name="bookings[]" classes="booking-select" squeeze :value="$row_id" switch="false" checked="true" />
                                        @endif
                                    </td>
                                    <td>{!! $booking->printableHeader() !!}</td>
                                    <td>{{ printablePriceCurrency($booking->getValue('effective', true)) }}</td>
                                    <td>
                                        @if($booking->status != 'shipped')
                                            <x-larastrap::datepicker name="date-{{ $internal_id }}" :value="date('Y-m-d')" squeeze />
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
                                                        <input type="radio" name="method-{{ $internal_id }}" value="{{ $method_id }}" autocomplete="off" {{ $method_id == $payment_method ? 'checked' : '' }}> {{ $name }}
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
        {{ __('texts.orders.help.waiting_closing_for_deliveries') }}
    </div>
@endif
