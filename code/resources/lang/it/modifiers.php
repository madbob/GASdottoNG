<?php

return array (
  'dynamics' => 
  array (
    'values' => 
    array (
      'quantity' => 'la quantità',
      'price' => 'il valore',
      'order_price' => 'il valore dell\'ordine',
      'weight' => 'il peso',
    ),
    'targets' => 
    array (
      'product' => 
      array (
        'booking' => 'di prodotto nella prenotazione',
        'order' => 'di prodotto nell\'ordine',
      ),
      'order' => 
      array (
        'booking' => 'della prenotazione',
        'order' => 'dell\'ordine',
      ),
      'aggregate' => 
      array (
        'booking' => 'della prenotazione aggregata',
        'order' => 'dell\'ordine aggregato',
      ),
      'circle' => 
      array (
        'booking' => 'della prenotazione assegnata al cerchio',
        'order' => 'della porzione di ordine destinata al cerchio',
      ),
    ),
    'scale' => 
    array (
      'minor' => 'è minore di',
      'major' => 'è maggiore di',
    ),
    'distribution' => 
    array (
      'sum' => 
      array (
        'product' => 'somma al costo del prodotto',
        'booking' => 'somma al costo della prenotazione',
        'order' => 'somma al costo dell\'ordine',
        'product_kg' => 'per ogni Kg di prodotto, somma',
        'booking_kg' => 'per ogni Kg nella prenotazione, somma',
        'order_kg' => 'per ogni Kg nell\'ordine, somma',
      ),
      'sub' => 
      array (
        'product' => 'sottrai al costo del prodotto',
        'booking' => 'sottrai al costo della prenotazione',
        'order' => 'sottrai al costo dell\'ordine',
        'product_kg' => 'per ogni Kg di prodotto, sottrai',
        'booking_kg' => 'per ogni Kg nella prenotazione, sottrai',
        'order_kg' => 'per ogni Kg nell\'ordine, sottrai',
      ),
      'passive' => 
      array (
        'product' => 'rispetto al costo del prodotto, calcola',
        'booking' => 'rispetto al costo della prenotazione, calcola',
        'order' => 'rispetto al costo dell\'ordine, calcola',
        'product_kg' => 'per ogni Kg di prodotto, calcola',
        'booking_kg' => 'per ogni Kg nella prenotazione, calcola',
        'order_kg' => 'per ogni Kg nell\'ordine, calcola',
      ),
      'apply' => 
      array (
        'product' => 'applica il prezzo unitario',
      ),
    ),
    'types' => 
    array (
      'quantity' => 'e distribuiscilo in base alle quantità prenotate',
      'price' => 'e distribuiscilo in base al valore delle prenotazioni',
      'weight' => 'e distribuiscilo in base al peso delle prenotazioni',
    ),
    'template' => 'Se :value :target :scale',
  ),
  'all' => 'Modificatori',
  'name' => 'Modificatore',
  'help' => 
  array (
    'no_modifiers_for_element' => 'Non ci sono modificatori assegnabili a questo tipo di elemento.',
  ),
);