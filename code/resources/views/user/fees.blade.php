<?php $previous_year_closing = date('Y-m-d', strtotime($currentgas->getConfig('year_closing') . ' -1 years')) ?>

<x-larastrap::modal classes="close-on-submit" :title="_i('Controllo Quote')">
    <x-larastrap::form method="POST" :action="route('users.savefees')">
        <input type="hidden" name="reload-whole-page" value="1">

        <div class="row">
            <div class="col">
                <x-larastrap::radios name="actual_status" :options="['all' => _i('Tutti'), 'active' => _i('Attivi'), 'suspended' => _i('Sospesi'), 'deleted' => _i('Cessati')]" squeeze classes="table-filters" value="active" data-table-target="#usersStatusTable" />
            </div>
        </div>

        <div class="row">
            <div class="col">
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
                            <x-larastrap::enclose :obj="$user">
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

                                $user_status = $user->plainStatus();
                                $active_identifier = 'active';

                                ?>

                                <tr data-filtered-actual_status="{{ $user_status }}" class="{{ $user_status != $active_identifier ? 'hidden' : '' }}">
                                    <td>
                                        <input type="hidden" name="user_id[]" value="{{ $user->id }}">
                                        {!! $user->printableName() !!}
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
                                        <x-larastrap::ambutton color="light" :label="_i('Nuova Quota')" :data-modal-url="$new_fee_url" />

                                        @if($user->fee)
                                            <x-larastrap::ambutton color="light" :label="_i('Modifica Quota')" :data-modal-url="route('movements.show', ['movement' => $user->fee->id, 'dom_id' => $dom_id])" />
                                        @endif
                                    </td>
                                </tr>
                            </x-larastrap::enclose>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::modal>
