<x-larastrap::enclose :obj="$obj">
    <div class="row">
        <div class="col">
            <?php

            $types = [];

            foreach (movementTypes() as $info) {
                $types[$info->id] = $info->name;
            }

            ?>

            <x-larastrap::select name="type" :label="_i('Tipo')" :options="$types" disabled readonly />
            <x-larastrap::price name="amount" :label="_i('Valore')" disabled readonly />
            <x-larastrap::datepicker name="date" :label="_i('Data')" disabled readonly />

            @if(filled($obj->identifier))
                <x-larastrap::text name="identifier" :label="_i('Identificativo')" disabled readonly />
            @endif

            @if(filled($obj->notes))
                <x-larastrap::textarea name="notes" :label="_i('Note')" disabled readonly />
            @endif
        </div>
        <div class="col">
            <x-larastrap::datepicker name="registration_date" :label="_i('Registrato Il')" disabled readonly />
            <x-larastrap::text name="registerer" :label="_i('Registrato Da')" :value="$obj->automatic ? _i('Automatico') : $obj->registerer->printableName()" disabled readonly />

            @if($obj->related->isEmpty() == false)
                <x-larastrap::field :label="_i('Movimenti Correlati')">
                    @foreach($obj->related as $rel)
                        @include('commons.staticmovementfield', ['obj' => $rel, 'label' => _i('Pagamento'), 'squeeze' => true])
                    @endforeach
                </x-larastrap::field>
            @endif
        </div>
    </div>
</x-larastrap::enclose>
