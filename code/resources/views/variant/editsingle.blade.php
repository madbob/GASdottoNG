<x-larastrap::modal>
    <?php $combos = $product->variant_combos ?>

    <x-larastrap::form classes="inner-form" method="POST" :action="route('variants.updatematrix', $product->id)">
        <input type="hidden" name="pre-saved-function" value="checkVariantsValues">
		<input type="hidden" name="reload-portion" value="#variants_editor_{{ sanitizeId($product->id) }}">
        <input type="hidden" name="close-modal" value="1">

        <div class="row">
            <div class="col">
                <x-larastrap::text name="name" tlabel="generic.name" :value="$product->variants->first()->name" />

                <hr />

                <table class="table table-borderless table-sm dynamic-table sortable-table">
					@include('variant.matrixhead', [
                        'combos' => $combos,
                    ])

                    <tbody>
                        @foreach($combos as $index => $combo)
                            <x-larastrap::enclose :obj="$combo">
                                <tr>
                                    <td>
                                        <span class="btn btn-info sorter"><i class="bi bi-arrow-down-up"></i></span>
                                    </td>

                                    @foreach($combo->values as $value)
                                        <td>
											<input type="hidden" name="combination[]" value="{{ $combo->values->pluck('id')->join(',') }}">
											<x-larastrap::text name="value" squeeze npostfix="[]" :value="$value->value" />
										</td>
                                    @endforeach

                                    <td>
                                        <x-larastrap::check name="active" squeeze npostfix="[]" :value="$combo->id" :checked="$combo->active" />
                                    </td>

                                    <td>
                                        <x-larastrap::text name="code" squeeze npostfix="[]" />
                                    </td>

                                    <td>
                                        <x-larastrap::price name="price_offset" squeeze npostfix="[]" />
                                    </td>

                                    @if ($product->measure->discrete)
                                        <td>
                                            <x-larastrap::number name="weight_offset" squeeze npostfix="[]" ttextappend="generic.kilos" step="0.01" />
                                        </td>
                                    @endif

									<td>
										<div class="btn btn-danger remove-row float-end {{ $combo->hasBookings() ? 'disabled' : '' }}">
											<i class="bi-x-lg"></i>
										</div>
									</td>
                                </tr>
                            </x-larastrap::enclose>
                        @endforeach

						<tr>
				            <td colspan="6">
				                <a href="#" class="btn btn-warning add-row">{{ __('texts.generic.add_new') }}</a>
				            </td>
				        </tr>
                    </tbody>
					<tfoot>
						<x-larastrap::enclose :obj="null">
							<tr>
                                <td>
                                    <span class="btn btn-info sorter"><i class="bi bi-arrow-down-up"></i></span>
                                </td>

								<td>
									<input type="hidden" name="combination[]" value="put_random_here">
									<x-larastrap::text name="value" squeeze npostfix="[]" />
								</td>

								<td>
									<x-larastrap::check name="active" squeeze npostfix="[]" value="put_random_here" checked="true" />
								</td>

								<td>
									<x-larastrap::text name="code" squeeze npostfix="[]" />
								</td>

								<td>
									<x-larastrap::price name="price_offset" squeeze npostfix="[]" />
								</td>

								@if ($product->measure->discrete)
									<td>
										<x-larastrap::number name="weight_offset" squeeze npostfix="[]" ttextappend="generic.kilos" step="0.01" />
									</td>
								@endif

								<td>
									<div class="btn btn-danger remove-row float-end">
										<i class="bi-x-lg"></i>
									</div>
								</td>
							</tr>
						</x-larastrap::enclose>
					</tfoot>
                </table>
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::modal>
