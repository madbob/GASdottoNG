<div class="row">
    <div class="col-md-12">
        <form class="form-horizontal main-form" method="PUT" action="{{ route('modtypes.update', $modtype->id) }}">
            <div class="row">
                <div class="col-md-12">
                    @include('modifiertype.base-edit', ['modtype' => $modtype])
                </div>
            </div>

            @include('commons.formbuttons', ['obj' => $modtype, 'no_delete' => $modtype->system == true || $modtype->modifiers->isEmpty() == false])
        </form>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-12">
        <div class="form-horizontal form-filler" data-action="{{ route('modtype.search') }}" data-toggle="validator" data-fill-target="#modified-values-{{ $modtype->id }}">
            <input type="hidden" name="modifiertype" value="{{ $modtype->id }}">

            @include('commons.genericdaterange', [
                'start_date' => strtotime('-1 months'),
                'end_date' => time(),
            ])

            <div class="form-group">
                <div class="col-sm-{{ $fieldsize }} col-sm-offset-{{ $labelsize }}">
                    <button type="submit" class="btn btn-info">{{ _i('Ricerca') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12" id="modified-values-{{ $modtype->id }}">
        @include('modifiertype.valuestable', ['modifiers' => $modtype->modifiers->pluck('id')])
    </div>
</div>
