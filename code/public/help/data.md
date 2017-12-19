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

## .user-editor

# Username

Username col quale l'utente si può autenticare. Deve essere univoco.

# Password

Password di accesso dell'utente. Lasciando in bianco questo campo, la password attuale non sarà modificata.

# Contatti

Qui si può specificare un numero arbitrario di contatti per l'utente. Le notifiche saranno spedite a tutti gli indirizzi e-mail indicati.

# Stato

Gli utenti Sospesi e Cessati non possono accedere alla piattaforma.

## .supplier-editor

# Nome

Nome informale del fornitore.

# Ragione Sociale

Nome completo del fornitore, da usare per fini contabili e fiscali.

# Descrizione

Breve descrizione leggibile da tutti gli utenti.

# Modalità Pagamento

Eventuale nota sulle modalità di pagamento al fornitore. Visibile solo ai referenti.

# Modalià Avanzamento Ordini

Eventuale nota sulle modalità per sottoporre gli ordini al fornitore. Visibile solo ai referenti.

# Contatti

Qui si può specificare un numero arbitrario di contatti per il fornitore.

## .product-editor

# Nome

Nome del prodotto.

# Prezzo Unitario

Prezzo unitario per unità di misura. Si intende "IVA inclusa", per maggiori dettagli si veda il campo "Aliquota IVA". Può assumere un significato particolare quando viene attivata la "Pezzatura".

# Prezzo Trasporto

Prezzo di trasporto per singola unità.

# Sconto

Sconto applicabile occasionalmente sul prezzo del prodotto. Se espresso con un numero decimale (e.g. 2,10) viene considerato come valore assoluto, altrimenti se termina con un simbolo di percentuale (e.g. 20%) viene considerato come percentuale sul Prezzo Unitario. Lo sconto può essere attivato o disattivato su ogni ordine che include il prodotto.

# Categoria

Categoria assegnata al prodotto.

# Unità di Misura

Unità di misura assegnata al prodotto. Attenzione: può influenzare l'abilitazione di alcune variabili del prodotto, si veda il parametro "Unità Discreta" nel pannello di amministrazione delle unità di misura (acessibile solo agli utenti abilitati).

# Descrizione

Breve descrizione del prodotto.

# Aliquota IVA

Le aliquote esistenti possono essere configurate nel pannello "Configurazioni".

# Codice Fornitore

Eventuale codice di riferimento del prodotto per il fornitore, viene incluso nei documenti esportati.

# Ordinabile

Indica se il prodotto potrà essere ordinabile o meno all'interno dei nuovi ordini per il fornitore. Lo stato dei singoli prodotti potrà comunque essere cambiato da parte dei referenti anche all'interno di un ordine aperto.

# Pezzatura

Se diverso da 0, ogni unità si intende espressa come questa quantità. Esempio:

  * unità di misura: chili
  * pezzatura: 0.3
  * prezzo unitario: 10 euro
  * quantità prenotata: 1 (che dunque si intende "1 pezzo da 0.3 chili")
  * costo: 1 x 0.3 x 10 = 3 euro

Utile per gestire prodotti distribuiti in pezzi, prenotabili dagli utenti in numero di pezzi ma da ordinare e/o pagare presso il fornitore come quantità complessiva.

# Variabile

Un prodotto "variabile" viene ordinato in pezzi la cui dimensione definitiva non è esattamente nota al momento della prenotazione. I prodotti così identificati attiveranno un ulteriore pannello in fase di consegna, per calcolarne il prezzo in funzione della pezzatura (vedi informazioni specifiche).

Da usare per prodotti consegnati in pezzi non sempre uniformi, come il formaggio o la carne, che sono pesati al momento della consegna.

# Confezione

Se il prodotto viene distribuito in confezioni da N pezzi, indicare qui il valore di N. Gli ordini da sottoporre al fornitore dovranno essere espressi in numero di confezioni, ovvero numero di pezzi ordinati / numero di pezzi nella confezione. Se la quantità totale di pezzi ordinati non è un multiplo di questo numero il prodotto sarà marcato con una icona rossa nel pannello di riassunto dell'ordine, da cui sarà possibile sistemare le quantità aggiungendo e togliendo ove opportuno.

# Multiplo

Se diverso da 0, il prodotto è prenotabile solo per multipli di questo valore. Utile per prodotti pre-confezionati ma prenotabili individualmente. Da non confondere con l'attributo "Confezione".

# Minimo

Se diverso da 0, il prodotto è prenotabile solo per una quantità superiore a quella indicata.

# Massimo Consigliato

