```

File della documentazione in linea.

E' formattato in Markdown (https://daringfireball.net/projects/markdown/), anche
se interpretato in maniera speciale quando la documentazione viene attivata.

I titoli di secondo livello contengono un selettore jQuery: all'interno degli
elementi filtrati andranno cercati quelli successivi.
I titoli di primo livello contengono il testo della <label> o del <button> che
si intende documentare. Più precisamente, il messaggio sarà assegnato al nodo
padre di quello selezionato.
Il testo seguente sarà il messaggio assegnato all'elemento sopra selezionato
in funzione dei titoli.

Dunque, ogni messaggio è assegnato ai nodi filtrati con la selezione
$('titolo_secondo_livello :contains(titolo_primo_livello)').parent();

Il messaggio può contenere più paragrafi e liste, saranno aggregati all'interno
del popup relativo.

```

## .user-editor, #createUser

# Benutzername

Benutzername, mit dem der Nutzer sich authentifiziert (muss eindeutig sein).

# Passwort

Zugangspasswort des Nutzers. Bereits registrierte Nutzer können das Feld frei lassen, wenn das bestehende Passwort nicht verändert werden soll.

# Kontakte

Hier lassen sich beliebige Kontaktdaten des Nutzers hinterlegen. Die Benachrichtigungen werden an alle angegebenen Emailadressen verschickt.

# Jahresbeitrag

Daten im Zusammenhang des Jahresbeitrags des Nutzers. Zur Deaktivierung dieser Option gehe zu<br>
Einstellungen -> Buchhaltung

# Kaution

Daten zur Einlage, die der Nutzer im Moment des Eintritts in die Bestellgemeinschaft bezahlt hat. Zur Deaktivierung dieser Option gehe zu<br>
Einstellungen -> Buchhaltung

# Lieferort

Wohin der Benutzer seine eigenen Produkte geliefert haben möchte. Sie können Lieferungen an verschiedenen Orten organisieren.

# Status

Suspendierte oder gesperrte Benutzer können nicht auf das Bestellsystem zugreifen, auch wenn sie registriert bleiben.

# SEPA Einstellung

Geben Sie hier die Parameter für die Generierung von RIDs für diesen Benutzer an. Für Benutzer, für die diese Felder nicht ausgefüllt wurden, ist es nicht möglich, RIDs zu generieren.

## .supplier-editor, #createSupplier

# Name

Informeller Name des Lieferanten.

# Firmenname

Vollständiger Name des Lieferanten, der für Buchhaltungs- und Steuerzwecke verwendet wird. Wenn nichts angegeben ist, wird der obenstehende Name verwendet.

# Beschreibung

Kurzbeschreibung, sichtbar für alle Nutzer.

# Bezahlmodus

Möglichkeit, eine Notiz über die Art der Bezahlung des Lieferanten zu hinterlassen. Sichtbar nur für Referenten (Ansprechpartner beim Lieferanten).

# Fortschritt der Bestellungen

Möglichkeit, einen Hinweis darauf zu hinterlassen, wie Bestellungen an den Lieferanten hinterlegt werden. Nur sichtbar für Referenten (Ansprechpartner beim Lieferanten).

# Kontakte

Hier kannst du eine beliebige Anzahl von Kontaktdaten / Ansprechpersonen für diesen Lieferanten angeben.

## .product-editor

# Name

Name des Produkts.

# Stückpreis

Preis pro Gebinde. Zu verstehen inklusive Mwst. Für mehr Details siehe Feld "Mwst-Satz". Der jeweilige Wert ergibt sich, wenn das Feld "Gebindegröße" aktiviert ist.

# Transportkosten

Transportkosten für eine einzelne Einheit. Achtung: nicht zu verwechseln mit den Transportkosten, die insgesamt auf eine Bestellung anzusetzen sind.

# Skonto

Rabatt, der gelegentlich auf den Produktpreis gegeben werden kann. Der Rabatt kann in jeder Bestellung, die das Produkt einschließt, aktiviert oder deaktiviert werden.

# Kategorie

Die dem Produkt zugewiesene Kategorie.

# Maßeinheit

Die dem Produkt zugewiesene Maßeinheit. Achtung: dies kann verschiedene Variablen des Produktes beeinflussen, siehe den Parameter "diskrete Einheit" im Administrationsoberfläche unter "Maßeinheiten verwalten" (einsehbar nur für Nutzer mit entsprechenden Rechten).

# Beschreibung

Kurzbeschreibung des Produkts.

# MwSt-Satz

Die Mehrwertsteuersätze können unter dem Menüpunkt "Einstellungen" konfiguriert werden.

# Artikelnummer Lieferant

