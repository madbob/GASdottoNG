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

.

# Nome

.

# Cognome

.

# Telefono

.

# E-Mail

.

# Password

.

# Data di Nascita

.

# Codice Fiscale

.

# Persone in Famiglia

.

# Membro da

.

# Numero Tessera

.

# Ultimo Login

.

## .supplier-editor

# Nome

.

# Descrizione

.

# Codice Fiscale

.

# Partita IVA

.

# Sito Web

.

## .product-editor

# Nome

.

# Prezzo Unitario

Prezzo unitario per unità di misura. Può assumere un significato particolare quando viene attivata la "Pezzatura".

# Prezzo Trasporto

Prezzo di trasporto per singola unità.

# Categoria

.

# Unità di Misura

.

# Descrizione

.

# Ordinabile

.

# Pezzatura

Se diverso da 0, ogni unità si intende espressa come questa quantità. Esempio:

  * unità di misura: chili
  * pezzatura: 0.3
  * prezzo unitario: 10 euro
  * quantità prenotata: 1 (che dunque si intende "1 pezzo da 0.3 chili")
  * costo: 1 x 0.3 x 10 = 3 euro

Utile per gestire prodotti distribuiti in pezzi, prenotabili dagli utenti in numero di pezzi ma da ordinare e/o pagare presso il fornitore come quantità complessiva.

# Variabile

.

# Confezione

.

# Multiplo

Se diverso da 0, il prodotto è prenotabile solo per multipli di questo valore. Utile per prodotti pre-confezionati ma prenotabili individualmente.

# Minimo

Se diverso da 0, il prodotto è prenotabile solo per una quantità superiore a quella indicata.

# Massimo

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

## .gas-editor

# Nome

.

# E-Mail

.

# Descrizione

.

# Messaggio Homepage

.
