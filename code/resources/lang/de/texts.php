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
    'accept_privacy' => 'Ich habe die <a href="%s" target="_blank">Datenschutzhinweise</a> gelesen und akzeptiere sie.',
    'username' => 'Benutzername',
    'help' =>
    array (
      'missing_user_or_mail' => 'Benutzername oder E-Mail-Adresse nicht gefunden',
      'missing_email' => 'Der angegebene Nutzer hat keine gültige E-Mail-Adresse',
      'reset_email_notice' => 'Du hast eine E-Mail mit einem Link zur Aktualisierung deines Passwortes erhalten',
      'username_same_password' => 'Das Passwort ist identisch mit dem Benutzernamen! Ändere es, sobald wie möglich, in deinem <a href=":link">Nutzerprofil</a>!',
      'suspended_account_notice' => 'Dein Konto wurde gesperrt und du kannst keine Bestellungen mehr vornehmen. Überprüfe den Status deiner Zahlungen und deines Guthabens oder die von den Administrator/innen gesendeten Benachrichtigungen.',
      'invalid_username' => 'ungültiger Benutzername',
      'required_new_password' => 'Um fortzufahren musst du ein neues Passwort für dein Konto erstellen.',
      'unconfirmed' => '',
      'username' => 'Benutzername, mit dem der Nutzer sich authentifiziert (muss eindeutig sein)',
      'email_mode' => 'Andernfalls erhalten Sie eine Einladungs-E-Mail mit dem Link, den Sie besuchen müssen, um sich zum ersten Mal anzumelden und Ihr Passwort festzulegen.',
    ),
    'reset_username' => 'Benutzername oder E-Mail-Adresse',
    'password' => 'Passwort',
    'password_request_link' => 'Passwort wiederherstellen',
    'maintenance_notice' => 'Wartungsmodus: der Zugriff ist momentan nur für Administratoren erlaubt',
    'login' => 'Anmelden',
    'remember' => 'Erinnere mich',
    'register' => 'Registrieren',
    'confirm_password' => 'Passwort bestätigen',
    'update_password' => 'Passwort bestätigen',
    'modes' =>
    array (
      'email' => 'E-Mail senden',
    ),
  ),
  'commons' =>
  array (
    'accept_conditions' => 'Ich habe die <a href="%s" target="_blank">Nutzungsbedingungen</a> gelesen und akzeptiere sie.',
    'warning' => 'Achtung',
    'loading' => 'Ladevorgang',
    'feedback' => 'Rückmeldung',
    'about' =>
    array (
      'opensource' => '',
      'contribute' => '',
      'donate' => '',
      'link' => '',
      'local_contact' => 'Beachte: bei Problemen mit den Inhalten dieser Seite (Lieferant, (Vor-)Bestellung...) kontaktiere die Administratoren deiner Bestellgemeinschaft (GAS, FoodCoop, Solawi...). Die über dieses Formular verschickten Daten sind öffentlich: übermittle keine sensiblen persönlichen Daten und Adressen!',
      'translations' => 'Wenn du zu der Übersetzung in deine Sprache beitragen willst, dann besuche <a href="https://hosted.weblate.org/projects/gasdottong/native/">diese Seite</a>.',
    ),
  ),
  'export' =>
  array (
    'help' =>
    array (
      'mandatory_column_error' => 'Unbestimmte Pflichtspalte',
      'importing' =>
      array (
        'deliveries' =>
        array (
          'first_product' => '',
        ),
        'user' =>
        array (
          'aggregation' => '',
          'deleted' => 'Anzeigen "true" oder "false"',
          'balance' => '',
          'instruction' => 'Wenn die Anmeldungsdaten bereits existieren, wird der entsprechende Benutzer mit den aus der Datei gelesenen Daten aktualisiert. Andernfalls erhalten Sie eine Einladungs-E-Mail mit dem Link, den Sie besuchen müssen, um sich zum ersten Mal anzumelden und Ihr Passwort festzulegen.',
        ),
      ),
      'csv_instructions' => 'Es sind nur Dateien im Format CSV erlaubt. Es wird darum gebeten, die eigene Tabelle homogen zu formatieren, ohne verbundene bzw. leere Zellen und Überschriften: jede Zeile soll alle Informationen zu dem Subjekt enthalten. Eventuelle Preise und Summen sind ohne Eurozeichen zu schreiben.',
      'selection_instructions' => 'Sobald die Datei hochgeladen ist, wird es möglich sein die Attribute für jede Spalte im Dokument zu spezifizieren.',
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
    'products_list_filename' => 'Liste :supplier.:format',
    'import' =>
    array (
      'csv' => 'CSV Import',
      'gdxp' => 'GDXP importieren',
    ),
    'help_csv_libreoffice' => 'Um CSV-Dateien (<i>Comma-Separated Values</i>) zu öffnen und zu bearbeiten wird <a target="_blank" href="https://de.libreoffice.org/">LibreOffice</a> empfohlen.',
    'data' =>
    array (
      'columns' => 'Spalten',
      'format' => 'Format',
      'formats' =>
      array (
        'pdf' => '',
        'csv' => '',
        'gdxp' => 'GDXP',
      ),
      'status' => 'Status der Vorbestellungen',
      'users' => 'Benutzer',
      'products' => 'Produktname',
      'split_friends' => '',
    ),
    'export' =>
    array (
      'database' => 'Exportieren',
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
      'home_message' => 'Möglichkeit eine Nachricht zu hinterlegen, die auf der Authentifizierungsseite angezeigt wird. Nützlich für spezielle Mitteilungen an die Mitglieder der Bestellgemeinschaft oder als Willkommensnachricht',
      'currency' => 'Symbol der verwendeten Währung, das in allen Ansichten verwendet wird, in denen Preise angegeben werden',
      'maintenance_mode' => 'Wenn aktiviert, wird der Login für jene Nutzer gesperrt, die nicht über die Berechtigung "Zugriff auch während der Wartung" verfügen',
      'enable_public_registration' => 'Wenn diese Option aktiviert ist, kann sich jeder über das entsprechende Formular registrieren (zugänglich über das Login-Formular). Die für die Nutzerverwaltung zuständigen Administratoren erhalten eine Benachrichtigung über jeden neu registrierten Nutzer',
      'empty_list_shared_files' => 'Es gibt derzeit keine Elemente, die angezeigt werden können.<br/>Wenn Dateien hinzugefügt werden, sind sie für alle Nutzer auf dem Dashboard zugänglich. Nützlich, um Dokumente im allgemeinen Interesse zu teilen.',
      'enable_deliveries_no_quantities' => '',
      'active_columns_summary' => 'Diese hier ausgewählten Spalten werden standardmäßig in der Übersicht zur Verwaltung der einzelnen Bestellungen angezeigt. Es ist jedoch jederzeit möglich, die Anzeige innerhalb der Übersicht selbst mit Hilfe des Auswahlbuttons oben rechts zu ändern',
      'default_columns_shipping_document' => '',
      'custom_emails' => 'Hier kannst du die Texte der E-Mails verändern, die durch GASdotto verschickt werden. Für jeden Typ sind Platzhalter vorgesehen, die im Moment der Erstellung durch die entsprechenden Werte ersetzt werden: um die Texte einzufügen, nutze die Syntax %[name_placeholder]',
      'global_placeholders' => 'Allgemeine Platzhalter, die in allen Benachrichtigungen genutzt werden können:',
      'manual_products_sorting' => '',
      'social_year' => 'An diesem Tag verfallen die Anmeldegebühren/Jahresbeiträge automatisch und werden erneut fällig',
      'fee' => 'Wenn nicht konfiguriert (Wert = 0), werden die Anmeldegebühren nicht verwaltet',
      'deposit' => 'Wenn nicht konfiguriert (Wert = 0), werden die Kautionseinzahlungen der neuen Mitglieder nicht verwaltet',
      'automatic_fees' => '',
      'enable_sepa' => 'Durch das Asufüllen dieser Feldern, wird der Export von SEPA-Dateien freigeschaltet, mit dem Überweisungen automatisiert werden können.<br/>Einige Parameter müssen für jeden Benutzer bestimmt werden.',
      'enable_satispay' => 'Wenn diese Option aktiviert ist und die entsprechenden Felder ausgefüllt sind, werden Zahlungen via Satispay ermöglicht, mit denen Nutzer ihr GASdotto-Guthaben direkt aufladen können. Um die Zugangsdaten zu erhalten, besuche folgende Seite: https://business.satispay.com/',
      'enabled_satispay' => '',
      'satispay_activation_code' => '',
      'enable_integralces' => '',
      'enable_invoicing' => 'Wenn diese Option aktiviert ist und die entsprechenden Felder ausgefüllt sind, wird das Ausstellen von Rechnungen an Nutzer, die an Bestellungen teilnehmen, aktiviert. Rechnungen werden zum Zeitpunkt der Speicherung oder der Lieferung der Bestellung ausgestellt und sind über Buchhaltung > Rechnungen zugänglich',
      'invoices_counter' => 'Diesen Parameter mit Vorsicht ändern!',
      'enable_hub' => '',
      'import' => 'Von hier aus kann eine GDXP-Datei importiert werden, die von einer anderen GASdotto-Instanz oder einer anderen Plattform erzeugt wurde, die dieses Dateiformat unterstützt',
      'gdxp_explain' => 'GDXP ist ein interfunktionsfähiges Format um Preislisten und Bestellungen zwischen verschiedene Verwaltungssoftwares zu tauschen. Hier kannst du eine GDXP-Datei importieren.',
      'multigas_mode' => '',
      'only_bookings_with_credit_limit' => '',
      'fast_product_change_columns' => '',
    ),
    'attribute_name' => 'Name der Bestellgemeinschaft',
    'logo' => 'Homepage Logo',
    'home_message' => 'Homepage-Nachricht',
    'language' => 'Sprache',
    'maintenance_mode' => 'Wartungsmodus',
    'enable_public_registration' => 'öffentliche Registrierung zugelassen',
    'manual_approve_users' => '',
    'privacy_policy_link' => 'Link Datenschutz',
    'terms_link' => '',
    'mandatory_fields' => 'Pflichtfelder',
    'orders_and_deliveries' => 'Bestellungen und Lieferungen',
    'only_bookings_with_credit' => 'Erlaube Vorbestellungen nur bei verfügbarem Guthaben',
    'enable_deliveries_no_quantities' => '',
    'display_contacts' => 'ziehe vom Gesamtbetrag der einzelnen Vorbestellung ab',
    'active_columns_summary' => 'Spalten Zusammenfassung Bestellungen',
    'default_columns_shipping_document' => 'Bestellübersicht (pdf)',
    'suppliers_and_products' => 'Importierte Lieferanten',
    'manual_products_sorting' => '',
    'fast_product_change_columns' => 'Schnelle Änderung',
    'social_year' => 'Jahresbeginn',
    'automatic_fees' => '',
    'enable_sepa' => 'Zugelassene SEPA',
    'enable_satispay' => 'Satispay zugelassen',
    'satispay' =>
    array (
      'activation_code' => 'Buchungsschlüssel Betrieb',
    ),
    'enable_integralces' => 'IntegralCES',
    'integralces_identifier' => 'SEPA-Bewegung ID',
    'enable_invoicing' => 'Zugelassene Rechnungslegung',
    'invoices_counter' => 'Endbetrag Rechnung',
    'import_export' => 'Importieren',
    'enable_hub' => '',
    'csv_separator' => '',
    'import' => 'Import',
    'multigas_mode' => '',
    'only_bookings_with_credit_limit' => '',
  ),
  'generic' =>
  array (
    'save' => 'Speichern',
    'create_format' => 'Erstellen :type',
    'empty_list' => 'Es gibt keine Elemente, die angezeigt werden können.',
    'add_new' => 'Hinzufügen',
    'type' => 'Typ',
    'none' => 'Kein',
    'manual_selection' => 'Manuelle Auswahl',
    'named_all' => 'Alle :name',
    'email' => 'E-Mail',
    'phone' => 'Telefon',
    'absolute' => 'Absolut',
    'percentage' => 'Prozentsatz',
    'by_weight' => 'Gewicht',
    'quantity' => 'Anzahl',
    'value' => 'Wert',
    'weight' => 'Gewicht',
    'remove' => 'Löschen',
    'export' => 'Exportieren',
    'undefined' => 'undefiniert',
    'updated_at_formatted' => '',
    'address' => 'Adresse',
    'email_no_notifications' => '',
    'cellphone' => 'Handy',
    'fax' => 'Fax',
    'website' => 'Website',
    'confirmed' => 'Bestätigt',
    'temporary' => 'Geplant',
    'measure' => 'Maßeinheit',
    'category' => 'Kategorie',
    'price' => 'Preis',
    'yes' => '',
    'no' => 'Nein',
    'iban' => 'IBAN',
    'gas' => 'Solidarische Bestellgemeinschaft',
    'status' => 'Status',
    'unspecified' => 'Nicht spezifiziert',
    'never' => 'Nie',
    'help' =>
    array (
      'save_reminder' => '',
      'preferred_date_format' => 'Bevorzugt im Format JJJJ-MM-TT (z.B. :now)',
      'contacts' => 'Hier lassen sich beliebige Kontaktdaten des Nutzers hinterlegen. Die Benachrichtigungen werden an alle angegebenen Emailadressen verschickt',
      'unchange_password' => 'Lehrlassen um das Passwort nicht zu ändern',
      'multigas_admin_instructions' => '',
      'discrete_measure' => 'Diskrete Einheiten sind nicht teilbar: für die Produkte, den eine diskrete Einheit zugewiesen ist, ist es nicht möglich Eigenschaften wie „Variabler Preis“ und „Gebindegröße“ freizuschalten',
      'categories_instructions' => 'Das Sortieren von Kategorien ist möglich durch Ziehen und Ablegen.',
      'insert_password_notice' => 'Um diesen Vorgang zu bestätigen musst du deinen Password eingeben',
      'unassigned_group_warning' => '',
    ),
    'definitive_delete' => 'Endgültig löschen',
    'all' => 'Alle',
    'unauthorized' => 'Nicht Erlaubt',
    'error' => 'Fehler',
    'date' => 'Datum',
    'number' => 'Nummer',
    'taxable_amount' => 'Bemessungsgrundlage',
    'vat' => 'MWSt',
    'payment' => 'Bezahlung',
    'identifier' => 'ID',
    'notes' => 'Bemerkungen',
    'id' => 'ID',
    'closing_date' => 'Ende',
    'stats' =>
    array (
      'involved_orders' => 'Bestellwert',
      'involved_users' => 'Involvierte Nutzer',
      'generic' => 'Allgemeine Statistiken',
      'supplier' => 'Statistiken nach Lieferant',
    ),
    'description' => 'Beschreibung',
    'invoice' => 'Rechnung',
    'no_value' => 'Kein Wert',
    'by_kg' => '',
    'selection' => 'Auswahl',
    'home' => 'Startseite',
    'menu' =>
    array (
      'bookings' => 'Bestellen',
      'accounting' => 'Buchhaltung',
      'stats' => 'Statistiken',
      'notifications' => 'Benachrichtigungen',
      'configs' => 'Einstellungen',
      'multigas' => 'Multi-Bestellgemeinschaft',
      'friends' => 'Freunde',
      'receipts' => 'Beleg',
    ),
    'image' => 'Bild',
    'limited_access' => 'Begrenzter Zugriff',
    'disabled' => 'Deaktiviert',
    'kilos' => 'kg',
    'sortings' =>
    array (
      'all_by_user' => 'Alle (nach Nutzer*innen)',
      'manual' => '',
      'all_by_group' => '',
    ),
    'minor_than' => 'kleiner ist als',
    'major_than' => 'größer ist als',
    'exports' =>
    array (
      'csv' => 'CSV exportieren',
      'integralces' => 'IntegralCES',
      'sepa' => 'SEPA exportieren',
      'pdf' => 'PDF exportieren',
    ),
    'change' => 'Ändern',
    'details' => 'Details',
    'photo' => 'Bild',
    'composable_all' => 'Alle :label',
    'attachments' =>
    array (
      'replace_file' => 'Datei ersetzen',
      'view' => 'Statistiken ansehen',
      'replace_url' => '',
    ),
    'recipients' => 'Empfänger',
    'click_here' => 'Hier klicken',
    'attachment' => 'Hinzugefügt',
    'contacts' => 'Kontakte',
    'errors' => 'Fehler',
    'search' =>
    array (
      'users' => 'Benutzer suchen',
      'all' => 'Suche',
    ),
    'comment' => 'Bemerkung',
    'interval' => 'Zeitspanne',
    'since' => 'Von',
    'to' => 'bis',
    'method' => 'Verfahren',
    'automatic' => '',
    'related' => 'Importierte Bewegungen',
    'more' => '',
    'send_mail' => 'E-Mail senden',
    'optional_message' => 'Nachricht (Optional)',
    'default_notes' => 'Default Benachrichtigungen',
    'default' => 'Default Benachrichtigungen',
    'suspend' => 'Gesperrt',
    'created_at' => 'Eintragsdatum',
    'updated_at' => 'Ändern',
    'multigas_name' => 'Name der Bestellgemeinschaft',
    'how_to_proceed' => '',
    'create' => 'Neu',
    'targets' => 'Subject',
    'suppliers_and_orders' => 'Lieferanten',
    'mailfield' =>
    array (
      'subject' => 'Subject',
      'body' => 'Text',
    ),
    'reference' => 'Referenz Bestellung',
    'measures' =>
    array (
      'discrete' => 'Diskrete Einheit',
    ),
    'do_filter' => 'Kreditstand filtern',
    'close' => 'Schliessen',
    'save_and_proceed' => 'Speichern und fortfahren',
    'behavior' => 'Bemerkung',
    'uppercare_gas_heading' => 'Solidarische Bestellgemeinschaft (GAS)',
    'confirm' => 'Bestätigung',
    'delete_confirmation' => 'Sind sie sicher, dass sie dieses Element löschen wollen?',
    'current_gas_name' => 'Aktiviert',
    'shared_files' => 'Geteilte Dateien',
    'file' => 'Datei',
    'logs' => 'Anmelden',
    'message' => 'Nachricht',
    'values' => 'Werte',
    'no_image' => 'Kein Schwellenwert',
    'finished_operation' => 'Aktion abgeschlossen.',
    'before' => 'Vorher',
    'after' => 'Nacher',
    'sort_by' => 'Sortiert nach',
    'view_all' => 'Alle',
    'update' => 'Aktualisiere',
    'fast_modify' => 'Schnelle Änderung',
    'download' => 'Herunterladen',
    'split' => 'wovon',
    'start' => 'Beginn',
    'expiration' => 'Ablauf',
    'do_duplicate' => 'duplizieren',
    'action' =>
    array (
      'ignore' => '[Ignorieren]',
      'disable' => 'Deaktiviert',
    ),
    'operation' => 'Import',
    'sum' => '',
    'sub' => 'Import',
    'passive' => 'Passwort',
    'apply' => 'duplizieren',
    'difference' => 'Gewichtsunterschied',
    'theshold' => '',
    'cost' => '',
    'forward' => 'Eingereicht',
    'do_not_modify' => 'Änderungen sind jetzt möglich',
    'named_multigas' => 'Multi-Bestellgemeinschaft :name',
    'categories' => 'Kategorien',
    'no_data' => 'Es gibt keine Daten, die angezeigt werden können',
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
      'new_remote_products_list' => 'Neues Update für die Produktliste :supplier (:date) verfügbar. Abrufbar über Lieferanten > Remote Index.',
      'failed_file' => 'Die Datei wurde nicht korrekt hochgeladen',
      'failure_notice' => 'Import fehlgeschlagen',
      'invalid_command' => 'Ungültiger Befehl :type/:step',
      'currency_id' => '',
      'unique_user_id' => 'Die Benutzer werden über den Nutzernamen oder die E-Mail-Adresse identifiziert (letztere muss eindeutig sein!).',
      'no_user_found' => 'Benutzer nicht gefunden: :name',
      'no_supplier_found' => 'Lieferant nicht gefunden: :name',
      'no_currency_found' => '',
      'imported_movements_notice' => 'Importierte Bewegungen',
      'main' => 'Klicke und ziehe die Attribute aus der rechten Spalte in die mittlere, um jeder Spalte deiner Datei eine Bedeutung bzw. Überschrift zuzuweisen.',
      'remote_index' => '',
    ),
    'ignore_slot' => '[Ignorieren]',
    'name_or_vat' => 'Name oder MwSt.-Nummer',
    'imported_users' => 'Importierte Nutzer',
    'do' => 'Importieren',
    'update_supplier' => 'Vorhandenen Lieferant ändern',
    'select_supplier' => 'Einen Lieferant auswählen',
    'products_count' => 'Die Datei enthält %s Produkte.',
    'index_column' => 'Spalte',
    'column' => 'Spalte',
    'imported_suppliers' => 'Importierte Lieferanten',
    'updated' => 'Aktualisiere',
    'last_read' => 'Letzte Bestellung',
    'error_main' => 'Fehler beim Laden oder Lesen der Datei.',
    'error_retry' => 'Bitte erneut versuchen, oder sich mit den Entwicklern von GASdotto in Verbindung zu setzen: info@madbob.org',
    'existing_products_action' => 'bestellte Produkte',
    'no_products' => 'Kein Produkt aktualisierbar',
  ),
  'invoices' =>
  array (
    'waiting' => 'In der Warteschleife',
    'statuses' =>
    array (
      'to_verify' => 'Zu verifizieren',
      'verified' => 'Kontrolle',
      'payed' => 'Bezahlt',
    ),
    'default_note' => 'Zahlung der Rechnung :name',
    'documents' =>
    array (
      'invoice' =>
      array (
        'heading' => 'Rechnung :identifier',
      ),
      'receipts' =>
      array (
        'list_filename' => '',
      ),
    ),
    'balances' =>
    array (
      'supplier' => 'Betrag Lieferant',
    ),
    'forwarded' => 'Eingereicht',
    'orders' => 'beinhaltete Bestellungen',
    'help' =>
    array (
      'orders' => 'Hier können die Bestellungen ausgewählt werden, die an dieser Rechnung beteiligt sind. Wenn die Rechnung als "bezahlt" markiert ist, wird der Verweis auf die Zahlungsbewegung hinzugefügt und automatisch archiviert',
      'no_orders' => '',
      'filtered_orders' => '',
    ),
    'change_orders' => 'Bestellungen anpassen',
    'verify' => 'Inhalte bestätigen',
    'other_modifiers' => '',
    'payment' => 'Bezahlung verbuchen',
    'get_or_send' => 'Herunterladen oder verschicken',
    'new' => 'Neue Rechnung hochladen',
    'send_pending_receipts' => '',
    'shipping_of' => 'Lieferung: %s',
  ),
  'mail' =>
  array (
    'help' =>
    array (
      'removed_email_log' => '',
      'send_error' => 'Es war nicht möglich, die E-Mail an :email: :message weiterzuleiten',
    ),
    'summary' =>
    array (
      'defaults' =>
      array (
        'subject' => 'GAS-Buchungsübersicht: :supplier – Lieferung :delivery',
      ),
    ),
    'closed' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Automatisch geschlossene Bestellung',
      ),
    ),
    'notification' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Neue Benachrichtigung von :gas',
      ),
    ),
    'new_user_notification' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Neuer Benutzer registriert',
      ),
    ),
    'contacts_prefix' => '',
    'approved' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Willkommen!',
        'body' => 'Willkommen im Bestellsystem von %[gas_name]!
    In Zukunft kannst du dich über folgenden Link mit deinem Nutzernamen „%[username]“ und dem von dir gewählten Passwort anmelden.
    %[gas_login_link]
    Eine Benachrichtigungs-E-Mail wurde an die Administrator*innen geschickt.',
      ),
      'description' => '',
      'username' => 'Benutzername des neuen Nutzers',
      'link' => 'Link zur Anmeldungseite',
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
        'subject' => 'Neue Bestellung eröffnet für %[supplier_name]',
        'body' => 'Eine neue Bestellung wurde eröffnet von der %[gas_name] für den Lieferanten %[supplier_name].
    Um daran teilzunehmen, melde dich über folgenden Link an:
    %[gas_booking_link]
    Die Bestellfrist endet am %[closing_date]',
      ),
      'description' => 'Benachrichtigung über neue offene Bestellungen (versandt an die Nutzer, die ausdrücklich die Benachrichtigungsfunktion bezüglich des Lieferanten in ihrem Profil aktiviert haben).',
      'comment' => 'Kommentar zur Bestellung',
      'link' => 'Link zu den Bestellungen',
      'mails' => 'E-Mail-Adressen der Ansprechpartner für die Bestellung',
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
        'body' => 'Es wurde eine Anfrage zur Aktualisierung deines GASdotto-Passwortes gestellt.
    Klicke unten auf den Link, um das Passwort zu aktualisieren. Falls du das Neusetzen des Passwortes nicht angefragt hast, ignoriere diese E-Mail.
    %[gas_reset_link]',
      ),
      'description' => 'Benachrichtigung zum Neusetzen des Passwortes.',
      'link' => 'Link zur Zurücksetzung des Passwortes',
    ),
    'receipt' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Neue Rechnung von %[gas_name]',
        'body' => 'Im Anhang die letzte Rechnung von %[gas_name]',
      ),
      'description' => 'Begleit-E-Mail für Quittungen.',
    ),
    'supplier' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Bestellung',
        'body' => 'Guten Tag.
    Anbei finden Sie die Auftragsbuchung von %[gas_name] in zweifacher Ausfertigung, PDF und CSV.
    Wenn Sie Fragen haben, wenden Sie sich bitte an die Ansprechpartner in der Kopie dieser E-Mail.
    Danke.',
      ),
      'description' => 'Benachrichtigung der Lieferanten über den automatischen Auftragsabschluss.',
    ),
    'credit' =>
    array (
      'current' => 'Aktueller Kredit',
    ),
    'welcome' =>
    array (
      'description' => 'Benachrichtigung an neue Nutzer, deren Account über das Backend des Bestellsystems hinzugefügt wurde.',
      'link' => 'Link für den erstmaligen Zugang',
      'defaults' =>
      array (
        'body' => 'Sie sind zu %[gas_name] eingeladen worden!

    Für den erstmaligen Zugang klicken Sie auf den unten stehenden Link.
    %[gas_access_link].

    In Zukunft können Sie sich über diesen anderen Link, den Benutzernamen „%[username]“ und das von Ihnen gewählte Passwort anmelden.
    %[gas_login_link]
    ',
      ),
    ),
    'newuser' =>
    array (
      'description_manual' => '',
      'description' => 'Benachrichtigung an Nutzer, die sich neu im Bestellsystem angemeldet haben.',
    ),
  ),
  'modifiers' =>
  array (
    'defaults' =>
    array (
      'discount' => 'Skonto',
      'rounding' => '',
      'delivery' => 'Frachtkosten',
    ),
    'dynamics' =>
    array (
      'values' =>
      array (
        'quantity' => 'die Anzahl',
        'price' => 'der Wert',
        'order_price' => '',
        'weight' => 'das Gewicht',
      ),
      'targets' =>
      array (
        'product' =>
        array (
          'booking' => 'des Produkts in der Vorbestellung',
          'order' => 'des Produkts in der Bestellung',
        ),
        'order' =>
        array (
          'booking' => 'der Vorbestellung',
          'order' => 'der Bestellung',
        ),
        'aggregate' =>
        array (
          'booking' => '',
          'order' => '',
        ),
        'circle' =>
        array (
          'booking' => '',
          'order' => '',
        ),
      ),
      'scale' =>
      array (
        'minor' => 'kleiner ist als',
        'major' => 'größer ist als',
      ),
      'distribution' =>
      array (
        'sum' =>
        array (
          'product' => 'addiere zu den Kosten des Produkts hinzu',
          'booking' => 'addiere zum Gesamtbetrag der einzelnen Vorbestellung hinzu',
          'order' => 'addiere zu den Kosten der Gesamtbestellung hinzu',
          'product_kg' => '',
          'booking_kg' => '',
          'order_kg' => '',
        ),
        'sub' =>
        array (
          'product' => 'ziehe von den Kosten des Produkts ab',
          'booking' => 'ziehe vom Gesamtbetrag der einzelnen Vorbestellung ab',
          'order' => 'ziehe von den Kosten der Gesamtbestellung ab',
          'product_kg' => '',
          'booking_kg' => '',
          'order_kg' => '',
        ),
        'passive' =>
        array (
          'product' => 'verglichen mit den Kosten des Produkts, berechne',
          'booking' => 'verglichen mit dem Gesamtbetrag der Vorbestellung, berechne',
          'order' => 'verglichen mit den Kosten der Gesamtbestellung, berechne',
          'product_kg' => '',
          'booking_kg' => '',
          'order_kg' => '',
        ),
        'apply' =>
        array (
          'product' => 'wende den Stück-/Gebindepreis an',
        ),
      ),
      'types' =>
      array (
        'quantity' => 'und verteile dies entsprechend den vorbestellten Mengen',
        'price' => 'und verteile dies entsprechend dem Wert der Vorbestellungen',
        'weight' => 'und verteile dies entsprechend dem Gewicht der Vorbestellungen',
      ),
      'template' => 'Wenn :value :target :scale',
    ),
    'all' => 'Ändern',
    'name' => 'Ändern',
    'help' =>
    array (
      'no_modifiers_for_element' => '',
    ),
  ),
  'movements' =>
  array (
    'modifier_no_theshold' => 'Kein Schwellenwert',
    'order_value' => '',
    'apply_to_booking' => 'Einzelne Vorbestellung',
    'apply_to_order' => 'Gesamtbestellung',
    'current_balance_amount' => 'Aktueller Kontostand: :amount',
    'balance' => 'Kontostand',
    'current_credit' => 'Aktueller Kontostand',
    'bank_account' => 'Girokonto',
    'cash_account' => 'Kasse',
    'deposits' => 'Kaution',
    'documents' =>
    array (
      'movements' =>
      array (
        'filename' => 'Export Bewegungen der Bestellgruppe :date.:format',
      ),
      'users' =>
      array (
        'filename' => 'Krediten am :date.csv',
        'integralces_filename' => '',
      ),
      'sepa' =>
      array (
        'filename' => 'SEPA am :date.xml',
      ),
      'suppliers' =>
      array (
        'filename' => 'Saldi Lieferanten am %s.csv',
        'integralces_filename' => '',
      ),
      'balances' =>
      array (
        'filename' => '',
      ),
    ),
    'registration_date' => 'Eintragsdatum',
    'execution_date' => 'Bewegungsdatum',
    'paying' => 'Bezahlende',
    'payed' => 'Bezahlt',
    'delete_confirmation' => '',
    'formatted_residual_credit' => 'Restkredit :currency',
    'formatted_balance' => 'Kontostand %s',
    'currency' => 'Währung',
    'credit' => 'Kontostand',
    'defaults' =>
    array (
      'fee' => '',
      'booking' => '',
      'booking_adjust' => '',
      'deposit' => '',
      'deposit_return' => '',
      'donation_from' => '',
      'donation' => '',
      'expense' => '',
      'put' => '',
      'invoice' => '',
      'order' => '',
      'rounding' => '',
      'credit' => '',
      'decredit' => '',
      'refund' => '',
    ),
    'methods' =>
    array (
      'bank' => 'Überweisung',
      'cash' => 'Bargeld',
      'credit' => 'Kontostand Benutzer',
      'sepa' => 'SEPA',
    ),
    'formatted_revenues' => '',
    'formatted_expenses' => '',
    'suppliers_status' => 'Betrag Lieferant',
    'causal' => 'Verwendungszweck',
    'generic_causal' => '',
    'help' =>
    array (
      'removing_balance_warning' => 'Warnung! Vergangene Salden können entfernt werden, aber Vorsicht, der Vorgang ist nicht umkehrbar und es ist nicht mehr möglich, diese Werte in irgendeiner Weise neu zu berechnen!',
      'missing_method_for_movement' => '',
      'accepts_negative_value' => 'Wenn auf \'off\' gestellt, verhindert dies, dass man einen negativen Betrag für die Zahlungsbewegung eingeben kann',
      'fixed_value' => 'Wenn anders als 0, wird es nicht möglich sein, den Wert von neuen Bewegungen dieses Typs zu ändern',
      'paying' => 'Die Art der Einheit, die die Zahlung vornimmt. Wenn diese Option ausgewählt ist, kann die Einheit innerhalb der Eingabemaske für die Erstellung neuer Zahlungsbewegungstypen ausgewählt und spezifiziert werden',
      'payed' => 'Die Art der Einheit, die die Zahlung empfängt. Wenn diese Option ausgewählt ist, kann die Einheit innerhalb der Eingabemaske für die Erstellung neuer Zahlungsbewegungstypen ausgewählt und spezifiziert werden',
      'system_type_notice' => '',
      'empty_list_vat_rates' => 'Es gibt derzeit keine Elemente, die angezeigt werden können.<br/>Die Steuersätze können den diversen Produkten zugeordnet werden; dieses Feld wird benötigt, um automatisch die Mehrwertsteuer für die Rechnungssumme zu kalkulieren und in <strong>\'Buchhaltung -> Rechnungen\'</strong> zu übertragen.',
      'balances_diff' => 'Die folgenden Kontostände wurden nach dem Wiederberechnen geändert.',
      'balances_same' => 'Alle Salden erweisen sich als kohärent.',
      'archiviation_notice' => '',
      'opened_orders_with_modifier' => '',
      'main_types_warning' => 'Achtung! Verändere das Verhalten der Aktionstypen für die Buchhaltung mit großer Vorsicht! Bevor du das Verhalten eines bereits existierenden Aktionstyps oder sogar eines Typs veränderst, dem bereits ein Buchhaltungsvorgang zugeordnet ist, empfielt es sich die Funktion „Kontostände archivieren“ so zu nutzen, dass die vorherigen Buchungsbewegungen nicht neu verarbeitet werden, während das neue Verhalten in Kraft tritt (was Kontostände produzieren würde, die komplett von den aktuellen abweichen).',
      'modifier_not_applied_in_time_range' => '',
      'current_balance' => '',
      'pending_bookings_to_pay' => '',
      'always_active_modifiers' => '',
      'missing_movements_for_modifiers' => '',
      'type_for_modifier' => '',
      'missing_method_for_movements_in_modifiers' => '',
      'missing_method_for_movement_in_modifier' => '',
    ),
    'balances_history' => 'Protokoll Kontostände',
    'current_balance' => 'Aktueller Kontostand',
    'registrar' => 'Registriert von',
    'accepts_negative_value' => 'Akzeptiert negative Werte',
    'fixed_value' => 'Konstanter Wert',
    'debit' => 'Schuld',
    'type' => 'Bewegungstyp',
    'credits_status' => 'Kreditstand',
    'vat_rates' => 'MwSt-Satz',
    'recalculate_balances' => 'Kontostände erneut berechnen',
    'balances_archive' => 'Kontostände archivieren',
    'all' => 'Aktionen',
    'name' => 'Bewegung',
    'amount' => 'Importieren',
    'types' => 'Aktionstypen',
    'invoices' => 'Rechnungen',
    'reference_for_modifier' => '',
    'distribute_on' => 'und verteile dies entsprechend dem Gewicht der Vorbestellungen',
    'to_pay' => 'Zu bezahlen',
    'available_credit' => 'Verfügbares Guthaben',
    'always_active_modifiers' => '',
    'apply_theshold_to' => '',
    'sepa' =>
    array (
      'creditor_identifier' => 'Identifizierung Gläubiger',
      'business_code' => 'Buchungsschlüssel Betrieb',
    ),
  ),
  'notifications' =>
  array (
    'global_filter' =>
    array (
      'roles' => 'Alle Nutzer mit der Rolle :role',
      'orders' => 'Alle Teilnehmer an der Bestellung :supplier :number',
    ),
    'help' =>
    array (
      'repeat_mail_warning' => 'Diese Nachricht wurde schon per E-Mail verschickt. Wenn du speichert und die Markierung bleibt aktiv, dann wird eine neue E-Mail verschickt.',
      'sending_mail_warning' => 'Wenn du diese Option aktivierst, wird die Nachricht sofort per E-Mail weitergeleitet. Wenn du beabsichtigst, sie vor der Weiterleitung zu ändern, nutze diese Option nur nach der Speicherung und Änderung der Nachricht.',
      'visibility_by_selection' => 'Strg/Ctrl Taste gedrückt halten, um mehrere Benutzer auszuwählen. Falls kein Benutzer ausgewählt ist, wird das Element für alle sichtbar.',
      'suspend' => '',
      'arbitrary_dates' => 'Von hier aus ist es möglich, beliebige Termine in den Lieferkalender einzutragen, auch für noch nicht vorhandene, zukünftige Bestellungen. Diese Funktion wird empfohlen, um die Koordination zwischen den verschiedenen Verantwortlichen innerhalb der Bestellgemeinschaft zu erleichtern und die Aktivitäten langfristig zu planen.',
    ),
    'cycle' =>
    array (
      'two_weeks' => 'Alle zwei Wochen',
      'first_of_month' => 'Erster des Monats',
      'second_of_month' => 'Zweiter des Monats',
      'third_of_month' => 'Dritter des Monats',
      'fourth_of_month' => 'Vierter des Monats',
      'last_of_month' => 'Letzter Tag des Monats',
    ),
    'name' => 'Benachrichtigung',
    'notices' =>
    array (
      'new_notification_from' => 'Neue Benachrichtigung von :author',
      'attached_order' => 'Im Anhang die Datei für die Bestellung :gasname.',
    ),
    'recurrence' => 'Wiederholung',
    'greetings' => 'Mit freundlichen Grüßen',
    'send_to_current_users' => 'Benachrichtige angezeigte Benutzer',
    'next_dates' => 'Nächste Daten im Kalender:',
    'next_auto_orders' => 'Nächste Daten im Kalender:',
    'list' =>
    array (
      'closed_orders' => 'Abgeschlossene Bestellungen',
      'confirmed_dates' => 'Bestätigte Termine',
      'temporary_dates' => 'Vorläufige Termine',
      'appointments' => 'Vereinbarte Termine',
    ),
    'calendar_date' => 'Kalenderdatum',
    'date_reference' => '',
  ),
  'orders' =>
  array (
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
  ),
  'permissions' =>
  array (
    'permissions' =>
    array (
      'maintenance_access' => 'Zugänglich auch während der Wartung',
      'alter_permissions' => 'Alle Erlaubnisse ändern',
      'alter_configs' => 'Einstellungen der Bestellgemeinschaft ändern',
      'create_suppliers' => 'Neue Lieferanten erstellen',
      'do_booking' => 'Bestellen',
      'view_suppliers' => 'Alle Lieferanten anzeigen',
      'view_orders' => 'Alle Bestellungen ansehen',
      'alter_self' => 'Die persönlichen Angaben anpassen',
      'delete_account' => '',
      'admin_users' => 'Benutzer verwalten',
      'view_users' => 'Alle Benutzer ansehen',
      'sub_users' => 'Mitbenutzer (Freunde) haben mit eingeschränkten Rechten',
      'admin_user_movements' => 'Buchungsbewegungen der Nutzer verwalten',
      'admin_movements' => 'Alle hauptbucheinträge verwalten',
      'view_movements' => 'Hauptbucheinträge ansehen',
      'admin_movements_types' => 'Typen der Hauptbucheinträgen verwalten',
      'admin_categories' => 'Kategorien verwalten',
      'admin_measures' => 'Maßeinheiten verwalten',
      'view_statistics' => 'Statistiken ansehen',
      'admin_notifications' => 'Benachrichtigungen verwalten',
      'alter_suppliers' => 'Beauftragte Lieferanten ändern',
      'open_orders' => 'Bestellungen öffnen und ändern',
      'do_deliveries' => 'Lieferungen eintragen',
      'admin_invoices' => '',
      'admin_supplier_movements' => '',
      'admin_multigas' => '',
    ),
    'roles' =>
    array (
      'admin' => '',
      'secondary_admin' => '',
    ),
    'name' => 'Berechtigungen',
    'supplier' =>
    array (
      'change' => 'Änderungen des Lieferanten sind jetzt möglich',
      'orders' => 'Sie dürfen neue Bestellungen eröffnen für den Lieferant',
      'deliveries' => 'Lieferungen für den Lieferanten verwalten',
    ),
    'role' => 'Rolle',
    'help' =>
    array (
      'global_permission_notice' => 'Diese spezielle Erlaubnis ist automatisch an alle (aktuellen und zukünftigen) Benutzern zugewiesen und sie erlaubt auf allem zu wirken, obwohl der zugewiesene Benutzer ist nicht für alle Andere sichtbar.',
      'blocked_autoremove' => 'Du kannst den Status als Administrator nicht eigenständig aufheben',
      'unique_role_warning' => '',
      'unprivileged' => 'Benutzername des neuen Nutzers',
      'sub_user' => 'Diese Rolle wird automatisch jedem \'Freund\' bereits bestehender Nutzer zugewiesen. Es wird empfohlen, eine eigene Rolle hierfür zu einzurichten, deren Berechtigungen allein auf das Bestellen begrenzt ist',
      'multigas_admin' => 'Benutzername des neuen Nutzers',
      'admin_not_authorized' => '',
      'parent_role' => '',
      'missing_elements_warning' => '',
    ),
    'revoke' => 'Rolle wiederrufen',
    'change_roles' => 'Rollen verwalten',
    'parent_role' => 'Rolle auf höherem Niveau',
    'add_user' => 'Neuen Benutzer hinzufügen',
    'unprivileged' => 'Rolle Benutzer ohne Sonderrechte',
    'sub_user' => 'Rolle Mitbenutzer (Freunde)',
    'multigas_admin' => 'Kategorien verwalten',
  ),
  'products' =>
  array (
    'prices' =>
    array (
      'unit' => 'Stückpreis',
      'unit_no_vat' => 'Einzelpreis (ohne Mwst.)',
      'package' => 'Preis der Verpackungseinheit',
    ),
    'name' => 'Produkt',
    'code' => 'Artikelnummer Lieferant',
    'bookable' => 'Bestellbar',
    'vat_rate' => 'MwSt-Satz',
    'portion_quantity' => 'Gebindegröße',
    'multiple' => 'Vielfaches',
    'min_quantity' => 'Minimum',
    'max_quantity' => 'Empfohlenes Maximum',
    'available' => 'Verfügbar',
    'help' =>
    array (
      'unit_no_vat' => 'Zu benutzen in Kombination mit dem jeweiligen Mehrwertsteuersatz',
      'package_price' => 'Wenn genauer angegeben, wird der Einheitspreis als Preis für die Verpackungseinheit / Gebindegröße berechnet',
      'importing_categories_and_measures' => 'Nicht aufgelistete Kategorien und Maßeinheiten werden erstellt.',
      'imported_notice' => 'Importierte Produkte',
      'available_explain' => '',
      'bookable' => '',
      'pending_orders_change_price' => '',
      'pending_orders_change_price_second' => '',
      'discrete_measure_selected_notice' => '',
      'measure' => 'Die dem Produkt zugewiesene Maßeinheit. Achtung: dies kann verschiedene Variablen des Produktes beeinflussen, siehe den Parameter „diskrete Einheit“ im Administrationsoberfläche unter „Maßeinheiten verwalten“ (einsehbar nur für Nutzer mit entsprechenden Rechten)',
      'portion_quantity' => 'Wenn Wert > 0 versteht sich jede Einheit in dieser angegebenen Größe. Zum Beispiel:<ul><li>Maßeinheit: Kilogramm</li><li>Stückzahl: 0.3</li><li>Stück/Gebindepreis: 10 Euro</li><li>bestellte Menge: 1 (die also zu verstehen ist als "1 Stück à 0.3 Kilo")</li><li>Kosten: 1 x 0.3 x 10 = 3 Euro</li></ul>Nützlich um Produkte zu verwalten, die in Einzelstücken verteilt werden, welche von den Nutzern vorbestellt werden können, aber beim Lieferant als Gesamtmenge bezahlt werden',
      'package_size' => 'Wenn das Produkt in Abpackungen von n Stücken ausgegeben wird, wird hier der Wert von n angezeigt. Die Bestellungen an den Lieferanten müssen die Anzahl der Verpackungen enthalten, d.h. die Anzahl der bestellten Stücke / die Anzahl der Stücke in der Verpackung. Wenn die Gesamtmenge der bestellten Stücke nicht ein Vielfaches dieser Anzahl ist, ist das Produkt in der Tabelle, die die Zusammenfassung der bestellten Produkte enthält, mit einem roten icon gekennzeichnet. Aus dieser Tabelle lassen sich die Mengen systematisieren, indem - wo erforderlich - hinzugefügt und weggenommen wird.',
      'multiple' => 'Wenn ungleich null, ist das Produkt nur vorbestellbar durch die Vervielfachung dieses Wertes. Nützlich für Produkte, die vorverpackt sind, aber individuell vorbestellt werden. Nicht zu verwechseln mit dem Attribut \'Verpackung\'',
      'min_quantity' => 'Wenn ungleich null, ist das Produkt nur bestellbar für eine Anzahl, die größer ist als die hier angezeigte',
      'max_quantity' => 'Wenn ungleich null, wird eine Warnung angezeigt, wenn eine höhere Anzahl als die hier Angegebene bestellt wird',
      'available' => 'Wenn ungleich null, ist dieses die maximale Anzahl der Produkte, die insgesamt in einer Bestellung vorgemerkt werden können. In der Bestellphase sehen die Nutzer, was bisher bestellt wurde',
      'global_min' => 'Wenn ungleich null, ist dieses die maximale Anzahl der Produkte, die insgesamt in einer Bestellung vorgemerkt werden können. In der Bestellphase sehen die Nutzer, was bisher bestellt wurde',
      'variants' => 'Jedes Produkt kann mehrere Varianten haben, z.B. in der Größe oder Farbe der Wäscheteile. In der Phase der Bestellung können die Nutzer die gewünschte Anzahl für jede Kombination der Varianten angeben. Die Varianten können darüberhinaus einen eigenen Preis haben, der abhängig vom Gebindepreis des Produktes angegeben wird (z.B. +1 Euro oder -0.80 Euro)',
      'duplicate_notice' => 'Mit dem Duplizieren eines Produkts werden auch die Varianten des Originalprodukts kopiert bzw. dupliziert. Nach dem Speichern des Duplikats können die Varianten verändert werden.',
      'unit_price' => '',
      'vat_rate' => '',
      'notice_removing_product_in_orders' => '',
    ),
    'weight_with_measure' => '',
    'list' => 'Produkte',
    'sorting' => '',
    'variant' =>
    array (
      'matrix' => 'Variante hinzufügen/verändern',
      'help' =>
      array (
        'code' => '',
        'price_difference' => '',
      ),
      'price_difference' => 'Preisdifferenz',
      'weight_difference' => 'Gewichtsunterschied',
    ),
    'package_size' => 'Verpackung',
    'global_min' => 'Gesamtbestellung',
    'variants' => 'Veränderbar',
    'remove_confirm' => '',
    'removing' =>
    array (
      'keep' => '',
      'leave' => '',
    ),
  ),
  'supplier' =>
  array (
    'referent' => 'Ansprechperson',
    'payment_method' => 'Bezahlmodus',
    'all' => 'Lieferanten',
    'products_list' =>
    array (
      'pdf' => 'Preisliste PDF (automatisch erstellt)',
      'csv' => 'Preisliste CSV (automatisch erstellt)',
    ),
    'attachments' => 'Dateien und Bilder',
    'remote_index' => '',
    'vat' => 'USt.-IDNr',
    'enable_fast_shipping' => 'Schnelle Lieferungen',
    'help' =>
    array (
      'enable_fast_shipping' => 'Wenn diese Option aktiviert ist, wird in der Übersicht zur Verwaltung der jeweiligen Bestellungen der Tab \'Schnelle Lieferung\' aktiviert (neben dem Tab \'Lieferungen\'). Dieser erlaubt es, mehrere Vorbestellungen auf einmal als \'geliefert\' zu kennzeichnen',
      'enable_no_quantities' => '',
      'modifiers_notice' => '',
      'import_products_notice' => '',
      'handling_products' => 'Achtung: es gibt Bestellungen für diesen Lieferanten, die noch nicht geliefert und archiviert wurden; eventuelle Veränderungen bezüglich der Produkte werden auch auf diese Bestellungen angewandt.',
      'name' => 'Name des Lieferanten',
      'legal_name' => 'Vollständiger Name des Lieferanten, der für Buchhaltungs- und Steuerzwecke verwendet wird. Wenn nichts angegeben ist, wird der obenstehende Name verwendet',
      'description' => 'Alle Benutzer ansehen',
      'payment_method' => 'Möglichkeit, eine Notiz über die Art der Bezahlung des Lieferanten zu hinterlassen. Sichtbar nur für Referenten (Ansprechpartner beim Lieferanten)',
      'orders_mode' => 'Möglichkeit, einen Hinweis darauf zu hinterlassen, wie Bestellungen an den Lieferanten hinterlegt werden. Nur sichtbar für Referenten (Ansprechpartner beim Lieferanten)',
      'send_notification_on_close' => '',
    ),
    'enable_no_quantities' => 'Schnelle Lieferung zulassen',
    'export_products' => 'Exportieren',
    'referents' => 'Ansprechperson',
    'products_list_heading' => 'Preisliste :supplier der :date',
    'admin_categories' => 'Kategorien verwalten',
    'admin_measures' => 'Maßeinheiten verwalten',
    'legal_name' => 'Firmenname',
    'orders_mode' => 'Fortschritt der Bestellungen',
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
      'title' => '',
      'body' => '',
    ),
    'bookings' =>
    array (
      'title' => '',
      'body' => '',
    ),
    'accounting' =>
    array (
      'title' => '',
      'body' => '',
    ),
    'config' =>
    array (
      'title' => 'Einstellungen',
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
      'suspended' => 'Suspendierte oder gesperrte Benutzer können nicht auf das Bestellsystem zugreifen, auch wenn sie registriert bleiben.',
      'wrong_control_error' => 'Die Prüfziffern sind falsch',
      'existing_email_error' => 'Die E-Mail-Adresse ist bereits registriert.',
      'duplicated_name_error' => '',
      'waiting_approval' => '',
      'promote_friend' => '',
      'promote_friend_enforce_mail' => '',
      'reassign_friend' => '',
      'notifications_instructions' => 'Wähle die Lieferanten aus für die du eine Benachrichtigung erhalten möchtest bei Eröffnung einer neuen Bestellung.',
      'fee' => 'Daten im Zusammenhang des Jahresbeitrags des Nutzers. Zur Deaktivierung dieser Option gehe zu Einstellungen -> Buchhaltung',
      'deposit' => 'Daten zur Einlage, die der Nutzer im Moment des Eintritts in die Bestellgemeinschaft bezahlt hat. Zur Deaktivierung dieser Option gehe zu Einstellungen -> Buchhaltung',
      'satispay' => 'Von hier kannst du deinen Kontostand mit Satispay aufladen. Sag wie viel du überweisen willst und ev. Noten für die Verwalter; dir wird eine Bestätigung auf dem Smartphone, innerhalb 15 min. geschickt.',
      'remove_profile' => '',
      'remove_profile_credit_notice' => '',
    ),
    'firstname' => 'Vorname',
    'lastname' => 'Nachname',
    'change_friend' => '',
    'formatted_aggregation' => '',
    'sepa' =>
    array (
      'mandate' => '',
      'date' => 'SEPA-Bewegung Datum',
      'intro' => 'SEPA Einstellung',
      'help' => 'Geben Sie hier die Parameter für die Generierung von RIDs für diesen Benutzer an. Für Benutzer, für die diese Felder nicht ausgefüllt wurden, ist es nicht möglich, RIDs zu generieren',
      'identifier' => 'SEPA-Bewegung ID',
    ),
    'last_login' => 'Letzter Zugang',
    'last_booking' => 'Letzte Bestellung',
    'member_since' => 'Mitglied seit',
    'birthplace' => '',
    'birthdate' => 'Geburtsdatum',
    'other_bookings' => '',
    'fullname' => 'Vollständiger Name',
    'taxcode' => 'Steuernummer',
    'card_number' => 'Mitgliedsnummer',
    'payment_method' => 'Bezahlmodus',
    'all' => 'Benutzer',
    'payed_fee' => 'Bezahlter Betrag',
    'name' => 'Benutzer',
    'address_part' =>
    array (
      'street' => 'Adresse (Straße)',
      'city' => 'Adresse (Stadt)',
      'zip' => 'Adresse (PLZ)',
    ),
    'statuses' =>
    array (
      'active' => 'Aktiviert',
      'suspended' => 'Gesperrt',
      'deleted' => '',
      'removed' => '',
    ),
    'friend' => 'Freund',
    'removed_user' => '',
    'booking_friend_header' => 'Hat bestellt :amount',
    'pending_deliveries' => '',
    'without_aggregation' => '',
    'aggregation' => '',
    'credit_below_zero' => 'Kredit < 0',
    'fee_not_payed' => 'Beitrag nicht bezahlt',
    'personal_data' => 'Profil',
    'approve' => '',
    'do_not_approve' => '',
    'family_members' => 'Familienmitglieder',
    'promote_friend' => 'Neuer Benutzer registriert',
    'reassign_friend' => 'Meine Vorbestellung',
    'change_friend_assignee' => '',
    'fee' => 'Beitrag',
    'deposit' => 'Depot',
    'fees_status' => 'Status',
    'all_ceased' => 'Beendet',
    'notices' =>
    array (
      'new_user' => 'Neuer Benutzer registriert :gasname:',
      'pending_approval' => '',
    ),
    'last_fee' => '',
    'fees' =>
    array (
      'new' => 'Anzahl ändern',
      'change' => 'Anzahl ändern',
    ),
    'empty' =>
    array (
      'friends' => 'Füge die Informationen bezüglich deiner Freunde hinzu, für die du eine Unter-Bestellung anlegen willst. Jede einzelne Bestellung ist dann autonom, wird aber behandelt als eine in der Phase der Lieferung. Jeder Freund kann auch eigene Zugangsdaten haben um sich bei GASdotto anzumelden und seine eigenen Bestellungen zu verwalten.',
    ),
    'satispay' =>
    array (
      'reload' => 'Kreditaufladung mit Satispay',
    ),
    'remove_profile' => '',
    'assign_aggregations' => '',
  ),
);
