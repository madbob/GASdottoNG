<div class="row">
    @foreach ($product->variants as $variant)
        <div class="row">
            <div class="col-md-3 variant_name control-label">
                <span class="variant_name">{{ $variant->name }}</span>
            </div>
            <div class="col-md-6 control-label">
                <span>{{ $variant->printableValues() }}</span>
            </div>
        </div>
    @endforeach
</div>
