<?php

return array (
  'waiting' => 'Waiting',
  'statuses' => 
  array (
    'to_verify' => 'To be Verified',
    'verified' => 'Verified',
    'payed' => 'Payed',
  ),
  'default_note' => 'Invoice payment :name',
  'documents' => 
  array (
    'invoice' => 
    array (
      'heading' => 'Invoice :identifier',
    ),
    'receipts' => 
    array (
      'list_filename' => 'Export GAS receipts :date.csv',
    ),
  ),
  'balances' => 
  array (
    'supplier' => 'Supplier Balance',
  ),
  'forwarded' => 'Forwarded',
  'orders' => 'Involved Orders',
  'help' => 
  array (
    'orders' => 'Choose the orders that are associated with this invoice. When the invoice is marked as paid, the reference to the payment accounting transaction will be added to them and they will be automatically archived',
    'no_orders' => 'There are no orders assignable to this invoice. Orders must: refer to the same supplier as the invoice; not have a payment to the supplier already registered; be in "Delivered" or "Archived" status; have at least one "Delivered" reservation (the total of delivered reservations is used to calculate the actual payment).',
    'filtered_orders' => 'Here you will see orders that: belong to the supplier to whom the invoice is addressed; are in Delivered or Archived status; have at least one reservation marked as Delivered. Totals are calculated on the quantities actually delivered, not on the reservations.',
  ),
  'change_orders' => 'Edit Orders',
  'verify' => 'Verify Contents',
  'other_modifiers' => 'Other modifiers not applicable to this invoice:',
  'payment' => 'Register Payment',
  'get_or_send' => 'Download or forward',
  'new' => 'Load new invoice',
  'send_pending_receipts' => 'Send Pending Receipts',
  'shipping_of' => 'Delivery: %s',
);
