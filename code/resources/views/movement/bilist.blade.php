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

                $peer_type = $mov->transationRole($main_target);
                if ($peer_type == 'target')
                    $reference = $mov->sender;
                else if ($peer_type == 'sender')
                    $reference = $mov->target;

                $relation = $mov->transactionType($peer_type);
                if ($relation == 'credit') {
                    $in = $mov->amount;
                    $out = 0;
                }
                else if ($relation == 'debit') {
                    $in = 0;
                    $out = $mov->amount;
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
