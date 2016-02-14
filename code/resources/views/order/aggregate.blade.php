<?php

$has_shipping = false;

foreach($aggregate->orders as $order) {
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

				<form class="form-horizontal main-form order-editor" method="PUT" action="{{ url('orders/' . $order->id) }}">
					<input type="hidden" name="id" value="{{ $order->id }}" />

					<div class="row">
						<div class="col-md-6">
							@include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => 'Fornitore'])
							@include('commons.datefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura', 'mandatory' => true])
							@include('commons.datefield', ['obj' => $order, 'name' => 'end', 'label' => 'Data Chiusura', 'mandatory' => true])
							@include('commons.datefield', ['obj' => $order, 'name' => 'shipping', 'label' => 'Data Consegna'])

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
										'label' => 'Sospeso',
										'value' => 'suspended',
									],
									[
										'label' => 'Non Prenotabile',
										'value' => 'private',
									],
									[
										'label' => 'In Consegna',
										'value' => 'shipping',
									],
									[
										'label' => 'Consegnato',
										'value' => 'shipped',
									],
									[
										'label' => 'Chiuso',
										'value' => 'closed',
									]
								]
							])

						</div>
					</div>

					<hr/>

					@include('order.summary', ['order' => $order])
					@include('commons.formbuttons')
				</form>

				@else

				<form class="form-horizontal main-form">
					<div class="col-md-6">
						@include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => 'Fornitore'])
						@include('commons.staticdatefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura', 'mandatory' => true])
						@include('commons.staticdatefield', ['obj' => $order, 'name' => 'end', 'label' => 'Data Chiusura', 'mandatory' => true])
						@include('commons.staticdatefield', ['obj' => $order, 'name' => 'shipping', 'label' => 'Data Consegna'])

						@include('commons.staticenumfield', [
							'obj' => $order,
							'name' => 'status',
							'label' => 'Stato',
							'values' => [
								[
									'label' => 'Aperto',
									'value' => 'open',
								],
								[
									'label' => 'Sospeso',
									'value' => 'suspended',
								],
								[
									'label' => 'Non Prenotabile',
									'value' => 'private',
								],
								[
									'label' => 'In Consegna',
									'value' => 'shipping',
								],
								[
									'label' => 'Consegnato',
									'value' => 'shipped',
								],
								[
									'label' => 'Chiuso',
									'value' => 'closed',
								]
							]
						])

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
			<li role="presentation"><a href="#others-{{ $aggregate->id }}" role="tab" data-toggle="tab">Prenotazioni per Altri</a></li>
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
