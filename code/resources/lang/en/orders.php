<?php

return array (
  'booking' => 
  array (
    'void' => 'Cancel Reservation',
    'statuses' => 
    array (
      'shipped' => 'Delivered',
      'booked' => 'Booked',
      'saved' => 'Saved',
    ),
    'nav' => 
    array (
      'mine' => 'My reservation',
      'friends' => 'Reservation for friends',
      'others' => 'Reservation for Other Users',
      'add' => 'Add/Edit reservation',
    ),
  ),
  'help' => 
  array (
    'pending_packages_notice' => 'Warning: this order is closed, but it is possible to still reserve some product to complete the packages to deliver.',
    'number' => 'Progressive number automatically assigned to each order',
    'insufficient_credit_notice' => 'Warning: your credit is insufficient to submit new bookings.',
    'friends_bookings_notice' => 'Here you can create sub-reservations assigned to your friends. Those will be part of your global reservation, but you can continue to keep the information divided. Fill up your list of friends from your profile page.',
    'no_friends' => 'There are no friends registered for this user.',
    'closed_order_alert_new_booking' => 'Attention: this order has been closed, before adding or editing a reservation be sure that the totals have not already been communicated to the supplier or they can be modified anyway.',
    'automatic_instructions' => 'With this tool, you can manage the automatic opening and closing of orders. Orders that are opened and closed together (i.e., they have the same recurrence, close, and delivery parameters) will be automatically aggregated. When a recurrence is exhausted (all of its occurrences are past dates), it will be removed from this list.',
    'contacts_notice' => 'Per communications about this order it is suggested to contact:',
    'comment' => '',
    'end' => '',
    'contacts' => '',
    'handle_packages' => '',
    'payment' => '',
    'no_partecipation_notice' => 'You did not participate in this order.',
    'modifiers_notice' => '',
    'no_categories' => '',
  ),
  'packages' => 
  array (
    'ignore' => 'No, ignore the package sizes',
    'permit' => 'Yes, permit other bookings',
    'permit_all' => 'Yes, and care on the quantities booked by all groups',
  ),
  'supplier' => 'Supplier',
  'list_open' => 'Open Orders',
  'dates' => 
  array (
    'shipping' => 'Delivery Date',
    'start' => 'Reservations Opening Date',
    'end' => 'Reservations Closing date',
  ),
  'name' => 'Order',
  'totals' => 
  array (
    'shipped' => 'Total Delivered',
    'total' => 'Total',
    'booked' => 'Total Booked',
    'complete' => 'Grand Total',
  ),
  'statuses' => 
  array (
    'unchange' => 'Unchanged',
  ),
  'files' => 
  array (
    'aggregate' => 
    array (
      'shipping' => 'Aggregated Deliveries Details',
      'summary' => 'Aggregated Products Summary',
      'table' => 'Aggregated Products Table',
    ),
    'order' => 
    array (
      'summary' => 'Products Summary',
      'shipping' => 'Shipping Details',
      'table' => 'Main Products Table',
    ),
  ),
  'help_aggregate_status' => 'From here, you can change the status of all orders included in the aggregate',
  'change_date' => 'Edit dates',
  'help_change_date' => 'From here, you can change the opening, closing, and delivery dates for all orders included in the aggregate',
  'help_order_export_shipping' => 'From here you can obtain a document with all the informations about each reservation. Useful to handle while managing deliveries.',
  'handle_packages' => 'Force pack completion',
  'help_aggregate_export_table' => 'Here you can obtain a CSV document with the details of all ordered products in the current order.',
  'help_aggregate_export_table_for_delivery' => '',
  'help_aggregate_export_shipping' => 'From here you can obtain a PDF document ready to be printed, with all information about all reservation to all orders included in this aggregate.',
);