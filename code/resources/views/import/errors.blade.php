@if(!empty($errors))
	<hr/>

	<p>
		{{ __('generic.errors') }}:
	</p>

	<ul class="list-group">
		@foreach($errors as $e)
			<li class="list-group-item">{!! $e !!}</li>
		@endforeach
	</ul>
@endif
