'use strict';

angular.module('app').controller('OrderController', ['$scope', '$rootScope', '$state', '$mdDialog', 'utils', 'order', function($scope, $rootScope, $state, $mdDialog, utils, order) {
    $scope.orders = {};

    $http.get('order').success(function(data) {
    	console.log(data);
        $scope.orders = data;
    })
}]);
