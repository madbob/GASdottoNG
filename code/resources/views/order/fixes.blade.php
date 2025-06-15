<x-larastrap::modal classes="order-fixes-modal" data-reload-url="{{ url('orders/fixes/' . $order->id . '/' . $product->id) }}">
    @php
    $bookings = $order->bookings->sortByUserName();
    $measure = $product->printableMeasure();
    @endphp

    <x-larastrap::iform method="POST" :action="url('orders/fixes/' . $order->id)">
        <input type="hidden" name="close-modal" value="1" class="skip-on-submit" />
        <input type="hidden" name="reload-portion" value=".order-summary-wrapper" class="skip-on-submit" />

        <input type="hidden" name="product" value="{{ $product->id }}" />

        @if($product->package_size != 0)
            <p>
                {{ __('texts.products.package_size') }}: {{ $product->package_size }} {{ $product->printableMeasure(true) }}
            </p>

            <hr/>
        @endif

        @if($product->global_min != 0)
            <p>
                {{ __('texts.products.global_min') }}: {{ $product->global_min }} {{ $product->measure->name }}
            </p>

            <hr/>
        @endif

        <x-larastrap::textarea name="notes" tlabel="orders.notes_to_supplier" rows="5" maxlength="500" :value="$product->pivot->notes" />

        <hr/>

        @if($bookings->isEmpty())
            <x-larastrap::suggestion>
                {{ __('texts.orders.help.manual_fixes_explain') }}
            </x-larastrap::suggestion>
        @else
            <div class="d-flex flowbox mb-3">
                <div class="mainflow">
                    <input type="text" class="form-control table-text-filter" data-table-target=".fixes-table">
                </div>

                <div class="btn-group table-sorter" data-table-target=".fixes-table">
                    <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        {{ __('texts.generic.sort_by') }} <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="#" class="dropdown-item" data-sort-by="name">{{ __('texts.generic.notes') }}</a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item" data-sort-by="date">{{ __('texts.generic.date') }}</a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item" data-sort-by="quantity">{{ __('texts.orders.quantities.booked') }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            @if($product->variants()->count() == 0)
                <table class="table table-striped fixes-table">
                    <thead>
                        <tr>
                            <th scope="col" width="35%">{{ __('texts.user.name') }}</th>
                            <th scope="col" width="35%">{{ __('texts.generic.date') }}</th>
                            <th scope="col" width="30%">{{ __('texts.orders.quantities.booked') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $po)
                            @php

                            if ($po->user->isFriend()) {
                                $masteruser = $po->user->parent;
                            }
                            else {
                                $masteruser = $po->user;
                            }

                            $row_date = $po->getBooked($product)?->created_at;
                            $row_quantity = $po->getBookedQuantity($product);

                            @endphp

                            <tr data-sorting-name="{{ $masteruser->printableName }}" data-sorting-date="{{ $row_date ?: 99999 }}" data-sorting-quantity="{{ $row_quantity ? $row_quantity : 99999 }}">
                                <td>
                                    <label class="text-filterable-cell">
                                        {{ $masteruser->printableName() }}
                                        @if($po->user->isFriend())
                                            <br><small>Amico: {{ $po->user->printableName() }}</small>
                                        @endif
                                    </label>
                                </td>
                                <td>
                                    <label>{{ printableDate($row_date) }}</label>
                                </td>
                                <td>
                                    <input type="hidden" name="booking[]" value="{{ $po->id }}" />

                                    <div class="input-group">
                                        <input type="text" class="form-control number" name="quantity[]" value="{{ $row_quantity }}" />
                                        <div class="input-group-text">{{ $measure }}</div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <x-larastrap::tabs>
                    @foreach($product->variant_combos as $index => $combo)
                        <x-larastrap::tabpane :label="$combo->printableShortName()" :active="$index == 0" icon="bi-zoom-in">
                            <table class="table table-striped fixes-table">
                                <thead>
                                    <tr>
                                        <th scope="col" width="35%">{{ __('texts.user.name') }}</th>
                                        <th scope="col" width="35%">{{ __('texts.generic.date') }}</th>
                                        <th scope="col" width="30%">{{ __('texts.orders.quantities.booked') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookings as $po)
                                        @php

                                        if ($po->user->isFriend()) {
                                            $masteruser = $po->user->parent;
                                        }
                                        else {
                                            $masteruser = $po->user;
                                        }

                                        $row_date = $po->getBooked($product)?->created_at;
                                        $row_quantity = $po->getBookedQuantity($combo);

                                        @endphp

                                        <tr data-sorting-name="{{ $masteruser->printableName }}" data-sorting-date="{{ $row_date ?: 99999 }}" data-sorting-quantity="{{ $row_quantity ? $row_quantity : 99999 }}">
                                            <td>
                                                <label class="text-filterable-cell">
                                                    {{ $masteruser->printableName() }}
                                                    @if($po->user->isFriend())
                                                        <br><small>Amico: {{ $po->user->printableName() }}</small>
                                                    @endif
                                                </label>
                                            </td>
                                            <td>
                                                <label>{{ printableDate($po->getBooked($product)?->created_at) }}</label>
                                            </td>
                                            <td>
                                                {{ sprintf('%s %s', $po->getBookedQuantity($combo), $measure) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </x-larastrap::tabpane>
                    @endforeach
                </x-larastrap::tabs>
            @endif

            @if($order->aggregate->gas()->count() > 1 && $currentuser->can('gas.multi', $currentuser->gas))
                <table class="table table-striped mt-3">
                    @foreach($order->aggregate->gas as $other_gas)
                        @if($other_gas->id != $currentuser->gas->id)
                            <tr>
                                <td>
                                    <label>{{ __('texts.generic.named_multigas', ['name' => $other_gas->name]) }}</label>
                                </td>
                                <td class="text-end">
                                    <label>
                                        <?php

                                        App::make('GlobalScopeHub')->setGas($other_gas->id);
                                        $summary = $order->reduxData();
                                        $other_gas_quantity = $summary->products[$product->id]->quantity ?? 0;

                                        ?>

                                        {{ sprintf('%s %s', $other_gas_quantity, $measure) }}
                                    </label>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            @endif
        @endif
    </x-larastrap::iform>
</x-larastrap::modal>
