<?php

return array (
  'help' => 
  array (
    'mandatory_column_error' => 'Colonna obbligatoria non specificata',
    'importing' => 
    array (
      'deliveries' => 
      array (
        'first_product' => 'Usa questo elemento per identificare il primo prodotto che appare nell\'elenco',
      ),
      'user' => 
      array (
        'aggregation' => 'Se specificato, deve contenere il nome di uno dei Gruppi impostati nel pannello "Configurazioni" per questa Aggregazione',
        'deleted' => 'Indicare "true" o "false"',
        'balance' => 'Attenzione! Usare questo attributo solo in fase di importazione iniziale degli utenti, e solo per i nuovi utenti, o i saldi risulteranno sempre incoerenti!',
        'instruction' => 'Se il login è già esistente il relativo utente sarà aggiornato coi dati letti dal file. Altrimenti verrà inviata una email di invito con il link da visitare per accedere la prima volta e definire la propria password.',
      ),
    ),
    'csv_instructions' => 'Sono ammessi solo files in formato CSV. Si raccomanda di formattare la propria tabella in modo omogeneo, senza usare celle unite, celle vuote, intestazioni: ogni riga deve contenere tutte le informazioni relative al soggetto. Eventuali prezzi e somme vanno espresse senza includere il simbolo dell\'euro.',
    'selection_instructions' => 'Una volta caricato il file sarà possibile specificare quale attributo rappresenta ogni colonna trovata nel documento.',
    'img_csv_instructions' => 'Istruzioni formattazione CSV',
  ),
  'importing' => 
  array (
    'deliveries' => 
    array (
      'first_product' => 'Primo prodotto',
      'instruction' => 'Da qui puoi reimportare un CSV generato dalla funzione "Tabella Complessiva Prodotti" dell\'ordine, dopo averlo manualmente elaborato con le quantità consegnate per ogni utente.',
      'notice' => 'Nota bene: come nelle normali consegne, si assume che la quantità consegnata dei prodotti con pezzatura sia qui espressa a peso e non in numero di pezzi.',
      'product_error' => 'Prodotto non identificato: :name',
      'order_error' => 'Ordine non identificato',
      'done' => 'Consegne importate',
    ),
  ),
  'balance_csv_filename' => 'Esportazione bilancio :date.csv',
  'products_list_filename' => 'Listino :supplier.:format',
  'import' => 
  array (
    'csv' => 'Importa CSV',
    'gdxp' => 'Importa GDXP',
  ),
  'help_csv_libreoffice' => 'Per la consultazione e l\'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l\'uso di <a target="_blank" href="http://it.libreoffice.org/">LibreOffice</a>.',
  'data' => 
  array (
    'columns' => 'Colonne',
    'format' => 'Formato',
    'formats' => 
    array (
      'pdf' => 'PDF',
      'csv' => 'CSV',
      'gdxp' => 'GDXP',
    ),
    'status' => 'Stato Prenotazioni',
    'users' => 'Dati Utenti',
    'products' => 'Colonne Prodotti',
    'split_friends' => 'Amici separati',
  ),
  'export' => 
  array (
    'database' => 'Esporta database',
  ),
  'help_split_friends' => 'Di default, le prenotazioni degli utenti "amici" vengono aggregate in quelle dei rispettivi utenti principali. Selezionando "Sì", vengono rappresentate nel documento come prenotazioni autonome.',
  'help_aggregate_export_summary' => 'Da qui puoi ottenere un documento che riassume le quantità prenotate di tutti i prodotti: utile da inviare al fornitore, una volta chiuso l\'ordine.',
  'flags' => 
  array (
    'include_unbooked' => 'Includi Prodotti non Prenotati',
  ),
  'do_balance' => 'Esporta Bilancio',
  'movements_heading' => 'Esportazione Movimenti del GAS al :date',
  'accepted_columns' => 'Le colonne ammesse per questo tipo di CSV sono:',
);
