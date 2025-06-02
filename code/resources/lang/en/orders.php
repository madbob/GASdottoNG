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
    'send_booking_summaries' => 'This mail will be sent to those who have participated in the order but whose reservation has not yet been delivered.',
    'send_delivery_summaries' => 'This mail will be sent to those who have participated in the order and whose reservation has actually been delivered.',
    'product_selection' => 'To enable or disable products from the supplier list into the order',
    'booked_modifier_column' => 'Product Modifier, on Booked Quantities. Displayed only if the modifier is active for a product within the order',
    'delivered_modifier_column' => 'Product Modifier, on Shipped Quantities. Displayed only if the modifier is active for a product within the order',
    'fixes_column' => 'From this panel, you can edit the quantity of a product for any reservation and add notes for the supplier',
    'number' => 'Progressive number automatically assigned to each order',
    'unarchived_notice' => 'There are orders that have been closed for over a year but have not been archived. You can search for them using the search function below. It is recommended to archive old orders so that they are no longer displayed in the dashboard and page loading is faster. Archived orders can still be retrieved using the search function.',
    'extimated_value' => 'The value indicated here is an estimate, it will be aimed at closing the order',
    'insufficient_credit_notice' => 'Warning: your credit is insufficient to submit new bookings.',
    'friends_bookings_notice' => 'Here you can create sub-reservations assigned to your friends. Those will be part of your global reservation, but you can continue to keep the information divided. Fill up your list of friends from your profile page.',
    'no_friends' => 'There are no friends registered for this user.',
    'closed_order_alert_new_booking' => 'Attention: this order has been closed, before adding or editing a reservation be sure that the totals have not already been communicated to the supplier or they can be modified anyway.',
    'send_summaries' => 'Send a summary email of their reservation to all users who have participated in the order. It is possible to add a message to be attached to all for any additional comments. The summary message is automatically sent upon order closure, whether automatic or manual, if configured from the Configuration panel.',
    'automatic_instructions' => 'With this tool, you can manage the automatic opening and closing of orders. Orders that are opened and closed together (i.e., they have the same recurrence, close, and delivery parameters) will be automatically aggregated. When a recurrence is exhausted (all of its occurrences are past dates), it will be removed from this list.',
    'changed_products' => 'Warning: Some items have been modified after delivery within this order. In case of price adjustments, it is essential to rerun the affected deliveries to consolidate the updated totals and reprocess the corresponding accounting transactions.',
    'waiting_closing_for_deliveries' => 'This panel will be active once all the bookings have been closed',
    'modifiers_require_redistribution' => 'Order :name has modifiers whose value must be distributed among reservations. During delivery, this value was assigned proportionally, but the actual quantities delivered do not match the booked quantities and there may be some discrepancies.',
    'contacts_notice' => 'Per communications about this order it is suggested to contact:',
    'explain_aggregations' => 'Once merged, orders will be shown as a single one, preserving the attributes of each one. This function is suggested to simplify orders administration, for example for orders delivered in the same date.',
    'aggregation_instructions' => 'Click and drag orders in the same cell to merge or in an empty cell to separate them.',
    'status' => 'Current order status. Can take on the following values:<ul><li>open for bookings: all users can view the order and submit their bookings. When the order is in this state, announcement emails are also sent</li><li>closed for bookings: all users can see the order but can\'t add or change bookings. However, authorised users can still make adjustments</li><li>delivered: the order only appears in the list for authorised users, with no option to modify values or bookings</li><li>archived: the order is no longer listed but can be retrieved using the search function</li><li>pending: the order is visible in the list for authorised users and can be modified</li></ul>',
    'prices_changed' => 'The prices of certain products have been updated since your reservation. You can now decide whether to stick with the originally applied price or opt for the current list price in case of any adjustments to the delivery.',
    'variant_no_longer_active' => 'Please note: the variant you selected when making your reservation is no longer listed',
    'pending_saved_bookings' => 'Some booking in this order is saved but are not yet actually delivered or paid.',
    'mail_order_notification' => 'From this panel, you can enable specific email alerts for orders, to be sent to different people based on each order\'s status.',
    'target_supplier_notifications' => '',
    'notify_only_partecipants' => 'Only users who have taken part in the order will receive the notification',
    'comment' => '',
    'end' => '',
    'contacts' => '',
    'handle_packages' => '',
    'payment' => '',
    'no_opened' => 'There are no open reservations.',
    'no_delivering' => 'There are no orders in delivery.',
    'include_all_modifiers' => '',
    'supplier_multi_select' => '',
    'start' => '',
    'manual_fixes_explain' => 'Here you can change the reserved quantity for this product for each reservation, but no user has already participated to the order.',
    'pending_notes' => 'Some users left some note to their bookings.',
    'no_partecipation_notice' => 'You did not participate in this order.',
    'modifiers_notice' => '',
    'no_categories' => '',
    'supplier_no_orders' => '',
    'supplier_has_orders' => '',
    'unremovable_warning' => '',
    'unremovable_instructions' => '',
    'unremovable_notice' => '',
  ),
  'booking_description' => 
  array (
    'shipped' => 'Here is a summary of the delivered products:',
    'saved' => 'Here is a summary of the reserved products:',
    'pending' => 'Here is a summary of the ordered products:',
  ),
  'send_booking_summaries' => 'Send Reservations Summaries',
  'send_delivery_summaries' => 'Send Deliveries Summaries',
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
  'formatted_name' => 'form :start to :end',
  'formatted_delivery_in_name' => ', to be delivered :delivery',
  'quantities' => 
  array (
    'booked' => 'Booked Quantity',
    'shipped' => 'Delivered Quantity',
  ),
  'weights' => 
  array (
    'booked' => 'Booked Weight',
    'delivered' => 'Delivered Weight',
  ),
  'totals' => 
  array (
    'shipped' => 'Total Delivered',
    'total' => 'Total',
    'taxable' => 'Taxable Total',
    'vat' => 'VAT Total',
    'booked' => 'Total Booked',
    'complete' => 'Grand Total',
    'invoice' => 'Total invoice',
    'orders' => 'Total orders',
    'manual' => 'Manual Total',
    'to_pay' => 'Amount to be paid',
    'selected' => '',
  ),
  'all' => 'Orders',
  'statuses' => 
  array (
    'unchange' => 'Unchanged',
    'to_pay' => 'Orders to be Payed',
    'open' => 'Open',
    'closing' => 'Closing',
    'closed' => 'Closed',
  ),
  'do_aggregate' => 'Merge Orders',
  'admin_dates' => 'Dates Management',
  'admin_automatics' => 'Automatic Orders Management',
  'notices' => 
  array (
    'closed_orders' => 'Following orders have been closed:',
    'email_attachments' => 'In attachment the booking summaries, both in PDF and CSV.',
    'calculator' => '',
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
      'shipping_and_summary' => '',
    ),
  ),
  'help_aggregate_status' => 'From here, you can change the status of all orders included in the aggregate',
  'change_date' => 'Edit dates',
  'help_change_date' => 'From here, you can change the opening, closing, and delivery dates for all orders included in the aggregate',
  'last_summaries_date' => 'Latest notifications sent',
  'aggregate' => 'Merged Orders',
  'deliveries' => 'Deliveries',
  'fast_deliveries' => 'Quick Deliveries',
  'modifiers_redistribution_summary' => ':name - defined value: :defvalue / distributed value: :disvalue',
  'modifiers_redistribution' => 
  array (
    'keep' => 'Do nothing: leave unchanged the values calculated for the modifiers and the corresponding charges to individual users, even if their sum does not match the expected final value.',
    'recalculate' => 'Recalculate the modifiers and redistribute them based on actual deliveries. Payments made with user credit will be adjusted, and the corresponding balances will be updated. Payments made with other methods (cash, bank transfer, etc.) will remain the same, and any adjustments will be added to the current balance of each user.',
  ),
  'importing' => 
  array (
    'save' => 'Apply the amounts as saved, but keep the deliveries open',
    'close' => 'Flag the bookings as completed and generate the financial records for payments',
  ),
  'booked_by' => 'Booked By',
  'delivered_by' => 'Delivered By',
  'load_booked_quantities' => 'Load Reserved Quantities',
  'save_delivery' => 'Save informations',
  'do_delivery' => 'Delivery',
  'help_order_export_shipping' => 'From here you can obtain a document with all the informations about each reservation. Useful to handle while managing deliveries.',
  'notify_days_before' => 'How many days before?',
  'handle_packages' => 'Force pack completion',
  'documents' => 
  array (
    'shipping' => 
    array (
      'heading' => 'Details order :identifier to :supplier on :date',
      'short_heading' => 'Deliveries Details on :date',
    ),
  ),
  'list_delivering' => 'Orders in Delivery',
  'help_aggregate_export_table' => 'Here you can obtain a CSV document with the details of all ordered products in the current order.',
  'help_aggregate_export_table_for_delivery' => '',
  'include_all_modifiers' => 'Include all modifiers',
  'help_aggregate_export_shipping' => 'From here you can obtain a PDF document ready to be printed, with all information about all reservation to all orders included in this aggregate.',
  'bookings_from_friends' => 'Reservations from you friends',
  'communications_points' => 'For communications about this order it is suggested to contact:',
  'booking_total_amount' => 'To pay: :amount',
  'formatted_delivery_date' => 'Delivery will happen on :date.',
  'notes_to_supplier' => 'Notes for the Supplier',
  'summaries_recipients_count' => '',
  'bookings_to_pay' => '',
  'automatic_labels' => 
  array (
    'delivery' => '',
    'days_after' => '',
    'close' => '',
    'days_before' => '',
    'open' => '',
  ),
);