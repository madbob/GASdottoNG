<table class="table table-striped">
	<thead>
		<tr>
			<th width="17%">Prodotto</th>
			<th width="12%">Prezzo</th>
			<th width="12%">Trasporto</th>
			<th width="9%">Unità di Misura</th>
			<th width="9%">Quantità Ordinata</th>
			<th width="9%">Totale Prezzo</th>
			<th width="9%">Totale Trasporto</th>
			<th width="9%">Quantità Consegnata</th>
			<th width="9%">Totale Consegnato</th>
			<th width="7%">Note</th>
		</tr>
	</thead>
	<tbody>
		@foreach($order->products as $product)
		<tr>
			<td>
				<input type="hidden" name="productid[]" value="{{ $product->id }}" />
				<label>{{ $product->printableName() }}</label>
			</td>
			<td>
				<div class="input-group">
					<input class="form-control" name="productprice[]" value="{{ printablePrice($product->price) }}" />
					<div class="input-group-addon">€</div>
				</div>
			</td>
			<td>
				<div class="input-group">
					<input class="form-control" name="producttransport[]" value="{{ printablePrice($product->transport) }}" />
					<div class="input-group-addon">€</div>
				</div>
			</td>
			<td><label>{{ $product->measure->printableName() }}</label></td>

			<td><label>{{ $summary->products[$product->id]['quantity'] }}</label></td>
			<td><label>{{ printablePrice($summary->products[$product->id]['price']) }} €</label></td>
			<td><label>{{ printablePrice($summary->products[$product->id]['transport']) }} €</label></td>
			<td><label>{{ $summary->products[$product->id]['delivered'] }}</label></td>
			<td><label>{{ printablePrice($summary->products[$product->id]['price_delivered']) }} €</label></td>
			<td><label>{{ $summary->products[$product->id]['notes'] }}</label></td>
		</tr>
		@endforeach
	</tbody>
	<thead>
		<tr>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th>{{ printablePrice($summary->price) }} €</th>
			<th>{{ printablePrice($summary->transport) }} €</th>
			<th></th>
			<th>{{ printablePrice($summary->price_delivered) }} €</th>
			<th></th>
		</tr>
	</thead>
</table>
