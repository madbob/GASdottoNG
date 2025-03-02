<?php

if(isset($exclude_sender) == false)
    $exclude_sender = false;

if(isset($exclude_target) == false)
    $exclude_target = false;

if(isset($main_target) == false) {
    $main_target_id = null;
    $main_target_class = null;
}
else {
    $main_target_id = $main_target->id;
    $main_target_class = get_class($main_target);
}

$total_amounts_on_screen = [];
foreach(App\Currency::enabled() as $curr) {
    $total_amounts_on_screen[$curr->id] = (object) [
        'currency' => $curr,
        'total' => 0,
    ];
}

?>

@if($movements->count() == 0)
    <x-larastrap::suggestion>
        {{ _i('Non ci sono elementi da visualizzare.') }}
    </x-larastrap::suggestion>
@else
    <div class="table-responsive">
        <table class="table" data-classes="table table-no-bordered">
            <thead>
                <tr>
                    <th scope="col">{{ _i('Data Registrazione') }}</th>
                    <th scope="col">{{ _i('Data Movimento') }}</th>
                    <th scope="col">{{ _i('Tipo') }}</th>
                    <th scope="col">{{ _i('Pagamento') }}</th>
                    @if($exclude_sender == false)
                        <th scope="col">{{ _i('Pagante') }}</th>
                    @endif
                    @if($exclude_target == false)
                        <th scope="col">{{ _i('Pagato') }}</th>
                    @endif
                    <th scope="col">{{ _i('Valore') }}</th>
                    <th scope="col">{{ _i('Note') }}</th>
                    @if(Gate::check('movements.admin', $currentgas))
                        <th scope="col">{{ _i('Modifica') }}</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @foreach($movements as $mov)
                    <?php

                    $filtered_type = 'all';
                    if ($main_target_id != null) {
                        $sender = $mov->sender;
                        if ($sender && $sender->id == $main_target_id && get_class($sender) == $main_target_class) {
                            $filtered_type = 'debt';
                        }

                        if ($filtered_type == 'all') {
                            $target = $mov->target;
                            if ($target && $target->id == $main_target_id && get_class($target) == $main_target_class) {
                                $filtered_type = 'credit';
                            }
                        }
                    }

                    $total_amounts_on_screen[$mov->currency->id]->total += $mov->amount;

                    ?>
                    <tr data-filtered-movements-filter="{{ $filtered_type }}">
                        <td>{{ printableDate($mov->registration_date, true) }}</td>
                        <td>{{ printableDate($mov->date, true) }}</td>
                        <td>{{ $mov->printableType() }}</td>
                        <td>{!! $mov->payment_icon !!}</td>

                        @if($exclude_sender == false)
                            <td>{{ $mov->sender ? $mov->sender->printableName() : '' }}</td>
                        @endif

                        @if($exclude_target == false)
                            <td>{{ $mov->target ? $mov->target->printableName() : '' }}</td>
                        @endif

                        <td>{{ printablePriceCurrency($mov->amount, '.', $mov->currency) }}</td>

                        <td>
                            @if(!empty($mov->notes))
                                <button type="button" class="btn btn-sm btn-light" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="left" data-bs-trigger="hover" data-bs-content="{{ str_replace('"', '\"', $mov->notes) }}">
                                    <i class="bi-info-square"></i>
                                </button>
                            @endif
                        </td>

                        @if(Gate::check('movements.admin', $currentgas))
                            <td>
                                @if($mov->archived == false)
                                    <button type="button" class="btn btn-xs btn-light async-modal" data-modal-url="{{ route('movements.show', $mov->id) }}">
                                        <i class="bi-pencil"></i>
                                    </button>
                                @else
                                    @include('commons.detailsbutton', ['obj' => $mov])
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    @if($exclude_sender == false)
                        <td>&nbsp;</td>
                    @endif
                    @if($exclude_target == false)
                        <td>&nbsp;</td>
                    @endif
                    <td class="fw-bold">
                        @foreach($total_amounts_on_screen as $data)
                            {{ printablePriceCurrency($data->total, '.', $data->currency) }}<br>
                        @endforeach
                    </td>
                    <td>&nbsp;</td>
                    @if(Gate::check('movements.admin', $currentgas))
                        <td>&nbsp;</td>
                    @endif
                </tr>
            </tfoot>
        </table>
    </div>
@endif
