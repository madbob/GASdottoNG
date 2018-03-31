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

# Username

Username col quale l'utente si può autenticare. Deve essere univoco.

# Password

Password di accesso dell'utente. Per gli utenti già esistenti, lasciando in bianco questo campo al salvataggio la password attuale non sarà modificata.

# Contatti

Qui si può specificare un numero arbitrario di contatti per l'utente. Le notifiche saranno spedite a tutti gli indirizzi e-mail indicati.

# Quota Associativa

Dati relativi alla quota associativa dell'utente, che scade ogni anno. Per disabilitare questa opzione, vai in<br>
Configurazione -> Contabilità

# Deposito

Dati relativi al deposito pagato dall'utente al momento dell'iscrizione al GAS. Per disabilitare questa opzione, vai in<br>
Configurazione -> Contabilità

# Luogo di Consegna

Dove l'utente preferisce avere i propri prodotti recapitati. Permette di organizzare le consegne in luoghi diversi.

# Stato

Gli utenti Sospesi e Cessati non possono accedere alla piattaforma, pur restando registrati.

# Configurazione SEPA

Specifica qui i parametri per la generazione dei RID per questo utente. Per gli utenti per i quali questi campi non sono stati compilati non sarà possibile generare alcun RID.

## .supplier-editor, #createSupplier

# Nome

Nome informale del fornitore.

# Ragione Sociale

Nome completo del fornitore, da usare per fini contabili e fiscali. Se non specificato, verrà usato il Nome

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

Prezzo di trasporto per singola unità. Attenzione: da non confondere con le Spese di Trasporto applicabili globalmente su un ordine.

# Sconto

Sconto applicabile occasionalmente sul prezzo del prodotto. Lo sconto può essere attivato o disattivato su ogni ordine che include il prodotto.

# Categoria

Categoria assegnata al prodotto.

# Unità di Misura

Unità di misura assegnata al prodotto. Attenzione: può influenzare l'abilitazione di alcune variabili del prodotto, si veda il parametro "Unità Discreta" nel pannello di amministrazione delle unità di misura (acessibile solo agli utenti abilitati).

# Descrizione

Breve descrizione del prodotto.

# Aliquota IVA

Le aliquote esistenti possono essere configurate nel pannello<br>
Configurazioni

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

## .order-editor, #createOrder

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

Sconto applicato su tutti i prodotti nell'ordine. Può eventualmente essere sommato allo sconto individualmente applicabile sui singoli prodotti, che va configurato nell'apposito pannello.

# Spese Trasporto

Eventuali spese di trasporto da applicare a tutte le prenotazioni avanzate su questo ordine. Tale valore sarà distribuito proporzionalmente tra le diverse prenotazioni ed apparirà alla voce "Trasporto" in fase di consegna. Va a sommarsi ad eventuali spese di trasporto definite sui singoli prodotti.

# Pagamento

Da qui è possibile immettere il movimento contabile di pagamento dell'ordine nei confronti del fornitore, che andrà ad alterare il relativo saldo.

## .gas-editor

# Nome

Nome del GAS.

# E-Mail

Indirizzo mail di riferimento del GAS. Attenzione: viene specificato a titolo informativo, le configurazioni per la spedizione di email generate dal sistema sono nel riquadro dedicato.

# Messaggio Homepage

Eventuale messaggio da visualizzare sulla pagina di autenticazione di GASdotto, utile per comunicazioni speciali verso i membri del GAS o come messaggio di benvenuto.

# Valuta

Simbolo della valuta in uso. Verrà usato in tutte le visualizzazioni in cui sono espressi dei prezzi

# Modalità Manutenzione

Se abilitato, il login sarà inibito a tutti gli utenti che non hanno il permesso "Accesso consentito anche in manutenzione"

# Indirizzo

Indirizzo mail da cui verranno spedite le mail.

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

# Abilita Registrazione Pubblica

Quando questa opzione è abilitata, chiunque potrà registrarsi all'istanza per mezzo dell'apposito pannello (accessibile da quello di login). Gli amministratori addetti agli utenti riceveranno una mail di notifica per ogni nuovo utente registrato.

# Abilita Consegne Rapide

Quando questa opzione è abilitata, nel pannello dell'ordine viene attivato il tab "Consegne Rapide" (accanto a "Consegne") che permette di marcare più prenotazioni come consegnate in un'unica operazione.

# Inizio Anno Sociale

In questa data le quote di iscrizione verranno automaticamente fatte scadere e dovranno essere rinnovate.

# Quota Annuale

Se non configurato (valore = 0) non verranno gestite le quote di iscrizione.

# Cauzione

Se non configurato (valore = 0) non verranno gestite le cauzioni da parte dei nuovi soci.

# IBAN

IBAN su cui dovranno avvenire i versamenti generati per mezzo dei RID.

# Identificativo Creditore

Codice identificativo erogato dalla banca.

# Codice Univoco Azienda

Codice identificativo erogato dalla banca, detto anche "CUC".

# Importazione

Da qui è possibile importare un file GDXP generato da un'altra istanza di GASdotto o da qualsiasi altra piattaforma che supporta il formato.

## .vatrate-editor

# Aliquota

Percentuale dell'aliquota da applicare sui prezzi

## .gas-permission-editor

# Ruolo Utente non Privilegiato

Questo ruolo sarà automaticamete assegnato ad ogni nuovo utente.

# Ruolo Sotto-Utente

Questo ruolo sarà automaticamente assegnato ad ogni "amico" degli utenti esistenti. Si consiglia di creare un ruolo dedicato, con permessi limitati alle sole prenotazioni.

# Ruolo Superiore

Gli utenti con assegnato un "ruolo superiore" potranno assegnare ad altri utenti questo ruolo.
