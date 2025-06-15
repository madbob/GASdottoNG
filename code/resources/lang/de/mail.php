<?php

return array (
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
);
