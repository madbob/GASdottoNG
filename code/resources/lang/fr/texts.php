<?php

return array (
  'aggregations' =>
  array (
    'all' => '',
    'limit_access' => '',
    'help' =>
    array (
      'limit_access_to_order' => '',
      'permit_selection' => '',
      'context' => '',
      'limit_access' => '',
      'no_user_aggregations' => '',
    ),
    'permit_selection' => '',
    'context' => '',
    'by_booking' => '',
    'cardinality' => '',
    'cardinality_one' => '',
    'cardinality_many' => '',
    'user_selectable' => '',
    'group' => '',
    'empty_list' => '',
    'name' => '',
  ),
  'auth' =>
  array (
    'accept_privacy' => 'J\'accepte la <a href="%s" target="_blank">politique de confidentialité</a>.',
    'username' => 'Nom d\'utilisateur',
    'help' =>
    array (
      'missing_user_or_mail' => 'Identifiant ou adresse courriel non trouvé',
      'missing_email' => 'L\'utilisateur/utilisatrice indiqué·e n\'a pas d\'adresse courriel valide',
      'reset_email_notice' => 'Un courriel avec un lien pour procéder à la mise à jour de votre mot de passe vous a été envoyé',
      'username_same_password' => 'Le mot de passe est identique à l\'identifiant ! Changez-le dès que possible depuis votre <a href=":link">panneau utilisateur</a> !',
      'suspended_account_notice' => 'Votre compte a été suspendu et vous ne pouvez pas effectuer de réservations. Vérifiez l\'état de vos paiements et de votre crédit ou éventuellement les notifications envoyées par les administrateurs.',
      'invalid_username' => 'Nom d\'utilisateur invalide',
      'required_new_password' => 'Avant de continuer, vous devez définir un nouveau mot de passe pour votre profil personnel.',
      'unconfirmed' => '',
      'username' => '',
      'email_mode' => 'Sinon, un courriel d\'invitation sera envoyé avec le lien que vous devez ouvrir pour vous connecter pour la première fois et définir votre mot de passe.',
    ),
    'reset_username' => 'Identifiant ou adresse courriel',
    'password' => 'Mot de passe',
    'password_request_link' => 'Récupérer le mot de passe',
    'maintenance_notice' => 'Mode maintenance : l\'accès est réservé aux administrateurs',
    'login' => 'Se connecter',
    'remember' => 'Se souvenir de moi',
    'register' => 'S\'inscrire',
    'confirm_password' => 'Confirmer le mot de passe',
    'update_password' => 'Confirmer le mot de passe',
    'modes' =>
    array (
      'email' => 'Envoyer le courriel',
    ),
  ),
  'commons' =>
  array (
    'accept_conditions' => 'J\'ai lu et j\'accepte les <a href="%s" target="_blank">Conditions d\'utilisation</a>.',
    'warning' => 'Attention',
    'loading' => 'Chargement en cours',
    'feedback' => 'Remarques',
    'about' =>
    array (
      'opensource' => '',
      'contribute' => '',
      'donate' => '',
      'link' => '',
      'local_contact' => 'Attention : les problèmes relatifs au contenu du site (fournisseurs, commandes, réservations...) sont à adresser à l\'administrateur de votre groupe. Les informations transmises par ce formulaire sont publiques. Ne transmettez pas d\'informations personnelles !',
      'translations' => 'Pour contribuer à la traduction dans votre langue, visitez <a href="https://hosted.weblate.org/projects/gasdottong/native/">cette page</a>.',
    ),
  ),
  'export' =>
  array (
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
  ),
  'gas' =>
  array (
    'help' =>
    array (
      'csv_separator' => '',
      'home_message' => '',
      'currency' => '',
      'maintenance_mode' => '',
      'enable_public_registration' => '',
      'empty_list_shared_files' => 'Aucun élément à afficher. <br/>Les fichiers ajoutés ici sont accessibles à tous les utilisateurs depuis le tableau de bord. Cela permet de communiquer des documents publics.',
      'enable_deliveries_no_quantities' => '',
      'active_columns_summary' => '',
      'default_columns_shipping_document' => '',
      'custom_emails' => 'D\'ici, vous pouvez modifier le texte des courriels sortants de GASdotto. Pour chaque type, il y a des espaces réservés qui seront remplacés par les valeurs appropriées au moment de la génération : pour les ajouter dans les textes, utiliser la syntaxe %[nom_espace-réservé]',
      'global_placeholders' => 'Espaces réservés globaux, qui peuvent être utilisés dans tous les messages :',
      'manual_products_sorting' => '',
      'social_year' => '',
      'fee' => '',
      'deposit' => '',
      'automatic_fees' => '',
      'enable_sepa' => 'Populating these fields export of SEPA files will be activated, so you it will be possible to automate banking transactions. <br> Files will be generated from <strong>Accounting -> Credit Status -> Export RID</strong> <br>After filling this form, you will need to specify some parameters for each user.',
      'enable_satispay' => 'PayPal payments will be activated populating those fields, so users will be able to increase their credit directly from GASdotto. To obtain credentials, <a href="https://developer.paypal.com/developer/applications/">visit this page</a>.',
      'enabled_satispay' => '',
      'satispay_activation_code' => '',
      'enable_integralces' => '',
      'enable_invoicing' => 'Filling this fields, it will be enabled generation of invoices. Invoices will be generated when each reservation is saved or shipped.',
      'invoices_counter' => 'Changez ce paramètre avec précaution !',
      'enable_hub' => '',
      'import' => '',
      'gdxp_explain' => 'GDXP est un format interopérable pour l\'échange de listes de prix et de commande entre différents systèmes de gestion. Vous pouvez importer un fichier GXDP ici.',
      'multigas_mode' => '',
      'only_bookings_with_credit_limit' => '',
      'fast_product_change_columns' => '',
    ),
    'attribute_name' => 'Nom du groupe',
    'logo' => 'Logo de la page d\'accueil',
    'home_message' => 'Message de la page d\'accueil',
    'language' => 'Langue',
    'maintenance_mode' => 'Mode Maintenance',
    'enable_public_registration' => 'Autoriser l\'inscription publique',
    'manual_approve_users' => '',
    'privacy_policy_link' => 'Lien vers la politique de confidentialité',
    'terms_link' => '',
    'mandatory_fields' => 'Champs obligatoires',
    'orders_and_deliveries' => 'Commandes et livraisons',
    'only_bookings_with_credit' => 'Autoriser les réservations uniquement dans la limite du crédit disponible',
    'enable_deliveries_no_quantities' => '',
    'display_contacts' => 'soustraire au coût de la réservation',
    'active_columns_summary' => 'Colonnes sommaires de commandes',
    'default_columns_shipping_document' => 'Détails de la livraison',
    'suppliers_and_products' => 'Fournisseurs importés',
    'manual_products_sorting' => '',
    'fast_product_change_columns' => 'Modification rapide',
    'social_year' => 'Début de l\'année',
    'automatic_fees' => '',
    'enable_sepa' => 'Autoriser SEPA',
    'enable_satispay' => 'Autoriser Satispay',
    'satispay' =>
    array (
      'activation_code' => 'Code unique de l\'entreprise',
    ),
    'enable_integralces' => 'IntegralCES',
    'integralces_identifier' => 'Identifiant de mandat SEPA',
    'enable_invoicing' => 'Autoriser l\'émission de factures',
    'invoices_counter' => 'Total facture',
    'import_export' => 'Importer',
    'enable_hub' => '',
    'csv_separator' => '',
    'import' => 'Imports',
    'multigas_mode' => '',
    'only_bookings_with_credit_limit' => '',
  ),
  'generic' =>
  array (
    'save' => 'Enregistrer',
    'create_format' => 'Créer :type',
    'empty_list' => 'Aucun élément à afficher.',
    'add_new' => 'Ajouter',
    'type' => 'Type',
    'none' => 'Aucun',
    'manual_selection' => 'Sélection manuelle',
    'named_all' => 'Tous :name',
    'email' => 'Courriel',
    'phone' => 'Téléphone',
    'absolute' => 'Absolu',
    'percentage' => 'Pourcentage',
    'by_weight' => 'Poids',
    'quantity' => 'Quantité',
    'value' => 'Montant',
    'weight' => 'Poids',
    'remove' => 'Supprimer',
    'export' => 'Exporter',
    'undefined' => 'indéfini(e)',
    'updated_at_formatted' => '',
    'address' => 'Adresse',
    'email_no_notifications' => '',
    'cellphone' => 'Mobile',
    'fax' => 'Fax',
    'website' => 'Site web',
    'confirmed' => 'Confirmé',
    'temporary' => 'Temporaire',
    'measure' => 'Unité de mesure',
    'category' => 'Catégorie',
    'price' => 'Prix',
    'yes' => '',
    'no' => 'Non',
    'iban' => 'IBAN',
    'gas' => 'GAS',
    'status' => 'État',
    'unspecified' => 'Non spécifié',
    'never' => 'Jamais',
    'help' =>
    array (
      'save_reminder' => '',
      'preferred_date_format' => 'De préférence au format AAAA-MM_JJ (ex. : :now)',
      'contacts' => '',
      'unchange_password' => 'Laisser vide pour ne pas modifier votre mot de passe',
      'multigas_admin_instructions' => '',
      'discrete_measure' => 'Les unités discrètes ne sont pas fractionnables : sur les produits concernés, il ne sera pas possible d\'activer les attributs « Prix variable » et « Taille unitaire »',
      'categories_instructions' => 'Glissez et déposez les catégories dans la liste pour les ordonner.',
      'insert_password_notice' => 'Pour confirmer cette opération, veuillez rentrer votre mot de passe',
      'unassigned_group_warning' => '',
    ),
    'definitive_delete' => 'Supprimer définitivement',
    'all' => 'Tous',
    'unauthorized' => 'Non autorisé',
    'error' => 'Erreur',
    'date' => 'Date',
    'number' => 'Numéro',
    'taxable_amount' => 'Imposable',
    'vat' => 'TVA',
    'payment' => 'Paiement',
    'identifier' => 'Identifiant',
    'notes' => 'Notes',
    'id' => 'ID',
    'closing_date' => 'Date de clôture',
    'stats' =>
    array (
      'involved_orders' => 'Valeur des commandes',
      'involved_users' => 'Utilisateurs concernés',
      'generic' => 'Statistiques générales',
      'supplier' => 'Statistiques par fournisseur',
    ),
    'description' => 'Description',
    'invoice' => 'Facture',
    'no_value' => 'Aucune valeur',
    'by_kg' => '',
    'selection' => 'Sélectionnez',
    'home' => 'Accueil',
    'menu' =>
    array (
      'bookings' => 'Réservations',
      'accounting' => 'Comptabilité',
      'stats' => 'Statistiques',
      'notifications' => 'Notifications',
      'configs' => 'Paramètres',
      'multigas' => 'Multi-GAS',
      'friends' => 'Amis',
      'receipts' => 'Reçu',
    ),
    'image' => 'Image',
    'limited_access' => 'Accès limité',
    'disabled' => 'Désactivé',
    'kilos' => 'Kg',
    'sortings' =>
    array (
      'all_by_user' => 'Tous (triés par utilisateur)',
      'manual' => 'Arrondi manuel des livraisons',
      'all_by_group' => '',
    ),
    'minor_than' => 'est inférieur à',
    'major_than' => 'est supérieur à',
    'exports' =>
    array (
      'csv' => 'Exporter CSV',
      'integralces' => 'IntegralCES',
      'sepa' => 'Exporter SEPA',
      'pdf' => 'Exporter un PDF',
    ),
    'change' => 'Éditer',
    'details' => 'Détails',
    'photo' => 'Photo de profil',
    'composable_all' => 'Tous :label',
    'attachments' =>
    array (
      'replace_file' => 'Remplacer le fichier',
      'view' => 'Voir les statistiques',
      'replace_url' => '',
    ),
    'recipients' => 'Destinataires',
    'click_here' => 'Cliquer ici',
    'attachment' => 'Fusionnés',
    'contacts' => 'Contacts',
    'errors' => 'Erreurs',
    'search' =>
    array (
      'users' => 'Trouver un utilisateur',
      'all' => 'Recherche',
    ),
    'comment' => 'Commentaire',
    'interval' => 'Intervalle',
    'since' => 'De',
    'to' => 'à',
    'method' => 'Méthode',
    'automatic' => '',
    'related' => 'Transactions importées',
    'more' => '',
    'send_mail' => 'Envoyer le courriel',
    'optional_message' => 'Message (optionnel)',
    'default_notes' => 'Notes par défaut',
    'default' => 'Notes par défaut',
    'suspend' => 'Suspendu',
    'created_at' => 'Date d\'inscription',
    'updated_at' => 'Éditer',
    'multigas_name' => 'Nom du groupe',
    'how_to_proceed' => '',
    'create' => 'Nouveau',
    'targets' => 'Objet',
    'suppliers_and_orders' => 'Fournisseurs',
    'mailfield' =>
    array (
      'subject' => 'Objet',
      'body' => 'Texte',
    ),
    'reference' => 'Référence',
    'measures' =>
    array (
      'discrete' => 'Unité discrète',
    ),
    'do_filter' => 'Filtrer',
    'close' => 'Fermer',
    'save_and_proceed' => 'Enregistrer et poursuivre',
    'behavior' => 'Commentaire',
    'uppercare_gas_heading' => 'Groupe d\'Achat Solidaire (GAS)',
    'confirm' => 'Confirmer',
    'delete_confirmation' => 'Êtes-vous sûr·e de vouloir supprimer cet élément ?',
    'current_gas_name' => 'Activé',
    'shared_files' => 'Fichiers partagés',
    'file' => 'Fichier',
    'logs' => 'Se connecter',
    'message' => 'Message',
    'values' => 'Valeurs',
    'no_image' => 'Aucun seuil',
    'finished_operation' => 'Opération terminée.',
    'before' => 'Avant',
    'after' => 'Après',
    'sort_by' => 'Trier par',
    'view_all' => 'Tous',
    'update' => 'Actualiser',
    'fast_modify' => 'Modification rapide',
    'download' => 'Télécharger',
    'split' => 'de',
    'start' => 'Début',
    'expiration' => 'Fin',
    'do_duplicate' => 'Dupliquer',
    'action' =>
    array (
      'ignore' => '[Ignorer]',
      'disable' => 'Désactivé',
    ),
    'operation' => 'Imports',
    'sum' => '',
    'sub' => 'Imports',
    'passive' => 'Mot de passe',
    'apply' => 'Dupliquer',
    'difference' => 'Différence de prix',
    'theshold' => '',
    'cost' => '',
    'forward' => 'Transféré',
    'do_not_modify' => 'Modifiable',
    'named_multigas' => 'Multi-GAS :name',
    'categories' => 'Catégories',
    'no_data' => 'Aucune donnée à afficher',
    'name' => '',
    'url' => '',
    'only_selected' => '',
    'subject' => '',
    'aggregations_and_groups' => '',
    'select' => '',
    'to_do' => '',
    'opening' => '',
    'closing' => '',
    'mandatory' => '',
  ),
  'imports' =>
  array (
    'help' =>
    array (
      'new_remote_products_list' => 'Nouvelle mise à jour disponible pour la liste de prix :supplier (:date). Consultez-le à partir du panneau Fournisseurs -> Index à distance.',
      'failed_file' => 'Le fichier n\'a pas été téléversé correctement',
      'failure_notice' => 'L\'importation a échoué',
      'invalid_command' => 'Commande :type/:step non valide',
      'currency_id' => 'Une des monnaies gérées par le système. Si elle n\'est pas spécifiée, la devise par défaut sera sélectionnée (:default). Valeurs autorisées: :values',
      'unique_user_id' => 'Les utilisateurs sont identifiés par leur nom d\'utilisateur ou leur adresse courriel (qui doivent être uniques).',
      'no_user_found' => 'Utilisateur non trouvé: :name',
      'no_supplier_found' => 'Fournisseur introuvable: :name',
      'no_currency_found' => 'Monnaie introuvable : :name',
      'imported_movements_notice' => 'Transactions importées',
      'main' => 'Cliquez et faites glisser les attributs de la colonne de droite à la colonne centrale, pour donner à chaque colonne de votre fichier une signification.',
      'remote_index' => '',
    ),
    'ignore_slot' => '[Ignorer]',
    'name_or_vat' => 'Nom ou numéro de TVA',
    'imported_users' => 'Utilisateurs importés',
    'do' => 'Importer',
    'update_supplier' => 'Modifier un fournisseur existant',
    'select_supplier' => 'Sélectionner un fournisseur',
    'products_count' => 'Le fichier contient %s produits.',
    'index_column' => 'Colonne',
    'column' => 'Colonne',
    'imported_suppliers' => 'Fournisseurs importés',
    'updated' => 'Actualiser',
    'last_read' => '',
    'error_main' => 'Erreur durant le téléversement ou la lecture du fichier.',
    'error_retry' => 'Veuillez essayer de nouveau. Si le problème persiste, merci de le signaler aux développeurs de GASdotto : info@madbob.org',
    'existing_products_action' => 'Produits des commandes',
    'no_products' => 'Aucun produit actualisable',
  ),
  'invoices' =>
  array (
    'waiting' => 'En attente',
    'statuses' =>
    array (
      'to_verify' => 'À valider',
      'verified' => 'Validé',
      'payed' => 'Payé',
    ),
    'default_note' => 'Paiement de la facture :name',
    'documents' =>
    array (
      'invoice' =>
      array (
        'heading' => 'Facture :identifier',
      ),
      'receipts' =>
      array (
        'list_filename' => 'Exporter les transactions GAS :date.csv',
      ),
    ),
    'balances' =>
    array (
      'supplier' => 'Solde fournisseur',
    ),
    'forwarded' => 'Transféré',
    'orders' => 'Commandes concernées',
    'help' =>
    array (
      'orders' => '',
      'no_orders' => '',
      'filtered_orders' => '',
    ),
    'change_orders' => 'Gérer les commandes',
    'verify' => 'Vérifier le contenu',
    'other_modifiers' => '',
    'payment' => 'Enregistrer le paiement',
    'get_or_send' => 'Télécharger ou transférer',
    'new' => 'Charger une nouvelle facture',
    'send_pending_receipts' => '',
    'shipping_of' => 'Livré : %s',
  ),
  'mail' =>
  array (
    'help' =>
    array (
      'removed_email_log' => 'Adresse électronique retirée :address',
      'send_error' => 'Impossible de transférer le message à :email: :message',
    ),
    'summary' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Résumé des réservations du GAS : :supplier - consegna :delivery',
      ),
    ),
    'closed' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Commande fermée automatiquement',
      ),
    ),
    'notification' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Nouvelle notification de :gas',
      ),
    ),
    'new_user_notification' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Nouvel utilisateur enregistré',
      ),
    ),
    'contacts_prefix' => '',
    'approved' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Bienvenue !',
        'body' => 'Bienvenue dans %[gas_name] !
    À l\'avenir, vous pourrez vous connecter en utilisant le lien ci-dessous, l\'identifiant « %[username] » et le mot de passe de ton choix.
    %[gas_login_link]
    Un courriel de notification a été envoyé aux administrateurs.',
      ),
      'description' => '',
      'username' => 'Identifiant assigné au nouvel utilisateur',
      'link' => 'Lien vers la page de connexion',
    ),
    'declined' =>
    array (
      'defaults' =>
      array (
        'subject' => '',
        'body' => '',
      ),
      'description' => '',
    ),
    'order' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Nouvelle commande ouverte pour %[supplier_name]',
        'body' => 'Une nouvelle commande a été ouverte sur %[gas_name] pour le fournisseur %[supplier_name].
    Pour participer, connectez-vous via ce lien :
    %[gas_booking_link]
    Les réservations seront fermées le %[closing_date]',
      ),
      'description' => 'Notification pour les nouvelles commandes ouvertes (envoyée aux utilisateurs qui ont activé explicitement les notifications pour le fournisseur).',
      'comment' => 'Texte commenté de la commande',
      'link' => 'Lien vers la réservation',
      'mails' => 'Adresses courriel des personnes de contact de la commande',
    ),
    'reminder' =>
    array (
      'defaults' =>
      array (
        'subject' => '',
        'body' => '',
      ),
      'description' => '',
      'list' => '',
    ),
    'password' =>
    array (
      'defaults' =>
      array (
        'body' => 'La mise à jour de votre mot de passe a été demandée sur GASdotto.
    Cliquez sur le lien ci-dessous pour mettre à jour votre mot de passe ou ignorer ce courriel si vous n\'avez pas demandé cette opération.
    %[gas_reset_link]',
      ),
      'description' => 'Message pour la récupération du mot de passe.',
      'link' => 'Lien pour la réinitialisation du mot de passe',
    ),
    'receipt' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Nouvelle facture de %[gas_name]',
        'body' => 'Ci-joint la dernière facture de% [gas_name]',
      ),
      'description' => 'Courriel d\'accompagnement pour les reçus.',
    ),
    'supplier' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Réservation de la commande %[gas_name]',
        'body' => 'Bonjour.
    Veuillez trouver ci-joint – en double, PDF et CSV – la réservation de la commande de %[gas_name].
    Si vous avez des questions, veuillez contacter les personnes de contact en copie de ce courriel.
    Merci.',
      ),
      'description' => 'Notification aux fournisseurs de la fermeture automatique des commandes.',
    ),
    'credit' =>
    array (
      'current' => '',
    ),
    'welcome' =>
    array (
      'description' => 'Message envoyé aux nouveaux utilisateurs créés sur la plateforme.',
      'link' => 'Lien pour se connecter la première fois',
      'defaults' =>
      array (
        'body' => 'Vous avez été invité·e à %[gas_name] !

    Pour vous connecter la première fois, cliquez sur le lien ci-dessous.
    %[gas_access_link].

    À l\'avenir, vous pourrez vous connecter en utilisant cet autre lien, le nom d\'utilisateur « %[username] » et le mot de passe que vous avez choisi.
    %[gas_login_link]
    ',
      ),
    ),
    'newuser' =>
    array (
      'description_manual' => '',
      'description' => 'Message envoyé aux nouveaux membres inscrits sur la plateforme.',
    ),
  ),
  'modifiers' =>
  array (
    'defaults' =>
    array (
      'discount' => 'Réduction',
      'rounding' => 'Arrondi manuel des livraisons',
      'delivery' => 'Frais de port',
    ),
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
    'all' => 'Éditer',
    'name' => 'Éditer',
    'help' =>
    array (
      'no_modifiers_for_element' => '',
    ),
  ),
  'movements' =>
  array (
    'modifier_no_theshold' => 'Aucun seuil',
    'order_value' => '',
    'apply_to_booking' => 'Commande unique',
    'apply_to_order' => 'Commande totale',
    'current_balance_amount' => 'Solde actuel : :amount',
    'balance' => 'Solde',
    'current_credit' => 'Crédit actuel',
    'bank_account' => 'Compte courant',
    'cash_account' => 'Liquide',
    'deposits' => 'Caution',
    'documents' =>
    array (
      'movements' =>
      array (
        'filename' => 'Exporter les transactions GAS :date.:format',
      ),
      'users' =>
      array (
        'filename' => 'Soldes sur :date.csv',
        'integralces_filename' => '',
      ),
      'sepa' =>
      array (
        'filename' => 'SEPA de :date.xml',
      ),
      'suppliers' =>
      array (
        'filename' => 'Soldes des fournisseurs au :date.csv',
        'integralces_filename' => '',
      ),
      'balances' =>
      array (
        'filename' => 'Historique du solde :date.csv',
      ),
    ),
    'registration_date' => 'Date d\'inscription',
    'execution_date' => 'Date de la transaction',
    'paying' => 'Payant',
    'payed' => 'Payé',
    'delete_confirmation' => '',
    'formatted_residual_credit' => 'Crédit restant :currency',
    'formatted_balance' => 'Solde :currency',
    'currency' => 'Monnaie',
    'credit' => 'Crédit',
    'defaults' =>
    array (
      'fee' => 'Paiement de la cotisation annuelle par un membre',
      'booking' => 'Paiement de la réservation par un membre',
      'booking_adjust' => 'Ajustement du paiement de la réservation par un membre',
      'deposit' => 'Dépôt du membre GAS',
      'deposit_return' => 'Restitution du dépôt du membre de GAS',
      'donation_from' => 'Donation de GAS',
      'donation' => 'Donation au GAS',
      'expense' => 'Achat/dépense de GAS',
      'put' => 'Transfert vers le compte',
      'invoice' => 'Paiement de la facture au fournisseur',
      'order' => 'Paiement de la commande au fournisseur',
      'rounding' => 'Arrondissement du fournisseur/escompte',
      'credit' => 'Dépôt de crédit par un associé',
      'decredit' => 'Retour de crédit pour un membre',
      'refund' => 'Remboursement des dépenses des membres',
    ),
    'methods' =>
    array (
      'bank' => 'Virement bancaire',
      'cash' => 'Liquide',
      'credit' => 'Crédit de l\'utilisateur',
      'sepa' => 'SEPA',
    ),
    'formatted_revenues' => '',
    'formatted_expenses' => '',
    'suppliers_status' => 'Solde fournisseur',
    'causal' => 'Motif',
    'generic_causal' => 'Transfert vers le compte',
    'help' =>
    array (
      'removing_balance_warning' => 'Attention ! Les soldes passés peuvent être supprimés mais, par prudence, l\'opération n\'est pas réversible et il ne sera plus possible de recalculer ces valeurs d\'aucune manière !',
      'missing_method_for_movement' => '',
      'accepts_negative_value' => '',
      'fixed_value' => '',
      'paying' => '',
      'payed' => '',
      'system_type_notice' => '',
      'empty_list_vat_rates' => 'Aucun élément à afficher. <br/>Les taux de TVA peuvent être assignés aux produits et sont utilisés pour calculer la TVA automatiquement dans les factures enregistrées dans <strong>Comptabilité -> Factures</strong>.',
      'balances_diff' => 'Les soldes suivants étaient différents après la fin du recalcul.',
      'balances_same' => 'Tous les soldes sont corrects.',
      'archiviation_notice' => '',
      'opened_orders_with_modifier' => '',
      'main_types_warning' => 'Attention: modifiez le comportement d\'un type de transaction avec précaution! Avant de modifier le comportement d\'un type de transaction existant, il est recommandé d\'utiliser la fonction « Archiver les soldes » afin d\'éviter de recalculer des mouvements antérieurs et affecter les soldes courants.',
      'modifier_not_applied_in_time_range' => '',
      'current_balance' => '',
      'pending_bookings_to_pay' => '',
      'always_active_modifiers' => '',
      'missing_movements_for_modifiers' => '',
      'type_for_modifier' => '',
      'missing_method_for_movements_in_modifiers' => '',
      'missing_method_for_movement_in_modifier' => '',
    ),
    'balances_history' => 'Historique du solde %s.csv',
    'current_balance' => 'Solde actuel',
    'registrar' => 'Enregistré par',
    'accepts_negative_value' => 'Autorise les valeurs négatives',
    'fixed_value' => 'Valeur fixe',
    'debit' => 'Débit',
    'type' => 'Type de transaction',
    'credits_status' => 'Statut du crédit',
    'vat_rates' => 'Taux de TVA',
    'recalculate_balances' => 'Recalcul des soldes',
    'balances_archive' => 'Archiver les soldes',
    'all' => 'Transactions',
    'name' => 'Transaction',
    'amount' => 'Importer',
    'types' => 'Types de transaction',
    'invoices' => 'Factures',
    'reference_for_modifier' => '',
    'distribute_on' => 'et le distribuer en fonction du poids des réservations',
    'to_pay' => 'À payer',
    'available_credit' => 'Crédit disponible',
    'always_active_modifiers' => '',
    'apply_theshold_to' => '',
    'sepa' =>
    array (
      'creditor_identifier' => 'Identifiant du créancier',
      'business_code' => 'Code unique de l\'entreprise',
    ),
  ),
  'notifications' =>
  array (
    'global_filter' =>
    array (
      'roles' => 'Tous les utilisateurs avec le rôle :role',
      'orders' => 'Tous les participants à la commande :supplier :number',
    ),
    'help' =>
    array (
      'repeat_mail_warning' => 'Cette notification a déjà été envoyée par courriel. Conservez cet attribut actif pour envoyer un nouveau courriel.',
      'sending_mail_warning' => 'Si vous activez cette option, la notification sera envoyée tout de suite par courriel. Si vous voulez la modifier avant de l\'envoyer, activez cette option seulement après avoir sauvegardé et modifié la notification.',
      'visibility_by_selection' => 'Sélectionnez plusieurs utilisateurs à l\'aide de la touche Ctrl. Si aucun utilisateur n\'est sélectionné, l\'élément sera visible par tous.',
      'suspend' => '',
      'arbitrary_dates' => 'Depuis cette page, vous pouvez ajouter des dates arbitraires dans le calendrier de livraison, même pour des commandes non existantes. Cette fonction est utile pour coordonner différents intervenants et planifier les activités de long terme du GAS.',
    ),
    'cycle' =>
    array (
      'two_weeks' => 'Toutes les deux semaines',
      'first_of_month' => 'Le premier du mois',
      'second_of_month' => 'Le 2 du mois',
      'third_of_month' => 'Le 3 du mois',
      'fourth_of_month' => 'Le 4 du mois',
      'last_of_month' => 'Le dernier jour du mois',
    ),
    'name' => 'Notification',
    'notices' =>
    array (
      'new_notification_from' => 'Nouvelle notification de :author',
      'attached_order' => 'Pièce-jointe : le fichier pour la commande :gasname.',
    ),
    'recurrence' => 'Récurrence',
    'greetings' => 'Cordialement',
    'send_to_current_users' => 'Notifier les utilisateurs sélectionnés',
    'next_dates' => 'Prochaines dates dans le calendrier :',
    'next_auto_orders' => 'Prochaines dates dans le calendrier :',
    'list' =>
    array (
      'closed_orders' => 'Commandes closes',
      'confirmed_dates' => 'Dates confirmées',
      'temporary_dates' => 'Date temporaire',
      'appointments' => 'Rendez-vous',
    ),
    'calendar_date' => 'Date sur le calendrier',
    'date_reference' => '',
  ),
  'orders' =>
  array (
    'booking' =>
    array (
      'void' => 'Annuler une réservation',
      'statuses' =>
      array (
        'open' => 'Réservations ouvertes',
        'closed' => 'Réservations closes',
        'shipped' => 'Livré',
        'paying' => 'Paiement par l\'utilisateur',
        'archived' => 'Archivé',
        'suspended' => 'Suspendu',
        'booked' => 'Réservé',
        'to_deliver' => 'À livrer',
        'saved' => 'Enregistré',
      ),
      'nav' =>
      array (
        'mine' => 'Ma réservation',
        'friends' => 'Réservations pour mes amis',
        'others' => 'Réservations pour d\'autres personnes',
        'add' => 'Ajouter/éditer une réservation',
      ),
    ),
    'help' =>
    array (
      'pending_packages_notice' => 'Attention : cette commande est fermée, mais vous pouvez encore réserver quelques produits pour compléter les colis à livrer.',
      'send_booking_summaries' => '',
      'send_delivery_summaries' => '',
      'no_partecipating' => 'Vous n\'avez pas participé à cette commande',
      'formatted_booked_amount' => 'Vous avez commandé :amount',
      'formatted_booked_amount_with_friends' => 'Vous avez commandé :amount + :friends',
      'product_selection' => 'Pour activer ou désactiver les produits de la liste des fournisseurs à l\'intérieur de la commande',
      'booked_modifier_column' => 'Modificateur de produit, sur le site Booked. S\'affiche uniquement si le modificateur est actif pour un produit de la commande',
      'delivered_modifier_column' => 'Modificateur de produit, sur le site Booked. S\'affiche uniquement si le modificateur est actif pour un produit de la commande',
      'fixes_column' => 'Depuis cette page, vous pouvez modifier la quantité commandée d\'un produit et ajouter des notes pour le fournisseur',
      'number' => 'Numéro progressif attribué automatiquement à chaque commande',
      'unarchived_notice' => '',
      'extimated_value' => '',
      'insufficient_credit_notice' => 'Attention : votre crédit est insuffisant pour effectuer de nouvelles réservations.',
      'friends_bookings_notice' => 'Depuis cette page, vous pouvez ajouter des sous-réservations associées à vos amis. Celles-ci feront partie de votre réservation globale, mais vous pouvez conserver des informations différentes pour chacun de vos amis. Gérer la liste de vos amis depuis votre profil personnel.',
      'no_friends' => 'Il n\'y a pas d’amis enregistrés pour cet utilisateur.',
      'closed_order_alert_new_booking' => 'Attention : cette commande est close. Avant d\'éditer une commande, soyez sûr que le total n\'a pas encore été communiqué au fournisseur, ou bien qu\'il peut tout de même être modifié.',
      'send_summaries' => '',
      'automatic_instructions' => '',
      'changed_products' => '',
      'waiting_closing_for_deliveries' => 'Ce champ sera activé lorsque l\'ensemble des réservations seront terminées',
      'modifiers_require_redistribution' => '',
      'contacts_notice' => '',
      'explain_aggregations' => 'Une fois fusionnées, les commandes seront affichés comme une seule commande conservant les paramètres de chacune. Cette fonction est conseillée pour simplifier la gestion des commandes, par exemple pour les commandes livrées à la même date.',
      'aggregation_instructions' => 'Glissez et déposez les commandes à fusionner dans la même case pour les fusionner, ou dans une case vide pour les séparer.',
      'status' => '',
      'prices_changed' => '',
      'variant_no_longer_active' => '',
      'pending_saved_bookings' => '',
      'mail_order_notification' => '',
      'target_supplier_notifications' => '',
      'notify_only_partecipants' => '',
      'comment' => '',
      'end' => '',
      'contacts' => '',
      'handle_packages' => '',
      'payment' => '',
      'no_opened' => 'Aucun élément à afficher.',
      'no_delivering' => 'Aucun élément en cours de livraison.',
      'include_all_modifiers' => '',
      'supplier_multi_select' => '',
      'start' => '',
      'manual_fixes_explain' => 'Vous pouvez modifier la quantité réservée de ce produit pour chaque réservation, mais pour l\'instant, aucun utilisateur n\'a participé à cette commande.',
      'pending_notes' => '',
      'no_partecipation_notice' => 'Vous n\'avez pas participé à cette commande.',
      'modifiers_notice' => '',
      'no_categories' => '',
      'supplier_no_orders' => '',
      'supplier_has_orders' => '',
      'unremovable_warning' => '',
      'unremovable_instructions' => '',
      'unremovable_notice' => '',
    ),
    'booking_description' =>
    array (
      'shipped' => 'Voici un résumé des produits qui vous ont été livrés :',
      'saved' => 'Voici un résumé des produits qui vous seront livrés :',
      'pending' => 'Résumé de votre commande :',
    ),
    'send_booking_summaries' => 'Envoyer le résumé des réservations',
    'send_delivery_summaries' => 'Envoyer le résumé des livraisons',
    'packages' =>
    array (
      'ignore' => 'Non, ignorer la taille du paquet',
      'permit' => 'Oui, autoriser toute autre réservation',
      'permit_all' => 'Oui, et il inclut les quantités comptabilisées par tous les GAS',
    ),
    'and_more' => 'et autres',
    'boxes' => 'Nombre de colis',
    'supplier' => 'Fournisseur',
    'booking_date_time' => '',
    'list_open' => 'Commandes ouvertes',
    'dates' =>
    array (
      'shipping' => 'Date de livraison',
      'start' => 'Date d\'ouverture des réservations',
      'end' => 'Date de clôture des réservations',
    ),
    'name' => 'Commande',
    'formatted_name' => 'de :start à :end',
    'formatted_delivery_in_name' => ', à livrer le :delivery',
    'quantities' =>
    array (
      'booked' => 'Quantité réservée',
      'shipped' => 'Quantité livrée',
    ),
    'weights' =>
    array (
      'booked' => 'Poids réservé',
      'delivered' => 'Poids livré',
    ),
    'totals' =>
    array (
      'shipped' => 'Total livré',
      'with_modifiers' => '',
      'total' => 'Total',
      'taxable' => 'Total imposable',
      'vat' => 'Total TVA',
      'booked' => 'Total réservé',
      'complete' => '',
      'invoice' => 'Total facture',
      'orders' => 'Total commandes',
      'manual' => 'Total facture',
      'to_pay' => 'Montant à payer',
      'selected' => '',
    ),
    'constraints' =>
    array (
      'quantity' => 'La quantité maximale est de 9999,99',
      'discrete' => '',
      'global_min' => 'Total minimal : :still (:global total)',
      'global_max_help' => 'Il manque :still :measure pour compléter le paquet de cette commande',
      'global_max_short' => ':icon disponible : :quantity',
      'global_max' => 'Disponible: :still (total :global)',
      'global_max_generic' => 'Quantité dépassant la disponibilité',
      'relative_max_formatted' => 'Maximum suggéré: :quantity',
      'relative_max' => 'Quantité dépassant le maximum recommandé',
      'relative_min_formatted' => 'Minimum: :quantity',
      'relative_min' => 'Quantité inférieure au minimum autorisé',
      'relative_multiple_formatted' => 'Multiplicateur: :quantity',
      'relative_multiple' => 'Quantité non multiple de la valeur autorisée',
    ),
    'documents' =>
    array (
      'shipping' =>
      array (
        'filename' => 'Détails des livraisons de commande :suppliers.pdf',
        'heading' => 'Détails des livraisons de commandes :identifier à :supplier du :date',
        'short_heading' => 'Détails de la livraison',
      ),
      'summary' =>
      array (
        'heading' => 'Produits de la commande :identifier à :supplier',
      ),
      'table' =>
      array (
        'filename' => 'Tableau des commandes :identifier à :supplier.csv',
      ),
    ),
    'all' => 'Commandes',
    'pending_packages' => 'Paquets à compléter',
    'booking_aggregation' => '',
    'statuses' =>
    array (
      'unchange' => 'Non modifié',
      'to_pay' => 'Commandes à payer',
      'open' => '',
      'closing' => 'Date de clôture',
      'closed' => 'Fermer',
    ),
    'do_aggregate' => 'Fusionner les commandes',
    'admin_dates' => 'Gestion des dates',
    'admin_automatics' => 'Prochaines dates dans le calendrier :',
    'notices' =>
    array (
      'closed_orders' => '',
      'email_attachments' => '',
      'calculator' => '',
    ),
    'files' =>
    array (
      'aggregate' =>
      array (
        'shipping' => 'Résumé des livraisons',
        'summary' => 'Résumé des produits',
        'table' => 'Tableau global des produits',
      ),
      'order' =>
      array (
        'summary' => 'Résumé des produits',
        'shipping' => 'Détails de la livraison',
        'table' => 'Tableau global des produits',
        'shipping_and_summary' => '',
      ),
    ),
    'help_aggregate_status' => '',
    'change_date' => 'Modifier les catégories',
    'help_change_date' => '',
    'last_summaries_date' => 'Dernière notification envoyée',
    'aggregate' => 'Fusionnés',
    'deliveries' => 'Livraisons',
    'fast_deliveries' => 'Livraison express',
    'modifiers_redistribution_summary' => '',
    'modifiers_redistribution' =>
    array (
      'keep' => '',
      'recalculate' => '',
    ),
    'importing' =>
    array (
      'save' => '',
      'close' => '',
    ),
    'booked_by' => 'Réservé',
    'delivered_by' => 'Livré',
    'load_booked_quantities' => 'Charger les quantités commandées',
    'save_delivery' => 'Enregistrer les informations',
    'do_delivery' => 'Livraison',
    'help_order_export_shipping' => 'Depuis cette page vous pouvez obtenir un document PDF à imprimer avec l\'ensemble des produits réservés. Utile à consulter pendant que vous faites les livraisons.',
    'notify_days_before' => '',
    'handle_packages' => 'Forcer la complétion des emballages',
    'list_delivering' => 'Commandes en cours de livraison',
    'help_aggregate_export_table' => 'Depuis cette page vous pouvez obtenir un fichier CSV avec le détail de l\'ensemble des produits commandés.',
    'help_aggregate_export_table_for_delivery' => '',
    'include_all_modifiers' => 'Voir tous les fournisseurs',
    'help_aggregate_export_shipping' => 'Depuis cette page, vous pouvez obtenir un document PDF à imprimer avec toutes les informations relatives aux commandes contenues dans cette sélection.',
    'bookings_from_friends' => 'Commandes de vos amis',
    'communications_points' => '',
    'booking_total_amount' => 'Total à payer: :amount',
    'formatted_delivery_date' => 'Livraison prévue le :date.',
    'notes_to_supplier' => 'Notes pour le fournisseur',
    'summaries_recipients_count' => '',
    'bookings_to_pay' => '',
    'automatic_labels' =>
    array (
      'delivery' => '',
      'days_after' => '',
      'close' => '',
      'days_before' => '',
      'open' => '',
    ),
  ),
  'permissions' =>
  array (
    'permissions' =>
    array (
      'maintenance_access' => 'Accès également autorisé durant les périodes de maintenance',
      'alter_permissions' => 'Modifier toutes les permissions',
      'alter_configs' => 'Modifier la configuration du GAS',
      'create_suppliers' => 'Créer un nouveau fournisseur',
      'do_booking' => 'Commander',
      'view_suppliers' => 'Voir tous les fournisseurs',
      'view_orders' => 'Voir toutes les commandes',
      'alter_self' => 'Modifiez vos données personnelles',
      'delete_account' => '',
      'admin_users' => 'Gérer les utilisateurs',
      'view_users' => 'Voir tous les utilisateurs',
      'sub_users' => 'Gérer les sous-utilisateurs avec des fonctions limitées',
      'admin_user_movements' => 'Éditer les transactions des utilisateurs',
      'admin_movements' => 'Éditer toutes les transactions',
      'view_movements' => 'Voir toutes les transactions',
      'admin_movements_types' => 'Éditer les types de transactions',
      'admin_categories' => 'Éditer les catégories',
      'admin_measures' => 'Éditer les unités de mesure',
      'view_statistics' => 'Voir les statistiques',
      'admin_notifications' => 'Gérer les notifications',
      'alter_suppliers' => 'Modifier les fournisseurs assignés',
      'open_orders' => 'Ouvrir et modifier les commandes',
      'do_deliveries' => 'Livrer',
      'admin_invoices' => '',
      'admin_supplier_movements' => 'Gestion des mouvements comptables des fournisseurs',
      'admin_multigas' => '',
    ),
    'roles' =>
    array (
      'admin' => '',
      'secondary_admin' => '',
    ),
    'name' => 'Permissions',
    'supplier' =>
    array (
      'change' => 'Les fournisseurs peuvent être modifiés',
      'orders' => 'Vous pouvez ouvrir de nouvelles commandes pour le fournisseur',
      'deliveries' => 'Gérer les livraisons du fournisseur',
    ),
    'role' => 'Rôle',
    'help' =>
    array (
      'global_permission_notice' => 'Cette permission spéciale est appliquée automatiquement à tous les utilisateurs (présents et futurs) et permet d\'agir sur chacun, bien que l\'utilisateur concerné ne soit pas visible par les autres.',
      'blocked_autoremove' => 'Vous ne pouvez pas recouvrer ce rôle administratif par vous-même',
      'unique_role_warning' => '',
      'unprivileged' => 'Numéro progressif attribué automatiquement à chaque commande',
      'sub_user' => '',
      'multigas_admin' => '',
      'admin_not_authorized' => '',
      'parent_role' => '',
      'missing_elements_warning' => '',
    ),
    'revoke' => 'Révoquer le rôle',
    'change_roles' => 'Éditer les rôles',
    'parent_role' => 'Rôle principal',
    'add_user' => 'Ajouter un nouvel utilisateur',
    'unprivileged' => 'Rôle utilisateur sans privilèges',
    'sub_user' => 'Rôle sous-utilisateur',
    'multigas_admin' => 'Éditer les catégories',
  ),
  'products' =>
  array (
    'prices' =>
    array (
      'unit' => 'Prix unitaire',
      'unit_no_vat' => 'Prix unitaire (sans TVA)',
      'package' => 'Prix de l\'emballage',
    ),
    'name' => 'Produit',
    'code' => 'Code fournisseur',
    'bookable' => 'Commandable',
    'vat_rate' => 'Taux de TVA',
    'portion_quantity' => 'Unités',
    'multiple' => 'Multiple',
    'min_quantity' => 'Minimum',
    'max_quantity' => 'Maximum conseillé',
    'available' => 'Disponible',
    'help' =>
    array (
      'unit_no_vat' => 'À utiliser en combinaison avec le taux de TVA',
      'package_price' => 'Si spécifié, le prix unitaire est calculé comme Prix de l\'emballage / Dimensions de l\'emballage',
      'importing_categories_and_measures' => 'Les catégories et les unités de mesures non existantes seront créées.',
      'imported_notice' => 'Produits importés',
      'available_explain' => '',
      'bookable' => '',
      'pending_orders_change_price' => '',
      'pending_orders_change_price_second' => '',
      'discrete_measure_selected_notice' => '',
      'measure' => '',
      'portion_quantity' => '',
      'package_size' => '',
      'multiple' => '',
      'min_quantity' => '',
      'max_quantity' => '',
      'available' => '',
      'global_min' => '',
      'variants' => '',
      'duplicate_notice' => 'Le produit dupliqué aura une copie des variantes du produit original, qui pourront ensuite être modifiés.',
      'unit_price' => '',
      'vat_rate' => '',
      'notice_removing_product_in_orders' => '',
    ),
    'weight_with_measure' => 'Poids (en kg)',
    'list' => 'Produits',
    'sorting' => '',
    'variant' =>
    array (
      'matrix' => 'Créer/éditer une variante',
      'help' =>
      array (
        'code' => '',
        'price_difference' => '',
      ),
      'price_difference' => 'Différence de prix',
      'weight_difference' => 'Différence de prix',
    ),
    'package_size' => 'Colis',
    'global_min' => 'Commande totale',
    'variants' => 'Variable',
    'remove_confirm' => '',
    'removing' =>
    array (
      'keep' => '',
      'leave' => '',
    ),
  ),
  'supplier' =>
  array (
    'referent' => 'Responsable',
    'payment_method' => 'Mode de paiement',
    'all' => 'Fournisseurs',
    'products_list' =>
    array (
      'pdf' => 'Liste des prix (PDF, généré automatiquement)',
      'csv' => 'Liste des prix (CSV, généré automatiquement)',
    ),
    'attachments' => 'Fichiers et images',
    'remote_index' => '',
    'vat' => 'Nom ou numéro de TVA',
    'enable_fast_shipping' => 'Livraison express',
    'help' =>
    array (
      'enable_fast_shipping' => '',
      'enable_no_quantities' => '',
      'modifiers_notice' => '',
      'import_products_notice' => '',
      'handling_products' => 'Attention : il y a des commandes qui ne sont pas encore livrées et archivées pour ce fournisseur ; toute modification des produits s\'appliquera également à ces commandes.',
      'name' => 'Nom du fournisseur',
      'legal_name' => '',
      'description' => 'Voir tous les utilisateurs',
      'payment_method' => '',
      'orders_mode' => '',
      'send_notification_on_close' => '',
    ),
    'enable_no_quantities' => 'Autoriser la livraison rapide',
    'export_products' => 'Exporter',
    'referents' => 'Responsable',
    'products_list_heading' => 'Liste de prix :supplier sur :date',
    'admin_categories' => 'Éditer les catégories',
    'admin_measures' => 'Gérer les unités de mesure',
    'legal_name' => 'Nom de l\'entreprise',
    'orders_mode' => 'Avancement de la commande',
    'send_notification_on_close' => '',
  ),
  'tour' =>
  array (
    'welcome' =>
    array (
      'title' => '',
      'body' => '',
    ),
    'profile' =>
    array (
      'title' => '',
      'body' => '',
    ),
    'users' =>
    array (
      'title' => '',
      'body' => '',
    ),
    'suppliers' =>
    array (
      'title' => '',
      'body' => '',
    ),
    'orders' =>
    array (
      'title' => 'Commandes',
      'body' => '',
    ),
    'bookings' =>
    array (
      'title' => 'Réservations',
      'body' => '',
    ),
    'accounting' =>
    array (
      'title' => 'Comptabilité',
      'body' => '',
    ),
    'config' =>
    array (
      'title' => 'Paramètres',
      'body' => '',
    ),
    'inline' =>
    array (
      'title' => '',
      'body' => '',
    ),
    'last' =>
    array (
      'title' => '',
      'body' => '',
    ),
    'finished' => '',
  ),
  'user' =>
  array (
    'help' =>
    array (
      'suspended' => 'Les utilisateurs suspendus et résiliés ne peuvent pas accéder à la plateforme, bien qu\'ils restent enregistrés. Une date de cessation/suspension doit être précisée.',
      'wrong_control_error' => 'Le code de contrôle est erroné',
      'existing_email_error' => 'L\'adresse courriel est déjà enregistrée.',
      'duplicated_name_error' => '',
      'waiting_approval' => '',
      'promote_friend' => '',
      'promote_friend_enforce_mail' => '',
      'reassign_friend' => '',
      'notifications_instructions' => 'Choisissez les fournisseurs pour lesquels vous souhaitez recevoir une notification lors de nouvelles commandes.',
      'fee' => '',
      'deposit' => '',
      'satispay' => 'Depuis cette page, vous pouvez ajouter du crédit avec Satispay. Spécifiez combien vous souhaitez payer et le cas échéant une note pour l\'administrateur. Vous recevrez une notification sur votre smartphone qui doit être confirmée en 15 minutes pour valider le paiement.',
      'remove_profile' => '',
      'remove_profile_credit_notice' => '',
    ),
    'firstname' => 'Prénom',
    'lastname' => 'Nom',
    'change_friend' => '',
    'formatted_aggregation' => '',
    'sepa' =>
    array (
      'mandate' => 'Mandat SEPA',
      'date' => 'Date du mandat SEPA',
      'intro' => 'Configuration SEPA',
      'help' => '',
      'identifier' => 'Identifiant de mandat SEPA',
    ),
    'last_login' => 'Dernier accès',
    'last_booking' => 'Dernière réservation',
    'member_since' => 'Membre depuis',
    'birthplace' => '',
    'birthdate' => 'Date de naissance',
    'other_bookings' => '',
    'fullname' => 'Nom complet',
    'taxcode' => 'Code fiscal',
    'card_number' => 'Numéro de membre',
    'payment_method' => 'Mode de paiement',
    'all' => 'Utilisateurs',
    'payed_fee' => 'Montant payé',
    'name' => 'Utilisateur',
    'address_part' =>
    array (
      'street' => 'Adresse (Rue)',
      'city' => 'Adresse (Ville)',
      'zip' => 'Adresse (Code postal)',
    ),
    'statuses' =>
    array (
      'active' => 'Activé',
      'suspended' => 'Suspendu',
      'deleted' => '',
      'removed' => '',
    ),
    'friend' => 'Ami',
    'removed_user' => '',
    'booking_friend_header' => 'À commandé :amount',
    'pending_deliveries' => '',
    'without_aggregation' => '',
    'aggregation' => '',
    'credit_below_zero' => 'Crédit négatif',
    'fee_not_payed' => 'Montant non payé',
    'personal_data' => 'Informations personnelles',
    'approve' => '',
    'do_not_approve' => '',
    'family_members' => 'Membres de la famille',
    'promote_friend' => 'Nouvel utilisateur enregistré',
    'reassign_friend' => 'Ma réservation',
    'change_friend_assignee' => '',
    'fee' => 'Cotisation',
    'deposit' => 'Dépôt',
    'fees_status' => 'État',
    'all_ceased' => 'Terminés',
    'notices' =>
    array (
      'new_user' => 'Nouvel utilisateur inscrit sur :gasname:',
      'pending_approval' => '',
    ),
    'last_fee' => '',
    'fees' =>
    array (
      'new' => '',
      'change' => 'Edit Quantity',
    ),
    'empty' =>
    array (
      'friends' => 'Ajoutez ici les informations concernant vos amis pour lesquels vous souhaitez créer des sous-réservations. Chaque réservation sera autonome, mais considérée comme une unique réservation lors de la livraison. Chaque ami peut avoir ses propres identifiants pour accéder à GASdotto et créer ses propres réservations.',
    ),
    'satispay' =>
    array (
      'reload' => 'Ajouter du crédit avec Satispay',
    ),
    'remove_profile' => '',
    'assign_aggregations' => '',
  ),
);
