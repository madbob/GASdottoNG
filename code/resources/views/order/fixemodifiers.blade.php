<x-larastrap::modal>
    <x-larastrap::iform method="POST" :action="route('orders.postfixmodifiers', $order->id)">
        <input type="hidden" name="close-modal" value="1" class="skip-on-submit">

        <p>
            {{ __('texts.orders.help.modifiers_require_redistribution', ['name' => $order->printableName()]) }}
        </p>
        <p>
            @php

            $master_summary = $order->aggregate->reduxData();
            $broken = $order->unalignedModifiers($master_summary);

            @endphp

            @foreach($broken as $b)
                {{ __('texts.orders.modifiers_redistribution_summary', [
                    'name' => $b->shipped->name,
                    'defvalue' => printablePriceCurrency($b->pending->amount),
                    'disvalue' => printablePriceCurrency($b->shipped->amount)
                ]) }}<br>
            @endforeach
        </p>
        <p>
            {{ __('texts.generic.how_to_proceed') }}
        </p>

        <p>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="action" value="none" id="action-none" required>
                <label class="form-check-label" for="action-none">
                    {{ __('texts.orders.modifiers_redistribution.keep') }}
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="action" value="adjust" id="action-adjust" required>
                <label class="form-check-label" for="action-adjust">
                    {{ __('texts.orders.modifiers_redistribution.recalculate') }}
                </label>
            </div>
        </p>
    </x-larastrap::iform>
</x-larastrap::modal>
