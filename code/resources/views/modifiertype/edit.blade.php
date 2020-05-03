<form class="form-horizontal main-form" method="PUT" action="{{ route('modtypes.update', $modtype->id) }}">
    <div class="row">
        <div class="col-md-12">
            @include('modifiertype.base-edit', ['modtype' => $modtype])
        </div>
    </div>

    @include('commons.formbuttons', ['obj' => $modtype, 'no_delete' => $modtype->system == true || $modtype->modifiers->isEmpty() == false])
</form>
