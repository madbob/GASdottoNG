<x-larastrap::modal :title="_i('Attenzione')">
    <x-larastrap::iform method="POST" :action="route('modifiers.postfixorderattach', $modifier->id)">
        <input type="hidden" name="close-modal" value="1" class="skip-on-submit">

        <p>
            {{ _i("Ci sono ordini non ancora consegnati ed archiviati per questo fornitore, che non hanno attivato il modificatore appena modificato. Seleziona gli ordini per i quali vuoi attivare questo modificatore (o clicca 'Chiudi' per non attivarlo su nessuno).") }}
        </p>
        <p>
            @foreach($modifier->target->active_orders as $order)
                <?php

                $active_modifier = $order->modifiers()->where('modifier_type_id', $modifier->modifier_type_id)->get()->reduce(function($active, $m) {
                    return $active || $m->active;
                }, false);

                ?>

                @if($active_modifier == false)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activated[]" value="{{ $order->id }}" id="activate-order-{{ sanitizeId($order->id) }}">
                        <label class="form-check-label" for="activate-order-{{ sanitizeId($order->id) }}">
                            {{ $order->printableName() }}
                        </label>
                    </div>
                @endif
            @endforeach
        </p>
    </x-larastrap::iform>
</x-larastrap::modal>
