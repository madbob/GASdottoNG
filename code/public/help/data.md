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

# Login

Username col quale l'utente si può autenticare. Deve essere univoco.

# E-Mail

Indirizzo mail dell'utente. Qui verranno spediti eventuali messaggi generati dal sistema.

# Password

Password di accesso dell'utente. Lasciando in bianco questo campo, la password attuale non sarà modificata.

## .supplier-editor

# Nome

Nome del fornitore.

# Descrizione

Breve descrizione leggibile da tutti gli utenti.

# Codice Fiscale

Codice fiscale del fornitore.

# Partita IVA

Partita IVA del fornitore.

# Sito Web

Indirizzo del sito web.

# Modalità Pagamento

Eventuale nota sulle modalità di pagamento al fornitore. Visibile solo ai referenti.

# Modalià Avanzamento Ordini

Eventuale nota sulle modalità per sottoporre gli ordini al fornitore. Visibile solo ai referenti.

## .product-editor

# Nome

Nome del prodotto.

# Prezzo Unitario

Prezzo unitario per unità di misura. Può assumere un significato particolare quando viene attivata la "Pezzatura".

# Prezzo Trasporto

Prezzo di trasporto per singola unità.

# Sconto

Sconto applicabile occasionalmente sul prezzo del prodotto. Se espresso con un numero decimale (e.g. 2,10) viene considerato come valore assoluto, altrimenti se termina con un simbolo di percentuale (e.g. 20%) viene considerato come percentuale sul Prezzo Unitario. Lo sconto può essere attivato o disattivato su ogni ordine che include il prodotto.

# Categoria

Categoria assegnata al prodotto.

# Unità di Misura

Unità di misura assegnata al prodotto. Attenzione: può influenzare l'abilitazione di alcune variabili.

# Descrizione

Breve descrizione del prodotto.

# Codice Fornitore

Eventuale codice di riferimento del prodotto per il fornitore, viene incluso nei documenti esportati.

# Ordinabile

Indica se il prodotto potrà essere ordinabile o meno all'interno dei nuovi ordini per il fornitore. Lo stato dei singoli prodotti potrà comunque essere cambiato da parte dei referenti anche all'interno di un ordine aperto.

# Archiviato

Un prodotto archiviato viene nascosto nell'elenco dei prodotti del fornitore, e non rientra (neppure come non ordinabile) all'interno dei nuovi ordini. Da usare per prodotti fuori listino, per mantenerne un riferimento storico.

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

.

# Multiplo

Se diverso da 0, il prodotto è prenotabile solo per multipli di questo valore. Utile per prodotti pre-confezionati ma prenotabili individualmente.

# Minimo

Se diverso da 0, il prodotto è prenotabile solo per una quantità superiore a quella indicata.

# Massimo Consigliato

Se diverso da 0, se viene prenotata una quantità superiore di quella indicata viene mostrato un warning.

# Disponibile

Se diverso da 0, questa è la quantità massima di prodotto che complessivamente può essere prenotata in un ordine. In fase di prenotazione gli utenti vedranno quanto è già stato sinora prenotato in tutto.

# Crea Nuova Variante

.

## .order-editor

# Fornitore

.

# Data Apertura

.

# Data Chiusura

.

# Data Consegna

.

# Stato

.

# Sconto Globale

Sconto applicato su tutti i prodotti nell'ordine. Può eventualmente essere sommato allo sconto individualmente applicabile sui singoli prodotti, che va configurato nell'apposito pannello. Se espresso con un numero decimale (e.g. 2,10) viene considerato come valore assoluto, altrimenti se termina con un simbolo di percentuale (e.g. 20%) viene considerato come percentuale sul Prezzo Unitario.

# Pagamento

.

## .gas-editor

# Nome

Nome del GAS.

# E-Mail

Indirizzo mail di riferimento del GAS. Attenzione: viene specificato a titolo informativo, le configurazioni per la spedizione di email generate dal sistema sono nel riquadro accanto.

# Descrizione

.

# Messaggio Homepage

Eventuale messaggio da visualizzare sulla pagina di autenticazione di GASdotto, utile per comunicazioni speciali verso i membri del GAS.

# Username

Username da utilizzare per connettersi al server SMTP (specificato sotto)

# Password

Password da utilizzare per connettersi al server SMTP (specificato sotto)

# Server SMTP

Server SMTP da utilizzare per l'invio delle mail di sistema. Se non viene specificato, questo o gli altri parametri all'interno di questo riquadro, nessuna mail potrà essere generata.

# Porta

Porta TCP da usare per connettersi al server SMTP. Consultare la documentazione del proprio fornitore di posta elettronica per questi dettagli.

# Indirizzo

Indirizzo mail autorizzato ad inviare mail usando le configurazioni sopra specificate. Attenzione: non necessariamente coincide con l'indirizzo mail generico del GAS indicato nel campo "E-Mail".

# Abilita SSL

Spuntare questa casella per abilitare la connessione TLS col server SMTP. Consultare la documentazione del proprio fornitore di posta elettronica per questi dettagli.

# Denominazione

.

# IBAN

.

# Codice Azienda

.
