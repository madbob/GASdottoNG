<?php

return array (
  'import' => 
  array (
    'csv' => 'Importer CSV',
    'gdxp' => 'Importer GDXP',
  ),
  'help' => 
  array (
    'csv_instructions' => 'Seuls les fichiers au format CSV sont autorisés. Il est recommandé de mettre en forme votre tableau de façon homogène, sans cellules fusionnées ou vides et sans en-tête : chaque ligne doit contenir toutes les informations relative au produit. Les prix sont à écrire sans le symbole euro.',
    'selection_instructions' => 'Une fois le fichier téléversé, il sera possible d\'attribuer chaque colonne du document à un paramètre.',
    'img_csv_instructions' => '',
  ),
  'help_csv_libreoffice' => 'L\'utilisation de <a target="_blank" href="https://fr.libreoffice.org/">LibreOffice</a> est conseillée pour la consultation et l\'édition de documents CSV (<i>Comma-Separated Values</i>).',
  'data' => 
  array (
    'columns' => 'Colonnes',
    'format' => 'Format',
    'formats' => 
    array (
      'pdf' => '',
      'csv' => '',
      'gdxp' => '',
    ),
    'status' => 'Statut de la réservation',
    'users' => 'Utilisateurs',
    'products' => 'Nom du produit',
    'split_friends' => '',
  ),
  'export' => 
  array (
    'database' => 'Exporter',
  ),
  'help_split_friends' => '',
  'help_aggregate_export_summary' => '',
  'flags' => 
  array (
    'include_unbooked' => '',
  ),
  'do_balance' => '',
  'movements_heading' => '',
  'accepted_columns' => '',
);