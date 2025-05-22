<x-larastrap::mform :obj="$date" classes="date-editor" method="PUT" :action="route('dates.update', $date->id)">
    <div class="row">
        <div class="col-6">
            <x-larastrap::textarea name="description" :label="_i('Contenuto')" required />
            <x-larastrap::datepicker name="date" tlabel="generic.date" required />
        </div>
    </div>
</x-larastrap::mform>

@stack('postponed')
