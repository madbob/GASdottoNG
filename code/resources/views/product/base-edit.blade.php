@include('commons.textfield', [
    'obj' => $product,
    'name' => 'name',
    'label' => _i('Nome'),
    'mandatory' => true
])

@include('commons.decimalfield', [
    'obj' => $product,
    'name' => 'price',
    'label' => _i('Prezzo Unitario'),
    'is_price' => true,
    'mandatory' => true,
    'help_popover' => _i("Prezzo unitario per unità di misura. Si intende IVA inclusa, per maggiori dettagli si veda il campo \"Aliquota IVA\". Può assumere un significato particolare quando viene attivata la \"Pezzatura\""),
])

@include('commons.decimalfield', [
    'obj' => $product,
    'name' => 'transport',
    'label' => _i('Prezzo Trasporto'),
    'is_price' => true,
    'help_popover' => _i("Prezzo di trasporto per singola unità. Attenzione: da non confondere con le Spese di Trasporto applicabili globalmente su un ordine"),
])

@include('commons.percentagefield', [
    'obj' => $product,
    'name' => 'discount',
    'label' => _i('Sconto')
])

@include('commons.selectobjfield', [
    'obj' => $product,
    'name' => 'category_id',
    'objects' => App\Category::orderBy('name', 'asc')->where('parent_id', '=', null)->get(),
    'label' => _i('Categoria'),
    'required' => ($product == null)
])

@include('commons.selectobjfield', [
    'obj' => $product,
    'name' => 'measure_id',
    'objects' => App\Measure::orderBy('name', 'asc')->get(),
    'extra_class' => 'measure-selector',
    'label' => _i('Unità di Misura'),
    'datafields' => ['discrete'],
    'required' => ($product == null),
    'help_text' => _i('Hai selezionato una unità di misura "discreta": per questo prodotto possono essere usate solo quantità intere.'),
    'help_block_class' => 'hidden discrete_unit_alert',
    'help_popover' => _i("Unità di misura assegnata al prodotto. Attenzione: può influenzare l'abilitazione di alcune variabili del prodotto, si veda il parametro \"Unità Discreta\" nel pannello di amministrazione delle unità di misura (acessibile solo agli utenti abilitati)"),
])

@include('commons.textarea', ['obj' => $product, 'name' => 'description', 'label' => _i('Descrizione')])

@include('commons.selectobjfield', [
    'obj' => $product,
    'name' => 'vat_rate_id',
    'objects' => App\VatRate::orderBy('name', 'asc')->get(),
    'label' => _i('Aliquota IVA'),
    'extra_selection' => [
        '0' => _i('Nessuna')
    ],
    'help_popover' => _i("Le aliquote esistenti possono essere configurate nel pannello Configurazioni"),
])
