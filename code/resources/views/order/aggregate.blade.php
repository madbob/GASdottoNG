<?php

$has_shipping = false;
$has_bookings = false;

foreach($aggregate->orders as $order) {
	if ($order->supplier->userCan('supplier.bookings'))
		$has_bookings = true;
	if ($order->supplier->userCan('supplier.shippings'))
		$has_shipping = true;
}

$more_orders = ($aggregate->orders->count() > 1);

?>

<div class="row">
	<div class="col-md-12">
		@if($more_orders)
		<ul class="nav nav-tabs" role="tablist">
			@foreach($aggregate->orders as $order)
			<li role="presentation"><a href="#order-{{ $order->id }}" role="tab" data-toggle="tab">{{ $order->printableName() }}</a></li>
			@endforeach
		</ul>
		@endif

		<div class="tab-content">
			@foreach($aggregate->orders as $order)
			<div role="tabpanel" class="tab-pane active" id="order-{{ $order->id }}">
				@if($order->supplier->userCan('supplier.orders'))
				<form class="form-horizontal main-form" method="POST" action="{{ url('orders/' . $order->id) }}">
					<input type="hidden" name="id" value="{{ $order->id }}" />

					<div class="row">
						<div class="col-md-6">
							@include('order.base-edit', ['order' => $order])
						</div>
						<div class="col-md-6">
							@include('commons.selectenumfield', [
								'obj' => $order,
								'name' => 'status',
								'label' => 'Stato',
								'values' => [
									[
										'label' => 'Aperto',
										'value' => 'open',
									],
									[
										'label' => 'Chiuso',
										'value' => 'closed',
									],
									[
										'label' => 'Sospeso',
										'value' => 'suspended',
									],
									[
										'label' => 'Consegnato',
										'value' => 'shipped',
									]
								]
							])
						</div>
					</div>

					@include('commons.formbuttons')
				</form>
				@else
				<p>TODO: caso in cui l'utente non può editare l'ordine</p>
				@endif
			</div>
			@endforeach
		</div>
	</div>
</div>

<hr/>

<div class="row">
	<div class="col-md-12">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#myself" role="tab" data-toggle="tab">La Mia Prenotazione</a></li>
			@if($has_bookings)
			<li role="presentation"><a href="#others" role="tab" data-toggle="tab">Prenotazioni per Altri</a></li>
			@endif
			@if($has_shipping)
			<li role="presentation"><a href="#shippings" role="tab" data-toggle="tab">Consegne</a></li>
			@endif
		</ul>

		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="myself">
				@include('booking.edit', ['aggregate' => $aggregate, 'user' => $currentuser])
			</div>

			@if($has_bookings)
			<div role="tabpanel" class="tab-pane" id="others">
				<div class="row">
					<div class="col-md-12">
						<input class="form-control" placeholder="Cerca Utente" />
					</div>
				</div>
			</div>
			@endif

			@if($has_shipping)
			<div role="tabpanel" class="tab-pane" id="shippings">
			</div>
			@endif
		</div>
	</div>
</div>
