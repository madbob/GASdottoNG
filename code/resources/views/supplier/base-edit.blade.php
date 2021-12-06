<x-larastrap::text name="name" :label="_i('Nome')" :pophelp="_i('Nome informale del fornitore')" required />
<x-larastrap::text name="business_name" :label="_i('Ragione Sociale')" :pophelp="_i('Nome completo del fornitore, da usare per fini contabili e fiscali. Se non specificato, verrà usato il Nome')" />
<x-larastrap::textarea name="description" :label="_i('Descrizione')" :pophelp="_i('Breve descrizione leggibile da tutti gli utenti')" maxlength="500" />
<x-larastrap::text name="taxcode" :label="_i('Codice Fiscale')" />
<x-larastrap::text name="vat" :label="_i('Partita IVA')" />
<x-larastrap::textarea name="payment_method" :label="_i('Modalità Pagamento')" :pophelp="_i('Eventuale nota sulle modalità di pagamento al fornitore. Visibile solo agli utenti abilitati alla modifica del fornitore')" />
<x-larastrap::textarea name="order_method" :label="_i('Modalità Avanzamento Ordini')" :pophelp="_i('Eventuale nota sulle modalità per sottoporre gli ordini al fornitore. Visibile solo agli utenti abilitati alla modifica del fornitore')" />
