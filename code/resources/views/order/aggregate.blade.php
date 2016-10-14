<?php

$has_shipping = false;

foreach($aggregate->orders as $order) {
	if ($order->supplier->userCan('supplier.shippings'))
		$has_shipping = true;
}

$more_orders = ($aggregate->orders->count() > 1);
$panel_rand_wrap = rand();

?>

<div class="row">
	<div class="col-md-12">
		@if($more_orders)
		<ul class="nav nav-tabs" role="tablist">
			@foreach($aggregate->orders as $index => $order)
			<li role="presentation" class="{{ $index == 0 ? 'active' : '' }}"><a href="#order-{{ $panel_rand_wrap }}-{{ $index }}" role="tab" data-toggle="tab">{{ $order->printableName() }}</a></li>
			@endforeach
		</ul>
		@endif

		<div class="tab-content">
			@foreach($aggregate->orders as $index => $order)
			<div role="tabpanel" class="tab-pane {{ $index == 0 ? 'active' : '' }}" id="order-{{ $panel_rand_wrap }}-{{ $index }}">
				@if($order->supplier->userCan('supplier.orders'))

				<?php $summary = $order->calculateSummary() ?>

				<form class="form-horizontal main-form order-editor" method="PUT" action="{{ url('orders/' . $order->id) }}">
					<input type="hidden" name="id" value="{{ $order->id }}" />

					<div class="row">
						<div class="col-md-6">
							@include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => 'Fornitore'])
							@include('commons.datefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura', 'mandatory' => true])
							@include('commons.datefield', ['obj' => $order, 'name' => 'end', 'label' => 'Data Chiusura', 'mandatory' => true])
							@include('commons.datefield', ['obj' => $order, 'name' => 'shipping', 'label' => 'Data Consegna'])
						</div>
						<div class="col-md-6">
							@include('commons.orderstatus', ['order' => $order])
							@include('commons.textfield', ['obj' => $order, 'name' => 'discount', 'label' => 'Sconto Globale', 'postlabel' => '€ / %'])

							@if($currentgas->userCan('movements.view|movements.admin'))
								@include('commons.movementfield', ['obj' => $order->payment, 'name' => 'payment_id', 'label' => 'Pagamento', 'default' => \App\Movement::generate('order-payment', $currentgas, $order->supplier, $summary->price_delivered)])
							@endif
						</div>
					</div>

					<hr/>

					@include('order.summary', ['order' => $order, 'summary' => $summary])
					@include('commons.formbuttons')
				</form>

				@else

				<form class="form-horizontal main-form">
					<div class="col-md-6">
						@include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => 'Fornitore'])
						@include('commons.staticdatefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura', 'mandatory' => true])
						@include('commons.staticdatefield', ['obj' => $order, 'name' => 'end', 'label' => 'Data Chiusura', 'mandatory' => true])
						@include('commons.staticdatefield', ['obj' => $order, 'name' => 'shipping', 'label' => 'Data Consegna'])
						@include('commons.orderstatus', ['order' => $order])
					</div>
				</form>
				@endif
			</div>
			@endforeach
		</div>
	</div>
</div>

<hr/>

<div class="row aggregate-bookings">
	<input type="hidden" name="aggregate_id" value="{{ $aggregate->id }}" />

	<div class="col-md-12">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#myself-{{ $aggregate->id }}" role="tab" data-toggle="tab">La Mia Prenotazione</a></li>
			@if($has_shipping)
				@if($aggregate->isActive())
					<li role="presentation"><a href="#others-{{ $aggregate->id }}" role="tab" data-toggle="tab">Prenotazioni per Altri</a></li>
				@endif
				<li role="presentation"><a href="#shippings-{{ $aggregate->id }}" role="tab" data-toggle="tab">Consegne</a></li>
			@endif
		</ul>

		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="myself-{{ $aggregate->id }}">
				@include('booking.edit', ['aggregate' => $aggregate, 'user' => $currentuser])
			</div>

			@if($has_shipping)
			<div role="tabpanel" class="tab-pane" id="others-{{ $aggregate->id }}">
				<div class="row">
					<div class="col-md-12">
						<input data-aggregate="{{ $aggregate->id }}" class="form-control bookingSearch" placeholder="Cerca Utente" />
					</div>
				</div>

				<div class="row">
					<div class="col-md-12 other-booking">
					</div>
				</div>
			</div>

			<div role="tabpanel" class="tab-pane shippable-bookings" id="shippings-{{ $aggregate->id }}">
				@include('booking.list', ['aggregate' => $aggregate])
			</div>
			@endif
		</div>
	</div>
</div>

@stack('postponed')
