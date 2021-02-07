<?php $previous_year_closing = date('Y-m-d', strtotime($currentgas->getConfig('year_closing') . ' -1 years')) ?>

<div class="modal fade close-on-submit" id="checkFees" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
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
                                    @foreach($currentgas->users()->topLevel()->get() as $user)
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
                                                <input type="hidden" name="user_id[]" value="{{ $user->id }}">
                                                {{ $user->printableName() }}
                                            </td>

                                            @if($user->fee)
                                                <td data-updatable-name="movement-id-{{ $dom_id }}" data-updatable-field="name">
                                                    {!! $user->fee->printableName() !!}
                                                </td>

                                                <td>
                                                    @if($user->fee->date < $previous_year_closing)
                                                        <button type="button" class="btn btn-success async-modal" data-target-url="{{ $new_fee_url }}">{{ _i('Nuova Quota') }}</button>
                                                    @endif

                                                    <button type="button" class="btn btn-default async-modal" data-target-url="{{ route('movements.show', ['movement' => $user->fee->id, 'dom_id' => $dom_id]) }}">{{ _i('Modifica Quota') }}</button>
                                                </td>
                                            @else
                                                <td data-updatable-name="movement-id-{{ $dom_id }}" data-updatable-field="name">
                                                    {{ printableDate(null) }}
                                                </td>

                                                <td>
                                                    <button type="button" class="btn btn-success async-modal" data-target-url="{{ $new_fee_url }}">{{ _i('Nuova Quota') }}</button>
                                                </td>
                                            @endif
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
