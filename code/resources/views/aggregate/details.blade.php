<x-larastrap::form :obj="$aggregate" classes="main-form" method="PUT" :action="route('aggregates.update', $aggregate->id)">
    <input type="hidden" name="post-saved-function" value="afterAggregateChange" class="skip-on-submit">

    <div class="row">
        <div class="col-md-4">
            <?php

            $statuses = ['no' => _i('Invariato')];
            foreach(\App\Helpers\Status::orders() as $identifier => $meta) {
                $statuses[$identifier] = $meta->label;
            }

            ?>

            <x-larastrap::select name="status" :label="_i('Stato')" :options="$statuses" value="no" :pophelp="_i('Da qui puoi modificare lo stato di tutti gli ordini inclusi nell\'aggregato')" />

            <x-larastrap::textarea name="comment" :label="_i('Commento')" rows="2" />

            <x-larastrap::check name="change_dates" :label="_i('Modifica Date')" triggers_collapse="change_dates" :pophelp="_i('Da qui è possibile modificare la data di apertura, chiusura a consegna di tutti gli ordini inclusi nell\'aggregato')" checked="false" />
            <x-larastrap::collapse id="change_dates">
                <x-larastrap::datepicker name="start" :label="_i('Data Apertura Prenotazioni')" />
                <x-larastrap::datepicker name="end" :label="_i('Data Chiusura Prenotazioni')" />
                <x-larastrap::datepicker name="shipping" :label="_i('Data Consegna')" />
            </x-larastrap::collapse>

            @if($currentgas->hasFeature('shipping_places'))
                <x-larastrap::selectobj name="deliveries" :label="_i('Luoghi di Consegna')" :options="$currentgas->deliveries" multiple />
            @endif
        </div>
        <div class="col-md-4">
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
				<thead>
					<tr>
						<th>Ordine</th>
						<th>Totale Prenotato</th>
						<th>Totale Consegnato</th>
					</tr>
				</thead>
				<tbody>
					@foreach($aggregate->orders as $order)
						@php

						$summary = $master_summary->orders[$order->id];
						$pending_modifiers = $order->applyModifiers($master_summary, 'pending');
						$shipped_modifiers = $order->applyModifiers($master_summary, 'shipped');
						$grand_total_pending = 0;
						$grand_total_delivered = 0;
						$modifiers_pending = 0;
						$modifiers_delivered = 0;

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
						<th>&nbsp</th>
						<th>
							{{ printablePriceCurrency($grand_total_pending ?? 0) }}
							@if($modifiers_pending != 0)
								<br>+ {{ printablePrice($modifiers_pending) }}
							@endif
						</th>
						<th>
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
