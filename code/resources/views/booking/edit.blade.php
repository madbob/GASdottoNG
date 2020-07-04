<?php

$more_orders = ($aggregate->orders->count() > 1);
$grand_total = 0;
$has_shipping = $aggregate->canShip();
$enforced = $enforced ?? false;

?>

@include('booking.head', ['aggregate' => $aggregate])

<form class="form-horizontal inner-form booking-form" method="PUT" action="{{ url('booking/' . $aggregate->id . '/user/' . $user->id) }}" data-dynamic-url="{{ route('booking.dynamics', ['aggregate_id' => $aggregate->id, 'user_id' => $user->id]) }}">
    <input type="hidden" name="post-saved-function" value="afterBookingSaved" class="skip-on-submit">

    @if($user->gas->restrict_booking_to_credit)
        <input type="hidden" name="max-bookable" value="{{ $user->activeBalance() }}" class="skip-on-submit">
    @endif

    @foreach($aggregate->orders as $order)
        @if($more_orders)
            <h3>{{ $order->printableName() }}</h3>
        @endif

        <?php

        $notice = null;

        $o = $order->userBooking($user->id);
        $mods = $o->applyModifiers(null, false);

        if ($order->keep_open_packages && $enforced == false) {
            if ($order->status == 'open') {
                $products = $order->products;
            }
            else {
                $products = $order->pendingPackages();
                $notice = _i("Attenzione: quest'ordine è chiuso, ma è possibile prenotare ancora alcuni prodotti per completare le confezioni da consegnare.");
            }
        }
        else {
            $products = $order->products;
        }

        $categories = $products->pluck('category_id')->toArray();
        $categories = array_unique($categories);
        $categories = App\Category::whereIn('id', $categories)->orderBy('name', 'asc')->get()->pluck('name')->toArray();

        ?>

        @if(!is_null($notice))
            <div class="alert alert-info">
                <input type="hidden" name="limited" value="1">
                {{ $notice }}
            </div>
            <br>
        @endif

        <div class="flowbox">
            <div class="mainflow hidden-md">
                <input type="text" class="form-control table-text-filter" data-list-target="#booking_{{ sanitizeId($order->id) }}">
            </div>

            <div class="btn-group table-sorter" data-table-target="#booking_{{ sanitizeId($order->id) }}">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    {{ _i('Ordina Per') }} <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a href="#" data-sort-by="name">{{ _i('Nome') }}</a>
                        <a href="#" data-sort-by="category_name">{{ _i('Categoria') }}</a>
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

            <thead>
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
                    <tr class="table-sorting-header hidden" data-sorting-category_name="{{ $cat }}">
                        <td colspan="5">
                            {{ $cat }}
                        </td>
                    </tr>
                @endforeach

                @foreach($products as $product)
                    <?php $p = $o->getBooked($product->id) ?>

                    <tr class="booking-product" data-sorting-name="{{ $product->name }}" data-sorting-category_name="{{ $product->category_name }}">
                        <td>
                            @include('commons.staticobjfield', ['squeeze' => true, 'target_obj' => $product, 'extra_class' => 'text-filterable-cell'])

                            <div class="hidden">
                                @foreach($product->icons() as $icon)
                                    <span class="glyphicon glyphicon-{{ $icon }}" aria-hidden="true"></span>
                                @endforeach
                            </div>
                        </td>

                        <td>
                            @include('booking.quantityselectrow', ['product' => $product, 'order' => $order, 'populate' => true])
                        </td>

                        <td>
                            <label class="static-label"><small>{{ $product->printableDetails($order) }}</small></label>
                        </td>

                        <td class="text-right">
                            <label class="static-label">
                                <small>{!! $product->printablePrice($order) !!}</small>
                                <div class="modifiers">
                                    @if($p)
                                        @foreach($p->modifiedValues as $mod_value)
                                            <br>
                                            <small>{{ $mod_value->modifier->modifierType->name }}: {{ printablePriceCurrency($mod_value->amount) }}</small>
                                        @endforeach
                                    @endif
                                </div>
                            </label>
                        </td>

                        <td>
                            <label class="static-label booking-product-price pull-right">
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

                @if($user->gas->restrict_booking_to_credit)
                    <tr class="do-not-sort">
                        <td><label class="static-label">{{ _i('Credito Disponibile') }}</label></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><label class="static-label pull-right">{{ printablePriceCurrency($user->activeBalance()) }}</label></td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th class="text-right">Totale: <span class="booking-total">{{ printablePrice($o->getValue('effective', false)) }}</span> {{ $currentgas->currency }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="row">
            <div class="col-md-12">
                @include('commons.textarea', ['obj' => $o, 'name' => 'notes', 'postfix' => '_' . $order->id, 'label' => _i('Note')])
            </div>
        </div>

        <?php $grand_total += $o->getValue('effective', false) ?>
    @endforeach

    @if($more_orders)
        <table class="table">
            <tfoot>
                <tr>
                    <th>
                        <div class="pull-right">
                            <strong>Totale Complessivo: <span class="all-bookings-total">{{ printablePrice($grand_total) }}</span> {{ $currentgas->currency }}</strong>
                        </div>
                    </th>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="btn-group pull-right main-form-buttons" role="group">
                <button type="button" class="btn btn-danger delete-booking">{{ _i('Annulla Prenotazione') }}</button>
                <button type="submit" class="btn btn-success saving-button" {{ $user->canBook() ? '' : 'disabled' }}>{{ _i('Salva') }}</button>
            </div>
        </div>
    </div>
</form>
