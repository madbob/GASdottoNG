<order>
    <openDate>{{ date('Ymd', strtotime($obj->start)) }}</openDate>
    <closeDate>{{ date('Ymd', strtotime($obj->end)) }}</closeDate>
    @if($obj->shipping)
        <deliveryDate>{{ date('Ymd', strtotime($obj->shipping)) }}</deliveryDate>
    @endif
</order>
