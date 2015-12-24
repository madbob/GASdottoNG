<form class="form-horizontal main-form" method="POST" action="{{ url('products/' . $product->id) }}">
	<div class="row">
		<div class="col-md-6">
			@include('product.base-edit', ['product' => $product])
		</div>
		<div class="col-md-6">
			<div class="well">
				<div class="row">
					<div class="col-md-6">
						@include('commons.decimalfield', ['obj' => $product, 'name' => 'partitioning', 'label' => 'Pezzatura'])
					</div>
					<div class="col-md-6">
						@include('commons.boolfield', ['obj' => $product, 'name' => 'variable', 'label' => 'Variabile'])
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						@include('commons.decimalfield', ['obj' => $product, 'name' => 'package', 'label' => 'Confezione'])
					</div>
					<div class="col-md-6">
						@include('commons.decimalfield', ['obj' => $product, 'name' => 'multiple', 'label' => 'Multiplo'])
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						@include('commons.decimalfield', ['obj' => $product, 'name' => 'minimum', 'label' => 'Minimo'])
					</div>
					<div class="col-md-6">
						@include('commons.decimalfield', ['obj' => $product, 'name' => 'maximum', 'label' => 'Massimo'])
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
		</div>
	</div>

	@include('commons.formbuttons')
</form>
