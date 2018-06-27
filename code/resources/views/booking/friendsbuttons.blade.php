<?php

$friend_buttons = [];

foreach($aggregate->orders as $order) {
    $o = $order->userBooking($user->id);
    $friends = $o->friends_bookings;

    foreach($friends as $f) {
        $friend_name = $f->user->printableName();
        if (!isset($friend_buttons[$friend_name])) {
            $friend_buttons[$friend_name] = (object) [
                'total' => 0,
                'url' => url('/booking/' . $aggregate->id . '/user/' . $f->user_id . '?enforce=' . $mode)
            ];
        }
        $friend_buttons[$friend_name]->total += $f->total_value;
    }
}

?>

@foreach($friend_buttons as $friend_name => $friend_value)
    <button type="button" class="btn btn-default load-other-booking" data-booking-url="{{ $friend_value->url }}">{{ sprintf('+ %s (%s)', $friend_name, printablePriceCurrency($friend_value->total)) }}</button>
@endforeach
