<?php

$orders = $aggregate->orders;
$more_orders = ($orders->count() > 1);
$grand_total = 0;

?>

<x-larastrap::mform nosave nodelete>
    @include('booking.head', ['aggregate' => $aggregate, 'editable' => false])

    @foreach($orders as $order)
        @if($more_orders)
            <h3>{{ $order->printableName() }}</h3>
        @endif

        <?php

        $order->setRelation('aggregate', $aggregate);
        $contacts = $order->showableContacts();
        $o = $order->userBooking($user->id);
        $mods = $o->applyModifiers(null, false);

        ?>

        @if($contacts->isEmpty() == false)
            <x-larastrap::suggestion>
                {{ __('texts.orders.help.contacts_notice') }}
                <ul>
                    @foreach($contacts as $contact)
                        <li>{{ $contact->printableName() }} - {{ join(', ', App\Formatters\User::format($contact, ['email', 'phone', 'mobile'])) }}</li>
                    @endforeach
                </ul>
            </x-larastrap::suggestion>
        @endif

        @include('booking.partials.showtable')
        <?php $grand_total += $o->getValue('effective', false) ?>
    @endforeach

    @if($more_orders)
        <table class="table">
            <thead>
                <tr>
                    <th class="text-end">
                        {{ __('texts.orders.totals.complete') }}: <span class="all-bookings-total">{{ printablePrice($grand_total) }}</span> {{ $currentgas->currency }}
                    </th>
                </tr>
            </thead>
        </table>
    @endif
</x-larastrap::mform>
