<x-larastrap::mform :obj="$circle" classes="form-horizontal main-form group-editor" method="PUT" :action="route('circles.update', $circle->id)">
    <div class="row">
        <div class="col-md-12">
            @include('circles.base-edit')
            <x-larastrap::check tlabel="generic.default" name="is_default" />
            @include('commons.modifications', ['obj' => $circle])
        </div>
    </div>
    <hr>
</x-larastrap::mform>

@stack('postponed')
