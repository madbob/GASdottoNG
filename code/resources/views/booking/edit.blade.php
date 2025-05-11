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
        'tlabel' => 'orders.booking.void',
        'color' => 'danger',
        'classes' => ['delete-booking'],
    ],
    [
        'tlabel' => 'generic.save',
        'type' => 'submit',
        'color' => 'success',
        'attributes' => ['type' => 'submit'],
    ]
];

?>

@include('booking.head', ['aggregate' => $aggregate, 'editable' => true])

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
                    {{ __('orders.help.no_categories') }}
                </div>
            @else
                <div class="table-icons-legend" data-list-target=".booking-editor">
                    <a href="#" class="btn btn-info mb-1 d-block show-all">Vedi tutti</i></a>

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
            <input type="hidden" name="pre-saved-function" value="beforeBookingSaved" class="skip-on-submit">
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
                        {{ __('generic.sort_by') }} <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="#" class="dropdown-item" data-sort-by="sorting" data-numeric-sorting="true">{{ __('generic.sortings.manual') }}</a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item" data-sort-by="name">{{ __('generic.name') }}</a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item" data-sort-by="category_name">{{ __('generic.category') }}</a>
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
                                $notice = __('orders.help.pending_packages_notice');
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
                                {{ __('orders.help.contacts_notice') }}
                                <ul>
                                    @foreach($contacts as $contact)
                                        <li>{{ $contact->printableName() }} - {{ join(', ', App\Formatters\User::format($contact, ['email', 'phone', 'mobile'])) }}</li>
                                    @endforeach
                                </ul>
                            </x-larastrap::suggestion>
                        @endif

                        <table class="table align-middle table-striped user-booking-editor booking-editor" id="booking_{{ sanitizeId($order->id) }}" data-order-id="{{ $order->id }}">
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
                                                <small>{!! $details !!}</small>
                                            @endif
                                        </td>

                                        <td class="text-end">
                                            @include('booking.pricerow', ['product' => $product, 'booked' => $p, 'order' => $order, 'populate' => true])
                                        </td>

                                        <td class="text-end">
                                            <span class="booking-product-price">{{ printablePrice($p ? $p->getValue('effective') : 0) }}</span> {{ $currency_symbol }}
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
                                        <td>{{ __('movements.available_credit') }}</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td class="text-end">{{ printablePriceCurrency($user->activeBalance()) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-end fw-bold">{{ __('orders.totals.total') }}:<br><span class="booking-total">{{ printablePrice($booking_total) }}</span> {{ $currency_symbol }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="row">
                            <div class="col-12 col-lg-4 offset-lg-8">
                                <x-larastrap::textarea name="notes" tlabel="generic.notes" rows="3" :value="$o->notes" squeeze="false" :npostfix="sprintf('_%s', $order->id)" />
                            </div>
                        </div>
                    @endif
                </div>

                <?php $grand_total += $booking_total ?>
            @endforeach

            @if($more_orders)
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-end">
                                {{ __('orders.totals.complete') }}:<br><span class="all-bookings-total">{{ printablePrice($grand_total) }}</span> {{ $currency_symbol }}
                            </th>
                        </tr>
                    </thead>
                </table>
            @endif

            <div class="fixed-bottom bg-success p-2 booking-bottom-helper">
                <div class="row justify-content-end align-items-center">
                    <div class="col-auto text-white">
                        {{ __('orders.totals.total') }}: <span class="all-bookings-total">{{ printablePrice($grand_total) }}</span> {{ $currency_symbol }}
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-success" type="submit">{{ __('generic.save') }}</button>
                    </div>
                </div>
            </div>

            <hr>
        </x-larastrap::iform>
    </div>
</div>
