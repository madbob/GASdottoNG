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
        {{ _i('Non ci sono elementi da visualizzare.') }}
    </div>
@else
    <table class="table addicted-table" data-classes="table table-no-bordered">
        <thead>
            <tr>
                <th data-sortable="true" data-sorter="sortingDates">{{ _i('Data Registrazione') }}</th>
                <th data-sortable="true" data-sorter="sortingDates">{{ _i('Data Movimento') }}</th>
                <th data-sortable="true">{{ _i('Tipo') }}</th>
                <th data-sortable="true">{{ _i('Pagamento') }}</th>
                @if($exclude_sender == false)
                    <th data-sortable="true">{{ _i('Pagante') }}</th>
                @endif
                @if($exclude_target == false)
                    <th data-sortable="true">{{ _i('Pagato') }}</th>
                @endif
                <th data-sortable="true" data-sorter="sortingValues">{{ _i('Valore') }}</th>
                <th>{{ _i('Note') }}</th>
                @if(Gate::check('movements.admin', $currentgas))
                    <th>{{ _i('Modifica') }}</th>
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

                    <td>{{ printablePrice($mov->amount) }} {{ $currentgas->currency }}</td>

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
                                <button class="btn btn-default async-modal" data-target-url="{{ url('/movements/' . $mov->id) }}">
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
