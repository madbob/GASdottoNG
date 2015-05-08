@extends('app') @section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-3 sidebar">
			<div class="panel panel-default">
				<ul class="list-group">
					<li class="list-group-item"><a href="/view/home">Home</a></li>
					<li class="list-group-item"><a href="/view/suppliers">Fornitori</a></li>
				</ul>
			</div>
		</div>
		<div class="col-md-9" id="area">
<!-- 		Questa sezione dovrebbe essere visibile solamente per la pagina suppliers...-->
			<div ng-app="suppliers" ng-controller="suppliersController">
				<ul class="list-group" ng-repeat="supplier in suppliersList">
					<li class="list-group-item"><a ng-click="openSupplierDetail(supplier.id)"><b>@{{supplier.name}}</b></a></li>
					<div class="container" ng-show="supplierDetail.id == supplier.id">
						<div>@{{supplierDetail.name}}</div>
						<div>@{{supplierDetail.mail}}</div>
						<div>@{{supplierDetail.phone}}</div>
						<div>@{{supplierDetail.description}}</div>
					</div>
				</ul>
			</div>
<!-- 			Fino a qui... -->
		</div>
	</div>
</div>
@endsection
