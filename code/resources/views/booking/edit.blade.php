<?php

$more_orders = ($aggregate->orders->count() > 1);
$grand_total = 0;
$has_shipping = $aggregate->canShip();
$enforced = $enforced ?? false;

$form_buttons = [
    [
        'label' => _i('Annulla Prenotazione'),
        'color' => 'danger',
        'classes' => ['delete-booking'],
    ],
    [
        'label' => _i('Salva'),
        'type' => 'submit',
        'color' => 'success',
        'classes' => ['saving-button'],
    ]
];

?>

@include('booking.head', ['aggregate' => $aggregate])

<x-larastrap::iform classes="booking-form" method="PUT" :action="url('booking/' . $aggregate->id . '/user/' . $user->id)" data-dynamic-url="{{ route('booking.dynamics', ['aggregate_id' => $aggregate->id, 'user_id' => $user->id]) }}" :buttons="$form_buttons">
    <input type="hidden" name="post-saved-function" value="afterBookingSaved" class="skip-on-submit">

    <!--
        Questo serve ad interagire col pannello dell'ordine, nel caso in cui
        apra il modale di modifica della prenotazione da lì (e.g. in fase di
        revisione dei fix all'ordine)
    -->
    <input type="hidden" name="reload-portion" value=".order-summary-wrapper" class="skip-on-submit" />
    <input type="hidden" name="reload-portion" value=".order-fixes-modal" class="skip-on-submit" />

    <input type="hidden" name="close-modal" value="1" class="skip-on-submit">
    <input type="hidden" name="action" value="booked">

    @if($user->gas->restrict_booking_to_credit)
        <input type="hidden" name="max-bookable" value="{{ $user->activeBalance() }}" class="skip-on-submit">
    @endif

    @foreach($aggregate->orders as $order)
        @if($more_orders)
            <hr/>
            <h3>{{ $order->printableName() }}</h3>
        @endif

        <?php

        $o = $order->userBooking($user->id);
        $booking_total = $o->getValue('effective', false);

        ?>

        @if($order->isRunning() == false && $enforced == false)
            @include('booking.partials.showtable', [
                'o' => $o,
                'order' => $order,
            ])
        @else
            <?php

            $notice = null;
            $mods = $o->applyModifiers(null, false);

            if ($order->keep_open_packages != 'no' && $enforced == false) {
                if ($order->status == 'open') {
                    $products = $order->products()->with(['category', 'measure'])->sorted()->get();
                }
                else {
                    $products = $order->pendingPackages();
                    $notice = _i("Attenzione: quest'ordine è chiuso, ma è possibile prenotare ancora alcuni prodotti per completare le confezioni da consegnare.");
                }
            }
            else {
                $products = $order->products()->with(['category', 'measure'])->sorted()->get();
            }

            $categories = $products->pluck('category_id')->toArray();
            $categories = array_unique($categories);
            $categories = App\Category::whereIn('id', $categories)->orderBy('name', 'asc')->get()->pluck('name')->toArray();

            $contacts = $order->showableContacts();

            ?>

            <div class="row mb-2">
                <div class="col-12 col-lg-4">
                    @include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => _i('Fornitore')])
                </div>
            </div>

            @if(!is_null($notice))
                <div class="alert alert-info">
                    <input type="hidden" name="limited" value="1">
                    {{ $notice }}
                </div>
                <br>
            @endif

            @if(!empty($order->long_comment))
                <div class="alert alert-info">
                    {!! nl2br($order->long_comment) !!}
                </div>
                <br>
            @endif

            @if($contacts->isEmpty() == false)
                <div class="alert alert-info">
                    {{ _i('Per segnalazioni relative a questo ordine si può contattare:') }}
                    <ul>
                        @foreach($contacts as $contact)
                            <li>{{ $contact->printableName() }} - {{ join(', ', App\Formatters\User::format($contact, ['email', 'phone', 'mobile'])) }}</li>
                        @endforeach
                    </ul>
                </div>
                <br>
            @endif

            <div class="d-none d-md-flex flowbox mb-1">
                <div class="mainflow">
                    <input type="text" class="form-control table-text-filter" data-table-target="#booking_{{ sanitizeId($order->id) }}">
                </div>

                <div class="btn-group table-sorter" data-table-target="#booking_{{ sanitizeId($order->id) }}">
                    <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        {{ _i('Ordina Per') }} <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="#" class="dropdown-item" data-sort-by="sorting" data-numeric-sorting="true">{{ _i('Ordinamento Manuale') }}</a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item" data-sort-by="name">{{ _i('Nome') }}</a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item" data-sort-by="category_name">{{ _i('Categoria') }}</a>
                        </li>
                    </ul>
                </div>&nbsp;

                @include('commons.iconslegend', [
                    'class' => 'Product',
                    'target' => '#booking_' . sanitizeId($order->id),
                    'table_filter' => true,
                    'limit_to' => ['th'],
                    'contents' => $products
                ])
            </div>

            <table class="table table-striped booking-editor" id="booking_{{ sanitizeId($order->id) }}">
                <input type="hidden" name="booking_id" value="{{ $o->id }}" class="skip-on-submit">

                <thead class="d-none d-md-table-header-group border-0">
                    <tr>
                        <th width="40%"></th>
                        <th width="30%"></th>
                        <th width="15%"></th>
                        <th width="10%"></th>
                        <th width="5%"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                        <tr class="table-sorting-header d-none" data-sorting-category_name="{{ $cat }}">
                            <td colspan="5">
                                {{ $cat }}
                            </td>
                        </tr>
                    @endforeach

                    @foreach($products as $product)
                        <?php $p = $o->getBooked($product->id) ?>

                        <tr class="booking-product" data-sorting-name="{{ $product->name }}" data-sorting-sorting="{{ $product->sorting }}" data-sorting-category_name="{{ $product->category_name }}">
                            <td>
                                @include('commons.staticobjfield', ['squeeze' => true, 'target_obj' => $product, 'extra_class' => 'text-filterable-cell'])

                                <div class="hidden">
                                    @foreach($product->icons() as $icon)
                                        <i class="bi-{{ $icon }}"></i>
                                    @endforeach
                                </div>
                            </td>

                            <td>
                                @include('booking.quantityselectrow', ['product' => $product, 'order' => $order, 'populate' => true])
                            </td>

                            <td>
                                <?php $details = $product->printableDetails($order) ?>
                                @if(filled($details))
                                    <label class="static-label"><small>{!! $details !!}</small></label>
                                @endif
                            </td>

                            <td class="text-end">
                                @include('booking.pricerow', ['product' => $product, 'booked' => $p, 'order' => $order, 'populate' => true])
                            </td>

                            <td>
                                <label class="static-label booking-product-price float-end">
                                    <span>{{ printablePrice($p ? $p->getValue('effective') : 0) }}</span> {{ $currentgas->currency }}
                                </label>
                            </td>
                        </tr>
                    @endforeach

                    @foreach($mods as $mod_value)
                        @include('delivery.modifierrow', [
                            'mod_value' => $mod_value,
                            'skip_cells' => 3
                        ])
                    @endforeach

                    @include('delivery.modifierrow', [
                        'mod_value' => null,
                        'skip_cells' => 3
                    ])

                    @if($user->gas->restrict_booking_to_credit)
                        <tr class="do-not-sort">
                            <td><label class="static-label">{{ _i('Credito Disponibile') }}</label></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td><label class="static-label float-end">{{ printablePriceCurrency($user->activeBalance()) }}</label></td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th class="text-end">Totale: <span class="booking-total">{{ printablePrice($booking_total) }}</span> {{ $currentgas->currency }}</th>
                    </tr>
                </tfoot>
            </table>

            <div class="row">
                <div class="col-12 col-lg-4 offset-lg-8">
                    <x-larastrap::textarea name="notes" :label="_i('Note')" rows="3" :value="$o->notes" squeeze="false" :npostfix="sprintf('_%s', $order->id)" />
                </div>
            </div>
        @endif

        <?php $grand_total += $booking_total ?>
    @endforeach

    @if($more_orders)
        <table class="table">
            <tfoot>
                <tr>
                    <th>
                        <div class="float-end">
                            <strong>Totale Complessivo: <span class="all-bookings-total">{{ printablePrice($grand_total) }}</span> {{ $currentgas->currency }}</strong>
                        </div>
                    </th>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="fixed-bottom bg-success p-2 booking-bottom-helper">
        <div class="row justify-content-end align-items-center">
            <div class="col-auto text-white">
                Totale: <span class="all-bookings-total">{{ printablePrice($grand_total) }}</span> {{ $currentgas->currency }}
            </div>
            <div class="col-auto">
                <button class="saving-button btn btn-success">Salva</button>
            </div>
        </div>
    </div>
</x-larastrap::iform>
