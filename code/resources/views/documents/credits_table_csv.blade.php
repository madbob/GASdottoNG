{{ _i('Nome') }};{{ _i('Credito Residuo') }}
@foreach($users as $user)
{{ $user->printableName() }};{{ printablePrice($user->current_balance_amount, ',') }}
@endforeach
