<?php

return array (
  'dynamics' => 
  array (
    'values' => 
    array (
      'quantity' => 'la quantité',
      'price' => 'la valeur',
      'order_price' => '',
      'weight' => 'le poids',
    ),
    'targets' => 
    array (
      'product' => 
      array (
        'booking' => 'du produit dans la réservation',
        'order' => 'du produit dans la commande',
      ),
      'order' => 
      array (
        'booking' => 'de la réservation',
        'order' => 'de la commande',
      ),
      'aggregate' => 
      array (
        'booking' => 'de la réservation groupée',
        'order' => 'de la commande groupée',
      ),
      'circle' => 
      array (
        'booking' => '',
        'order' => '',
      ),
    ),
    'scale' => 
    array (
      'minor' => 'est inférieur à',
      'major' => 'est supérieur à',
    ),
    'distribution' => 
    array (
      'sum' => 
      array (
        'product' => 'ajouter au coût du produit',
        'booking' => 'ajouter au coût de la réservation',
        'order' => 'ajouter au coût de la commande',
        'product_kg' => '',
        'booking_kg' => '',
        'order_kg' => '',
      ),
      'sub' => 
      array (
        'product' => 'soustraire au coût du produit',
        'booking' => 'soustraire au coût de la réservation',
        'order' => 'soustraire au coût de la commande',
        'product_kg' => '',
        'booking_kg' => '',
        'order_kg' => '',
      ),
      'passive' => 
      array (
        'product' => 'par rapport au coût du produit, calculer',
        'booking' => 'par rapport au coût de la réservation, calculer',
        'order' => 'par rapport au coût de la commande, calculer',
        'product_kg' => '',
        'booking_kg' => '',
        'order_kg' => '',
      ),
      'apply' => 
      array (
        'product' => 'appliquer le prix unitaire',
      ),
    ),
    'types' => 
    array (
      'quantity' => 'et le distribuer en fonction des quantités réservées',
      'price' => 'et le distribuer en fonction de la valeur des réservations',
      'weight' => 'et le distribuer en fonction du poids des réservations',
    ),
    'template' => 'Si :value :target :scale',
  ),
);