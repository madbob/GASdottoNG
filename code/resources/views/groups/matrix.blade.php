<?php

$users = App\User::topLevel()->sorted()->get();
$groups = App\Group::orderBy('name', 'asc')->where('context', 'user')->get();

?>

<x-larastrap::modal :title="_i('Assegna Aggregazioni')" size="xl">
    @if($groups->isEmpty())
        <div class="alert alert-info">
            {{ _i('Non ci sono ancora aggregazioni assegnabili direttamente agli utenti.') }}
        </div>
    @else
        <x-larastrap::iform :action="route('groups.matrix.save')">
            <input type="hidden" name="close-modal" value="1">

            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('generic.name') }}</th>
                        @foreach($groups as $group)
                            <th>
                                <x-larastrap::hidden name="groups[]" :value="$group->id" />
                                {{ $group->printableName() }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <x-larastrap::enclose :obj="$user">
                            <tr>
                                <td>
                                    <x-larastrap::hidden name="users[]" :value="$user->id" />
                                    {{ $user->printableName() }}
                                </td>
                                @foreach($groups as $group)
                                    <td>
                                        <x-dynamic-component :component="sprintf('larastrap::%s', $group->cardinality == 'single' ? 'radios-model' : 'checks-model')" :params="['name' => 'circles', 'npostfix' => sprintf('__%s__%s[]', $user->id, $group->id), 'squeeze' => true, 'options' => $group->circles]" />
                                    </td>
                                @endforeach
                            </tr>
                        </x-larastrap::enclose>
                    @endforeach
                </tbody>
            </table>
        </x-larastrap::iform>
    @endif
</x-larastrap::modal>
