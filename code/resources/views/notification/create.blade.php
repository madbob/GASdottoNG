<x-larastrap::radios name="type" classes="notification-type-switch" tlabel="generic.type" :options="[
    'notification' => __('notifications.name'),
    'date' => __('notifications.calendar_date')
]" value="notification" />

@include('notification.base-edit', ['notification' => null])
