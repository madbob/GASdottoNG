@include('commons.textfield', [
    'obj' => $supplier,
    'name' => 'name',
    'label' => _i('Nome'),
    'mandatory' => true,
    'help_popover' => _i("Nome informale del fornitore"),
])

@include('commons.textfield', [
    'obj' => $supplier,
    'name' => 'business_name',
    'label' => _i('Ragione Sociale'),
    'help_popover' => _i("Nome completo del fornitore, da usare per fini contabili e fiscali. Se non specificato, verrà usato il Nome"),
])

@include('commons.textarea', [
    'obj' => $supplier,
    'name' => 'description',
    'label' => _i('Descrizione'),
    'maxlength' => 500,
    'help_popover' => _i("Breve descrizione leggibile da tutti gli utenti"),
])

@include('commons.textfield', [
    'obj' => $supplier,
    'name' => 'taxcode',
    'label' => _i('Codice Fiscale')
])

@include('commons.textfield', [
    'obj' => $supplier,
    'name' => 'vat',
    'label' => _i('Partita IVA')
])

@include('commons.textarea', [
    'obj' => $supplier,
    'name' => 'payment_method',
    'label' => _i('Modalità Pagamento'),
    'help_popover' => _i("Eventuale nota sulle modalità di pagamento al fornitore. Visibile solo agli utenti abilitati alla modifica del fornitore"),
])

@include('commons.textarea', [
    'obj' => $supplier,
    'name' => 'order_method',
    'label' => _i('Modalità Avanzamento Ordini'),
    'help_popover' => _i("Eventuale nota sulle modalità per sottoporre gli ordini al fornitore. Visibile solo agli utenti abilitati alla modifica del fornitore"),
])
