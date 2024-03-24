<?php

if (!isset($duplicate)) {
    $duplicate = false;
}

?>

<input type="hidden" name="post-saved-function" value="afterProductChange" class="skip-on-submit">

<div class="row">
    <div class="col-md-6">
        @include('product.base-edit', ['product' => $product])

        <x-larastrap::text name="supplier_code" :label="_i('Codice Fornitore')" />
        <x-larastrap::check name="active" :label="_i('Ordinabile')" :pophelp="_i('Indica se il prodotto potrà essere ordinato o meno all\'interno dei nuovi ordini per il fornitore. Lo stato dei singoli prodotti potrà comunque essere cambiato da parte dei referenti anche all\'interno di un ordine aperto')" />

        @if($duplicate == false)
            @include('commons.modifications', ['obj' => $product, 'duplicate' => $duplicate])
        @endif
    </div>
    <div class="col-md-6">
        @include('commons.imagefield', [
            'obj' => $product,
            'name' => 'picture',
            'label' => _i('Foto'),
            'valuefrom' => 'picture_url'
        ])

        <x-larastrap::decimal name="portion_quantity" :label="_i('Pezzatura')" decimals="3" :pophelp="_i('Se diverso da 0, ogni unità si intende espressa come questa quantità. Esempio:<ul><li>unità di misura: chili</li><li>pezzatura: 0.3</li><li>prezzo unitario: 10 euro</li><li>quantità prenotata: 1 (che dunque si intende 1 pezzo da 0.3 chili)</li><li>costo: 1 x 0.3 x 10 = 3 euro</li></ul>Utile per gestire prodotti distribuiti in pezzi, prenotabili dagli utenti in numero di pezzi ma da ordinare e/o pagare presso il fornitore come quantità complessiva')" />
        <x-larastrap::integer name="package_size" :label="_i('Confezione')" :pophelp="_i('Se il prodotto viene distribuito in confezioni da N pezzi, indicare qui il valore di N. Gli ordini da sottoporre al fornitore dovranno essere espressi in numero di confezioni, ovvero numero di pezzi ordinati / numero di pezzi nella confezione. Se la quantità totale di pezzi ordinati non è un multiplo di questo numero il prodotto sarà marcato con una icona rossa nel pannello di riassunto dell\'ordine, da cui sarà possibile sistemare le quantità aggiungendo e togliendo ove opportuno.')" />
        <x-larastrap::decimal name="weight" :label="_i('Peso')" decimals="3" textappend="Kg" />
        <x-larastrap::integer name="multiple" :label="_i('Multiplo')" :pophelp="_i('Se diverso da 0, il prodotto è prenotabile solo per multipli di questo valore. Utile per prodotti pre-confezionati ma prenotabili individualmente. Da non confondere con l\'attributo Confezione')" />
        <x-larastrap::decimal name="min_quantity" :label="_i('Minimo')" decimals="3" :pophelp="_i('Se diverso da 0, il prodotto è prenotabile solo per una quantità superiore a quella indicata')" />
        <x-larastrap::decimal name="max_quantity" :label="_i('Massimo Consigliato')" decimals="3" :pophelp="_i('Se diverso da 0, se viene prenotata una quantità superiore di quella indicata viene mostrato un warning')" />
        <x-larastrap::decimal name="max_available" :label="_i('Disponibile')" decimals="3" :pophelp="_i('Se diverso da 0, questa è la quantità massima di prodotto che complessivamente può essere prenotata in un ordine. In fase di prenotazione gli utenti vedranno quanto è già stato sinora prenotato in tutto')" />
        <x-larastrap::decimal name="global_min" :label="_i('Minimo Complessivo')" decimals="3" :pophelp="_i('Se diverso da 0, questa è la quantità minima di prodotto che complessivamente può essere prenotata in un ordine. In fase di prenotazione gli utenti vedranno quanto è già stato sinora prenotato in tutto')" />

        @if($duplicate == false)
            <x-larastrap::field :label="_i('Varianti')" :pophelp="_i('Ogni prodotto può avere delle varianti, ad esempio la taglia o il colore per i capi di abbigliamento. In fase di prenotazione, gli utenti potranno indicare quantità diverse per ogni combinazione di varianti.')">
                @include('variant.editor', ['product' => $product])
            </x-larastrap::field>
        @endif
    </div>

    @if($duplicate)
        <div class="col-12">
            <x-larastrap::suggestion>
                {{ _i('Il duplicato avrà una copia delle varianti e dei modificatori del prodotto originario. Potranno essere eventualmente modificati dopo il salvataggio.') }}
            </x-larastrap::suggestion>
        </div>
    @endif
</div>
