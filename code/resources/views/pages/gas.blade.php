@extends($theme_layout)

@section('content')

<div class="row">
</div>

<div class="page-header">
	<h3>Configurazioni Generali</h3>
</div>

<form class="form-horizontal inner-form gas-editor" method="PUT" action="{{ url('gas/' . $gas->id) }}">
	<div class="row">
		<div class="col-md-6">
			@include('commons.textfield', ['obj' => $gas, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
			@include('commons.textfield', ['obj' => $gas, 'name' => 'email', 'label' => 'E-Mail', 'mandatory' => true])
			@include('commons.textarea', ['obj' => $gas, 'name' => 'description', 'label' => 'Descrizione'])
			@include('commons.textarea', ['obj' => $gas, 'name' => 'message', 'label' => 'Messaggio Homepage'])
		</div>
		<div class="col-md-6">
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="btn-group pull-right main-form-buttons" role="group" aria-label="Opzioni">
				<button type="submit" class="btn btn-success">Salva</button>
			</div>
		</div>
	</div>
</form>

<div class="page-header">
	<h3>Permessi</h3>
</div>

<div class="row permissions-editor">
	<div class="col-md-3">
		<select multiple name="subject" class="form-control" size="20">
			@foreach($permissions_subjects as $subject)
			<option value="{{ $subject->id }}" data-permissions-class="{{ get_class($subject) }}">{{ $subject->printableName() }}</option>
			@endforeach
			<option value="all" data-permissions-class="all">[[ TUTTI ]]</option>
		</select>
	</div>

	<div class="col-md-3">
		<select multiple name="rule" class="form-control" size="20" data-permissions-class="none">
			<option disabled="disabled">Seleziona un elemento dall'elenco di sinistra</option>
		</select>

		@foreach($permissions_rules as $class => $rules)
		<select multiple name="rule" class="form-control hidden" size="20" data-permissions-class="{{ $class }}">
			@foreach($rules as $identifier => $name)
			<option value="{{ $identifier }}">{{ $name }}</option>
			@endforeach
		</select>
		@endforeach

		<select multiple name="rule" class="form-control hidden" size="20" data-permissions-class="all">
			@foreach($permissions_rules as $class => $rules)
				@foreach($rules as $identifier => $name)
				<option value="{{ $identifier }}">{{ $name }}</option>
				@endforeach
			@endforeach
		</select>
	</div>

	<div class="col-md-3">
		<select multiple name="user" class="form-control" size="20">
			<option disabled="disabled">Seleziona una regola</option>
		</select>
	</div>

	<div class="col-md-3">
		<div class="form-group">
			<button class="btn btn-danger remove-auth">Rimuovi Utente Selezionato</button>
		</div>
		<div class="form-group">
			<input name="adduser" class="form-control" placeholder="Digita il nome di un utente da aggiungere all'elenco" />
		</div>
		<div class="radio">
			<label>
				<input type="radio" name="behaviour" value="selected">
				Autorizza solo gli utenti nell'elenco
			</label>
		</div>
		<div class="radio">
			<label>
				<input type="radio" name="behaviour" value="except">
				Autorizza tutti, tranne gli utenti nell'elenco
			</label>
		</div>
		<div class="radio">
			<label>
				<input type="radio" name="behaviour" value="all">
				Autorizza tutti gli utenti (indipendentemente dall'elenco)
			</label>
		</div>
	</div>
</div>

@endsection
