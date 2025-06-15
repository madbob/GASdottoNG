<?php

return array (
  'booking' => 
  array (
    'void' => 'Vorbestellung stornieren',
    'statuses' => 
    array (
      'open' => 'Aktuell laufende Bestellungen',
      'closed' => 'Abgeschlossene Bestellungen',
      'shipped' => 'Geliefert',
      'paying' => '',
      'archived' => 'Archiviert',
      'suspended' => 'In der Schwebe',
      'booked' => 'Bestellt',
      'to_deliver' => 'Zu liefern',
      'saved' => 'Gespeichert',
    ),
    'nav' => 
    array (
      'mine' => 'Meine Vorbestellung',
      'friends' => 'Vorbestellungen für Freunde',
      'others' => 'Vorbestellungen für andere',
      'add' => 'Vorbestellung hinzufügen/ändern',
    ),
  ),
  'help' => 
  array (
    'pending_packages_notice' => 'Achtung: diese Bestellung ist geschlossen, aber es ist möglich, einige Produkte noch vorzubestellen, um die bereits bestellten Packeinheiten zu vervollständigen.',
    'send_booking_summaries' => '',
    'send_delivery_summaries' => '',
    'no_partecipating' => 'Du hast an dieser Bestellung nicht teilgenommen',
    'formatted_booked_amount' => 'Du hast bestellt :amount',
    'formatted_booked_amount_with_friends' => 'Du hast bestellt :amount + :friends',
    'product_selection' => 'Um Produkte aus der Liste des Lieferanten innerhalb der Bestellung zu aktivieren oder deaktivieren',
    'booked_modifier_column' => '',
    'delivered_modifier_column' => '',
    'fixes_column' => 'Feld in dem die Bestellmenge jeder Vorbestellung angepasst und Kommentare an den Lieferanten hinzugefügt werden können',
    'number' => 'Fortlaufende Nummer, die jeder Bestellung automatisch zugewiesen wird',
    'unarchived_notice' => '',
    'extimated_value' => '',
    'insufficient_credit_notice' => 'Achtung: dein Guthaben reicht nicht aus, um neue Bestellungen vorzunehmen.',
    'friends_bookings_notice' => 'Hier kannst du Unter-Bestellungen hinzufügen für deine Freunde. Diese werden Teil deiner Bestellung sein, aber du kannst die Informationen trotzdem getrennt voneinander verwalten. Die Liste deiner Freunde kannst du auf der Seite deines Profils verwalten.',
    'no_friends' => 'Für diesen Nutzer sind keine \'Freunde\' registriert.',
    'closed_order_alert_new_booking' => 'Achtung: Diese Bestellung ist geschlossen. Bevor Sie eine Vorbestellung hinzufügen oder ändern, überprüfen Sie bitte, dass die Bestellung noch nicht dem Lieferant übermittelt wurde.',
    'send_summaries' => 'Verschicke an alle Teilnehmer einer Bestellung eine Bestätigungsmail für ihre individuelle Bestellung. Es ist möglich, eine Nachricht an alle anzuhängen mit zusätzlichen Informationen',
    'automatic_instructions' => '',
    'changed_products' => '',
    'waiting_closing_for_deliveries' => 'Dieses Feld wird freigeschaltet sobald die Vorbestellungen geschlossen sind',
    'modifiers_require_redistribution' => '',
    'contacts_notice' => '',
    'explain_aggregations' => 'Nach der Angliederung werden die Bestellungen als eine angezeigt, trotzdem behalten alle Dateien ihre individuellen Eigenschaften. Diese Funktion wird empfohlen um die Verwaltung der Bestellungen, die auf dasselbe Datum geliefert werden, zu vereinfachen.',
    'aggregation_instructions' => 'Klick und nimm die Bestellungen in der gleiche Zelle um sie zusammen zu stellen, oder in eine lehre Zelle um sie zu trennen.',
    'status' => 'Aktueller Status der Bestellung. Kann folgende Werte annehmen:<ul><li>aktuell laufende Bestellungen: alle Nutzer sehen die Bestellung und können ihre Vormerkungen vornehmen</li><li>abgeschlossene Bestellungen: alle Nutzer sehen die Bestellung, aber können ihre Vorbestellung nicht mehr ergänzen oder verändern. Nur diesbzgl. autorisierte Nutzer können hier Änderungen nachträglich vornehmen</li><li>geliefert: die Bestellung wird den autorisierten Nutzern im Verzeichnis \'Bestellungen\' angezeigt, aber weder der Wert noch die Vormerkungen können verändert werden</li><li>archiviert: die Bestellung erscheint nicht mehr im Verzeichnis und kann nur noch über die Suchfunktion wieder herausgefischt werden</li><li>in der Schwebe: die Bestellung erscheint im Verzeichnis \'Bestellungen\' nur für die autorisierten Nutzer und kann geändert werden</li></ul>',
    'prices_changed' => '',
    'variant_no_longer_active' => '',
    'pending_saved_bookings' => '',
    'mail_order_notification' => '',
    'target_supplier_notifications' => '',
    'notify_only_partecipants' => '',
    'comment' => 'Möglicher Hinweis, der aufgenommen wird im Titel der Bestellung und der weder den Namen des Lieferanten noch die Daten zu Beginn und Ende der Bestellzeit enthält',
    'end' => 'Datum des Bestellschlusses. Am Ende des hier angegebenen Tages wird die Bestellung automatisch in den Status \'abgeschlossene Bestellungen\' überführt',
    'contacts' => '',
    'handle_packages' => '',
    'payment' => 'Von hier ab ist es möglich, die Zahlung der Bestellung an den Lieferanten einzugeben, die den entsprechenden Saldo verändern wird',
    'no_opened' => 'Es gibt keine offenen Vorbestellungen.',
    'no_delivering' => 'Es gibt keine abgeschlossenen Bestellungen, für die eine Auslieferung ansteht.',
    'include_all_modifiers' => '',
    'supplier_multi_select' => '',
    'start' => '',
    'manual_fixes_explain' => 'Hier ist es möglich die vorbestellte Menge dieses Produkts in jeder Lieferung zu ändern, aber noch kein Benutzer hat an dieser Bestellung teilgenommen.',
    'pending_notes' => '',
    'no_partecipation_notice' => 'Du hast an dieser Bestellung nicht teilgenommen.',
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
    'shipped' => 'Im Folgenden die Zusammenfassung der Produkte, die dir geliefert wurden:',
    'saved' => 'Im Folgenden die Zusammenfassung der Produkte, die dir geliefert werden:',
    'pending' => 'Zusammenfassung deiner Bestellung:',
  ),
  'send_booking_summaries' => 'Zusammenfassungen der Vorbestellungen verschicken',
  'send_delivery_summaries' => 'Verschicke Zusammenfassungen der Lieferungen',
  'packages' => 
  array (
    'ignore' => 'Nein, Packmaß ignorieren',
    'permit' => 'Ja, andere Vorbestellungen zulassen',
    'permit_all' => 'Ja, und es umfasst die von allen GAS gebuchten Mengen',
  ),
  'and_more' => 'und andere',
  'boxes' => 'Verpackungsanzahl',
  'supplier' => 'Lieferant',
  'booking_date_time' => '',
  'list_open' => 'Offene Bestellungen',
  'dates' => 
  array (
    'shipping' => 'Lieferdatum',
    'start' => 'Beginn Vorbestellungen',
    'end' => 'Ende Vorbestellungen',
  ),
  'name' => 'Bestellung',
  'formatted_name' => 'von :start bis :end',
  'formatted_delivery_in_name' => ', für die Auslieferung am :delivery',
  'quantities' => 
  array (
    'booked' => 'Menge Vorbestellung',
    'shipped' => 'Gelieferte Menge',
  ),
  'weights' => 
  array (
    'booked' => 'Gewicht Vorbestellung',
    'delivered' => 'Geliefertes Gewicht',
  ),
  'totals' => 
  array (
    'shipped' => 'Gesamtbetrag Lieferung',
    'with_modifiers' => '',
    'total' => 'Gesamtbetrag',
    'taxable' => 'gesamt Besteuerbar',
    'vat' => 'Gesamt MWSt',
    'booked' => 'Gesamtbetrag Vorbestellung',
    'complete' => 'Gesamt',
    'invoice' => 'Endbetrag Rechnung',
    'orders' => 'Gesamtbestellungen',
    'manual' => 'Endbetrag Rechnung',
    'to_pay' => 'Offener Betrag',
    'selected' => '',
  ),
  'constraints' => 
  array (
    'quantity' => '',
    'discrete' => '',
    'global_min' => '',
    'global_max_help' => 'Es fehlen :still :measure um das Gebinde für diese Bestellung zu vervollsändigen',
    'global_max_short' => ':icon Verfügbar: :quantity',
    'global_max' => 'Verfügbar: :still (:global Gesamtbetrag)',
    'global_max_generic' => '',
    'relative_max_formatted' => 'Empfohlenes Maximum: :quantity',
    'relative_max' => '',
    'relative_min_formatted' => 'Mindest: :quantity',
    'relative_min' => '',
    'relative_multiple_formatted' => 'Multiplikator: :quantity',
    'relative_multiple' => '',
  ),
  'documents' => 
  array (
    'shipping' => 
    array (
      'filename' => 'Bestell-und Lieferuebersicht :suppliers.pdf',
      'heading' => 'Bestellübersichtl :identifier beim Lieferant :supplier am :date',
      'short_heading' => 'Bestellübersicht (pdf)',
    ),
    'summary' => 
    array (
      'heading' => 'Produkte der Bestellung %s bei %s',
    ),
    'table' => 
    array (
      'filename' => 'Gesamttabelle Bestellung %s bei %s.csv',
    ),
  ),
  'all' => 'Bestellungen',
  'pending_packages' => 'Zu komplettierende Gebinde',
  'booking_aggregation' => '',
  'statuses' => 
  array (
    'unchange' => 'Unverändert',
    'to_pay' => 'Zu bezahlende Bestellungen',
    'open' => '',
    'closing' => 'Ende',
    'closed' => 'Schliessen',
  ),
  'do_aggregate' => 'Bestellungen angliedern',
  'admin_dates' => 'Kalender verwalten',
  'admin_automatics' => 'Nächste Daten im Kalender:',
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
      'shipping' => 'Bestellübersicht der zusammengelegten Lieferungen',
      'summary' => 'Zusammenfassung der bestellten Produkte',
      'table' => 'Gesamttabelle der Produkte',
    ),
    'order' => 
    array (
      'summary' => 'Zusammenfassung der bestellten Produkte',
      'shipping' => 'Bestellübersicht (pdf)',
      'table' => 'Gesamttabelle der Produkte',
      'shipping_and_summary' => '',
    ),
  ),
  'help_aggregate_status' => '',
  'change_date' => 'Kategorien ändern',
  'help_change_date' => '',
  'last_summaries_date' => 'Letzte gesendete Benachrichtigungen',
  'aggregate' => 'Hinzugefügt',
  'deliveries' => 'Lieferungen',
  'fast_deliveries' => 'Schnelle Lieferungen',
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
  'booked_by' => 'Bestellt',
  'delivered_by' => 'Geliefert',
  'load_booked_quantities' => 'Vorbestellte Quantitäten bearbeiten',
  'save_delivery' => 'Infos speichern',
  'do_delivery' => 'Lieferung',
  'help_order_export_shipping' => 'Hier kannst du eine PDF-Datei zum Ausdrucken erstellen, in der Informationen zu den einzelnen Bestellungen aufgelistet sind. Nützlich für die Überprüfung bei der Anlieferung.',
  'notify_days_before' => '',
  'handle_packages' => 'Vervollständige Gebinde',
  'list_delivering' => 'Abgeschlossene Bestellungen - Lieferung ausstehend',
  'help_aggregate_export_table' => 'Hier kannst du eine CSV-Datei erhalten, in der Details zu allen vorbestellten Produkten vorhanden sind.',
  'help_aggregate_export_table_for_delivery' => '',
  'include_all_modifiers' => 'Alle Lieferanten anzeigen',
  'help_aggregate_export_shipping' => 'Hier kannst du ein pdf-Dokument zum Ausdrucken erhalten, in dem sich alle Informationen finden bezüglich der einzelnen Bestellungen aller, die an dieser Teil-Bestellung teilgenommen haben.',
  'bookings_from_friends' => 'Bestellungen deiner Freunde',
  'communications_points' => '',
  'booking_total_amount' => 'Gesamtpreis: :amount',
  'formatted_delivery_date' => 'Geplante Lieferung am :date.',
  'notes_to_supplier' => 'Bemerkungen für den Lieferant',
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
