<div class="well variants-editor" id="variants_editor_{{ sanitizeId($product->id) }}" data-reload-url="{{ route('variants.show', $product->id) }}">
    <div class="row">
        <div class="col-md-12">
            @if($duplicate)
                @if($product->variants()->count() != 0)
                    {{ _i('Il prodotto duplicato avrà una copia delle varianti del prodotto originario. Potranno essere modificate dopo il salvataggio del duplicato.') }}
                @else
                    {{ _i('Non ci sono varianti da duplicare') }}
                @endif
            @else
                <a class="btn btn-warning async-modal" data-target-url="{{ route('variants.create', ['product_id' => $product->id]) }}">{{ _i('Crea Nuova Variante') }}</a>

                @if($product->variantCombos->isEmpty() == false)
                    <button class="btn btn-default async-modal" data-target-url="{{ route('variants.matrix', $product->id) }}">
                        Modifica Matrice Varianti <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span>
                    </button>
                @endif

                @include('commons.helpbutton', [
                    'extra_class' => 'pull-right',
                    'placement' => 'left',
                    'help_popover' => _i("Ogni prodotto può avere delle varianti, ad esempio la taglia o il colore per i capi di abbigliamento. In fase di prenotazione, gli utenti potranno indicare quantità diverse per ogni combinazione di varianti.")
                ])
            @endif
        </div>
    </div>

    @if($product->variants->isEmpty() == false)
        <br>
        <table class="table">
            @foreach($product->variants as $variant)
                <tr data-variant-id="{{ $variant->id }}">
                    <td width="30%">{{ $variant->name }}</td>
                    <td width="50%">{{ $variant->printableValues() }}</td>

                    <td width="20%">
                        @if($duplicate == false)
                            <a class="btn btn-warning async-modal" data-target-url="{{ route('variants.edit', $variant->id) }}"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a>
                            <div class="btn btn-danger delete-variant"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
</div>
