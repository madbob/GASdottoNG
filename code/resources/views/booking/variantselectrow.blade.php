<div class="row inline-variant-selector {{ $master ? 'master-variant-selector' : '' }} g-0 mb-1">
    <div class="col pe-1">
        <div class="input-group booking-product-quantity booking-variant-quantity">
            <input type="text" class="form-control number {{ $master ? 'skip-on-submit' : '' }}" name="variant_quantity_{{ $product->id }}[]" value="{{ ($saved != null) ? $saved->quantity : '0' }}" />
            <div class="input-group-text d-none d-xl-inline-block">{{ $product->printableMeasure() }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>

    @foreach(App\VariantCombo::activeValues($product->variant_combos) as $variant_id => $variant_values)
        <div class="col px-1">
            <select class="form-select {{ $master ? 'skip-on-submit' : '' }}" name="variant_selection_{{ $variant_id }}[]" {{ $order->isActive() == false ? 'disabled' : '' }}>
                @foreach($variant_values as $value_id => $value)
                    <option value="{{ $value_id }}" {{ ($saved != null && $saved->hasCombination($variant_id, $value_id)) ? 'selected="selected"' : '' }}>{{ $value }}</option>
                @endforeach
            </select>
        </div>
    @endforeach

    @if($order->isActive() && $while_shipping == false)
        <div class="col-auto">
            <button class="btn btn-light btn-icon float-end add-variant">
                <i class="bi-plus"></i>
            </button>
        </div>
    @endif
</div>
