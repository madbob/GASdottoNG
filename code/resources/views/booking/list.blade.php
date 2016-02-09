<div class="row">
	<div class="col-md-12">
		@include('commons.loadablelist', ['identifier' => 'booking-list', 'items' => $aggregate->bookings, 'url' => url('delivery/' . $aggregate->id . '/user')])
	</div>
</div>
