<x-larastrap::enclose :obj="$user">
    <?php

    $dom_id = rand();

    $new_fee_url = route('movements.show', [
        'movement' => 0,
        'dom_id' => $dom_id,
        'type' => 'annual-fee',
        'sender_id' => $user->id,
        'sender_type' => get_class($user),
        'target_id' => $user->gas,
        'target_type' => get_class($user->gas),
        'amount' => $user->gas->getConfig('annual_fee_amount')
    ]);

    $user_status = $user->plainStatus();

    $classes = [];
    if ($user_status != 'active') {
        $classes[] = 'hidden';
    }
    if ($user->fee) {
        $classes[] = 'holding-movement-' . $user->fee->id;
    }

    ?>

    <tr data-filtered-actual_status="{{ $user_status }}" class="{{ join(' ', $classes) }}" data-reload-url="{{ route('users.fee', $user->id) }}">
        <td>
            <input type="hidden" name="user_id[]" value="{{ $user->id }}">
            {!! $user->printableName() !!}
        </td>

        <td data-updatable-name="movement-id-{{ $dom_id }}" data-updatable-field="name">
            @php

            if ($user->fee) {
                $last_fee = $user->fee;
            }
            else {
                $last_fee = $user->queryMovements(null, 'sender')->where('type', 'annual-fee')->orderBy('date', 'desc')->first();
            }

            @endphp

            @if($last_fee)
                {!! $last_fee->printableName() !!}
            @else
                {{ printableDate(null) }}
            @endif
        </td>

        <td>
            @include('commons.statusfield', ['target' => $user, 'squeeze' => true, 'postfix' => $user->id])
        </td>

        <td>
            <x-larastrap::ambutton color="success" :label="_i('Nuova Quota')" :data-modal-url="$new_fee_url" />

            @if($user->fee)
                <x-larastrap::ambutton color="warning" :label="_i('Modifica Quota')" :data-modal-url="route('movements.show', ['movement' => $user->fee->id, 'dom_id' => $dom_id])" />
            @endif
        </td>
    </tr>
</x-larastrap::enclose>
