<?php

return array (
  'waiting' => 'In Attesa',
  'statuses' => 
  array (
    'to_verify' => 'Da Verificare',
    'verified' => 'Verificata',
    'payed' => 'Pagata',
  ),
  'default_note' => 'Pagamento fattura :name',
  'documents' => 
  array (
    'invoice' => 
    array (
      'heading' => 'Fattura :identifier',
    ),
    'receipts' => 
    array (
      'list_filename' => 'Esportazione ricevute GAS :date.csv',
    ),
  ),
  'balances' => 
  array (
    'supplier' => 'Saldo Fornitore',
  ),
  'forwarded' => 'Inoltrata',
  'orders' => 'Ordini Coinvolti',
  'help' => 
  array (
    'orders' => 'Seleziona gli ordini che sono coinvolti in questa fattura. Quando la fatturà sarà marcata come pagata, ad essi sarà aggiunto il riferimento al movimento contabile di pagamento e saranno automaticamente archiviati',
    'no_orders' => 'Non ci sono ordini assegnabili a questa fattura. Gli ordini devono: fare riferimento allo stesso fornitore cui è assegnata la fattura; non avere un pagamento al fornitore già registrato; essere in stato "Consegnato" o "Archiviato"; avere almeno una prenotazione "Consegnata" (il totale delle prenotazioni consegnate viene usato per effettuare il calcolo del pagamento effettivo).',
    'filtered_orders' => 'Qui appaiono gli ordini che: appartengono al fornitore intestatario della fattura; sono in stato Consegnato o Archiviato; hanno almeno una prenotazione marcata come Consegnata. I totali vengono calcolati sulle quantità effettivamente consegnate, non sulle prenotazioni.',
  ),
  'change_orders' => 'Modifica Ordini',
  'verify' => 'Verifica Contenuti',
  'other_modifiers' => 'Altri modificatori non destinati a questa fattura:',
  'payment' => 'Registra Pagamento',
  'get_or_send' => 'Scarica o Inoltra',
  'new' => 'Carica Nuova Fattura',
  'send_pending_receipts' => 'Inoltra Ricevute in Attesa',
  'shipping_of' => 'Consegna: :date',
);
