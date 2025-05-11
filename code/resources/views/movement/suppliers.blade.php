<div class="modal fade delete-on-close">
    <?php $currencies = App\Currency::enabled() ?>

    <div class="modal-dialog modal-xl modal-fullscreen-md-down modal-dialog-scrollable">
        <div class="modal-content credits-modal">
            <div class="modal-header">
                <h5 class="modal-title">{{ _i('Stato Fornitori') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <x-larastrap::field :label="_i('Saldo Attuale')">
                            <div class="input-group table-number-filters" data-table-target="#suppliersTable">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" value="min" name="filter_mode">&nbsp;{{ _i('Minore di') }}
                                </div>
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" value="max" name="filter_mode">&nbsp;{{ _i('Maggiore di') }}
                                </div>
                                <input type="number" class="form-control table-number-filter" placeholder="{{ _i('Filtra Credito') }}">
                                <div class="input-group-text">
                                    {{ defaultCurrency()->symbol }}
                                </div>
                            </div>
                        </x-larastrap::field>
                    </div>
                </div>

				<hr/>

                <div class="row">
					<div class="col" id="user-list">
						<div class="table-responsive">
							<table class="table" id="suppliersTable">
								<?php $currencies = App\Currency::enabled() ?>

								<thead>
									<tr>
										<th scope="col" width="50%">{{ __('generic.name') }}</th>

										@foreach($currencies as $curr)
											<th scope="col" width="{{ round(50 / $currencies->count(), 2) }}%">{{ _i('Saldo Attuale') }}</th>
										@endforeach
									</tr>
								</thead>
								<tbody>
									@foreach($currentgas->suppliers as $supplier)
										<tr>
											<td>
												{{ $supplier->printableName() }}
											</td>

											@foreach($currencies as $curr)
												<td class="text-filterable-cell">
													{{ printablePriceCurrency($supplier->currentBalanceAmount($curr), '.', $curr) }}
												</td>
											@endforeach
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
                </div>
            </div>

            <div class="modal-footer">
                <form class="form-inline iblock inner-form" action="{{ url('movements/document/suppliers/csv?dummy=1') }}" method="GET">
                    <input type="hidden" name="pre-saved-function" value="formToDownload">
                    <button type="submit" class="btn btn-success">{{ _i('Esporta CSV') }} <i class="bi-download"></i></button>
                </form>

                @if($currentgas->hasFeature('integralces'))
                    <a type="button" class="btn btn-success" data-bs-toggle="collapse" href="#exportIntegralCES">{{ _i('Esporta IntegralCES') }}<span class="caret"></span></a>

                    <div class="collapse well" id="exportIntegralCES">
                        <form class="form-horizontal inner-form" action="{{ url('movements/document/suppliers/integralces?download=1') }}" method="GET">
                            <input type="hidden" name="pre-saved-function" value="formToDownload">
                            <x-larastrap::text name="body" :label="_i('Causale')" :value="_i('Versamento GAS')" />
                            <button type="submit" class="btn btn-success">{{ _i('Esporta IntegralCES') }}</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
