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


?>

@if($movements->count() == 0)
    <div class="alert alert-info" role="alert">
        Non ci sono elementi da visualizzare.
    </div>
@else
    <table class="table">
        <thead>
            <tr>
                <th>Data Registrazione</th>
                <th>Data Movimento</th>
                <th>Tipo</th>
                <th>Pagamento</th>
                @if($exclude_sender == false)
                    <th>Pagante</th>
                @endif
                @if($exclude_target == false)
                    <th>Pagato</th>
                @endif
                <th>Valore</th>
                @if(Gate::check('movements.admin', $currentgas))
                    <th>Modifica</th>
                @endif
            </tr>
        </thead>

        <tbody>
            @foreach($movements as $mov)
                <?php

                $filtered_type = 'all';
                if ($main_target_id != null) {
                    $sender = $mov->sender;
                    if ($sender && $sender->id == $main_target_id && get_class($sender) == $main_target_class)
                        $filtered_type = 'debt';

                    if ($filtered_type == 'all') {
                        $target = $mov->target;
                        if ($target && $target->id == $main_target_id && get_class($target) == $main_target_class)
                            $filtered_type = 'credit';
                    }
                }

                ?>
                <tr data-filtered-movements-filter="{{ $filtered_type }}">
                    <td>{{ $mov->printableDate('registration_date') }}</td>
                    <td>{{ $mov->printableDate('date') }}</td>
                    <td>{{ $mov->printableType() }}</td>
                    <td>{!! $mov->payment_icon !!}</td>

                    @if($exclude_sender == false)
                        <td>{{ $mov->sender ? $mov->sender->printableName() : '' }}</td>
                    @endif

                    @if($exclude_target == false)
                        <td>{{ $mov->target ? $mov->target->printableName() : '' }}</td>
                    @endif

                    <td>{{ printablePrice($mov->amount) }} €</td>

                    @if(Gate::check('movements.admin', $currentgas))
                        <td>
                            <button class="btn btn-default async-modal" data-target-url="{{ url('/movements/' . $mov->id) }}">
                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                            </button>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
