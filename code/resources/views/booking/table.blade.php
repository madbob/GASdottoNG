@if($aggregate->isRunning() == false)
    <?php $payments = App\MovementType::paymentsByType('booking-payment') ?>

    <div class="row">
        <form class="inner-form" method="POST" action="{{ url('deliveries/' . $aggregate->id . '/fast') }}">
            <input class="hidden" name="post-saved-function" value="reloadCurrentLoadable">

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
                                <div class="btn-group pull-right triggers-all-radio" data-toggle="buttons">
                                    @foreach($payments as $method_id => $info)
                                        <label class="btn btn-default" data-target-class="method-select-{{ $method_id }}">
                                            <input type="radio" value="{{ $method_id }}" autocomplete="off"> {{ $info->name }}
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
                                <td>{{ printablePrice($booking->total_value) }} €</td>
                                <td>
                                    @if($booking->status != 'shipped')
                                        <div class="btn-group pull-right" data-toggle="buttons">
                                            @foreach($payments as $method_id => $info)
                                                <label class="btn btn-default method-select-{{ $method_id }}">
                                                    <input type="radio" name="method-{{ $booking->id }}" value="{{ $method_id }}" autocomplete="off"> {{ $info->name }}
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

            <div class="col-md-12">
                <div class="btn-group pull-right" role="group">
                    <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
                </div>
            </div>
        </form>
    </div>
@else
    <div class="alert alert-danger">
        {{ _i('Questo pannello sarà attivo quando le prenotazioni saranno chiuse') }}
    </div>
@endif
