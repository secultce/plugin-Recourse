(function (angular) {
    var module = angular.module('ng.recourse', ['ngSanitize']);
    
    // modifica as requisições POST para que sejam lidas corretamente pelo Slim
    module.config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.headers.put['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.headers.common['X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $httpProvider.defaults.transformRequest = function (data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

            return result;
        };
    }]);



    // Seriço que executa no servidor as requisições HTTP
    module.factory('RecourseService', ['$http',  function ($http) {
        var urlBase = MapasCulturais.baseURL
        return {
            getRecourseAll: function(opportunityId){
               return $http.get(urlBase + 'recursos/todos/' + opportunityId).
                success(function (data, status) {
                   console.log({data})
                   return data
                }).
                error(function (data, status) {
                    console.log(data)
                });
            }
        };
    }]);

    // Controlador da interface
    module.controller('RecourseController', ['$scope', 'RecourseService', function ($scope, RecourseService) {
        $scope.data = {
            recourses: []
         };
        
        var recoursesAll = RecourseService.getRecourseAll(MapasCulturais.entity.id)
        .then(res => JSON.parse(JSON.stringify(res)).data)
        .then(res => {
            console.log(res);
            $scope.data.recourses = res
            // you returned no value here!
            // return res;
        })
        console.log(recoursesAll)
        
    }]);
})(angular);
