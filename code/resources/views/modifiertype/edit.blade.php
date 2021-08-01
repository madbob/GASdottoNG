<div class="row">
    <div class="col">
        <x-larastrap::mform :obj="$modtype" method="PUT" :action="route('modtypes.update', $modtype->id)">
            <div class="row">
                <div class="col">
                    @include('modifiertype.base-edit', ['modtype' => $modtype])
                </div>
            </div>
        </x-larastrap::form>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-12">
        <x-filler :data-action="route('modtype.search')" data-fill-target="#modified-values-{{ $modtype->id }}">
            <input type="hidden" name="modifiertype" value="{{ $modtype->id }}">

            @include('commons.genericdaterange', [
                'start_date' => strtotime('-1 months'),
                'end_date' => time(),
            ])
        </x-filler>
    </div>

    <div class="col-12" id="modified-values-{{ $modtype->id }}">
        @include('modifiertype.valuestable', ['modifiers' => $modtype->modifiers->pluck('id')])
    </div>
</div>
