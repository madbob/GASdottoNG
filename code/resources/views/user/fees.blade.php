<x-larastrap::modal classes="close-on-submit" :title="_i('Controllo Quote')" size="fullscreen">
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
                            @include('user.partials.fee_row', [
                                'user' => $user,
                            ])
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::modal>
