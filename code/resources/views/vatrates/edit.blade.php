<x-larastrap::form :obj="$vatrate" classes="main-form vatrate-editor" method="PUT" :action="route('vatrates.update', $vatrate->id)">
    <div class="row">
        <div class="col">
            @include('vatrates.base-edit', ['vatrate' => $vatrate])
        </div>
    </div>
</x-larastrap::form>

@stack('postponed')
