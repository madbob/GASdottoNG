Data Registrazione;Data Movimento;Tipo;Pagamento;Pagante;Pagato;Valore
@foreach($movements as $mov)
{{ $mov->printableDate('registration_date') }};{{ $mov->printableDate('date') }};{{ $mov->printableType() }};{{ $mov->printablePayment() }};{{ $mov->sender ? $mov->sender->printableName() : '' }};{{ $mov->target ? $mov->target->printableName() : '' }};{{ printablePrice($mov->amount) }}
@endforeach
