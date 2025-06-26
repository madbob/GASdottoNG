<x-larastrap::form :obj="$aggregate" classes="main-form" method="PUT" :action="route('aggregates.update', $aggregate->id)">
    <input type="hidden" name="post-saved-function" value="afterAggregateChange" class="skip-on-submit">

    <div class="row">
        <div class="col-md-4">
            @php

            $statuses = ['no' => __('texts.orders.statuses.unchange')];
            foreach(\App\Helpers\Status::orders() as $identifier => $meta) {
                $statuses[$identifier] = $meta->label;
            }

            @endphp

            <x-larastrap::select name="status" tlabel="generic.status" :options="$statuses" value="no" tpophelp="orders.help_aggregate_status" />

            <x-larastrap::textarea name="comment" tlabel="generic.comment" rows="2" />

            <x-larastrap::check name="change_dates" tlabel="orders.change_date" triggers_collapse="change_dates" tpophelp="orders.help_change_date" checked="false" />
            <x-larastrap::collapse id="change_dates">
                <x-larastrap::datepicker name="start" tlabel="orders.dates.start" />
                <x-larastrap::datepicker name="end" tlabel="orders.dates.end" />
                <x-larastrap::datepicker name="shipping" tlabel="orders.dates.shipping" />
            </x-larastrap::collapse>
        </div>
        <div class="col-md-4">
            @include('order.partials.groups', ['order' => $aggregate, 'readonly' => false])
            @include('commons.modifications', ['obj' => $aggregate])
        </div>
        <div class="col-md-4">
            @include('aggregate.files', ['aggregate' => $aggregate, 'managed_gas' => $currentgas->id])
        </div>
    </div>

	<hr>

	<div class="row d-none d-md-flex mb-1">
		<div class="table-responsive">
			<table class="table">
				@php

				$grand_total_pending = 0;
				$grand_total_delivered = 0;
				$modifiers_pending = 0;
				$modifiers_delivered = 0;

				@endphp

				<thead>
					<tr>
						<th scope="col">{{ __('texts.orders.name') }}</th>
						<th scope="col">{{ __('texts.orders.totals.booked') }}</th>
						<th scope="col">{{ __('texts.orders.totals.shipped') }}</th>
					</tr>
				</thead>
				<tbody>
					@foreach($aggregate->orders as $order)
						@php

						$summary = $master_summary->orders[$order->id];
						$pending_modifiers = $order->applyModifiers($master_summary, 'pending');
						$shipped_modifiers = $order->applyModifiers($master_summary, 'shipped');

						@endphp

						<tr>
							<td>
								{{ $order->printableName() }}
								{!! $order->statusIcons() !!}
							</td>
							<td>
								<?php $grand_total_pending += $summary->price ?>

								{{ printablePriceCurrency($summary->price ?? 0) }}

								@foreach(App\ModifiedValue::aggregateByType($pending_modifiers) as $am)
									<?php $modifiers_pending += $am->amount ?>
									<br>+ {{ $am->name }}: {{ printablePrice($am->amount) }}
								@endforeach
							</td>
							<td>
								<?php $grand_total_delivered += $summary->price_delivered ?>

								{{ printablePriceCurrency($summary->price_delivered ?? 0) }}

								@foreach(App\ModifiedValue::aggregateByType($shipped_modifiers) as $am)
									<?php $modifiers_delivered += $am->amount ?>
									<br>+ {{ $am->name }}: {{ printablePrice($am->amount) }}
								@endforeach
							</td>
						</tr>
					@endforeach
				</tbody>
				<thead>
					<tr>
						<th scope="col">&nbsp</th>
						<th scope="col">
							{{ printablePriceCurrency($grand_total_pending ?? 0) }}
							@if($modifiers_pending != 0)
								<br>+ {{ printablePrice($modifiers_pending) }}
							@endif
						</th>
						<th scope="col">
							{{ printablePriceCurrency($grand_total_delivered ?? 0) }}
							@if($modifiers_delivered != 0)
								<br>+ {{ printablePrice($modifiers_delivered) }}
							@endif
						</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</x-larastrap::form>
