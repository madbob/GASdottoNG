@include('commons.multipleusers', ['obj' => $notification, 'name' => 'users', 'label' => __('texts.generic.recipients')])
<x-larastrap::textarea name="content" tlabel="generic.mailfield.body" required />
@include('notification.partials.attachment', ['notification' => $notification])
