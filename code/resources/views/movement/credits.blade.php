<div class="modal fade delete-on-close">
    <div class="modal-dialog modal-xl modal-fullscreen-md-down modal-dialog-scrollable">
        <div class="modal-content credits-modal">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('texts.movements.credits_status') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
				<div class="row">
                    <div class="col">
                        <x-larastrap::field tlabel="movements.credit">
                            <div class="input-group table-number-filters" data-table-target="#creditsTable">
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

						@php

						$payment_options = [
							'all' => __('texts.generic.all'),
							'none' => __('texts.generic.unspecified'),
						];

						foreach(paymentTypes() as $payment_identifier => $payment_meta) {
							$payment_options[$payment_identifier] = $payment_meta->name;
						}

                        $groups = App\Group::orderBy('name', 'asc')->where('context', 'user')->get();

						@endphp

						<x-larastrap::radios
                            name="payment_method"
                            :label="__('texts.user.payment_method')"
                            :options="$payment_options"
                            value="all"
                            classes="table-filters"
                            data-table-target="#creditsTable" />

                        @foreach($groups as $group)
                            <x-larastrap::radios-model
                                color="outline-info"
                                :name="sprintf('group_%s', $group->id)"
                                :options="$group->circles"
                                :label="$group->printableName()"
                                classes="table-filters"
                                data-table-target="#creditsTable"
                                :extra_options="['all' => __('texts.generic.all')]" />
                        @endforeach
                    </div>
                </div>

				<hr />

                <div class="row">
					<div class="col" id="user-list">
						<div class="table-responsive">
							<table class="table" id="creditsTable">
								<?php $currencies = App\Currency::enabled() ?>

								<thead>
									<tr>
										<th scope="col" width="40%">{{ __('texts.generic.name') }}</th>
										@foreach($currencies as $curr)
											<th scope="col" width="{{ round(35 / $currencies->count(), 2) }}%">{{ __('texts.movements.credit') }}</th>
										@endforeach
										<th scope="col" width="25%">{{ __('texts.user.payment_method') }}</th>
									</tr>
								</thead>
								<tbody>
									@foreach($currentgas->users()->topLevel()->get() as $user)
                                        @php

                                        $serialized_circles = [];

                                        foreach($groups as $group) {
                                            $circles = $user->circles->filter(fn($c) => $c->group_id == $group->id);
                                            $serialized_circles[] = sprintf('data-filtered-group_%s="%s"', $group->id, $circles->pluck('id')->join('|'));
                                        }

                                        @endphp

										<tr data-filtered-payment_method="{{ $user->payment_method_id }}" {!! join(' ', $serialized_circles) !!}>
											<td>
												<input type="hidden" name="user_id[]" value="{{ $user->id }}">
												{{ $user->printableName() }}
											</td>

											@foreach($currencies as $curr)
												<td class="text-filterable-cell">
													{{ printablePriceCurrency($user->currentBalanceAmount($curr), '.', $curr) }}
												</td>
											@endforeach

											<td>
												{{ $user->payment_method->name }}

												@if(($user->payment_method->valid_config)($user) == false)
													<i class="bi-slash-circle"></i>
												@endif
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
                </div>
            </div>

            <div class="modal-footer">
                <form class="form-inline iblock inner-form" action="{{ url('movements/document/credits/csv?dummy=1') }}" method="GET">
                    <input type="hidden" name="pre-saved-function" value="collectFilteredUsers">
                    <input type="hidden" name="collectFilteredUsers" value="#creditsTable">
                    <input type="hidden" name="pre-saved-function" value="formToDownload">
                    <button type="submit" class="btn btn-success">{{ __('texts.generic.exports.csv') }} <i class="bi-download"></i></button>
                </form>

                @if($currentgas->hasFeature('rid'))
                    <a type="button" class="btn btn-success" data-bs-toggle="collapse" href="#exportRID">{{ __('texts.generic.exports.sepa') }}<span class="caret"></span></a>
                @endif

                @if($currentgas->hasFeature('integralces'))
                    <a type="button" class="btn btn-success" data-bs-toggle="collapse" href="#exportIntegralCES">{{ __('texts.generic.exports.integralces') }}<span class="caret"></span></a>
                @endif

                <a type="button" class="btn btn-success" data-bs-toggle="collapse" href="#sendCreditsMail">{{ __('texts.notifications.send_to_current_users') }}<span class="caret"></span></a>

                @if($currentgas->hasFeature('rid'))
                    <div class="collapse well" id="exportRID">
                        <form class="form-horizontal inner-form" action="{{ url('movements/document/credits/rid?download=1') }}" method="GET">
                            <input type="hidden" name="pre-saved-function" value="formToDownload">
                            <x-larastrap::datepicker name="date" tlabel="generic.date" requird defaults_now />
                            <x-larastrap::text name="body" tlabel="movements.causal" :value="__('texts.movements.generic_causal')" />
                            <button type="submit" class="btn btn-success">{{ __('texts.generic.exports.sepa') }}</button>
                        </form>
                    </div>
                @endif

                @if($currentgas->hasFeature('integralces'))
                    <div class="collapse well" id="exportIntegralCES">
                        <form class="form-horizontal inner-form" action="{{ url('movements/document/credits/integralces?download=1') }}" method="GET">
                            <input type="hidden" name="pre-saved-function" value="formToDownload">
                            <x-larastrap::text name="body" tlabel="movements.causal" :value="__('texts.movements.generic_causal')" />
                            <button type="submit" class="btn btn-success">{{ __('texts.generic.exports.integralces') }}</button>
                        </form>
                    </div>
                @endif

                <div class="collapse well" id="sendCreditsMail">
                    <form class="form-horizontal inner-form" method="POST" action="{{ route('notifications.store') }}">
                        <input type="hidden" name="close-modal" value="1">
                        <input type="hidden" name="pre-saved-function" value="collectFilteredUsers">
                        <input type="hidden" name="collectFilteredUsers" value="#creditsTable">
                        <input type="hidden" name="type" value="notification">

                        @include('notification.base-edit', [
							'notification' => null,
							'select_users' => false,
							'instant' => true,
							'mailtype' => 'credit_notification',
						])

                        <button type="submit" class="btn btn-success">{{ __('texts.notifications.name') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
