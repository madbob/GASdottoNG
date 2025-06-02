<x-larastrap::modal>
    <x-larastrap::iform method="POST" :action="route('modifiers.postfixorderattach', $modifier->id)">
        <input type="hidden" name="close-modal" value="1" class="skip-on-submit">

        <p>
            {{ __('movements.help.opened_orders_with_modifier') }}
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
