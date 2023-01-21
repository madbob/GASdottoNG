<div>
    <div class="row">
        <div class="col-12 col-md-6">
			@php

			$actions = [
				['link' => route('receipts.search', ['format' => 'send']), 'label' => _i('Inoltra Ricevute in Attesa')],
			];

			$downloads = [
				['link' => route('receipts.search', ['format' => 'csv']), 'label' => _i('Esporta CSV')],
			];

			@endphp

            <x-filler :data-action="route('receipts.search')" data-fill-target="#receipts-in-range" :actionButtons="$actions" :downloadButtons="$downloads">
                @include('commons.genericdaterange', ['start_date' => strtotime('-1 months')])
                <x-larastrap::selectobj name="supplier_id" :label="_i('Fornitore')" :options="$currentgas->suppliers" :extraitem="_i('Nessuno')" />
            </x-filler>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col" id="receipts-in-range">
            @include('commons.loadablelist', [
                'identifier' => 'receipts-list',
                'items' => $receipts,
                'legend' => (object)[
                    'class' => 'Receipt',
                ],
            ])
        </div>
    </div>
</div>
