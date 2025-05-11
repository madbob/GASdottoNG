<x-larastrap::modal>
    <x-larastrap::iform method="POST" :action="route('orders.postfixmodifiers', $order->id)">
        <input type="hidden" name="close-modal" value="1" class="skip-on-submit">

        <p>
            {{ _i("L'ordine %s include modificatori il cui valore deve essere distribuito tra le prenotazioni, ed in fase di consegna tale valore è stato assegnato proporzionalmente, ma le quantità effettivamente consegnate non corrispondono a quelle prenotate e possono esserci delle discrepanze.", [$order->printableName()]) }}
        </p>
        <p>
            <?php

            $master_summary = $order->aggregate->reduxData();
            $broken = $order->unalignedModifiers($master_summary);

            ?>

            @foreach($broken as $b)
                {{ _i('%s - valore definito: %s / valore distribuito: %s', $b->shipped->name, printablePriceCurrency($b->pending->amount), printablePriceCurrency($b->shipped->amount)) }}<br>
            @endforeach
        </p>
        <p>
            {{ _i('Come vuoi procedere?') }}
        </p>

        <p>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="action" value="none" id="action-none" required>
                <label class="form-check-label" for="action-none">
                    {{ _i('Non fare nulla: lascia invariati i valori calcolati per i modificatori, ed i relativi addebiti ai singoli utenti, anche se la loro somma non corrisponde al valore finale atteso.') }}
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="action" value="adjust" id="action-adjust" required>
                <label class="form-check-label" for="action-adjust">
                    {{ _i('Ricalcola il valore dei modificatori e ridistribuiscili in base alle consegne effettive registrate. I pagamenti avvenuti usando il Credito Utente saranno alterati, ed i relativi saldi saranno conseguentemente aggiornati; i pagamenti avvenuti con altri metodi (contanti, bonifico...) resteranno inalterati, ed eventuali aggiustamenti saranno consolidati nel saldo corrente di ciascun utente.') }}
                </label>
            </div>
        </p>
    </x-larastrap::iform>
</x-larastrap::modal>
