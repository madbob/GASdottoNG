<?php $annotated_bookings = $order->bookings->filter(fn($b) => $b->notes != '')->sortByUserName() ?>

@if($annotated_bookings->isEmpty() == false)
    <div class="row">
        <div class="col">
            <x-larastrap::suggestion>
                {{ __('orders.help.pending_notes') }}
            </x-larastrap::suggestion>

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" width="20%">{{ __('user.name') }}</th>
                        <th scope="col" width="80%">{{ __('generic.notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($annotated_bookings as $annotated_booking)
                        <tr>
                            <td>{{ $annotated_booking->user->printableName() }}</td>
                            <td>{{ $annotated_booking->notes }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
