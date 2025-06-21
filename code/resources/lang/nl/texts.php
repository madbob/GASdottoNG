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
    'accept_privacy' => 'Ik heb de <a href="%s" target="_blank">Privacyverklaring</a> gelezen en geaccepteerd.',
    'username' => 'Username',
    'help' =>
    array (
      'missing_user_or_mail' => 'Gebruikersnaam of e-mailadres niet gevonden',
      'missing_email' => 'De aangegeven gebruiker heeft geen geldig e-mailadres',
      'reset_email_notice' => '',
      'username_same_password' => '',
      'suspended_account_notice' => '',
      'invalid_username' => '',
      'required_new_password' => 'Om door te gaan moet je een nieuw wachtwoord voor jouw profiel instellen.',
      'unconfirmed' => '',
      'username' => '',
      'email_mode' => '',
    ),
    'reset_username' => 'Gebruikersnaam of e-mailadres',
    'password' => 'Wachtwoord',
    'password_request_link' => 'Wachtwoordherstel',
    'maintenance_notice' => 'Onderhoudsmodus: tijdelijke toegang tot uitsluitend beheerders beperkt',
    'login' => 'Aanmelden',
    'remember' => 'Onthoud mij',
    'register' => 'Registreer je',
    'confirm_password' => 'Wachtwoord bevestigen',
    'update_password' => 'Wachtwoord bevestigen',
    'modes' =>
    array (
      'email' => 'Stuur e-mail',
    ),
  ),
  'commons' =>
  array (
    'accept_conditions' => '',
    'warning' => 'Selectie',
    'loading' => '',
    'feedback' => '',
    'about' =>
    array (
      'opensource' => '',
      'contribute' => '',
      'donate' => '',
      'link' => '',
      'local_contact' => 'Opgelet: voor problemen met de inhoud van deze site (leveranciers, bestellingen, reserveringen....) verwijzen wij u naar de administraties van uw GAS. De gegevens die via dit paneel worden verzonden zijn openbaar: voer geen persoonlijke informatie en contacten in!',
      'translations' => 'Als je wilt bijdragen aan de vertaling in jouw taal, bezoek dan <a href="https://hosted.weblate.org/projects/gasdottong/native/">deze pagina</a>.',
    ),
  ),
  'export' =>
  array (
    'help' =>
    array (
      'mandatory_column_error' => 'Verplichte kolom niet opgegeven',
      'importing' =>
      array (
        'deliveries' =>
        array (
          'first_product' => '',
        ),
        'user' =>
        array (
          'aggregation' => '',
          'deleted' => '',
          'balance' => '',
          'instruction' => '',
        ),
      ),
      'csv_instructions' => 'Alleen CSV-bestanden zijn toegestaan. Het is aan te raden om uw tabel homogeen te formatteren, zonder gebruik te maken van samengevoegde cellen, lege cellen, headers: elke rij moet alle informatie met betrekking tot het onderwerp bevatten. Alle prijzen en bedragen moeten worden uitgedrukt zonder vermelding van het euroteken.',
      'selection_instructions' => 'Zodra het bestand is geladen, kunt u aangeven welk attribuut elke kolom in het document vertegenwoordigt.',
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
    'products_list_filename' => 'Prijslijst :supplier.:format',
    'import' =>
    array (
      'csv' => 'CSV importeren',
      'gdxp' => 'GDXP importeren',
    ),
    'help_csv_libreoffice' => 'Voor het raadplegen en uitwerken van bestanden in CSV-formaat (<i>Comma-Separated Values</i>) wordt het gebruik aanbevolen van <a target="_blank" href="http://it.libreoffice.org/">LibreOffice</a>.',
    'data' =>
    array (
      'columns' => 'Kolommen',
      'format' => 'Formaat',
      'formats' =>
      array (
        'pdf' => '',
        'csv' => '',
        'gdxp' => 'GDXP',
      ),
      'status' => 'Status reserveringen',
      'users' => 'Gebruikers',
      'products' => 'Naam product',
      'split_friends' => '',
    ),
    'export' =>
    array (
      'database' => 'Exporteren',
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
      'empty_list_shared_files' => 'Er zijn geen items om te bekijken.<br/>De hier toegevoegde bestanden zijn toegankelijk voor alle gebruikers vanaf het dashboard: nuttig om documenten van gemeenschappelijk belang te delen.',
      'enable_deliveries_no_quantities' => '',
      'active_columns_summary' => '',
      'default_columns_shipping_document' => '',
      'custom_emails' => '',
      'global_placeholders' => '',
      'manual_products_sorting' => '',
      'social_year' => '',
      'fee' => '',
      'deposit' => '',
      'automatic_fees' => '',
      'enable_sepa' => 'Het invullen van deze velden activeert de export van SEPA-bestanden, waarmee banktransacties geautomatiseerd kunnen worden.<br>De bestanden worden gegenereerd door <strong>Boekhouding -> Kredietenstatus -> RID exporteren</strong><br><br>Na het invullen van dit formulier, moet u voor elke gebruiker een aantal parameters opgeven.',
      'enable_satispay' => 'Door deze velden in te vullen, worden de betalingen met PayPal geactiveerd, waarmee gebruikers zelfstandig hun tegoed direct vanuit GASdotto kunnen opladen. Om de toegangsgegevens te verkrijgen, <a href="https://developer.paypal.com/developer/applications/">moet u deze pagina bezoeken</a>.',
      'enabled_satispay' => '',
      'satispay_activation_code' => '',
      'enable_integralces' => '',
      'enable_invoicing' => '',
      'invoices_counter' => 'Bewerk deze parameter met de nodige voorzichtigheid!',
      'enable_hub' => '',
      'import' => '',
      'gdxp_explain' => 'GDXP is een interoperabel formaat voor het uitwisselen van prijslijsten en bestellingen tussen verschillende beheersystemen. Vanaf hier kunt u een bestand in dit formaat importeren.',
      'multigas_mode' => '',
      'only_bookings_with_credit_limit' => '',
      'fast_product_change_columns' => '',
    ),
    'attribute_name' => '',
    'logo' => 'Homepage-logo',
    'home_message' => 'Bericht Homepage',
    'language' => 'Taal',
    'maintenance_mode' => 'Onderhoudsmodus',
    'enable_public_registration' => 'Publieke registratie activeren',
    'manual_approve_users' => '',
    'privacy_policy_link' => 'Link privacybeleid',
    'terms_link' => '',
    'mandatory_fields' => 'Verplichte velden',
    'orders_and_deliveries' => 'Bestellingen en leveringen',
    'only_bookings_with_credit' => '',
    'enable_deliveries_no_quantities' => '',
    'display_contacts' => '',
    'active_columns_summary' => 'Kolommen samenvatting bestellingen',
    'default_columns_shipping_document' => 'Leveringsdetails',
    'suppliers_and_products' => 'Geïmporteerde leveranciers',
    'manual_products_sorting' => '',
    'fast_product_change_columns' => 'Snelle wijziging',
    'social_year' => 'Begin sociaal jaar',
    'automatic_fees' => '',
    'enable_sepa' => 'SEPA activeren',
    'enable_satispay' => 'Satispay activeren',
    'satispay' =>
    array (
      'activation_code' => 'Unieke bedrijfscode',
    ),
    'enable_integralces' => 'IntegralCES',
    'integralces_identifier' => 'ID van SEPA-opdracht',
    'enable_invoicing' => 'Uitgifte facturen activeren',
    'invoices_counter' => 'Totaal factuur',
    'import_export' => 'Importeren',
    'enable_hub' => '',
    'csv_separator' => '',
    'import' => 'Importeren',
    'multigas_mode' => '',
    'only_bookings_with_credit_limit' => '',
  ),
  'generic' =>
  array (
    'save' => 'Opslaan',
    'create_format' => 'Nieuwe :type',
    'empty_list' => 'Er zijn geen elementen om weer te geven.',
    'add_new' => 'Nieuwe toevoegen',
    'type' => 'Type',
    'none' => 'Niemand',
    'manual_selection' => '',
    'named_all' => '',
    'email' => 'E-mail',
    'phone' => 'Telefoonnummer',
    'absolute' => '',
    'percentage' => '',
    'by_weight' => '',
    'quantity' => 'Hoeveelheid',
    'value' => 'Waarde',
    'weight' => '',
    'remove' => 'Verwijderen',
    'export' => 'Exporteren',
    'undefined' => '',
    'updated_at_formatted' => '',
    'address' => 'Adres',
    'email_no_notifications' => '',
    'cellphone' => 'Mobiel',
    'fax' => 'Fax',
    'website' => 'Website',
    'confirmed' => 'Bevestigd',
    'temporary' => 'Voorlopig',
    'measure' => 'Meeteenheid',
    'category' => 'Categorie',
    'price' => 'Prijs',
    'yes' => '',
    'no' => 'Nee',
    'iban' => 'IBAN',
    'gas' => '',
    'status' => 'Status',
    'unspecified' => 'Niet opgegeven',
    'never' => 'Nooit',
    'help' =>
    array (
      'save_reminder' => '',
      'preferred_date_format' => 'Bij voorkeur in JJJJ-MM-DD formaat (bijv. :now)',
      'contacts' => '',
      'unchange_password' => 'Leeg laten om het wachtwoord niet te wijzigen',
      'multigas_admin_instructions' => '',
      'discrete_measure' => 'Discrete eenheden zijn niet deelbaar: op producten waaraan een meeteenheid met dit attribuut is toegewezen, is het niet mogelijk om eigenschappen zoals "Variabele prijs" en "Grootte" te activeren',
      'categories_instructions' => 'Klik en sleep de categorieën naar de lijst om ze hiërarchisch te sorteren.',
      'insert_password_notice' => 'Om deze transactie te bevestigen moet u uw gebruikerswachtwoord invoeren',
      'unassigned_group_warning' => '',
    ),
    'definitive_delete' => 'Definitief verwijderen',
    'all' => 'Alle',
    'unauthorized' => 'Niet geautoriseerd',
    'error' => 'Fout',
    'date' => 'Datum',
    'number' => 'Nummer',
    'taxable_amount' => 'Bedrag',
    'vat' => 'BTW',
    'payment' => 'Betaling',
    'identifier' => 'Identificatie',
    'notes' => 'Opmerkingen',
    'id' => 'ID',
    'closing_date' => 'Sluitingsdatum',
    'stats' =>
    array (
      'involved_orders' => 'Waarde bestellingen',
      'involved_users' => 'Betrokken gebruikers',
      'generic' => 'Algemene statistieken',
      'supplier' => 'Statistieken per leverancier',
    ),
    'description' => 'Beschrijving',
    'invoice' => 'Factuur',
    'no_value' => '',
    'by_kg' => '',
    'selection' => 'Selectie',
    'home' => 'Home',
    'menu' =>
    array (
      'bookings' => 'Reserveringen',
      'accounting' => 'Boekhouding',
      'stats' => 'Statistieken',
      'notifications' => 'Meldingen',
      'configs' => 'Configuraties',
      'multigas' => 'Multi-GAS',
      'friends' => 'Vrienden',
      'receipts' => 'Ontvangen',
    ),
    'image' => 'Afbeelding',
    'limited_access' => '',
    'disabled' => 'Gedeactiveerd',
    'kilos' => '',
    'sortings' =>
    array (
      'all_by_user' => 'Alle (gesorteerd op gebruiker)',
      'manual' => '',
      'all_by_group' => '',
    ),
    'minor_than' => '',
    'major_than' => 'Bericht Homepage',
    'exports' =>
    array (
      'csv' => 'Exporteren CSV',
      'integralces' => 'IntegralCES',
      'sepa' => 'SEPA exporteren',
      'pdf' => 'PDF exporteren',
    ),
    'change' => 'Wijzigen',
    'details' => 'Details',
    'photo' => 'Foto',
    'composable_all' => 'Alle',
    'attachments' =>
    array (
      'replace_file' => 'Bestand vervangen',
      'view' => 'De statistieken weergeven',
      'replace_url' => '',
    ),
    'recipients' => 'Ontvangers',
    'click_here' => 'Klik Hier',
    'attachment' => 'Samengevoegd',
    'contacts' => 'Contactgegevens',
    'errors' => 'Fouten',
    'search' =>
    array (
      'users' => 'Gebruiker aanmaken',
      'all' => 'Zoeken',
    ),
    'comment' => 'Opmerking',
    'interval' => 'IntegralCES',
    'since' => 'Van',
    'to' => 'tot',
    'method' => 'Methode',
    'automatic' => '',
    'related' => 'Geïmporteerde verrichtingen',
    'more' => '',
    'send_mail' => 'Stuur e-mail',
    'optional_message' => 'Bericht (facultatief)',
    'default_notes' => 'Standaard opmerkingen',
    'default' => 'Standaard opmerkingen',
    'suspend' => 'Opgeschort',
    'created_at' => 'Registratiedatum',
    'updated_at' => 'Wijzigen',
    'multigas_name' => '',
    'how_to_proceed' => '',
    'create' => 'Nieuwe aanmaken',
    'targets' => 'Onderwerp',
    'suppliers_and_orders' => 'Leveranciers',
    'mailfield' =>
    array (
      'subject' => 'Onderwerp',
      'body' => 'Tekst van de e-mail',
    ),
    'reference' => 'Referentie',
    'measures' =>
    array (
      'discrete' => 'Discrete eenheid',
    ),
    'do_filter' => 'Tegoed filteren',
    'close' => 'Sluiten',
    'save_and_proceed' => 'Opslaan en doorgaan',
    'behavior' => 'Opmerking',
    'uppercare_gas_heading' => 'SOLIDAIRE AANKOOPGROEP',
    'confirm' => 'Bevestigen',
    'delete_confirmation' => 'Weet je zeker dat je dit element wilt verwijderen?',
    'current_gas_name' => 'Actief',
    'shared_files' => 'Gedeelde bestanden',
    'file' => 'Bestand',
    'logs' => 'Aanmelden',
    'message' => 'Bericht Homepage',
    'values' => 'Waarden',
    'no_image' => 'Afbeelding',
    'finished_operation' => 'Transactie beëindigd.',
    'before' => 'Voor',
    'after' => 'Na',
    'sort_by' => 'Sorteren op',
    'view_all' => 'Alle',
    'update' => '',
    'fast_modify' => 'Snelle wijziging',
    'download' => 'Downloaden',
    'split' => 'waarvan',
    'start' => 'Begin',
    'expiration' => 'Termijn',
    'do_duplicate' => 'Dupliceren',
    'action' =>
    array (
      'ignore' => '[Negeren]',
      'disable' => 'Gedeactiveerd',
    ),
    'operation' => 'Transactie bevestigen',
    'sum' => '',
    'sub' => 'Borgsom',
    'passive' => 'Wachtwoord',
    'apply' => 'Dupliceren',
    'difference' => 'Prijsverschil',
    'theshold' => '',
    'cost' => '',
    'forward' => 'Doorgezonden',
    'do_not_modify' => 'U kunt wijzigen',
    'named_multigas' => 'Multi-GAS :name',
    'categories' => 'Categorieën',
    'no_data' => 'Er zijn geen gegevens weer te geven',
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
      'new_remote_products_list' => '',
      'failed_file' => 'Bestand niet juist geladen',
      'failure_notice' => '',
      'invalid_command' => 'Ongeldige :type/:step opdracht',
      'currency_id' => '',
      'unique_user_id' => 'Gebruikers worden geïdentificeerd door middel van een gebruikersnaam of e-mailadres (dat uniek moet zijn!).',
      'no_user_found' => 'Gebruiker niet gevonden: :name',
      'no_supplier_found' => '',
      'no_currency_found' => '',
      'imported_movements_notice' => 'Geïmporteerde verrichtingen',
      'main' => 'Klik en sleep de attributen van de rechterkolom naar de middelste kolom, om aan elke kolom van uw bestand een betekenis toe te kennen.',
      'remote_index' => '',
    ),
    'ignore_slot' => '[Negeren]',
    'name_or_vat' => '',
    'imported_users' => 'Geïmporteerde gebruikers',
    'do' => 'Importeren',
    'update_supplier' => 'Bestaande leverancier bijwerken',
    'select_supplier' => 'Een leverancier selecteren',
    'products_count' => 'In het bestand zijn er %s producten.',
    'index_column' => 'Kolom',
    'column' => 'Kolom',
    'imported_suppliers' => 'Geïmporteerde leveranciers',
    'updated' => '',
    'last_read' => '',
    'error_main' => 'Fout in het laden of het lezen van het bestand.',
    'error_retry' => 'Probeer het opnieuw of leg het probleem voor aan de ontwikkelaars van GASdotto: info@madbob.org',
    'existing_products_action' => 'Geïmporteerde producten',
    'no_products' => '',
  ),
  'invoices' =>
  array (
    'waiting' => 'In de wacht',
    'statuses' =>
    array (
      'to_verify' => 'Te controleren',
      'verified' => 'Gecontroleerd',
      'payed' => 'Betaald',
    ),
    'default_note' => 'Betaling factuur :name',
    'documents' =>
    array (
      'invoice' =>
      array (
        'heading' => 'Factuur :identifier',
      ),
      'receipts' =>
      array (
        'list_filename' => '',
      ),
    ),
    'balances' =>
    array (
      'supplier' => 'Saldo leverancier',
    ),
    'forwarded' => 'Doorgezonden',
    'orders' => 'Betrokken bestellingen',
    'help' =>
    array (
      'orders' => '',
      'no_orders' => '',
      'filtered_orders' => '',
    ),
    'change_orders' => 'Bestellingen wijzigen',
    'verify' => 'Inhoud controleren',
    'other_modifiers' => '',
    'payment' => 'Betaling registreren',
    'get_or_send' => 'Downloaden of Doorsturen',
    'new' => 'Nieuwe factuur laden',
    'send_pending_receipts' => '',
    'shipping_of' => 'Levering: %s',
  ),
  'mail' =>
  array (
    'help' =>
    array (
      'removed_email_log' => '',
      'send_error' => '',
    ),
    'summary' =>
    array (
      'defaults' =>
      array (
        'subject' => '',
      ),
    ),
    'closed' =>
    array (
      'defaults' =>
      array (
        'subject' => '',
      ),
    ),
    'notification' =>
    array (
      'defaults' =>
      array (
        'subject' => '',
      ),
    ),
    'new_user_notification' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Nieuwe gebruiker geregistreerd',
      ),
    ),
    'contacts_prefix' => '',
    'approved' =>
    array (
      'defaults' =>
      array (
        'subject' => 'Welkom!',
        'body' => '',
      ),
      'description' => '',
      'username' => '',
      'link' => '',
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
        'subject' => '',
        'body' => '',
      ),
      'description' => '',
      'comment' => '',
      'link' => '',
      'mails' => '',
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
        'body' => '',
      ),
      'description' => '',
      'link' => '',
    ),
    'receipt' =>
    array (
      'defaults' =>
      array (
        'subject' => '',
        'body' => '',
      ),
      'description' => 'Begeleidende e-mail voor ontvangstbewijzen.',
    ),
    'supplier' =>
    array (
      'defaults' =>
      array (
        'subject' => '',
        'body' => '',
      ),
      'description' => '',
    ),
    'credit' =>
    array (
      'current' => '',
    ),
    'welcome' =>
    array (
      'description' => '',
      'link' => '',
      'defaults' =>
      array (
        'body' => '',
      ),
    ),
    'newuser' =>
    array (
      'description_manual' => '',
      'description' => '',
    ),
  ),
  'modifiers' =>
  array (
    'defaults' =>
    array (
      'discount' => 'Korting',
      'rounding' => '',
      'delivery' => 'Transportkosten',
    ),
    'dynamics' =>
    array (
      'values' =>
      array (
        'quantity' => '',
        'price' => '',
        'order_price' => '',
        'weight' => '',
      ),
      'targets' =>
      array (
        'product' =>
        array (
          'booking' => '',
          'order' => '',
        ),
        'order' =>
        array (
          'booking' => '',
          'order' => '',
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
        'minor' => '',
        'major' => '',
      ),
      'distribution' =>
      array (
        'sum' =>
        array (
          'product' => '',
          'booking' => '',
          'order' => '',
          'product_kg' => '',
          'booking_kg' => '',
          'order_kg' => '',
        ),
        'sub' =>
        array (
          'product' => '',
          'booking' => '',
          'order' => '',
          'product_kg' => '',
          'booking_kg' => '',
          'order_kg' => '',
        ),
        'passive' =>
        array (
          'product' => '',
          'booking' => '',
          'order' => '',
          'product_kg' => '',
          'booking_kg' => '',
          'order_kg' => '',
        ),
        'apply' =>
        array (
          'product' => '',
        ),
      ),
      'types' =>
      array (
        'quantity' => '',
        'price' => '',
        'weight' => '',
      ),
      'template' => '',
    ),
    'all' => 'Wijzigen',
    'name' => 'Wijzigen',
    'help' =>
    array (
      'no_modifiers_for_element' => '',
    ),
  ),
  'movements' =>
  array (
    'modifier_no_theshold' => '',
    'order_value' => '',
    'apply_to_booking' => '',
    'apply_to_order' => '',
    'current_balance_amount' => '',
    'balance' => 'Saldo',
    'current_credit' => 'Huidige tegoed',
    'bank_account' => 'Rekening-courant',
    'cash_account' => 'Kas contanten',
    'deposits' => 'Borgsommen',
    'documents' =>
    array (
      'movements' =>
      array (
        'filename' => 'Export transacties GAS :date.:format',
      ),
      'users' =>
      array (
        'filename' => 'Tegoeden tot :date.csv',
        'integralces_filename' => '',
      ),
      'sepa' =>
      array (
        'filename' => 'SEPA van :date.xml',
      ),
      'suppliers' =>
      array (
        'filename' => '',
        'integralces_filename' => '',
      ),
      'balances' =>
      array (
        'filename' => '',
      ),
    ),
    'registration_date' => 'Registratiedatum',
    'execution_date' => 'Transactiedatum',
    'paying' => 'Betaler',
    'payed' => 'Betaald',
    'delete_confirmation' => '',
    'formatted_residual_credit' => 'Resterend tegoed :currency',
    'formatted_balance' => 'Saldo %s',
    'currency' => 'Beoordelen',
    'credit' => 'Krediet',
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
      'bank' => 'Overboeking',
      'cash' => 'Contanten',
      'credit' => 'Gebruikerskrediet',
      'sepa' => '',
    ),
    'formatted_revenues' => '',
    'formatted_expenses' => '',
    'suppliers_status' => 'Saldo leverancier',
    'causal' => 'Betalingskenmerk',
    'generic_causal' => '',
    'help' =>
    array (
      'removing_balance_warning' => '',
      'missing_method_for_movement' => '',
      'accepts_negative_value' => '',
      'fixed_value' => '',
      'paying' => '',
      'payed' => '',
      'system_type_notice' => '',
      'empty_list_vat_rates' => 'Er zijn geen items om te bekijken.<br/>De tarieven kunnen worden toegewezen aan verschillende producten in de leverancierslijsten en worden gebruikt om automatisch de BTW te scheiden van de totalen van de facturen die zijn geladen in  <strong>Boekhouding -> Facturen</strong>.',
      'balances_diff' => 'De volgende saldo\'s zijn anders gebleken na herberekening.',
      'balances_same' => 'Alla saldo\'s blijken coherent.',
      'archiviation_notice' => '',
      'opened_orders_with_modifier' => '',
      'main_types_warning' => 'Opgelet! Verander het gedrag van de soorten boekhoudtransacties met grote voorzichtigheid! Alvorens het gedrag van een bestaand type te veranderen, en dat misschien al toegewezen is aan een aantal geregistreerde boekhoudtransacties, is het aan te raden om de functie "Saldo\'s archiveren" te gebruiken zodat de eerder geboekte transacties niet worden herwerkt met het nieuwe gedrag (waardoor saldo\'s worden geproduceerd die volledig van de huidige saldo\'s verschillen).',
      'modifier_not_applied_in_time_range' => '',
      'current_balance' => '',
      'pending_bookings_to_pay' => '',
      'always_active_modifiers' => '',
      'missing_movements_for_modifiers' => '',
      'type_for_modifier' => '',
      'missing_method_for_movements_in_modifiers' => '',
      'missing_method_for_movement_in_modifier' => '',
    ),
    'balances_history' => 'Saldo-historiek',
    'current_balance' => 'Huidige saldo',
    'registrar' => 'Geregistreerd door',
    'accepts_negative_value' => 'Negatieve waarden accepteren',
    'fixed_value' => 'Vaste waarde',
    'debit' => 'Verschuldigd',
    'type' => 'Type transactie',
    'credits_status' => 'Status tegoeden',
    'vat_rates' => 'BTW-tarieven',
    'recalculate_balances' => 'Saldo\'s herberekenen',
    'balances_archive' => 'Saldo\'s archiveren',
    'all' => 'Transacties',
    'name' => 'Transactie',
    'amount' => 'Importeren',
    'types' => 'Types transacties',
    'invoices' => 'Facturen',
    'reference_for_modifier' => '',
    'distribute_on' => '',
    'to_pay' => 'Te betalen',
    'available_credit' => 'beschikbaar',
    'always_active_modifiers' => '',
    'apply_theshold_to' => '',
    'sepa' =>
    array (
      'creditor_identifier' => 'Identificatie krediteur',
      'business_code' => 'Unieke bedrijfscode',
    ),
  ),
  'notifications' =>
  array (
    'global_filter' =>
    array (
      'roles' => 'Alle gebruikers met rol :role',
      'orders' => 'Alle deelnemers aan de bestelling :supplier :number',
    ),
    'help' =>
    array (
      'repeat_mail_warning' => 'Deze melding is al per e-mail verzonden. Het opslaan van deze melding terwijl deze flag actief blijft, zorgt voor verzending van een nieuwe e-mail.',
      'sending_mail_warning' => '',
      'visibility_by_selection' => 'Houd Ctrl ingedrukt om meerdere gebruikers te selecteren. Als er geen gebruikers zijn geselecteerd, geldt de melding voor alle gebruikers.',
      'suspend' => '',
      'arbitrary_dates' => 'Vanaf hier kun je willekeurige data toevoegen aan je leveringskalender, zelfs voor bestellingen die nog niet bestaan. Deze functie wordt aanbevolen om de coördinatie van de verschillende contacten binnen de GAS te vergemakkelijken en om activiteiten op lange termijn te plannen.',
    ),
    'cycle' =>
    array (
      'two_weeks' => '',
      'first_of_month' => '',
      'second_of_month' => '',
      'third_of_month' => '',
      'fourth_of_month' => '',
      'last_of_month' => '',
    ),
    'name' => 'Melding',
    'notices' =>
    array (
      'new_notification_from' => 'Nieuwe mededeling door :author',
      'attached_order' => 'Bijgevoegd is het bestand voor de bestelling van :gasname.',
    ),
    'recurrence' => 'Onthoud mij',
    'greetings' => 'Vriendelijke groeten',
    'send_to_current_users' => 'Melding gebruiker weergegeven',
    'next_dates' => 'Komende data op kalender:',
    'next_auto_orders' => 'Komende data op kalender:',
    'list' =>
    array (
      'closed_orders' => 'Gesloten bestellingen',
      'confirmed_dates' => 'Bevestigde data',
      'temporary_dates' => 'Tijdelijke data',
      'appointments' => 'Afspraken',
    ),
    'calendar_date' => 'Datum op kalender',
    'date_reference' => '',
  ),
  'orders' =>
  array (
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
  ),
  'permissions' =>
  array (
    'permissions' =>
    array (
      'maintenance_access' => 'Toegang ook bij onderhoud toegestaan',
      'alter_permissions' => 'Alle toestemmingen wijzigen',
      'alter_configs' => 'De configuraties van de GAS wijzigen',
      'create_suppliers' => 'Nieuwe leveranciers aanmaken',
      'do_booking' => 'Bestellingen plaatsen',
      'view_suppliers' => 'Alle leveranciers zien',
      'view_orders' => '',
      'alter_self' => 'Eigen persoonsgegevens wijzigen',
      'delete_account' => '',
      'admin_users' => '',
      'view_users' => 'Alle gebruikers zien',
      'sub_users' => 'Sub-gebruikers met beperkte functies hebben',
      'admin_user_movements' => 'Boekhoudkundige bewegingen van gebruikers beheren',
      'admin_movements' => 'Alle boekhoudkundige verrichtingen beheren',
      'view_movements' => 'De boekhoudkundige verrichtingen zien',
      'admin_movements_types' => 'De soorten boekhoudkundige verrichtingen beheren',
      'admin_categories' => 'De categoprieën beheren',
      'admin_measures' => 'De meeteenheden beheren',
      'view_statistics' => 'De statistieken weergeven',
      'admin_notifications' => 'De meldingen beheren',
      'alter_suppliers' => 'De toegewezen leveranciers wijzigen',
      'open_orders' => 'Bestellingen openen en wijzigen',
      'do_deliveries' => 'De leveringen uitvoeren',
      'admin_invoices' => '',
      'admin_supplier_movements' => '',
      'admin_multigas' => '',
    ),
    'roles' =>
    array (
      'admin' => '',
      'secondary_admin' => '',
    ),
    'name' => 'Toegestaan',
    'supplier' =>
    array (
      'change' => 'U kunt de leverancier wijzigen',
      'orders' => 'U kunt nieuwe bestellingen voor de leverancier plaatsen',
      'deliveries' => 'Beheer de leveringen voor de leverancier',
    ),
    'role' => 'Rol',
    'help' =>
    array (
      'global_permission_notice' => 'Deze speciale toestemming geldt automatisch voor alle onderwerpen (huidig en toekomstig) en stelt in staat om op alles in te werken, hoewel de aangewezen gebruiker niet expliciet zichtbaar voor anderen zal zijn.',
      'blocked_autoremove' => '',
      'unique_role_warning' => '',
      'unprivileged' => '',
      'sub_user' => '',
      'multigas_admin' => '',
      'admin_not_authorized' => '',
      'parent_role' => '',
      'missing_elements_warning' => '',
    ),
    'revoke' => 'Rol herroepen',
    'change_roles' => 'Rollen bewerken',
    'parent_role' => 'Rol superieur',
    'add_user' => 'Nieuwe gebruiker toevoegen',
    'unprivileged' => 'Niet-geprivilegieerde gebruikersrol',
    'sub_user' => 'Rol van subgebruiker',
    'multigas_admin' => 'De categoprieën beheren',
  ),
  'products' =>
  array (
    'prices' =>
    array (
      'unit' => 'Eenheidsprijs',
      'unit_no_vat' => '',
      'package' => '',
    ),
    'name' => 'Product',
    'code' => 'Leverancierscode',
    'bookable' => 'Bestelbaar',
    'vat_rate' => 'BTW-tarief',
    'portion_quantity' => 'Formaat',
    'multiple' => 'Meervoudig',
    'min_quantity' => 'Minimum',
    'max_quantity' => 'Aanbevolen maximum',
    'available' => 'beschikbaar',
    'help' =>
    array (
      'unit_no_vat' => '',
      'package_price' => '',
      'importing_categories_and_measures' => 'Er worden categorieën en meeteenheden aangemaakt waarvan de naam niet onder de bestaande categorieën en meeteenheden worden gevonden.',
      'imported_notice' => 'Geïmporteerde producten',
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
      'duplicate_notice' => 'Het duplicaatproduct heeft een kopie van de varianten van het oorspronkelijke product. Ze kunnen worden gewijzigd na het opslaan van het duplicaat.',
      'unit_price' => '',
      'vat_rate' => '',
      'notice_removing_product_in_orders' => '',
    ),
    'weight_with_measure' => '',
    'list' => 'Producten',
    'sorting' => '',
    'variant' =>
    array (
      'matrix' => 'Variant aanmaken/wijzigen',
      'help' =>
      array (
        'code' => '',
        'price_difference' => '',
      ),
      'price_difference' => 'Prijsverschil',
      'weight_difference' => 'Prijsverschil',
    ),
    'package_size' => 'Verpakking',
    'global_min' => 'Overzichtstabel producten',
    'variants' => 'Variabele',
    'remove_confirm' => '',
    'removing' =>
    array (
      'keep' => '',
      'leave' => '',
    ),
  ),
  'supplier' =>
  array (
    'referent' => 'Contactpersoon',
    'payment_method' => 'Betaalwijze',
    'all' => 'Leveranciers',
    'products_list' =>
    array (
      'pdf' => 'PDF-lijst (automatisch gegenereerd)',
      'csv' => 'CSV-lijst (automatisch gegenereerd)',
    ),
    'attachments' => 'Bestanden en afbeeldingen',
    'remote_index' => '',
    'vat' => 'BTW-tarief',
    'enable_fast_shipping' => 'Snelle leveringen',
    'help' =>
    array (
      'enable_fast_shipping' => '',
      'enable_no_quantities' => '',
      'modifiers_notice' => '',
      'import_products_notice' => '',
      'handling_products' => '',
      'name' => 'Opmerkingen voor de leverancier',
      'legal_name' => '',
      'description' => 'Alle gebruikers zien',
      'payment_method' => '',
      'orders_mode' => '',
      'send_notification_on_close' => '',
    ),
    'enable_no_quantities' => 'Snelle leveringen activeren',
    'export_products' => 'Exporteren',
    'referents' => 'Contactpersoon',
    'products_list_heading' => 'Prijslijst :supplier van :date',
    'admin_categories' => 'De categoprieën beheren',
    'admin_measures' => 'Meeteenheden beheren',
    'legal_name' => 'Bedrijfsnaam',
    'orders_mode' => 'Afwikkelingsmodus bestellingen',
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
      'title' => 'Reserveringen',
      'body' => '',
    ),
    'accounting' =>
    array (
      'title' => 'Boekhouding',
      'body' => '',
    ),
    'config' =>
    array (
      'title' => 'Configuraties',
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
      'suspended' => '',
      'wrong_control_error' => '',
      'existing_email_error' => '',
      'duplicated_name_error' => '',
      'waiting_approval' => '',
      'promote_friend' => '',
      'promote_friend_enforce_mail' => '',
      'reassign_friend' => '',
      'notifications_instructions' => 'Selecteer de leveranciers voor wie u een melding wilt ontvangen bij het openen van nieuwe bestellingen.',
      'fee' => '',
      'deposit' => '',
      'satispay' => 'Vanaf hier kunt u uw tegoed m.b.v. Satispay opladen. Geef aan hoeveel u wilt betalen en eventuele notities voor beheerders; u ontvangt een melding op uw smartphone om de betaling binnen 15 minuten te bevestigen.',
      'remove_profile' => '',
      'remove_profile_credit_notice' => '',
    ),
    'firstname' => 'Naam',
    'lastname' => 'Achternaam',
    'change_friend' => '',
    'formatted_aggregation' => '',
    'sepa' =>
    array (
      'mandate' => '',
      'date' => 'Datum SEPA-opdracht',
      'intro' => 'Configuratie SEPA',
      'help' => '',
      'identifier' => 'ID van SEPA-opdracht',
    ),
    'last_login' => 'Laatste toegang',
    'last_booking' => '',
    'member_since' => 'Lid sinds',
    'birthplace' => '',
    'birthdate' => 'Geboortedatum',
    'other_bookings' => '',
    'fullname' => '',
    'taxcode' => 'Fiscaal nummer',
    'card_number' => 'Kaartnummer',
    'payment_method' => 'Betaalwijze',
    'all' => 'Gebruikers',
    'payed_fee' => 'Betaalde aandeel',
    'name' => 'Gebruiker',
    'address_part' =>
    array (
      'street' => 'Adres (Straat)',
      'city' => 'Adres (Stad)',
      'zip' => 'Adres (postcode)',
    ),
    'statuses' =>
    array (
      'active' => 'Actief',
      'suspended' => 'Opgeschort',
      'deleted' => '',
      'removed' => '',
    ),
    'friend' => 'Vriend',
    'removed_user' => '',
    'booking_friend_header' => 'Heeft besteld :amount',
    'pending_deliveries' => '',
    'without_aggregation' => '',
    'aggregation' => '',
    'credit_below_zero' => 'Krediet < 0',
    'fee_not_payed' => 'Aandeel niet betaald',
    'personal_data' => 'Persoonsgegevens',
    'approve' => '',
    'do_not_approve' => '',
    'family_members' => 'Personen in gezin',
    'promote_friend' => 'Nieuwe gebruiker geregistreerd',
    'reassign_friend' => '',
    'change_friend_assignee' => '',
    'fee' => 'Ledenbijdrage',
    'deposit' => 'Deposito',
    'fees_status' => 'Status',
    'all_ceased' => 'Gestopt',
    'notices' =>
    array (
      'new_user' => 'Nieuwe gebruiker geregistreerd op :gasname:',
      'pending_approval' => '',
    ),
    'last_fee' => '',
    'fees' =>
    array (
      'new' => '',
      'change' => 'Wijzigen',
    ),
    'empty' =>
    array (
      'friends' => 'Voeg informatie toe over vrienden voor wie u subreserveringen wilt maken. Elke reservering is zelfstandig, maar wordt op het moment van levering als een enkele reservering behandeld. Elke vriend kan ook zijn eigen inloggegevens hebben, om in GASdotto te komen en zelf zijn reserveringen in te voeren.',
    ),
    'satispay' =>
    array (
      'reload' => 'Tegoed opladen met Satispay',
    ),
    'remove_profile' => '',
    'assign_aggregations' => '',
  ),
);
