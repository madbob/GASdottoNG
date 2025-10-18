<x-larastrap::radios name="type" classes="selective-display" :attributes="['data-target' => '.notify-field']" tlabel="generic.type" :options="[
    'notification' => __('texts.notifications.name'),
    'permanent' => __('texts.notifications.permanent_notification'),
    'date' => __('texts.notifications.calendar_date')
]" value="notification" tpophelp="notifications.help.types" />

<div class="notify-field" data-type="notification">
    @include('notification.base-edit', ['notification' => null])
</div>

<div class="notify-field" data-type="permanent">
    @include('notification.permanent-base-edit', ['notification' => null])
</div>

<div class="notify-field" data-type="date">
    <x-larastrap::textarea name="content" tlabel="generic.mailfield.body" required />
    <x-larastrap::datepicker name="start_date" tlabel="generic.date" defaults_now required />
</div>
