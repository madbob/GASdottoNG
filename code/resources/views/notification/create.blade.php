<x-larastrap::radios name="type" classes="notification-type-switch" tlabel="generic.type" :options="['notification' => _i('Notifica'), 'date' => _i('Data sul Calendario')]" value="notification" />

@include('notification.base-edit', ['notification' => null])
