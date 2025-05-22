<?php

return array (
  'prices' => 
  array (
    'unit' => 'Unit price',
  ),
  'name' => 'Product',
  'code' => 'Supplier code',
  'bookable' => 'Available to order',
  'vat_rate' => 'VAT rate',
  'portion_quantity' => 'Pieces',
  'multiple' => 'Multiple',
  'min_quantity' => 'Minimum',
  'max_quantity' => 'Suggested Maximum',
  'available' => 'Available',
  'list' => 'Products',
  'help' => 
  array (
    'bookable' => 'Specifies if the product is available for ordering within the upcoming supplier orders',
    'pending_orders_change_price' => 'There are pending orders and archived ones where the product whose price you just modified is listed. Please choose those in which you want the new price to be applied (for the product and/or any price differences in potential variants).',
    'pending_orders_change_price_second' => 'If you modify the prices and there are reservations in the order that have already been delivered, you will need to manually re-save those deliveries to regenerate the updated accounting transactions.',
    'discrete_measure_selected_notice' => 'You\'ve chosen a discrete unit of measurement, meaning only whole quantities can be used for this product.',
    'measure' => 'Assigned measurement unit for the product. Note: it may affect the activation of certain product variables. Please refer to the Discrete Unit parameter in the measurement unit administration panel (accessible only to authorised users)',
    'portion_quantity' => 'If different from 0, each unit is considered as expressed in this quantity. For example:<ul><li>unit of measurement: kilograms</li><li>quantity per unit: 0.3</li><li>unit price: 10 euros</li><li>reserved quantity: 1 (thus understood as 1 piece weighing 0.3 kilograms)</li><li>cost: 1 x 0.3 x 10 = 3 euros</li></ul>This is helpful for managing products sold by pieces, which users can reserve in specific quantities but need to be ordered and/or paid for in total quantity from the supplier',
    'package_size' => 'If the product is distributed in packages of N pieces, enter the value of N here. Orders to the supplier must be expressed in the number of packages. The number of pieces ordered must be a multiple of N. If the total number of pieces ordered is not a multiple of N, the product will be marked with a red icon in the order summary panel. You can then adjust the quantities by adding or removing pieces as needed.',
    'multiple' => '',
    'min_quantity' => 'If different from 0, reservations for the product are accepted only for quantities greater than the specified one',
    'max_quantity' => 'If different from 0, a warning is displayed when a quantity greater than the one indicated is booked',
    'available' => '',
    'global_min' => '',
    'variants' => 'Every product can have variants, such as size or color for clothing items. During the booking phase, users will be able to specify different quantities for each variant combination.',
    'duplicate_notice' => 'The duplicated product will have a copy of the variants from the original product. Those will be editable after saving.',
    'unit_price' => '',
    'vat_rate' => '',
    'notice_removing_product_in_orders' => '',
  ),
  'variant' => 
  array (
    'matrix' => 'Variants\' Modifications Matrix',
    'help' => 
    array (
      'code' => '',
      'price_difference' => '',
    ),
    'price_difference' => 'Price Difference',
    'weight_difference' => 'Weight Difference',
  ),
  'package_size' => 'Box',
  'global_min' => 'Total Minimum',
  'variants' => 'Variants',
  'remove_confirm' => '',
  'removing' => 
  array (
    'keep' => '',
    'leave' => '',
  ),
);