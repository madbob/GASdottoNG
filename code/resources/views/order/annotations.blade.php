<?php $annotated_bookings = $order->bookings()->where('notes', '!=', '')->get() ?>

@if($annotated_bookings->isEmpty() == false)
    <div class="row">
        <div class="col-md-12">
            <div class="well">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="20%">Utente</th>
                            <th width="80%">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($annotated_bookings as $annotated_booking) {
                            <tr>
                                <td>{{ $annotated_booking->user->printableName() }}</td>
                                <td>{{ $annotated_booking->notes }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
