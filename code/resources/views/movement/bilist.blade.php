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

                $in = 0;
                $out = 0;

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
                    <td>{{ printableDate($mov->date, true) }}</td>
                    <td>{{ $mov->printableType() }}</td>
                    <td>{!! $mov->payment_icon !!}</td>
                    <td>{{ $reference ? $reference->printableName() : '' }}</td>
                    <td>{{ $in != 0 ? printablePriceCurrency($in, '.', $mov->currency) : '' }}</td>
                    <td>{{ $out != 0 ? printablePriceCurrency($out, '.', $mov->currency) : '' }}</td>

                    <td>
                        @if(!empty($mov->notes))
                            <button type="button" class="btn btn-xs btn-light" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="left" data-bs-trigger="hover" data-bs-content="{{ str_replace('"', '\"', $mov->notes) }}">
                                <i class="bi-info-square"></i>
                            </button>
                        @endif
                    </td>

                    @if(Gate::check('movements.admin', $currentgas))
                        <td>
                            @if($mov->archived == false)
                                <a href="{{ route('movements.show', $mov->id) }}" class="btn btn-light async-modal">
                                    <i class="bi-pencil"></i>
                                </a>
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