Artikelnummer des Produkts für den Lieferanten, wird in die Exportdokumente aufgenommen.

# Bestellbar

Zeigt an, ob das Produkt im Rahmen einer neuen Bestellrunde bestellbar ist oder nicht. Dieser Status einzelner Produkte kann noch während einer laufenden Bestellung geändert werden.

# Gebindegröße

Wenn Wert > 0 versteht sich jede Einheit in dieser angegebenen Größe. Zum Beispiel:

    * Maßeinheit: Kilogramm
    * Stückzahl: 0.3
    * Stück/Gebindepreis: 10 Euro
    * bestellte Menge: 1 (die also zu verstehen ist als "1 Stück à 0.3 Kilo")
    * Kosten: 1 x 0.3 x 10 = 3 Euro

Nützlich um Produkte zu verwalten, die in Einzelstücken verteilt werden, welche von den Nutzern vorbestellt werden können, aber beim Lieferant als Gesamtmenge bezahlt werden.

# veränderbar

Ein "variables" Produkt wird in Stücken bestellt, deren Größe zum Zeitpunkt der Bestellung nicht genau bekannt ist. Diese Produkte aktivieren während der Lieferung ein zusätzliches Panel, um den Preis nach Stückgröße zu berechnen (siehe entsprechende Hinweise).

Zu verwenden für Produkte, die in nicht immer gleichmäßigen Stücken geliefert werden, wie Käse oder Fleisch, und die bei der Lieferung gewogen werden.

# Verpackung

Wenn das Produkt in Abpackungen von n Stücken ausgegeben wird, wird hier der Wert von n angezeigt. Die Bestellungen an den Lieferanten müssen die Anzahl der Verpackungen enthalten, d.h. die Anzahl der bestellten Stücke / die Anzahl der Stücke in der Verpackung. Wenn die Gesamtmenge der bestellten Stücke nicht ein Vielfaches dieser Anzahl ist, ist das Produkt in der Tabelle, die die Zusammenfassung der bestellten Produkte enthält, mit einem roten icon gekennzeichnet. Aus dieser Tabelle lassen sich die Mengen systematisieren, indem - wo erforderlich - hinzugefügt und weggenommen wird.

# Vielfaches

Wenn ungleich null, ist das Produkt nur vorbestellbar durch die Vervielfachung dieses Wertes.
Nützlich für Produkte, die vorverpackt sind, aber individuell vorbestellt werden. Nicht zu verwechseln mit dem Attribut 'Verpackung'.

# Minimum

Wenn ungleich null, ist das Produkt nur bestellbar für eine Anzahl, die größer ist als die hier angezeigte.

# Empfohlenes Maximum

Wenn ungleich null, wird eine Warnung angezeigt, wenn eine höhere Anzahl als die hier Angegebene bestellt wird.

# Verfügbar

Wenn ungleich null, ist dieses die maximale Anzahl der Produkte, die insgesamt in einer Bestellung vorgemerkt werden können. In der Bestellphase sehen die Nutzer, was bisher bestellt wurde.

# Eine neue Variante hinzufügen

Jedes Produkt kann mehrere Varianten haben, z.B. in der Größe oder Farbe der Wäscheteile. In der Phase der Bestellung können die Nutzer die gewünschte Anzahl für jede Kombination der Varianten angeben. Die Varianten können darüberhinaus einen eigenen Preis haben, der abhängig vom Gebindepreis des Produktes angegeben wird (z.B. +1 Euro oder -0.80 Euro).

## .order-extras

# Zusammenfassungen der Vorbestellungen verschicken

Verschicke an alle Teilnehmer einer Bestellung eine Bestätigungsmail für ihre individuelle Bestellung. Es ist möglich, eine Nachricht an alle anzuhängen mit zusätzlichen Informationen.

## .order-editor, #createOrder

# Lieferant

Der Lieferant, bei dem bestellt wird.

# Nummer

Fortlaufende Nummer, die jeder Bestellung automatisch zugewiesen wird.

# Bemerkung

Möglicher Hinweis, der aufgenommen wird im Titel der Bestellung und der weder den Namen des Lieferanten noch die Daten zu Beginn und Ende der Bestellzeit enthält.

# Beginn

Datum des Bestellbeginns

# Ende

Datum des Bestellschlusses. Am Ende des hier angegebenen Tages wird die Bestellung automatisch in den Status 'abgeschlossene Bestellungen' überführt.

# Lieferdatum

Mögliches Lieferdatum. Zur Information für die Nutzer.

# Status

Aktueller Status der Bestellung. Kann folgende Werte annehmen:

