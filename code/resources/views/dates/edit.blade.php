<x-larastrap::mform :obj="$date" classes="date-editor" method="PUT" :action="route('dates.update', $date->id)">
    <div class="row">
        <div class="col-6">
            <x-larastrap::textarea name="description" tlabel="generic.mailfield.body" required />
            <x-larastrap::datepicker name="date" tlabel="generic.date" required />
        </div>
    </div>
</x-larastrap::mform>

@stack('postponed')
