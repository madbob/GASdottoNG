@extends($theme_layout)

@section('content')

<div class="row">
</div>

<div class="page-header">
	<h3>Configurazioni Generali</h3>
</div>

<div class="row">
	<div class="col-md-12">
		<form class="form-horizontal inner-form" method="PUT" action="{{ url('gas/' . $gas->id) }}">
			@include('commons.textfield', ['obj' => $gas, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
			@include('commons.textfield', ['obj' => $gas, 'name' => 'email', 'label' => 'E-Mail', 'mandatory' => true])
			@include('commons.textarea', ['obj' => $gas, 'name' => 'description', 'label' => 'Descrizione'])
			@include('commons.textarea', ['obj' => $gas, 'name' => 'message', 'label' => 'Messaggio Homepage'])

			@include('commons.formbuttons')
		</form>
	</div>
</div>

<div class="page-header">
	<h3>Permessi</h3>
</div>

<div class="alert alert-info">
	<p>TODO</p>
</div>

@endsection
