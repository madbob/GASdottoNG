<?php

return array (
  'booking' => 
  array (
    'void' => 'Cancel Reservation',
    'statuses' => 
    array (
      'open' => 'Reservations Open',
      'closed' => 'Reservations Closed',
      'shipped' => 'Delivered',
      'paying' => 'User Payment',
      'archived' => 'Archived',
      'suspended' => 'Pending',
      'booked' => 'Booked',
      'to_deliver' => 'To deliver',
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
    'no_partecipating' => 'You did not participate in this order',
    'formatted_booked_amount' => 'You have ordered :amount',
    'formatted_booked_amount_with_friends' => 'You have ordered :amount + :friends',
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
    'target_supplier_notifications' => 'If this option is not enabled, users will only receive email notifications for orders from suppliers they have individually enabled from their personal configuration panel. If enabled, all users will receive an email notification each time an order is opened.',
    'notify_only_partecipants' => 'Only users who have taken part in the order will receive the notification',
    'comment' => 'Any informational text to display in the order title. If longer than :limit characters, the text is included in the relevant reservations panel instead.',
    'end' => 'Order closing date. At the end of the day indicated here, the order will automatically be set to the "Closed" status',
    'contacts' => 'The contacts of the selected users will be displayed in the booking panel. Hold down Ctrl to select multiple users',
    'handle_packages' => 'If this option is enabled, when closing the order it will be checked whether there are products whose total quantity ordered is not a multiple of the size of the relative package. If so, the order will remain open and it will be possible for users to book only those specific products until the desired quantity is reached.',
    'payment' => 'From here you can enter the accounting movement of the order payment to the supplier, which will alter the relative balance.',
    'no_opened' => 'There are no open reservations.',
    'no_delivering' => 'There are no orders in delivery.',
    'include_all_modifiers' => 'Use this function to include or exclude modifiers that are not intended for the supplier. It is recommended to select \'No\' if the document will be forwarded to the supplier, and \'Yes\' if the document is used for deliveries by qualified persons.',
    'supplier_multi_select' => 'By selecting multiple suppliers, the respective orders will be generated and automatically aggregated. This function is activated if there are at least 3 aggregates in the database with at least :theshold orders each.',
    'start' => 'By setting a future date here, and the status Pending, this order will automatically be opened on the specified date.',
    'manual_fixes_explain' => 'Here you can change the reserved quantity for this product for each reservation, but no user has already participated to the order.',
    'pending_notes' => 'Some users left some note to their bookings.',
    'no_partecipation_notice' => 'You did not participate in this order.',
    'modifiers_notice' => 'The value of some modifiers will be recalculated when the order is in "Delivered" status.<br><a target="_blank" href="https://www.gasdotto.net/docs/modificatori#distribuzione">Read more</a>',
    'no_categories' => 'There are no categories to filter',
    'supplier_no_orders' => 'There are currently no open orders for this supplier.',
    'supplier_has_orders' => 'There are open orders for this supplier',
    'unremovable_warning' => 'The order :name currently has active reservations, and therefore cannot be removed.',
    'unremovable_instructions' => 'It is recommended to access the <a href=":link">reservations panel for this order</a> and, using the "Reservations for Other Users" tool, invalidate existing reservations.',
    'unremovable_notice' => 'This mechanism is deliberately non-automatic and intentionally complex, to avoid involuntary data loss.',
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
  'and_more' => 'and other',
  'boxes' => 'Number of Packs',
  'supplier' => 'Supplier',
  'booking_date_time' => 'Date/Time of Booking',
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
    'with_modifiers' => 'Total including Modifiers',
    'total' => 'Total',
    'taxable' => 'Taxable Total',
    'vat' => 'VAT Total',
    'booked' => 'Total Booked',
    'complete' => 'Grand Total',
    'invoice' => 'Total invoice',
    'orders' => 'Total orders',
    'manual' => 'Manual Total',
    'to_pay' => 'Amount to be paid',
    'selected' => 'Selected Total',
  ),
  'constraints' => 
  array (
    'quantity' => 'Maximum quantity is 9999.99',
    'discrete' => 'Quantity of this product must be integer',
    'global_min' => 'Global Minimum: :still (:global total)',
    'global_max_help' => ':still :measure missing to complete the package for this order',
    'global_max_short' => ':icon Available: :quantity',
    'global_max' => 'Available: :still (:global total)',
    'global_max_generic' => 'Quantity over the availability',
    'relative_max_formatted' => 'Suggested Max: :quantity',
    'relative_max' => 'Quantity over the suggested maximum',
    'relative_min_formatted' => 'Min: :quantity',
    'relative_min' => 'Quantity below the minimum allowed',
    'relative_multiple_formatted' => 'Multiple: :quantity',
    'relative_multiple' => 'Quantity not a multiple of the allowed value',
  ),
  'documents' => 
  array (
    'shipping' => 
    array (
      'filename' => 'Delivery details orders :suppliers.pdf',
      'heading' => 'Details order :identifier to :supplier on :date',
      'short_heading' => 'Deliveries Details on :date',
    ),
    'summary' => 
    array (
      'heading' => 'Products in order %s at %s',
    ),
    'table' => 
    array (
      'filename' => 'Table Order %s to %s.csv',
    ),
  ),
  'all' => 'Orders',
  'pending_packages' => 'Packages to be Completed',
  'booking_aggregation' => 'Booking Aggregation',
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
    'calculator' => 'Enter the weight of the individual pieces involved in the delivery here to obtain the total.',
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
      'shipping_and_summary' => 'Shipping Details + Products Summary',
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
  'list_delivering' => 'Orders in Delivery',
  'help_aggregate_export_table' => 'Here you can obtain a CSV document with the details of all ordered products in the current order.',
  'help_aggregate_export_table_for_delivery' => 'If you intend to use this document with the \'Deliveries -> Import CSV\' function, to import the delivered quantities after having processed them manually, we recommend that you also include the Username of the users involved in the export.',
  'include_all_modifiers' => 'Include all modifiers',
  'help_aggregate_export_shipping' => 'From here you can obtain a PDF document ready to be printed, with all information about all reservation to all orders included in this aggregate.',
  'bookings_from_friends' => 'Reservations from you friends',
  'communications_points' => 'For communications about this order it is suggested to contact:',
  'booking_total_amount' => 'To pay: :amount',
  'formatted_delivery_date' => 'Delivery will happen on :date.',
  'notes_to_supplier' => 'Notes for the Supplier',
  'summaries_recipients_count' => 'Users who will receive the email: :count',
  'bookings_to_pay' => 'Bookings to pay',
  'automatic_labels' => 
  array (
    'delivery' => 'delivery',
    'days_after' => 'days after',
    'close' => 'close',
    'days_before' => 'days before',
    'open' => 'open',
  ),
);
