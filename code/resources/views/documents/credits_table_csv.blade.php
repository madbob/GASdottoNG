Nome;Credito Residuo
@foreach($users as $user)
{{ $user->printableName() }};{{ $user->current_balance_amount }}
@endforeach
