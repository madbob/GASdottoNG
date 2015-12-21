<?php

$more_orders = ($aggregate->orders->count() > 1);

?>

<form class="form-horizontal main-form" method="POST" action="{{ url('bookings/' . $aggregate->id) }}">
	<input type="hidden" name="userid" value="{{ $user->id }}" />

	@foreach($aggregate->orders as $order)
		@if($more_orders)
			<h3>{{ $order->printableName() }}</h3>
		@endif

		<?php $o = $order->userBooking($user->id) ?>

		@foreach($order->products as $product)
			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<label class="col-sm-3 control-label">{{ $product->name }}</label>
						<div class="col-md-3">
							<input class="form-control" name="{{ $product->id }}" value="{{ $o->getBooked($product) }}" />
						</div>
						<label class="col-sm-3 control-label">{{ $product->measure->name }}</label>
						<label class="col-sm-3 control-label">{{ $product->printablePrice($order) }}</label>
					</div>
				</div>
			</div>
		@endforeach
	@endforeach
</form>
