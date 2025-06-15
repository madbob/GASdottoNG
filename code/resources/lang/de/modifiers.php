<?php

return array (
  'defaults' => 
  array (
    'discount' => 'Skonto',
    'rounding' => '',
    'delivery' => 'Frachtkosten',
  ),
  'dynamics' => 
  array (
    'values' => 
    array (
      'quantity' => 'die Anzahl',
      'price' => 'der Wert',
      'order_price' => '',
      'weight' => 'das Gewicht',
    ),
    'targets' => 
    array (
      'product' => 
      array (
        'booking' => 'des Produkts in der Vorbestellung',
        'order' => 'des Produkts in der Bestellung',
      ),
      'order' => 
      array (
        'booking' => 'der Vorbestellung',
        'order' => 'der Bestellung',
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
      'minor' => 'kleiner ist als',
      'major' => 'größer ist als',
    ),
    'distribution' => 
    array (
      'sum' => 
      array (
        'product' => 'addiere zu den Kosten des Produkts hinzu',
        'booking' => 'addiere zum Gesamtbetrag der einzelnen Vorbestellung hinzu',
        'order' => 'addiere zu den Kosten der Gesamtbestellung hinzu',
        'product_kg' => '',
        'booking_kg' => '',
        'order_kg' => '',
      ),
      'sub' => 
      array (
        'product' => 'ziehe von den Kosten des Produkts ab',
        'booking' => 'ziehe vom Gesamtbetrag der einzelnen Vorbestellung ab',
        'order' => 'ziehe von den Kosten der Gesamtbestellung ab',
        'product_kg' => '',
        'booking_kg' => '',
        'order_kg' => '',
      ),
      'passive' => 
      array (
        'product' => 'verglichen mit den Kosten des Produkts, berechne',
        'booking' => 'verglichen mit dem Gesamtbetrag der Vorbestellung, berechne',
        'order' => 'verglichen mit den Kosten der Gesamtbestellung, berechne',
        'product_kg' => '',
        'booking_kg' => '',
        'order_kg' => '',
      ),
      'apply' => 
      array (
        'product' => 'wende den Stück-/Gebindepreis an',
      ),
    ),
    'types' => 
    array (
      'quantity' => 'und verteile dies entsprechend den vorbestellten Mengen',
      'price' => 'und verteile dies entsprechend dem Wert der Vorbestellungen',
      'weight' => 'und verteile dies entsprechend dem Gewicht der Vorbestellungen',
    ),
    'template' => 'Wenn :value :target :scale',
  ),
  'all' => 'Ändern',
  'name' => 'Ändern',
  'help' => 
  array (
    'no_modifiers_for_element' => '',
  ),
);