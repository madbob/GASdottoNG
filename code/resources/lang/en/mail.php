<?php

return array (
  'help' => 
  array (
    'removed_email_log' => 'Removed mail address :address',
    'send_error' => 'Could not forward e-mail to :email: :message',
  ),
  'summary' => 
  array (
    'defaults' => 
    array (
      'subject' => 'GAS booking summary: :supplier – delivery :delivery',
    ),
  ),
  'closed' => 
  array (
    'defaults' => 
    array (
      'subject' => 'Order automatically closed',
    ),
  ),
  'notification' => 
  array (
    'defaults' => 
    array (
      'subject' => 'New notification from :gas',
    ),
  ),
  'new_user_notification' => 
  array (
    'defaults' => 
    array (
      'subject' => 'New user registered',
    ),
  ),
  'contacts_prefix' => 'For info: :contacts',
  'approved' => 
  array (
    'defaults' => 
    array (
      'subject' => 'Welcome!',
      'body' => 'Welcome to %[gas_name]!
From now on, you can access it via the link below with the username ‘%[username]’ and the password of your choice.
%[gas_login_link]',
    ),
    'description' => 'Message for approved users.',
    'username' => 'Username assigned to the new user',
    'link' => 'Login page link',
  ),
  'declined' => 
  array (
    'defaults' => 
    array (
      'subject' => 'You have not been approved!',
      'body' => 'Sorry, but your account has not been approved by %[gas_name].',
    ),
    'description' => 'Message for unapproved users.',
  ),
  'mail' => 
  array (
    'welcome' => 
    array (
      'defaults' => 
      array (
        'body' => 'You have been invited to %[gas_name]!

To access the first time, click the link below.
%[gas_access_link]

Then you will be able to access thorugh this other link, using the username "%[username]" and the password you have choose.
%[gas_login_link]
',
      ),
    ),
  ),
  'order' => 
  array (
    'defaults' => 
    array (
      'subject' => 'New order opened for %[supplier_name]',
      'body' => '%[gas_name] just opened a new order for %[supplier_name].
You can make your reservation at the following link:
%[gas_booking_link]
Reservations will be closed on %[closing_date]',
    ),
    'description' => 'Notification for newly opened orders (sent to users who explicitely enabled notifications for the supplier).',
    'comment' => 'Order comment',
    'link' => 'Link for reservations',
    'mails' => 'E-mail addresses of the referents for the order',
  ),
  'reminder' => 
  array (
    'defaults' => 
    array (
      'subject' => 'Closing orders for %[gas_name]',
      'body' => 'Orders opened by %[gas_name] for those suppliers will be closed within a few days:

%[orders_list]',
    ),
    'description' => 'Notification for closing orders (sent to users who explicitely enabled notifications for the supplier).',
    'list' => 'List of closing orders',
  ),
  'password' => 
  array (
    'defaults' => 
    array (
      'body' => 'The reset of your GASdotto password has been required.
Click the link below to update your password, or ignore this e-email if you have not required this operation.
%[gas_reset_link]',
    ),
    'description' => 'Message for password reset.',
    'link' => 'Password reset link',
  ),
  'receipt' => 
  array (
    'defaults' => 
    array (
      'subject' => 'New Invoice from %[gas_name]',
      'body' => 'In attachment, the last invoice from %[gas_name]',
    ),
    'description' => 'E-mail for receipts.',
  ),
  'supplier' => 
  array (
    'defaults' => 
    array (
      'subject' => 'Booking order %[gas_name]',
      'body' => 'Hello.
In attachment you find - both in PDF and CSV - the bookings from %[gas_name].
For contacts, you can write to the referents here in CC.
Thank you.',
    ),
    'description' => 'Notification for suppliers when the order is automatically closed.',
  ),
  'credit' => 
  array (
    'current' => 'Current credit for the user',
  ),
  'welcome' => 
  array (
    'description' => 'Message for users newly created on the platform.',
    'link' => 'Link to access the first time',
  ),
  'newuser' => 
  array (
    'description_manual' => 'Message for users newly subscribed to the platform, waiting for approval.',
    'description' => 'Message for users newly subscribed to the platform.',
  ),
);