<x-larastrap::mform :obj="$vatrate" classes="main-form vatrate-editor" method="PUT" :action="route('vatrates.update', $vatrate->id)" :nodelete="$vatrate->products()->count() > 0" autoread>
    <div class="row">
        <div class="col">
            @include('vatrates.base-edit', ['vatrate' => $vatrate])
        </div>
    </div>
</x-larastrap::mform>

@stack('postponed')
