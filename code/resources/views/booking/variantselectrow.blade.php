<div class="row inline-variant-selector {{ $master ? 'master-variant-selector' : '' }} g-0">
    <div class="col pe-2">
        <div class="input-group booking-product-quantity booking-variant-quantity">
            <input type="text" class="form-control number {{ $master ? 'skip-on-submit' : '' }}" name="variant_quantity_{{ $product->id }}[]" value="{{ ($saved != null) ? $saved->quantity : '0' }}" />
            <div class="input-group-text">{{ $product->printableMeasure() }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>

    @foreach(App\VariantCombo::activeValues($product->variantCombos) as $variant_id => $variant_values)
        <div class="col-auto px-2">
            <select class="form-select {{ $master ? 'skip-on-submit' : '' }}" name="variant_selection_{{ $variant_id }}[]" {{ $order->isActive() == false ? 'disabled' : '' }}>
                @foreach($variant_values as $value_id => $value)
                    <option value="{{ $value_id }}" {{ ($saved != null && $saved->hasCombination($variant_id, $value_id)) ? 'selected="selected"' : '' }}>{{ $value }}</option>
                @endforeach
            </select>
        </div>
    @endforeach

    @if($order->isActive())
        <div class="col-1">
            <button class="btn btn-light float-end add-variant"><i class="bi-plus"></i></button>
        </div>
    @endif
</div>
