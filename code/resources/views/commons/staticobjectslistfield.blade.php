<x-larastrap::field :label="$label">
    <label class="static-label text-body-secondary">
        @if($obj)
            <?php $final = [] ?>
            @foreach($obj->$name as $n)
                <?php $final[] = $n->printableName() ?>
            @endforeach
            {{ join(', ', $final) }}
        @endif
    </label>
</x-larastrap::field>
