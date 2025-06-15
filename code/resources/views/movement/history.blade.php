<?php $can_edit = Gate::check('movements.admin', $currentgas) || Gate::check('movements.view', $currentgas) ?>

<x-larastrap::modal>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">{{ __('texts.generic.date') }}</th>
                @foreach($obj->balanceFields() as $identifier => $name)
                    <th scope="col">{{ $name }}</th>
                @endforeach

                @if($can_edit)
                    <th scope="col"></th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($obj->balances as $index => $bal)
				<?php $date = \Carbon\Carbon::parse($bal->date) ?>

                <tr class="{{ $index == 0 ? 'current-balance' : '' }}">
                    <td>{{ $index == 0 ? __('texts.movements.current_balance') : ucwords($date->isoFormat('D MMMM YYYY')) }}</td>

                    @foreach($obj->balanceFields() as $identifier => $name)
                        <td class="{{ $index == 0 ? $identifier : '' }}">
                            <span>{{ $bal->$identifier }}</span> {{ $currentgas->currency }}
                        </td>
                    @endforeach

                    @if($can_edit)
                        <td class="text-end">
                            @if($index != 0)
								@if(is_a($obj, App\Gas::class))
									<x-larastrap::ambutton tlabel="generic.remove" color="danger" :data-modal-url="route('movements.askdeletebalance', ['id' => $bal->id])" size="sm" />
									<x-larastrap::ambutton tlabel="generic.details" color="info" :data-modal-url="route('movements.history.details', ['date' => $date->format('Y-m-d')])" size="sm" />
								@endif
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</x-larastrap::modal>
