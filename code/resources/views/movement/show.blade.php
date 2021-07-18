<x-larastrap::enclose :obj="$obj">
    <div class="row">
        <div class="col-md-6">
            <?php

            $types = [];

            foreach (App\MovementType::types() as $info) {
                $types[$info->id] = $info->name;
            }

            ?>

            <x-larastrap::select name="type" :label="_i('Tipo')" :options="$types" disabled readonly />
            <x-larastrap::price name="amount" :label="_i('Valore')" disabled readonly />
            <x-larastrap::datepicker name="date" :label="_i('Data')" disabled readonly />
            <x-larastrap::text name="identifier" :label="_i('Identificativo')" disabled readonly />
            <x-larastrap::textarea name="notes" :label="_i('Note')" disabled readonly />
        </div>
        <div class="col-md-6">
            <x-larastrap::datepicker name="registration_date" :label="_i('Registrato Il')" disabled readonly />
            <x-larastrap::text name="registerer" :label="_i('Registrato Da')" :value="$obj->registerer->printableName()" disabled readonly />
        </div>
    </div>
</x-larastrap::enclose>
