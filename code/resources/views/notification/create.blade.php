<x-larastrap::radios name="type" classes="notification-type-switch" :label="_i('Tipo')" :options="['notification' => _i('Notifica'), 'date' => _i('Data sul Calendario')]" value="notification" />

@include('notification.base-edit', ['notification' => null])
