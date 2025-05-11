<?php

return array (
  'code' => 'Codice Fornitore',
  'bookable' => 'Ordinabile',
  'vat_rate' => 'Aliquota IVA',
  'list' => 'Prodotti',
  'help' => 
  array (
    'bookable' => 'Indica se il prodotto potrà essere ordinato o meno all\'interno dei nuovi ordini per il fornitore',
  ),
  'variant' => 
  array (
    'matrix' => 'Modifica Matrice Varianti',
    'help' => 
    array (
      'code' => 'Se non viene specificato, tutte le varianti usano il Codice Fornitore del prodotto principale.',
      'price_difference' => 'Differenza di prezzo, positiva o negativa, da applicare al prezzo del prodotto quando una specifica combinazione di varianti viene selezionata.',
    ),
    'price_difference' => 'Differenza Prezzo',
    'weight_difference' => 'Differenza Peso',
  ),
);