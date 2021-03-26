<?php $previous_year_closing = date('Y-m-d', strtotime($currentgas->getConfig('year_closing') . ' -1 years')) ?>

<div class="modal fade close-on-submit" id="checkFees" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('users.savefees') }}">
                <input type="hidden" name="reload-whole-page" value="1">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Controllo Quote') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group hidden-md">
                                <div class="btn-group table-filters" data-toggle="buttons" data-table-target="#usersStatusTable">
                                    <label class="btn btn-default active">
                                        <input type="radio" name="actual_status" class="active" value="all"> {{ _i('Tutti') }}
                                    </label>
                                    <label class="btn btn-default">
                                        <input type="radio" name="actual_status" value="{{ _i('Attivo') }}"> {{ _i('Attivi') }}
                                    </label>
                                    <label class="btn btn-default">
                                        <input type="radio" name="actual_status" value="{{ _i('Sospeso') }}"> {{ _i('Sospesi') }}
                                    </label>
                                    <label class="btn btn-default">
                                        <input type="radio" name="actual_status" value="{{ _i('Cessato') }}"> {{ _i('Cessati') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <table class="table" id="usersStatusTable">
                                <thead>
                                    <tr>
                                        <th width="20%">{{ _i('Nome') }}</th>
                                        <th width="30%">{{ _i('Ultima Quota Versata') }}</th>
                                        <th width="30%">{{ _i('Stato') }}</th>
                                        <th width="20%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <?php

                                        $dom_id = rand();

                                        $new_fee_url = route('movements.show', [
                                            'movement' => 0,
                                            'dom_id' => $dom_id,
                                            'type' => 'annual-fee',
                                            'sender_id' => $user->id,
                                            'sender_type' => get_class($user),
                                            'target_id' => $currentgas,
                                            'target_type' => get_class($currentgas),
                                            'amount' => $currentgas->getConfig('annual_fee_amount')
                                        ]);

                                        ?>

                                        <tr data-filtered-actual_status="{{ $user->printableStatus() }}">
                                            <td>
                                                <input type="hidden" name="user_id[]" value="{{ $user->id }}">
                                                {!! $user->printableHeader() !!}
                                            </td>

                                            <td data-updatable-name="movement-id-{{ $dom_id }}" data-updatable-field="name">
                                                @if($user->fee)
                                                    {!! $user->fee->printableName() !!}
                                                @else
                                                    {{ printableDate(null) }}
                                                @endif
                                            </td>

                                            <td>
                                                @include('commons.statusfield', ['target' => $user, 'squeeze' => true, 'postfix' => $user->id])
                                            </td>

                                            <td>
                                                <button type="button" class="btn btn-success async-modal" data-target-url="{{ $new_fee_url }}">{{ _i('Nuova Quota') }}</button>

                                                @if($user->fee)
                                                    <button type="button" class="btn btn-default async-modal" data-target-url="{{ route('movements.show', ['movement' => $user->fee->id, 'dom_id' => $dom_id]) }}">{{ _i('Modifica Quota') }}</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Chiudi') }}</button>
                    <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
