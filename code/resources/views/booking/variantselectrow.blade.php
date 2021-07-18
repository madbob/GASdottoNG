<div class="row inline-variant-selector {{ $master ? 'master-variant-selector' : '' }} g-0">
    <div class="col pe-2">
        <div class="input-group booking-product-quantity booking-variant-quantity">
            <input type="text" class="form-control number {{ $master ? 'skip-on-submit' : '' }}" name="variant_quantity_{{ $product->id }}[]" value="{{ ($saved != null) ? $saved->quantity : '0' }}" />
            <div class="input-group-text">{{ $product->printableMeasure() }}</div>
        </div>
    </div>

    @foreach($product->variants as $variant)
        <div class="col px-2">
            <select class="form-select {{ $master ? 'skip-on-submit' : '' }}" name="variant_selection_{{ $variant->id }}[]" {{ $order->isActive() == false ? 'disabled' : '' }}>
                @foreach($variant->values as $value)
                    <option data-variant-price="{{ $value->price_offset }}" value="{{ $value->id }}" {{ ($saved != null && $saved->hasCombination($variant, $value)) ? 'selected="selected"' : '' }}>{{ $value->value }}</option>
                @endforeach
            </select>
        </div>
    @endforeach

    @if($order->isActive())
        <div class="col-2">
            <button class="btn btn-light float-end add-variant"><i class="bi-plus"></i></button>
        </div>
    @endif
</div>
