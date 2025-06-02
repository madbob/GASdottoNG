<x-larastrap::modal>
	<?php $currencies = App\Currency::enabled() ?>

	<x-larastrap::tabs active="0">
		<x-larastrap::tabpane tlabel="user.all">
			<div class="row">
				<div class="col">
					<a href="{{ route('movements.history.details', ['date' => $date, 'format' => 'csv', 'target' => 'users']) }}" class="btn btn-light">{{ __('generic.exports.csv') }} <i class="bi-download"></i></a>
				</div>
			</div>

			<hr/>

			<div class="table-responsive">
				<table class="table">
					<thead>
						<tr>
							<th scope="col" width="70%">{{ __('generic.name') }}</th>
							@foreach($currencies as $curr)
								<th scope="col" width="{{ round(30 / $currencies->count(), 2) }}%">{{ __('movements.credit') }}</th>
							@endforeach
						</tr>
					</thead>
					<tbody>
						@foreach($users as $name => $amounts)
							<tr>
								<td>
									{{ $name }}
								</td>

								@foreach($currencies as $index => $curr)
									<td class="text-filterable-cell">
										{{ printablePriceCurrency($amounts[$index], '.', $curr) }}
									</td>
								@endforeach
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</x-larastrap::tabpane>

		<x-larastrap::tabpane tlabel="supplier.all">
			<div class="row">
				<div class="col">
					<a href="{{ route('movements.history.details', ['date' => $date, 'format' => 'csv', 'target' => 'suppliers']) }}" class="btn btn-light">{{ __('generic.exports.csv') }} <i class="bi-download"></i></a>
				</div>
			</div>

			<hr/>

			<div class="table-responsive">
				<table class="table">
					<thead>
						<tr>
							<th scope="col" width="70%">{{ __('generic.name') }}</th>

							@foreach($currencies as $curr)
								<th scope="col" width="{{ round(30 / $currencies->count(), 2) }}%">{{ __('movements.balance') }}</th>
							@endforeach
						</tr>
					</thead>
					<tbody>
						@foreach($suppliers as $name => $amounts)
							<tr>
								<td>
									{{ $name }}
								</td>

								@foreach($currencies as $index => $curr)
									<td class="text-filterable-cell">
										{{ printablePriceCurrency($amounts[$index], '.', $curr) }}
									</td>
								@endforeach
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</x-larastrap::tabpane>
	</x-larastrap::tabs>
</x-larastrap::modal>
