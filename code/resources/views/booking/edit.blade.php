<?php

$currency_symbol = defaultCurrency()->symbol;
$more_orders = ($aggregate->orders->count() > 1);
$grand_total = 0;
$has_shipping = $aggregate->canShip();
$enforced = $enforced ?? false;

$booking = $aggregate->bookingBy($user->id);
$all_products = $aggregate->orders->reduce(fn($carry, $o) => $carry->merge($o->products), new \Illuminate\Support\Collection());
$side_filter = $aggregate->orders->count() > aggregatesConvenienceLimit();

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
        'attributes' => ['type' => 'submit'],
    ]
];

?>

@include('booking.head', ['aggregate' => $aggregate])

<div class="row">
    @if($side_filter)
        <div class="col-2 d-none d-lg-block">
            @php

            $filter_categories = App\Category::whereIn('id', $all_products->pluck('category_id')->toArray())->orderBy('name', 'asc')->get();
            $sorted_filter_categories = [];

            foreach($filter_categories as $filtercat) {
                $parent = $filtercat->parent;

                if ($parent) {
                    if (isset($sorted_filter_categories[$parent->id]) === false) {
                        $sorted_filter_categories[$parent->id] = (object) [
                            'label' => $parent->printableName(),
                            'children' => [],
                        ];
                    }

                    $sorted_filter_categories[$parent->id]->children[$filtercat->id] = (object) [
                        'label' => $filtercat->printableName()
                    ];
                }
                else {
                    $sorted_filter_categories[$filtercat->id] = (object) [
                        'label' => $filtercat->printableName(),
                        'children' => [],
                    ];
                }
            }

            uasort($sorted_filter_categories, fn($a, $b) => $a->label <=> $b->label);

            @endphp

            @if(empty($sorted_filter_categories))
                <div class="alert alert-danger">
                    {{ _i('Non ci sono categorie da filtrare') }}
                </div>
            @else
                <div class="table-icons-legend" data-list-target=".booking-editor">
                    @foreach($sorted_filter_categories as $cat_id => $cat_data)
                        @if(empty($cat_data->children))
                            <a href="#" class="btn btn-info mb-1 d-block">{{ $cat_data->label }}<i class="bi-hidden-cat-{{ $cat_id }}"></i></a>
                        @else
                            <a href="#filterCat{{ $cat_id }}" class="btn btn-info mb-1 d-block" data-bs-toggle="collapse" aria-expanded="false">{{ $cat_data->label }}</a>
                            <div class="collapse" id="filterCat{{ $cat_id }}">
                                @foreach($cat_data->children as $sub_cat_id => $sub_cat_data)
                                    <a href="#" class="btn btn-light mb-1 d-block">{{ $sub_cat_data->label }}<i class="bi-hidden-cat-{{ $sub_cat_id }}"></i></a>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div class="col">
        <x-larastrap::iform :obj="$booking" classes="booking-form" method="PUT" :action="url('booking/' . $aggregate->id . '/user/' . $user->id)" data-dynamic-url="{{ route('booking.dynamics', ['aggregate_id' => $aggregate->id, 'user_id' => $user->id]) }}" :buttons="$form_buttons">
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

            @if($user->gas->hasFeature('restrict_booking_to_credit'))
                <input type="hidden" name="max-bookable" value="{{ $user->activeBalance() - $user->gas->restrict_booking_to_credit['limit'] }}" class="skip-on-submit">
            @endif

            <div class="d-flex flowbox mb-3">
                <div class="mainflow">
                    <input type="text" class="form-control table-text-filter" data-table-target=".booking-editor">
                </div>

                <div class="btn-group table-sorter" data-table-target=".booking-editor">
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
                </div>

                @include('commons.iconslegend', [
                    'class' => App\Product::class,
                    'target' => '.booking-editor',
                    'table_filter' => true,
                    'limit_to' => ['th'],
                    'contents' => $all_products,
                ])
            </div>

            @foreach($aggregate->orders as $order)
                <?php

                $o = $booking->getOrderBooking($order);
                $booking_total = $o->getValue('effective', false);
                $mods = $o->applyModifiers(null, false);

                ?>

                <div class="filter-master-block">
                    <div class="row mb-2">
                        <div class="col">
                            <h1>
                                {{ $order->supplier->printableName() }}
                                @include('commons.detailsbutton', ['obj' => $order->supplier])
                            </h1>
                        </div>
                    </div>

                    @if($order->isRunning() === false && $enforced === false)
                        @include('booking.partials.showtable', [
                            'o' => $o,
                            'order' => $order,
                            'mods' => $mods,
                        ])
                    @else
                        <?php

                        $notice = null;

                        if ($order->keep_open_packages != 'no' && $enforced === false) {
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

                        $categories = $products->getProductsCategories();
                        $contacts = $order->showableContacts();

                        ?>

                        @if(!is_null($notice))
                            <x-larastrap::suggestion>
                                <input type="hidden" name="limited" value="1">
                                {{ $notice }}
                            </x-larastrap::suggestion>
                        @endif

                        @if(!empty($order->long_comment))
                            <x-larastrap::suggestion>
                                {!! nl2br($order->long_comment) !!}
                            </x-larastrap::suggestion>
                        @endif

                        @if($contacts->isEmpty() === false)
                            <x-larastrap::suggestion>
                                {{ _i('Per segnalazioni relative a questo ordine si può contattare:') }}
                                <ul>
                                    @foreach($contacts as $contact)
                                        <li>{{ $contact->printableName() }} - {{ join(', ', App\Formatters\User::format($contact, ['email', 'phone', 'mobile'])) }}</li>
                                    @endforeach
                                </ul>
                            </x-larastrap::suggestion>
                        @endif

                        <table class="table table-striped user-booking-editor booking-editor" id="booking_{{ sanitizeId($order->id) }}" data-order-id="{{ $order->id }}">
                            <input type="hidden" name="booking_id" value="{{ $o->id }}" class="skip-on-submit">

                            <tbody>
                                @foreach($categories as $cat)
                                    <tr class="table-sorting-header d-none" data-sorting-category_name="{{ $cat->name }}">
                                        <td colspan="5">
                                            {{ $cat->name }}
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
                                                <span>{{ printablePrice($p ? $p->getValue('effective') : 0) }}</span> {{ $currency_symbol }}
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

                                @if($user->gas->hasFeature('restrict_booking_to_credit'))
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
                                    <th class="text-end">Totale:<br><span class="booking-total">{{ printablePrice($booking_total) }}</span> {{ $currency_symbol }}</th>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="row">
                            <div class="col-12 col-lg-4 offset-lg-8">
                                <x-larastrap::textarea name="notes" :label="_i('Note')" rows="3" :value="$o->notes" squeeze="false" :npostfix="sprintf('_%s', $order->id)" />
                            </div>
                        </div>
                    @endif
                </div>

                <?php $grand_total += $booking_total ?>
            @endforeach

            @if($more_orders)
                <table class="table">
                    <tfoot>
                        <tr>
                            <th>
                                <div class="float-end text-end">
                                    <strong>Totale Complessivo:<br><span class="all-bookings-total">{{ printablePrice($grand_total) }}</span> {{ $currency_symbol }}</strong>
                                </div>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            @endif

            <div class="fixed-bottom bg-success p-2 booking-bottom-helper">
                <div class="row justify-content-end align-items-center">
                    <div class="col-auto text-white">
                        {{ _i('Totale:') }} <span class="all-bookings-total">{{ printablePrice($grand_total) }}</span> {{ $currency_symbol }}
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-success" type="submit">{{ _i('Salva') }}</button>
                    </div>
                </div>
            </div>

            <hr>
        </x-larastrap::iform>
    </div>
</div>