- aktuell laufende Bestellungen: alle Nutzer sehen die Bestellung und können ihre Vormerkungen vornehmen
- abgeschlossene Bestellungen: alle Nutzer sehen die Bestellung, aber können ihre Vorbestellung nicht mehr ergänzen oder verändern. Nur diesbzgl. autorisierte Nutzer können hier Änderungen nachträglich vornehmen
- geliefert: die Bestellung wird den autorisierten Nutzern im Verzeichnis 'Bestellungen' angezeigt, aber weder der Wert noch die Vormerkungen können verändert werden
- archiviert: die Bestellung erscheint nicht mehr im Verzeichnis und kann nur noch über die Suchfunktion wieder herausgefischt werden
- in der Schwebe: die Bestellung erscheint im Verzeichnis 'Bestellungen' nur für die autorisierten Nutzer und kann geändert werden

# Globaler Rabatt

Rabatt, der auf alle Produkte der Bestellung angewandt wird. Kann evtl. zusammengefasst werden mit dem individuellen Rabatt, der auf einzelne Produkte gewährt wird und der in dem entsprechenden Feld festgelegt wird.

# Frachtkosten

Eventuelle Transportkosten, die auf alle Teilnehmer der Bestellung umgelegt werden. Ein solcher Wert wird proportial unter den individuellen Bestellungen aufgeteilt und erscheint unter dem Schlagwort 'Transport' bei der Aufgabe der Bestellung. Dies geschieht zusätzlich zu den für einzelne Produkte definierten Transportkosten.

# Bezahlung

Von hier ab ist es möglich, die Zahlung der Bestellung an den Lieferanten einzugeben, die den entsprechenden Saldo verändern wird.

## .gas-editor

# Name der Bestellgemeinschaft

Name der Bestellgemeinschaft

# Bezugs-E-Mail

Emailadresse der Bestellgemeinschaft. Achtung: diese wird zu Informationszwecken angegeben, die Konfigurationen zum Versenden der vom System generierten Emails befinden sich in dem entsprechenden Feld.

# Homepage-Nachricht

Möglichkeit eine Nachricht zu hinterlegen, die auf der Authentifizierungsseite angezeigt wird. Nützlich für spezielle Mitteilungen an die Mitglieder der Bestellgemeinschaft oder als Willkommensnachricht.

# Währung

Symbol der verwendeten Währung, das in allen Ansichten verwendet wird, in denen Preise angegeben werden.

# Wartungsmodus

Wenn aktiviert, wird der Login für jene Nutzer gesperrt, die nicht über die Berechtigung "Zugriff auch während der Wartung" verfügen.

# Email

Emailadresse, von der aus Emails versandt werden.

# Nutzername

Nutzername für die Verbindung zum SMTP-Server (wird nebenstehend angegeben)

# Passwort

Passwort für die Verbindung zum SMTP-Server (wird nebenstehend angegeben)

# SMTP-Server

SMPT-Server, der zum Versenden von System-Mails genutzt wird. Wenn dieser oder andere Parameter in diesem Feld nicht angegeben werden, kann keine Email generiert werden.

# Port

TCP-Port für die Verbindung zum SMTP-Server. Details hierzu findest du in der Dokumentation deines Email-Anbieters.

# Verschlüsselung

Art der sicheren Verbindung, die vom SMTP-Server verwendet wird. Details hierzu findest du in der Dokumentation deines Email-Anbieters.

# öffentliche Registrierung zugelassen

Wenn diese Option aktiviert ist, kann sich jeder über das entsprechende Formular registrieren (zugänglich über das Login-Formular). Die für die Nutzerverwaltung zuständigen Administratoren erhalten eine Benachrichtigung über jeden neu registrierten Nutzer.

# Schnelle Lieferung zulassen

Wenn diese Option aktiviert ist, wird in der Übersicht zur Verwaltung der jeweiligen Bestellungen der Tab 'Schnelle Lieferung' aktiviert (neben dem Tab 'Lieferungen'). Dieser erlaubt es, mehrere Vorbestellungen auf einmal als 'geliefert' zu kennzeichnen.

# Bestellungen zur Komplettierung von Gebinden

Wenn diese Option aktiviert ist und wenn die Bestellungen Produkte beinhalten, die in Gebinde bzw. Verpachungseinheiten unterteilt sind, und die Summe der hierbei bestellten Mengen nicht ein Vielfaches davon beträgt, ist es möglich, auch nach Ende der Bestellfrist weitere Vorbestellungen zu tätigen (jedoch nur für Produkte, deren Gebinde noch nicht 'voll' sind).

# Spalten Zusammenfassung Bestellungen

Diese hier ausgewählten Spalten werden standardmäßig in der Übersicht zur Verwaltung der einzelnen Bestellungen angezeigt. Es ist jedoch jederzeit möglich, die Anzeige innerhalb der Übersicht selbst mit Hilfe des Auswahlbuttons oben rechts zu ändern.

