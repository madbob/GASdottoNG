@include('commons.radios', [
    'obj' => null,
    'name' => 'type',
    'values' => [
        'notification' => (object)['name' => _i('Notifica'), 'checked' => 1],
        'date' => (object)['name' => _i('Data sul Calendario')],
    ],
    'label' => _i('Tipo'),
    'extra_wrap_class' => 'notification-type-switch'
])

@include('notification.base-edit', ['notification' => null])
