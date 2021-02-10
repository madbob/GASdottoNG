<?php

if (!isset($duplicate))
    $duplicate = false;

?>

<div class="row">
    <div class="col-md-6">
        @include('product.base-edit', ['product' => $product])

        @include('commons.textfield', [
            'obj' => $product,
            'name' => 'supplier_code',
            'label' => _i('Codice Fornitore')
        ])

        @include('commons.boolfield', [
            'obj' => $product,
            'name' => 'active',
            'label' => _i('Ordinabile'),
            'help_popover' => _i("Indica se il prodotto potrà essere ordinato o meno all'interno dei nuovi ordini per il fornitore. Lo stato dei singoli prodotti potrà comunque essere cambiato da parte dei referenti anche all'interno di un ordine aperto"),
        ])

        @include('commons.modifications', ['obj' => $product])
    </div>
    <div class="col-md-6">
        @include('commons.imagefield', [
            'obj' => $product,
            'name' => 'picture',
            'label' => _i('Foto'),
            'valuefrom' => 'picture_url'
        ])

        @include('commons.decimalfield', [
            'obj' => $product,
            'name' => 'portion_quantity',
            'label' => _i('Pezzatura'),
            'decimals' => 3,
            'help_popover' => _i("Se diverso da 0, ogni unità si intende espressa come questa quantità. Esempio:<ul><li>unità di misura: chili</li><li>pezzatura: 0.3</li><li>prezzo unitario: 10 euro</li><li>quantità prenotata: 1 (che dunque si intende \"1 pezzo da 0.3 chili\")</li><li>costo: 1 x 0.3 x 10 = 3 euro</li></ul>Utile per gestire prodotti distribuiti in pezzi, prenotabili dagli utenti in numero di pezzi ma da ordinare e/o pagare presso il fornitore come quantità complessiva"),
        ])

        @include('commons.boolfield', [
            'obj' => $product,
            'name' => 'variable',
            'label' => _i('Variabile'),
            'help_popover' => _i("Un prodotto variabile viene ordinato in pezzi la cui dimensione definitiva non è esattamente nota al momento della prenotazione. I prodotti così identificati attiveranno un ulteriore pannello in fase di consegna, per calcolarne il prezzo in funzione della pezzatura (vedi informazioni specifiche). Da usare per prodotti consegnati in pezzi non sempre uniformi, come il formaggio o la carne, che sono pesati al momento della consegna."),
        ])

        @include('commons.decimalfield', [
            'obj' => $product,
            'name' => 'package_size',
            'label' => _i('Confezione'),
            'decimals' => 3,
            'help_popover' => _i("Se il prodotto viene distribuito in confezioni da N pezzi, indicare qui il valore di N. Gli ordini da sottoporre al fornitore dovranno essere espressi in numero di confezioni, ovvero numero di pezzi ordinati / numero di pezzi nella confezione. Se la quantità totale di pezzi ordinati non è un multiplo di questo numero il prodotto sarà marcato con una icona rossa nel pannello di riassunto dell'ordine, da cui sarà possibile sistemare le quantità aggiungendo e togliendo ove opportuno."),
        ])

        @include('commons.decimalfield', [
            'obj' => $product,
            'name' => 'weight',
            'label' => _i('Peso'),
            'decimals' => 4,
            'postlabel' => 'Kg'
        ])

        @include('commons.decimalfield', [
            'obj' => $product,
            'name' => 'multiple',
            'label' => _i('Multiplo'),
            'decimals' => 3,
            'help_popover' => _i("Se diverso da 0, il prodotto è prenotabile solo per multipli di questo valore. Utile per prodotti pre-confezionati ma prenotabili individualmente. Da non confondere con l'attributo \"Confezione\""),
        ])

        @include('commons.decimalfield', [
            'obj' => $product,
            'name' => 'min_quantity',
            'label' => _i('Minimo'),
            'decimals' => 3,
            'help_popover' => _i("Se diverso da 0, il prodotto è prenotabile solo per una quantità superiore a quella indicata"),
        ])

        @include('commons.decimalfield', [
            'obj' => $product,
            'name' => 'max_quantity',
            'label' => _i('Massimo Consigliato'),
            'decimals' => 3,
            'help_popover' => _i("Se diverso da 0, se viene prenotata una quantità superiore di quella indicata viene mostrato un warning"),
        ])

        @include('commons.decimalfield', [
            'obj' => $product,
            'name' => 'max_available',
            'label' => _i('Disponibile'),
            'decimals' => 3,
            'help_popover' => _i("Se diverso da 0, questa è la quantità massima di prodotto che complessivamente può essere prenotata in un ordine. In fase di prenotazione gli utenti vedranno quanto è già stato sinora prenotato in tutto"),
        ])

        @include('product.variantseditor', ['product' => $product, 'duplicate' => $duplicate])
    </div>
</div>
