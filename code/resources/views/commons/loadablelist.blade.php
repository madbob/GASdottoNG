<div class="alert alert-info" role="alert" id="empty-{{ $identifier }}">
	Non ci sono elementi da visualizzare.
</div>

<div class="list-group loadablelist" id="{{ $identifier }}">
	@foreach($items as $item)
	<a href="{{ url($url . '/' . $item->id) }}" class="loadable-item list-group-item">{!! $item->printableHeader() !!}</a>
	@endforeach
</div>
