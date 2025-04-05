@php
$groups = App\Group::orderBy('name', 'asc')->where('context', 'user')->get();
@endphp

<x-larastrap::modal classes="close-on-submit" :title="_i('Controllo Quote')" size="fullscreen">
    <x-larastrap::form method="POST" :action="route('users.savefees')">
        <input type="hidden" name="reload-whole-page" value="1">

        <div class="row">
            <div class="col-12 col-md-6">
                <x-larastrap::radios
                    name="actual_status"
                    :options="['all' => _i('Tutti'), 'active' => _i('Attivi'), 'suspended' => _i('Sospesi'), 'deleted' => _i('Cessati')]"
                    :label="_i('Stato')"
                    classes="table-filters"
                    value="active"
                    data-table-target="#usersStatusTable" />

                @foreach($groups as $group)
                    <x-larastrap::radios-model
                        color="outline-info"
                        :name="sprintf('group_%s', $group->id)"
                        :options="$group->circles"
                        :label="$group->printableName()"
                        classes="table-filters"
                        data-table-target="#usersStatusTable"
                        :extra_options="['all' => 'Tutti']" />
                @endforeach
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col">
                <table class="table align-middle" id="usersStatusTable">
                    <thead>
                        <tr>
                            <th scope="col" width="20%">{{ _i('Nome') }}</th>
                            <th scope="col" width="30%">{{ _i('Ultima Quota Versata') }}</th>
                            <th scope="col" width="30%">{{ _i('Stato') }}</th>
                            <th scope="col" width="20%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            @include('user.partials.fee_row', [
                                'user' => $user,
                                'groups' => $groups,
                            ])
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::modal>
