<div class="well">
    <div class="row">
        <div class="col-md-12">
            @include('commons.addingbutton', [
                'template' => 'movementtypes.base-edit',
                'typename' => 'movementtype',
                'typename_readable' => _i('Tipo Movimento'),
                'targeturl' => 'movtypes'
            ])
        </div>
    </div>

    <div class="clearfix"></div>
    <hr/>

    <div class="row">
        <div class="col-md-12">
            @include('commons.loadablelist', [
                'identifier' => 'movementtype-list',
                'items' => $types,
            ])
        </div>
    </div>
</div>