Se diverso da 0, se viene prenotata una quantità superiore di quella indicata viene mostrato un warning.

# Disponibile

Se diverso da 0, questa è la quantità massima di prodotto che complessivamente può essere prenotata in un ordine. In fase di prenotazione gli utenti vedranno quanto è già stato sinora prenotato in tutto.

# Crea Nuova Variante

Ogni prodotto può avere delle varianti, ad esempio la taglia o il colore per i capi di abbigliamento. In fase di prenotazione, gli utenti potranno indicare quantità diverse per ogni combinazione di varianti. Le varianti possono inoltre avere un proprio prezzo, da specificare in funzione del prezzo unitario del prodotto (ad esempio: +1 euro o -0.8 euro).

## .order-editor

# Fornitore

Il fornitore presso cui l'ordine è aperto.

# Numero

Numero progressivo automaticamente assegnato ad ogni ordine.

# Commento

Eventuale testo informativo da visualizzare nel titolo dell'ordine, oltre al nome del fornitore e alle date di apertura e chiusura.

# Data Apertura

Data di apertura dell'ordine.

# Data Chiusura

Data di chiusura dell'ordine. Al termine del giorno qui indicato, l'ordine sarà automaticamente impostato nello stato "Prenotazioni Chiuse".

# Data Consegna

Eventuale data di consegna dell'ordine. Ha solo un valore informativo per gli utenti.

# Stato

Stato attuale dell'ordine. Può assumere i valori:

  * prenotazioni aperte: tutti gli utenti vedono l'ordine e possono sottoporre le loro prenotazioni
  * prenotazioni chiuse: tutti gli utenti vedono l'ordine ma non possono aggiungere o modificare le prenotazioni. Gli utenti abilitati a modificare il fornitore possono comunque intervenire
  * consegnato: l'ordine appare nell'elenco degli ordini solo per gli utenti abilitati, ma nessun valore può essere modificato né tantomeno possono essere modificate le prenotazioni
  * archiviato: l'ordine non appare più nell'elenco, può solo essere ripescato con la funzione di ricerca
  * in sospeso: l'ordine appare nell'elenco degli ordini solo per gli utenti abilitati, e può essere modificato

# Sconto Globale

Sconto applicato su tutti i prodotti nell'ordine. Può eventualmente essere sommato allo sconto individualmente applicabile sui singoli prodotti, che va configurato nell'apposito pannello. Se espresso con un numero decimale (e.g. 2,10) viene considerato come valore assoluto, altrimenti se termina con un simbolo di percentuale (e.g. 20%) viene considerato come percentuale sul Prezzo Unitario.

# Spese Trasporto

Eventuali spese di trasporto da applicare a tutte le prenotazioni avanzate su questo ordine. Tale valore sarà distribuito proporzionalmente tra le diverse prenotazioni ed apparirà alla voce "Trasporto" in fase di consegna. Va a sommarsi ad eventuali spese di trasporto definite sui singoli prodotti.

# Pagamento

Da qui è possibile immettere il movimento contabile di pagamento dell'ordine nei confronti del fornitore.

## .gas-editor

# Nome

Nome del GAS.

# E-Mail

Indirizzo mail di riferimento del GAS. Attenzione: viene specificato a titolo informativo, le configurazioni per la spedizione di email generate dal sistema sono nel riquadro accanto.

# Messaggio Homepage

Eventuale messaggio da visualizzare sulla pagina di autenticazione di GASdotto, utile per comunicazioni speciali verso i membri del GAS.

# Username

Username da utilizzare per connettersi al server SMTP (specificato accanto)

# Password

Password da utilizzare per connettersi al server SMTP (specificato accanto)

# Server SMTP

Server SMTP da utilizzare per l'invio delle mail di sistema. Se non viene specificato, questo o gli altri parametri all'interno di questo riquadro, nessuna mail potrà essere generata.

# Porta

Porta TCP da usare per connettersi al server SMTP. Consultare la documentazione del proprio fornitore di posta elettronica per questi dettagli.

# Crittografia

Tipo di connessione sicura usata dal proprio server SMTP. Consultare la documentazione del proprio fornitore di posta elettronica per questi dettagli.

# Denominazione

.

# Inizio Anno Sociale

In questa data le quote di iscrizione verranno automaticamente fatte scadere e dovranno essere rinnovate.

# Quota Annuale

Se non configurato (valore = 0) non verranno gestite le quote di iscrizione.

# Cauzione

Se non configurato (valore = 0) non verranno gestite le cauzioni da parte dei nuovi soci.

# IBAN

.

# Identificativo Azienda

.
