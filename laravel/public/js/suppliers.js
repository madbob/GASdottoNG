var app = angular.module("suppliers", []);

app.controller("suppliersController", function($scope, $http) {

	$scope.controller = 'suppliersController';
	
	$http.get('/supplier').success(function(data) {
		$scope.suppliersList = data;
	}).error(function(data, status) {
		$scope.error = data.message || " Request failed " || status;
	});

	$scope.openSupplierDetail = function(id) {
		$http.get('/supplier/' + id).success(function(data) {
			$scope.supplierDetail = data;
		}).error(function(data, status) {
			$scope.error = data.message || " Request failed " || status;
		});
	}

});