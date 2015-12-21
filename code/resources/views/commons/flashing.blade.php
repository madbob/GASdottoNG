@if(Session::has('message'))
<div class="alert alert-{{ Session::get('message_type', 'info') }}">
	{!! Session::get('message') !!}
</div>
@endif
