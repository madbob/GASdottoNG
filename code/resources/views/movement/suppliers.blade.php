<div class="modal fade delete-on-close">
    <?php $currencies = App\Currency::enabled() ?>

    <div class="modal-dialog modal-xl modal-fullscreen-md-down modal-dialog-scrollable">
        <div class="modal-content credits-modal">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('texts.movements.suppliers_status') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <x-larastrap::field tlabel="movements.current_balance">
                            <div class="input-group table-number-filters" data-table-target="#suppliersTable">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" value="min" name="filter_mode">&nbsp;{{ __('texts.generic.minor_than') }}
                                </div>
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" value="max" name="filter_mode">&nbsp;{{ __('texts.generic.major_than') }}
                                </div>
                                <input type="number" class="form-control table-number-filter" placeholder="{{ __('texts.generic.do_filter') }}">
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
										<th scope="col" width="50%">{{ __('texts.generic.name') }}</th>

										@foreach($currencies as $curr)
											<th scope="col" width="{{ round(50 / $currencies->count(), 2) }}%">{{ __('texts.movements.current_balance') }}</th>
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
                    <button type="submit" class="btn btn-success">{{ __('texts.generic.exports.csv') }} <i class="bi-download"></i></button>
                </form>

                @if($currentgas->hasFeature('integralces'))
                    <a type="button" class="btn btn-success" data-bs-toggle="collapse" href="#exportIntegralCES">{{ __('texts.generic.exports.integralces') }}<span class="caret"></span></a>

                    <div class="collapse well" id="exportIntegralCES">
                        <form class="form-horizontal inner-form" action="{{ url('movements/document/suppliers/integralces?download=1') }}" method="GET">
                            <input type="hidden" name="pre-saved-function" value="formToDownload">
                            <x-larastrap::text name="body" tlabel="movements.causal" :value="__('texts.movements.generic_causal')" />
                            <button type="submit" class="btn btn-success">{{ __('texts.generic.exports.integralces') }}</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
