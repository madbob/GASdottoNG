<form class="form-horizontal main-form" method="PUT" action="{{ url('products/' . $product->id) }}">
	<div class="row">
		<div class="col-md-6">
			@include('commons.staticstringfield', ['obj' => $product, 'name' => 'price', 'label' => 'Prezzo Unitario', 'mandatory' => true])
			@include('commons.staticstringfield', ['obj' => $product, 'name' => 'transport', 'label' => 'Prezzo Trasporto'])
			@include('commons.staticobjfield', ['obj' => $product, 'name' => 'category', 'label' => 'Categoria'])
			@include('commons.staticobjfield', ['obj' => $product, 'name' => 'measure', 'label' => 'Unità di Misura'])
			@include('commons.staticstringfield', ['obj' => $product, 'name' => 'description', 'label' => 'Descrizione'])
			@include('commons.staticboolfield', ['obj' => $product, 'name' => 'active', 'label' => 'Ordinabile'])
		</div>
		<div class="col-md-6">
			<div class="row">
				<div class="col-md-6">
					@include('commons.staticstringfield', ['obj' => $product, 'name' => 'partitioning', 'label' => 'Pezzatura'])
				</div>
				<div class="col-md-6">
					@include('commons.staticboolfield', ['obj' => $product, 'name' => 'variable', 'label' => 'Variabile'])
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					@include('commons.staticstringfield', ['obj' => $product, 'name' => 'package', 'label' => 'Confezione'])
				</div>
				<div class="col-md-6">
					@include('commons.staticstringfield', ['obj' => $product, 'name' => 'multiple', 'label' => 'Multiplo'])
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					@include('commons.staticstringfield', ['obj' => $product, 'name' => 'minimum', 'label' => 'Minimo'])
				</div>
				<div class="col-md-6">
					@include('commons.staticstringfield', ['obj' => $product, 'name' => 'totalmax', 'label' => 'Massimo'])
				</div>
			</div>

			@include('product.variantsviewer', ['product' => $product])
		</div>
	</div>
</form>
