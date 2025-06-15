<?php

return array (
  'booking' => 
  array (
    'void' => 'Reservering annuleren',
    'statuses' => 
    array (
      'open' => 'Open reserveringen',
      'closed' => 'Gesloten reserveringen',
      'shipped' => 'Geleverd',
      'paying' => '',
      'archived' => 'Gearchiveerd',
      'suspended' => 'Opgeschort',
      'booked' => '',
      'to_deliver' => 'Te leveren',
      'saved' => 'Opgeslagen',
    ),
    'nav' => 
    array (
      'mine' => 'Reservering annuleren',
      'friends' => 'Reserveringen voor vrienden',
      'others' => 'Reserveringen voor anderen',
      'add' => 'Reservering toevoegen/wijzigen',
    ),
  ),
  'help' => 
  array (
    'pending_packages_notice' => '',
    'send_booking_summaries' => '',
    'send_delivery_summaries' => '',
    'no_partecipating' => 'Je hebt niet aan deze bestelling deelgenomen',
    'formatted_booked_amount' => 'Je hebt besteld :amount',
    'formatted_booked_amount_with_friends' => 'je hebt besteld :amount + :friends',
    'product_selection' => 'Om producten van de prijslijst van de leverancier binnen de bestelling in of uit te schakelen',
    'booked_modifier_column' => '',
    'delivered_modifier_column' => '',
    'fixes_column' => 'Paneel voor rechtstreekse wijziging van de producthoeveelheden in elke reservering en toevoeging opmerkingen voor leverancier',
    'number' => '',
    'unarchived_notice' => '',
    'extimated_value' => '',
    'insufficient_credit_notice' => '',
    'friends_bookings_notice' => 'Vanaf hier kun je subreserveringen maken die aan je vrienden zijn toegewezen. Ze zullen deel uitmaken van je algehele reservering maar je kunt de informatie nog steeds apart houden. Vul je vriendenlijst in vanaf je profielpagina.',
    'no_friends' => 'Er zijn geen open reserveringen.',
    'closed_order_alert_new_booking' => 'Let op: deze bestelling is gesloten, voordat je een reservering toevoegt of wijzigt, dient je je ervan te vergewissen dat de gewenste totale hoeveelheid nog niet aan de leverancier is meegedeeld of dat deze nog kan worden gewijzigd.',
    'send_summaries' => '',
    'automatic_instructions' => '',
    'changed_products' => '',
    'waiting_closing_for_deliveries' => 'Dit paneel zal actief zijn wanneer de reserveringen gesloten zijn',
    'modifiers_require_redistribution' => '',
    'contacts_notice' => '',
    'explain_aggregations' => 'Eenmaal samengevoegd worden de bestellingen als één geheel weergegeven met behoud van de afzonderlijke attributen. Deze functie wordt aanbevolen om de administratie te vergemakkelijken van bestellingen die bijvoorbeeld op dezelfde datum worden geleverd.',
    'aggregation_instructions' => 'Klik en sleep bestellingen naar dezelfde cel om ze samen te voegen, of naar een lege cel om ze op te splitsen.',
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
    'no_opened' => 'Er zijn geen open reserveringen.',
    'no_delivering' => 'Er zijn geen bestellingen in leveringsfase.',
    'include_all_modifiers' => '',
    'supplier_multi_select' => '',
    'start' => '',
    'manual_fixes_explain' => 'Vanaf hier kunt u de gereserveerde hoeveelheid van dit product voor elke reservering wijzigen, maar nog geen enkele gebruiker heeft deelgenomen aan de bestelling.',
    'pending_notes' => '',
    'no_partecipation_notice' => 'Je hebt niet deelgenomen aan deze bestelling.',
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
    'shipped' => '',
    'saved' => '',
    'pending' => 'Hieronder vindt u een overzicht van de door u bestelde producten:',
  ),
  'send_booking_summaries' => 'Samenvattingen reserveringen verzenden',
  'send_delivery_summaries' => '',
  'packages' => 
  array (
    'ignore' => '',
    'permit' => '',
    'permit_all' => '',
  ),
  'and_more' => '',
  'boxes' => 'Aantal verpakkingen',
  'supplier' => 'Leverancier',
  'booking_date_time' => '',
  'list_open' => 'Open bestellingen',
  'dates' => 
  array (
    'shipping' => 'Afleverdatum',
    'start' => 'Openingsdatum reserveringen',
    'end' => 'Sluitingsdatum reserveringen',
  ),
  'name' => 'Bestelling',
  'formatted_name' => 'van :start tot :end',
  'formatted_delivery_in_name' => ', te leveren :delivery',
  'quantities' => 
  array (
    'booked' => 'Gereserveerde hoeveelheid',
    'shipped' => 'Geleverde hoeveelheid',
  ),
  'weights' => 
  array (
    'booked' => '',
    'delivered' => '',
  ),
  'totals' => 
  array (
    'shipped' => 'Totaal geleverd',
    'with_modifiers' => '',
    'total' => 'Totaal',
    'taxable' => 'Totaal bedrag',
    'vat' => 'Totaal BTW',
    'booked' => 'Totaal gereserveerd',
    'complete' => 'Overzichtstabel producten',
    'invoice' => 'Totaal factuur',
    'orders' => 'Totaal bestellingen',
    'manual' => 'Totaal factuur',
    'to_pay' => 'Te betalen bedrag',
    'selected' => '',
  ),
  'constraints' => 
  array (
    'quantity' => '',
    'discrete' => '',
    'global_min' => '',
    'global_max_help' => '',
    'global_max_short' => '',
    'global_max' => 'Beschikbaar: :still (:global totaal)',
    'global_max_generic' => '',
    'relative_max_formatted' => 'Aanbevolen maximum: :quantity',
    'relative_max' => '',
    'relative_min_formatted' => 'Minimum: :quantity',
    'relative_min' => '',
    'relative_multiple_formatted' => 'Meervoud: :quantity',
    'relative_multiple' => '',
  ),
  'documents' => 
  array (
    'shipping' => 
    array (
      'filename' => 'Detail leveringen bestellingen :suppliers.pdf',
      'heading' => 'Detail leveringen bestelling :identifier bij :supplier van :date',
      'short_heading' => 'Leveringsdetails',
    ),
    'summary' => 
    array (
      'heading' => '',
    ),
    'table' => 
    array (
      'filename' => 'Tabel Bestelling %s bij %s.csv',
    ),
  ),
  'all' => 'Bestellingen',
  'pending_packages' => '',
  'booking_aggregation' => '',
  'statuses' => 
  array (
    'unchange' => '',
    'to_pay' => 'Te betalen bestellingen',
    'open' => '',
    'closing' => 'Sluitingsdatum',
    'closed' => 'Sluiten',
  ),
  'do_aggregate' => 'Bestellingen samenvoegen',
  'admin_dates' => 'Beheer data',
  'admin_automatics' => 'Komende data op kalender:',
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
      'shipping' => 'Detail gegroepeerde leveringen',
      'summary' => '',
      'table' => 'Overzichtstabel producten',
    ),
    'order' => 
    array (
      'summary' => 'Er zijn geen producten',
      'shipping' => 'Leveringsdetails',
      'table' => 'Overzichtstabel producten',
      'shipping_and_summary' => '',
    ),
  ),
  'help_aggregate_status' => '',
  'change_date' => 'Categorieën wijzigen',
  'help_change_date' => '',
  'last_summaries_date' => 'Laatste verzonden meldingen',
  'aggregate' => 'Samengevoegd',
  'deliveries' => 'Leveringen',
  'fast_deliveries' => 'Snelle leveringen',
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
  'booked_by' => 'Reserveringen',
  'delivered_by' => 'Geleverd',
  'load_booked_quantities' => 'Gereserveerde hoeveelheden laden',
  'save_delivery' => 'Informatie opslaan',
  'do_delivery' => 'Levering',
  'help_order_export_shipping' => 'Vanaf hier kunt u een PDF-document verkrijgen dat u kunt afdrukken en waarin u informatie over individuele reserveringen kunt vinden.',
  'notify_days_before' => '',
  'handle_packages' => '',
  'list_delivering' => 'Bestellingen in leveringsfase',
  'help_aggregate_export_table' => 'Vanaf hier kunt u een CSV-document verkrijgen met de details van alle producten die in deze bestelling zijn gereserveerd.',
  'help_aggregate_export_table_for_delivery' => '',
  'include_all_modifiers' => 'Alle leveranciers zien',
  'help_aggregate_export_shipping' => 'Hier kunt u een PDF-document verkrijgen dat voor het afdrukken is geformatteerd en dat informatie bevat over de afzonderlijke reserveringen van alle bestellingen die in deze groepering zijn opgenomen.',
  'bookings_from_friends' => 'De bestellingen van uw vrienden',
  'communications_points' => '',
  'booking_total_amount' => '',
  'formatted_delivery_date' => 'De levering zal plaatsvinden :date.',
  'notes_to_supplier' => 'Opmerkingen voor de leverancier',
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