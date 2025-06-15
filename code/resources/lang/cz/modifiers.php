<?php

return array (
  'defaults' => 
  array (
    'discount' => '',
    'rounding' => '',
    'delivery' => '',
  ),
  'dynamics' => 
  array (
    'values' => 
    array (
      'quantity' => '',
      'price' => '',
      'order_price' => '',
      'weight' => '',
    ),
    'targets' => 
    array (
      'product' => 
      array (
        'booking' => '',
        'order' => '',
      ),
      'order' => 
      array (
        'booking' => '',
        'order' => '',
      ),
      'aggregate' => 
      array (
        'booking' => '',
        'order' => '',
      ),
      'circle' => 
      array (
        'booking' => '',
        'order' => '',
      ),
    ),
    'scale' => 
    array (
      'minor' => '',
      'major' => '',
    ),
    'distribution' => 
    array (
      'sum' => 
      array (
        'product' => '',
        'booking' => '',
        'order' => '',
        'product_kg' => '',
        'booking_kg' => '',
        'order_kg' => '',
      ),
      'sub' => 
      array (
        'product' => '',
        'booking' => '',
        'order' => '',
        'product_kg' => '',
        'booking_kg' => '',
        'order_kg' => '',
      ),
      'passive' => 
      array (
        'product' => '',
        'booking' => '',
        'order' => '',
        'product_kg' => '',
        'booking_kg' => '',
        'order_kg' => '',
      ),
      'apply' => 
      array (
        'product' => '',
      ),
    ),
    'types' => 
    array (
      'quantity' => '',
      'price' => '',
      'weight' => '',
    ),
    'template' => '',
  ),
  'all' => '',
  'name' => '',
  'help' => 
  array (
    'no_modifiers_for_element' => '',
  ),
);
