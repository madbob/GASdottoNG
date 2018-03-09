<?php

if(isset($main_target) == false) {
    $main_target_id = null;
    $main_target_class = null;
}
else {
    $main_target_id = $main_target->id;
    $main_target_class = get_class($main_target);
}


?>

@if($movements->count() == 0)
    <div class="alert alert-info" role="alert">
        {{ _i('Non ci sono elementi da visualizzare.') }}
    </div>
@else
    <table class="table">
        <thead>
            <tr>
                <th>{{ _i('Data') }}</th>
                <th>{{ _i('Tipo') }}</th>
                <th>{{ _i('Pagamento') }}</th>
                <th>{{ _i('Riferimento') }}</th>
                <th>{{ _i('Credito') }}</th>
                <th>{{ _i('Debito') }}</th>
                <th>{{ _i('Note') }}</th>
                @if(Gate::check('movements.admin', $currentgas))
                    <th>{{ _i('Modifica') }}</th>
                @endif
            </tr>
        </thead>

        <tbody>
            @foreach($movements as $mov)
                <?php

                $reference = null;
                $in = 0;
                $out = 0;

                /*
                    Attenzione: qui si sceglie deliberatamente di non testare
                    anche il target del movimento contabile, ma assumere che
                    esso corrisponda con l'oggetto di riferimento per default.
                    Questo per gestire in modo sommario i tipi di movimento che
                    hanno come target un oggetto che fa riferimento ad un
                    fornitore (ordini e prenotazioni), e far si che il tutto
                    torni
                */
                if ($mov->sender_id == $main_target_id && $mov->sender_type == $main_target_class) {
                    $out = $mov->amount;
                    $reference = $mov->target;
                }
                else {
                    $in = $mov->amount;
                    $reference = $mov->sender;
                }

                ?>
                <tr>
                    <td>{{ $mov->printableDate('registration_date') }}</td>
                    <td>{{ $mov->printableType() }}</td>
                    <td>{!! $mov->payment_icon !!}</td>
                    <td>{{ $reference ? $reference->printableName() : '' }}</td>
                    <td>{{ $in != 0 ? printablePriceCurrency($in) : '' }}</td>
                    <td>{{ $out != 0 ? printablePriceCurrency($out) : '' }}</td>

                    <td>
                        @if(!empty($mov->notes))
                            <button type="button" class="btn btn-xs btn-default" data-container="body" data-toggle="popover" data-placement="left" data-trigger="hover" data-content="{{ str_replace('"', '\"', $mov->notes) }}">
                                <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                            </button>
                        @endif
                    </td>

                    @if(Gate::check('movements.admin', $currentgas))
                        <td>
                            @if($mov->archived == false)
                                <button class="btn btn-default async-modal" data-target-url="{{ route('movements.show', $mov->id) }}">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </button>
                            @else
                                @include('commons.detailsbutton', ['obj' => $mov])
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
