<?php

return array (
  'global_filter' => 
  array (
    'roles' => 'All users with role :role',
    'orders' => 'All Participants to the order :supplier :number',
  ),
  'help' => 
  array (
    'repeat_mail_warning' => 'This notification has already been sent by e-mail. Keep this flag enabled to send a new e-mail.',
    'sending_mail_warning' => 'If you enable this option, the notification will be immediately sent by e-mail. If you want to edit it before forwarding it, enable this option only after having saved and edited the notification.',
    'visibility_by_selection' => 'If no users are selected, the element will be accessible to everyone.',
    'suspend' => 'If an automatic order is suspended, the next openings will be skipped. Use this option to manage periods when GAS is not active, such as holidays.',
    'arbitrary_dates' => 'Here you can add arbitrary dates into the shippings calendar, even for non-existing orders. This function is suggested to coordinate different referents and schedule long-term activities.',
  ),
  'name' => 'Notification',
  'notices' => 
  array (
    'new_notification_from' => 'New notification from :author',
    'attached_order' => 'Attached the file for the order :gasname.',
  ),
  'recurrence' => 'Recurrence',
  'greetings' => 'Best regards',
  'send_to_current_users' => 'Notify filtered users',
  'next_dates' => 'Next dates in calendar:',
  'next_auto_orders' => 'Next dates for automation orders:',
  'list' => 
  array (
    'closed_orders' => 'Closed orders',
    'confirmed_dates' => 'Confirmed dates',
    'temporary_dates' => 'Temporary dates',
    'appointments' => 'Appointments',
  ),
  'calendar_date' => 'Date on Calendar',
  'date_reference' => '',
);