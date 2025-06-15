<?php

return array (
  'prices' => 
  array (
    'unit' => 'Prezzo Unitario',
    'unit_no_vat' => 'Prezzo Unitario (senza IVA)',
    'package' => 'Prezzo Confezione',
  ),
  'name' => 'Prodotto',
  'code' => 'Codice Fornitore',
  'bookable' => 'Ordinabile',
  'vat_rate' => 'Aliquota IVA',
  'portion_quantity' => 'Pezzatura',
  'multiple' => 'Multiplo',
  'min_quantity' => 'Minimo',
  'max_quantity' => 'Massimo Consigliato',
  'available' => 'Disponibile',
  'help' => 
  array (
    'unit_no_vat' => 'Da usare in combinazione con Aliquota IVA',
    'package_price' => 'Se specificato, il prezzo unitario viene calcolato come Prezzo Confezione / Dimensione Confezione',
    'importing_categories_and_measures' => 'Le categorie e le unità di misura il cui nome non sarà trovato tra quelle esistenti saranno create.',
    'imported_notice' => 'Prodotti importati',
    'available_explain' => 'Quantità massima di prodotto che complessivamente può essere prenotata in un ordine',
    'bookable' => 'Indica se il prodotto potrà essere ordinato o meno all\'interno dei nuovi ordini per il fornitore',
    'pending_orders_change_price' => 'Ci sono ordini non ancora consegnati e archiviati in cui appare il prodotto di cui ha appena modificato il prezzo. Seleziona quelli in cui vuoi che venga applicato il nuovo prezzo (del prodotto e/o le differenze prezzi delle eventuali varianti).',
    'pending_orders_change_price_second' => 'Se modifichi i prezzi e nell\'ordine ci sono prenotazioni che sono già state consegnate, dovrai manualmente salvare nuovamente tali consegne affinché vengano rigenerati i nuovi movimenti contabili aggiornati.',
    'discrete_measure_selected_notice' => 'Hai selezionato una unità di misura discreta: per questo prodotto possono essere usate solo quantità intere.',
    'measure' => 'Unità di misura assegnata al prodotto. Attenzione: può influenzare l\'abilitazione di alcune variabili del prodotto, si veda il parametro Unità Discreta nel pannello di amministrazione delle unità di misura (acessibile solo agli utenti abilitati)',
    'portion_quantity' => 'Se diverso da 0, ogni unità si intende espressa come questa quantità. Esempio:<ul><li>unità di misura: chili</li><li>pezzatura: 0.3</li><li>prezzo unitario: 10 euro</li><li>quantità prenotata: 1 (che dunque si intende 1 pezzo da 0.3 chili)</li><li>costo: 1 x 0.3 x 10 = 3 euro</li></ul>Utile per gestire prodotti distribuiti in pezzi, prenotabili dagli utenti in numero di pezzi ma da ordinare e/o pagare presso il fornitore come quantità complessiva',
    'package_size' => 'Se il prodotto viene distribuito in confezioni da N pezzi, indicare qui il valore di N. Gli ordini da sottoporre al fornitore dovranno essere espressi in numero di confezioni, ovvero numero di pezzi ordinati / numero di pezzi nella confezione. Se la quantità totale di pezzi ordinati non è un multiplo di questo numero il prodotto sarà marcato con una icona rossa nel pannello di riassunto dell\'ordine, da cui sarà possibile sistemare le quantità aggiungendo e togliendo ove opportuno.',
    'multiple' => 'Se diverso da 0, il prodotto è prenotabile solo per multipli di questo valore. Utile per prodotti pre-confezionati ma prenotabili individualmente. Da non confondere con l\'attributo Confezione',
    'min_quantity' => 'Se diverso da 0, il prodotto è prenotabile solo per una quantità superiore a quella indicata',
    'max_quantity' => 'Se diverso da 0, se viene prenotata una quantità superiore di quella indicata viene mostrato un warning',
    'available' => 'Se diverso da 0, questa è la quantità massima di prodotto che complessivamente può essere prenotata in un ordine. In fase di prenotazione gli utenti vedranno quanto è già stato sinora prenotato in tutto',
    'global_min' => 'Se diverso da 0, questa è la quantità minima di prodotto che complessivamente può essere prenotata in un ordine. In fase di prenotazione gli utenti vedranno quanto è già stato sinora prenotato in tutto',
    'variants' => 'Ogni prodotto può avere delle varianti, ad esempio la taglia o il colore per i capi di abbigliamento. In fase di prenotazione, gli utenti potranno indicare quantità diverse per ogni combinazione di varianti.',
    'duplicate_notice' => 'Il duplicato avrà una copia delle varianti e dei modificatori del prodotto originario. Potranno essere eventualmente modificati dopo il salvataggio.',
    'unit_price' => 'Prezzo unitario per unità di misura. Si intende IVA inclusa, per maggiori dettagli si veda il campo Aliquota IVA. Può assumere un significato particolare quando viene attivata la Pezzatura',
    'vat_rate' => 'Le aliquote esistenti possono essere configurate nel pannello Configurazioni',
    'notice_removing_product_in_orders' => 'Il prodotto è attualmente incluso in ordini non ancora consegnati. Cosa vuoi fare?',
  ),
  'weight_with_measure' => 'Peso (in KG)',
  'list' => 'Prodotti',
  'sorting' => 'Ordinamento',
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
  'package_size' => 'Dimensione Confezione',
  'global_min' => 'Minimo Complessivo',
  'variants' => 'Varianti',
  'remove_confirm' => 'Vuoi davvero eliminare il prodotto :name?',
  'removing' => 
  array (
    'keep' => 'Lascia il prodotto',
    'leave' => 'Togli il prodotto ed elimina tutte le relative prenotazioni',
  ),
);