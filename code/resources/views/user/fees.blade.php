<?php $previous_year_closing = date('Y-m-d', strtotime($currentgas->getConfig('year_closing') . ' -1 years')) ?>

<div class="modal fade close-on-submit" id="checkFees" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal" method="GET" data-toggle="validator" novalidate>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Controllo Quote') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="credits_status_table">
                            <table class="table" id="creditsTable">
                                <thead>
                                    <tr>
                                        <th width="30%">{{ _i('Nome') }}</th>
                                        <th width="50%">{{ _i('Ultima Quota Versata') }}</th>
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

                                        <tr>
                                            <td>
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
                </div>
            </form>
        </div>
    </div>
</div>
