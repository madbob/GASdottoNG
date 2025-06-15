<?php

return array (
  'help' => 
  array (
    'mandatory_column_error' => 'Champ obligatoire non spécifié',
    'importing' => 
    array (
      'deliveries' => 
      array (
        'first_product' => '',
      ),
      'user' => 
      array (
        'aggregation' => '',
        'deleted' => 'Indiquer « true » ou « false »',
        'balance' => 'Attention ! N\'utilisez cet attribut que lors de l\'importation initiale des utilisateurs, et uniquement pour les nouveaux utilisateurs, sinon les soldes seront toujours incohérents !',
        'instruction' => 'Si l\'identifiant existe déjà, l\'utilisateur correspondant sera mis à jour avec les données lues dans le fichier. Sinon, un courriel d\'invitation sera envoyé avec le lien que vous devez ouvrir pour vous connecter pour la première fois et définir votre mot de passe.',
      ),
    ),
    'csv_instructions' => 'Seuls les fichiers au format CSV sont autorisés. Il est recommandé de mettre en forme votre tableau de façon homogène, sans cellules fusionnées ou vides et sans en-tête : chaque ligne doit contenir toutes les informations relative au produit. Les prix sont à écrire sans le symbole euro.',
    'selection_instructions' => 'Une fois le fichier téléversé, il sera possible d\'attribuer chaque colonne du document à un paramètre.',
    'img_csv_instructions' => '',
  ),
  'importing' => 
  array (
    'deliveries' => 
    array (
      'first_product' => '',
      'instruction' => '',
      'notice' => '',
      'product_error' => '',
      'order_error' => '',
      'done' => '',
    ),
  ),
  'balance_csv_filename' => '',
  'products_list_filename' => 'Liste de prix :supplier.:format',
  'import' => 
  array (
    'csv' => 'Importer CSV',
    'gdxp' => 'Importer GDXP',
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
