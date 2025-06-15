<?php

return array (
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
      'heading' => 'Produits de la commande %s à %s',
    ),
    'table' => 
    array (
      'filename' => 'Tableau des commandes %s à %s.csv',
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
);