<product>
    @if(!empty($obj->barcode))
        <sku>{{ $obj->supplier_code }}</sku>
    @endif
    <name>{{ $obj->name }}</name>
    <category>{{ $obj->category->name }}</category>
    <um>{{ $obj->measure->name }}</um>
    <description>{{ $obj->description }}</description>
    <active>{{ $obj->active ? 'true' : 'false' }}</active>

    <orderInfo>
        <umPrice>{{ $obj->price }}</umPrice>

        @if($obj->package_size != 0)
            <packageQty>{{ $obj->package_size }}</packageQty>
        @endif
        @if($obj->min_quantity != 0)
            <minQty>{{ $obj->min_quantity }}</minQty>
        @endif
        @if($obj->max_quantity != 0)
            <maxQty>{{ $obj->max_quantity }}</maxQty>
        @endif
        @if($obj->transport != 0)
            <shippingCost>{{ $obj->transport }}</shippingCost>
        @endif
    </orderInfo>

    @if($obj->variants->isEmpty() == false)
        <variants>
            @foreach($obj->variants as $variant)
                <variant name="{{ $variant->name }}">
                    @foreach($variant->values as $value)
                        <value>{{ $value->value }}</value>
                    @endforeach
                </variant>
            @endforeach
        </variants>
    @endif
</product>
