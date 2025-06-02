<x-larastrap::enclose :obj="$obj">
    <div class="row">
        <div class="col">
            @php

            $types = [];

            foreach (movementTypes() as $info) {
                $types[$info->id] = $info->name;
            }

            @endphp

            <x-larastrap::select name="type" tlabel="generic.type" :options="$types" disabled readonly />
            <x-larastrap::price name="amount" tlabel="generic.value" disabled readonly />
            <x-larastrap::datepicker name="date" tlabel="generic.date" disabled readonly />

            @if(filled($obj->identifier))
                <x-larastrap::text name="identifier" tlabel="generic.identifier" disabled readonly />
            @endif

            @if(filled($obj->notes))
                <x-larastrap::textarea name="notes" tlabel="generic.notes" disabled readonly />
            @endif
        </div>
        <div class="col">
            <x-larastrap::datepicker name="registration_date" tlabel="movements.registration_date" disabled readonly />
            <x-larastrap::text name="registerer" tlabel="movements.registrar" :value="$obj->automatic ? __('movements.automatic') : $obj->registerer->printableName()" disabled readonly />

            @if($obj->related->isEmpty() == false)
                <x-larastrap::field tlabel="generic.related">
                    @foreach($obj->related as $rel)
                        @include('commons.staticmovementfield', ['obj' => $rel, 'label' => __('generic.payment'), 'squeeze' => true])
                    @endforeach
                </x-larastrap::field>
            @endif
        </div>
    </div>
</x-larastrap::enclose>
