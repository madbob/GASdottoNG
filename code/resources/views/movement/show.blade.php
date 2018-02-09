<form class="form-horizontal main-form">
    <div class="row">
        <div class="col-md-6">
            <?php

            $types = [];

            foreach (App\MovementType::types() as $info) {
                $types[] = [
                    'label' => $info->name,
                    'value' => $info->id,
                ];
            }

            ?>

            @include('commons.staticenumfield', [
                'obj' => $obj,
                'name' => 'type',
                'values' => $types,
                'label' => _i('Tipo')
            ])

            @include('commons.staticpricefield', [
                'obj' => $obj,
                'name' => 'amount',
                'label' => _i('Valore')
            ])
            @include('commons.staticdatefield', [
                'obj' => $obj,
                'name' => 'date',
                'label' => _i('Data')
            ])
            @include('commons.staticstringfield', [
                'obj' => $obj,
                'name' => 'identifier',
                'label' => _i('Identificativo')
            ])
            @include('commons.staticstringfield', [
                'obj' => $obj,
                'name' => 'notes',
                'label' => _i('Note')
            ])
        </div>
        <div class="col-md-6">
            @include('commons.staticdatefield', [
                'obj' => $obj,
                'name' => 'registration_date',
                'label' => _i('Registrato Il')
            ])
            @include('commons.staticobjfield', [
                'obj' => $obj,
                'name' => 'registerer',
                'label' => _i('Registrato Da')
            ])
        </div>
    </div>
</form>