# Jahresbeginn

An diesem Tag verfallen die Anmeldegebühren/Jahresbeiträge automatisch und werden erneut fällig.

# Jahresbeitrag

Wenn nicht konfiguriert (Wert = 0), werden die Anmeldegebühren nicht verwaltet.

# Kaution

Wenn nicht konfiguriert (Wert = 0), werden die Kautionseinzahlungen der neuen Mitglieder nicht verwaltet.

# Zugelassene SEPA

Wenn diese Option aktiviert ist und die entsprechenden Felder ausgefüllt sind, wird der Export von SEPA-Dateien aktiviert, mit denen der Zahlungsverkehr automatisiert werden kann. Die Dateien werden generiert über <strong>Buchhaltung > Kreditstand > SEPA exportieren</strong>.
Nach dem Ausfüllen das Forumulars müssen zusätzlich für jeden Nutzer noch einige Parameter spezifiziert werden.

# IBAN

IBAN, auf der die vom RID generierten Zahlungen erfolgen müssen.

# Identifizierung Gläubiger

Der von der Bank bereitgestellte Identifizierungscode.

# Buchungsschlüssel Betrieb

Von der Bank vergebener Identifizierungscode, auch bekannt als 'CUC'.

# Paypal zugelassen

Wenn diese Option aktiviert ist und die entsprechenden Felder ausgefüllt sind, werden Zahlungen via PayPal ermöglicht, mit denen Nutzer ihr GASdotto-Guthaben direkt aufladen können. Um die Zugangsdaten zu erhalten, besuche folgende Seite: https://developer.paypal.com/

# Satispay zugelassen

Wenn diese Option aktiviert ist und die entsprechenden Felder ausgefüllt sind, werden Zahlungen via Satispay ermöglicht, mit denen Nutzer ihr GASdotto-Guthaben direkt aufladen können. Um die Zugangsdaten zu erhalten, besuche folgende Seite: https://business.satispay.com/

# Zugelassene Rechnungslegung

Wenn diese Option aktiviert ist und die entsprechenden Felder ausgefüllt sind, wird das Ausstellen von Rechnungen an Nutzer, die an Bestellungen teilnehmen, aktiviert. Rechnungen werden zum Zeitpunkt der Speicherung oder der Lieferung der Bestellung ausgestellt und sind über <strong>Buchhaltung > Rechnungen</strong> zugänglich.

# Import

Von hier aus kann eine GDXP-Datei importiert werden, die von einer anderen GASdotto-Instanz oder einer anderen Plattform erzeugt wurde, die dieses Dateiformat unterstützt.

## .vatrate-editor

# Steuersatz

Prozentsatz des auf die Preise anzuwendenden Steuersatzes

## .gas-permission-editor

# Rolle Benutzer ohne Sonderrechte

Diese Rolle wird jedem neuen Benutzer automatisch zugewiesen.

# Rolle Mitbenutzer (Freunde)

Diese Rolle wird automatisch jedem 'Freund' bereits bestehender Nutzer zugewiesen. Es wird empfohlen, eine eigene Rolle hierfür zu einzurichten, deren Berechtigungen allein auf das Bestellen begrenzt ist.

# Rolle auf höherem Niveau

Nutzer mit einer Rolle auf 'höherem Niveau' können diese Rolle auch anderen Nutzern zuweisen.

## .movement-type-editor, #createMovementtype

# Akzeptiert negative Werte

Wenn auf 'off' gestellt, verhindert dies, dass man einen negativen Betrag für die Zahlungsbewegung eingeben kann.

# Konstanter Wert

Wenn anders als 0, wird es nicht möglich sein, den Wert von neuen Bewegungen dieses Typs zu ändern.

# Bezahlende

Die Art der Einheit, die die Zahlung vornimmt. Wenn diese Option ausgewählt ist, kann die Einheit innerhalb der Eingabemaske für die Erstellung neuer Zahlungsbewegungstypen ausgewählt und spezifiziert werden.

# Bezahlt

Die Art der Einheit, die die Zahlung empfängt. Wenn diese Option ausgewählt ist, kann die Einheit innerhalb der Eingabemaske für die Erstellung neuer Zahlungsbewegungstypen ausgewählt und spezifiziert werden.

## .invoice-editor

# Beinhaltete Bestellungen

Hier können die Bestellungen ausgewählt werden, die an dieser Rechnung beteiligt sind. Wenn die Rechnung als "bezahlt" markiert ist, wird der Verweis auf die Zahlungsbewegung hinzugefügt und automatisch archiviert.
