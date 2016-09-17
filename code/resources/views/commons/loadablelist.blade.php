<div class="alert alert-info {{ count($items) != 0 ? 'hidden' : '' }}" role="alert" id="empty-{{ $identifier }}">
	Non ci sono elementi da visualizzare.
</div>

<div class="list-group loadablelist" id="{{ $identifier }}">
	@foreach($items as $item)
		@if(isset($url))
			<?php $u = url($url . '/' . $item->id) ?>
		@else
			<?php $u = $item->getShowURL() ?>
		@endif

		<a data-element-id="{{ $item->id }}" href="{{ $u }}" class="loadable-item list-group-item">{!! $item->printableHeader() !!}</a>
	@endforeach
</div>
