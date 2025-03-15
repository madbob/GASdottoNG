<div class="row">
    <div class="col-12 col-md-6">
        <x-filler :data-action="route('users.orders', $user->id)" data-fill-target="#user-booking-list">
            <x-larastrap::select-model name="supplier_id" :label="_i('Fornitore')" required :options="$currentgas->suppliers" :extra_options="[0 => _i('Tutti')]" />
            @include('commons.genericdaterange')
        </x-filler>
    </div>
</div>

<div class="row">
    <div class="col" id="user-booking-list">
        @include('commons.orderslist', ['orders' => $booked_orders ?? []])
    </div>
</div>
