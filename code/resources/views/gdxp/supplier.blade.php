<?xml version="1.0" encoding="UTF-8"?>
<gdxp protocolVersion="0.2" creationDate="{{ date('YmdHis') }}" applicationSignature="GASdottoNG">
	<supplier>
		<taxCode>{{ $obj->taxcode }}</taxCode>
		<vatNumber>{{ $obj->vat }}</vatNumber>
		<name>{{ $obj->business_name }}</name>

		<address>
            <?php list($street, $city, $cap) = $obj->getAddress() ?>
			<street>{{ $street }}</street>
			<locality>{{ $city }}</locality>
			<zipCode>{{ $cap }}</zipCode>
			<country>IT</country>
		</address>

		<contacts>
			<contact>
				<primary>
                    @foreach($obj->contacts as $contact)
                        @if($contact->type == 'phone')
                            <phoneNumber>{{ $contact->value }}</phoneNumber>
                        @elseif($contact->type == 'fax')
                            <faxNumber>{{ $contact->value }}</faxNumber>
                        @elseif($contact->type == 'email')
                            <emailAddress>{{ $contact->value }}</emailAddress>
                        @elseif($contact->type == 'website')
                            <webSite>{{ $contact->value }}</webSite>
                        @endif
                    @endforeach
				</primary>
			</contact>
		</contacts>

		<note>{{ !empty($obj->order_method) ? $obj->order_method . "\n" : '' }}{{ !empty($obj->payment_method) ? $obj->payment_method : '' }}</note>

		<products>
            @if(isset($orders))
                @foreach($orders as $order)
                    @foreach($order->products as $product)
                        @include('gdxp.product', ['obj' => $product])
                    @endforeach
                @endforeach
            @else
                @foreach($obj->products as $product)
                    @include('gdxp.product', ['obj' => $product])
                @endforeach
            @endif
		</products>

        @if(isset($orders))
            <orders>
                @foreach($orders as $order)
                    @include('gdxp.order', ['obj' => $order])
                @endforeach
            </orders>
        @endif
	</supplier>
</gdxp>
