<?php

return array (
  'booking' => 
  array (
    'void' => 'Vorbestellung stornieren',
    'statuses' => 
    array (
      'shipped' => 'Geliefert',
      'booked' => 'Bestellt',
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
    'number' => 'Fortlaufende Nummer, die jeder Bestellung automatisch zugewiesen wird',
    'unarchived_notice' => '',
    'insufficient_credit_notice' => 'Achtung: dein Guthaben reicht nicht aus, um neue Bestellungen vorzunehmen.',
    'friends_bookings_notice' => 'Hier kannst du Unter-Bestellungen hinzufügen für deine Freunde. Diese werden Teil deiner Bestellung sein, aber du kannst die Informationen trotzdem getrennt voneinander verwalten. Die Liste deiner Freunde kannst du auf der Seite deines Profils verwalten.',
    'no_friends' => 'Für diesen Nutzer sind keine \'Freunde\' registriert.',
    'closed_order_alert_new_booking' => 'Achtung: Diese Bestellung ist geschlossen. Bevor Sie eine Vorbestellung hinzufügen oder ändern, überprüfen Sie bitte, dass die Bestellung noch nicht dem Lieferant übermittelt wurde.',
    'automatic_instructions' => '',
    'contacts_notice' => '',
    'pending_saved_bookings' => '',
    'comment' => 'Möglicher Hinweis, der aufgenommen wird im Titel der Bestellung und der weder den Namen des Lieferanten noch die Daten zu Beginn und Ende der Bestellzeit enthält',
    'end' => 'Datum des Bestellschlusses. Am Ende des hier angegebenen Tages wird die Bestellung automatisch in den Status \'abgeschlossene Bestellungen\' überführt',
    'contacts' => '',
    'handle_packages' => '',
    'payment' => 'Von hier ab ist es möglich, die Zahlung der Bestellung an den Lieferanten einzugeben, die den entsprechenden Saldo verändern wird',
    'no_opened' => 'Es gibt keine offenen Vorbestellungen.',
    'no_delivering' => 'Es gibt keine abgeschlossenen Bestellungen, für die eine Auslieferung ansteht.',
    'pending_notes' => '',
    'no_partecipation_notice' => 'Du hast an dieser Bestellung nicht teilgenommen.',
    'modifiers_notice' => '',
    'no_categories' => '',
    'supplier_no_orders' => '',
    'supplier_has_orders' => '',
  ),
  'packages' => 
  array (
    'ignore' => 'Nein, Packmaß ignorieren',
    'permit' => 'Ja, andere Vorbestellungen zulassen',
    'permit_all' => 'Ja, und es umfasst die von allen GAS gebuchten Mengen',
  ),
  'supplier' => 'Lieferant',
  'list_open' => 'Offene Bestellungen',
  'dates' => 
  array (
    'shipping' => 'Lieferdatum',
    'start' => 'Beginn Vorbestellungen',
    'end' => 'Ende Vorbestellungen',
  ),
  'name' => 'Bestellung',
  'quantities' => 
  array (
    'shipped' => 'Gelieferte Menge',
  ),
  'totals' => 
  array (
    'shipped' => 'Gesamtbetrag Lieferung',
    'total' => 'Gesamtbetrag',
    'taxable' => 'gesamt Besteuerbar',
    'vat' => 'Gesamt MWSt',
    'booked' => 'Gesamtbetrag Vorbestellung',
    'complete' => 'Gesamt',
    'invoice' => 'Endbetrag Rechnung',
    'orders' => 'Gesamtbestellungen',
    'selected' => '',
  ),
  'all' => 'Bestellungen',
  'statuses' => 
  array (
    'unchange' => 'Unverändert',
    'to_pay' => 'Zu bezahlende Bestellungen',
  ),
  'do_aggregate' => 'Bestellungen angliedern',
  'admin_dates' => 'Kalender verwalten',
  'admin_automatics' => 'Nächste Daten im Kalender:',
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
  'aggregate' => 'Hinzugefügt',
  'help_order_export_shipping' => 'Hier kannst du eine PDF-Datei zum Ausdrucken erstellen, in der Informationen zu den einzelnen Bestellungen aufgelistet sind. Nützlich für die Überprüfung bei der Anlieferung.',
  'handle_packages' => 'Vervollständige Gebinde',
  'list_delivering' => 'Abgeschlossene Bestellungen - Lieferung ausstehend',
  'help_aggregate_export_table' => 'Hier kannst du eine CSV-Datei erhalten, in der Details zu allen vorbestellten Produkten vorhanden sind.',
  'help_aggregate_export_table_for_delivery' => '',
  'help_aggregate_export_shipping' => 'Hier kannst du ein pdf-Dokument zum Ausdrucken erhalten, in dem sich alle Informationen finden bezüglich der einzelnen Bestellungen aller, die an dieser Teil-Bestellung teilgenommen haben.',
);