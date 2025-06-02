<?php

return array (
  'import' => 
  array (
    'csv' => 'Importa CSV',
    'gdxp' => 'Importa GDXP',
  ),
  'help' => 
  array (
    'csv_instructions' => 'Sono ammessi solo files in formato CSV. Si raccomanda di formattare la propria tabella in modo omogeneo, senza usare celle unite, celle vuote, intestazioni: ogni riga deve contenere tutte le informazioni relative al soggetto. Eventuali prezzi e somme vanno espresse senza includere il simbolo dell\'euro.',
    'selection_instructions' => 'Una volta caricato il file sarà possibile specificare quale attributo rappresenta ogni colonna trovata nel documento.',
    'img_csv_instructions' => 'Istruzioni formattazione CSV',
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