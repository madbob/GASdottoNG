<?php

return array (
  'defaults' => 
  array (
    'discount' => 'Discount',
    'rounding' => 'Manual Delivery Rounding',
    'delivery' => 'Shipment Cost',
  ),
  'dynamics' => 
  array (
    'values' => 
    array (
      'quantity' => 'the quantity',
      'price' => 'the value',
      'order_price' => 'order\'s value',
      'weight' => 'the weight',
    ),
    'targets' => 
    array (
      'product' => 
      array (
        'booking' => 'of product in the booking',
        'order' => 'of product in the order',
      ),
      'order' => 
      array (
        'booking' => 'of the booking',
        'order' => 'of the order',
      ),
      'aggregate' => 
      array (
        'booking' => 'of the aggregate booking',
        'order' => 'of the aggregate order',
      ),
      'circle' => 
      array (
        'booking' => '',
        'order' => '',
      ),
    ),
    'scale' => 
    array (
      'minor' => 'is minor than',
      'major' => 'is major than',
    ),
    'distribution' => 
    array (
      'sum' => 
      array (
        'product' => 'sum to the cost of the product',
        'booking' => 'sum to the cost of the booking',
        'order' => 'sum to the cost of the order',
        'product_kg' => 'for each Kg of product, sum',
        'booking_kg' => 'for each Kg in the booking, add',
        'order_kg' => 'for each Kg in the order, sum',
      ),
      'sub' => 
      array (
        'product' => 'subtract from the cost of the product',
        'booking' => 'subtract to the cost of the booking',
        'order' => 'subtract to the cost of the order',
        'product_kg' => 'for each Kg of product, substract',
        'booking_kg' => 'for each Kg in the booking, substract',
        'order_kg' => 'for each Kg in the order, substract',
      ),
      'passive' => 
      array (
        'product' => 'compared to the cost of the product, calculate',
        'booking' => 'compared to the cost of the booking, calculate',
        'order' => 'compared to the cost of the order, calculate',
        'product_kg' => 'for each Kg of product, calculate',
        'booking_kg' => 'for each Kg in the booking, calculate',
        'order_kg' => 'for each Kg in the order, calculate',
      ),
      'apply' => 
      array (
        'product' => 'apply the unit price',
      ),
    ),
    'types' => 
    array (
      'quantity' => 'and distribute it according to the quantities booked',
      'price' => 'and distribute it according to the value of the bookings',
      'weight' => 'and distribute it according to the weight of the bookings',
    ),
    'template' => 'If :value :target :scale',
  ),
  'all' => 'Modifiers',
  'name' => 'Modifier',
  'help' => 
  array (
    'no_modifiers_for_element' => '',
  ),
);