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
    module.factory('RecourseService', ['$http', function ($http) {
        var urlBase = MapasCulturais.baseURL
        return {
            getRecourseAll: function (opportunityId) {
                return $http.get(urlBase + 'recursos/todos/' + opportunityId).
                    success(function (data, status) {
                        console.log({ data })
                        return data
                    }).
                    error(function (data, status) {
                        console.log(data)
                    });
            },
            sendReply: function (id, reply) {
                var data = {
                    entityId: id,
                    reply
                };
                return $http.post(urlBase + 'recursos/responder', data).
                    success(function (data, status) {
                        console.log({ data })
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
        $scope.tableRecourse = false;
        $scope.recourseAdmin = {
            idRecourse: '',
            registration: '',
            agent: '',
            recourseText: '',
            recourseSend: '',
            status: ''
        }

        var recoursesAll = RecourseService.getRecourseAll(MapasCulturais.entity.id)
            .then(res => JSON.parse(JSON.stringify(res)).data)
            .then(res => {
                console.log(res);
                $scope.data.recourses = res
                if(res.length > 0 )
                {
                    $scope.tableRecourse = true
                }
                // you returned no value here!
                // return res;
            })

        $scope.replyRecourse = function (id, registration, agent, text, status) {
            $scope.tableRecourse = false
            $scope.recourseAdmin = {
                idRecourse: id,
                registration: registration,
                agent: agent,
                recourseText: text,
                recourseSend: '',
                status: ''
            }
            console.log($scope.recourseAdmin);
            // $scope.dialogSecult(id, 'Responder Recurso', text);
        }

        $scope.dialogSecult = function (id, title, text, icon = '', footer = '') {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                width: 850,
                footer: footer,
                input: "textarea",
                html: `<label>Recurso Enviado:</label><br/>${text} <br/><hr/>
                <label>Situação do Recurso:</label>
                <select name="status" class="swal2-input" id="cars">
                    <option value="">--Selecione--</option>
                    <option value="deferido">Deferido</option>
                    <option value="indeferido">Indeferido</option>
                </select>
                `,
                showDenyButton: true,
                showCancelButton: false,
                denyButtonText: `Sair`,
                confirmButtonText: "Responder",
                allowOutsideClick: false,
                allowEscapeKey: false,
                preConfirm: (result) => {
                    console.log({ result })
                    statusRecourse = document.getElementById("cars").value,
                        RecourseService.sendReply(id, result, statusRecourse)
                    return false; // Prevent confirmed
                },
                preDeny: () => {
                    if (someLogic()) {
                        return false; // Prevent denied
                    }
                },
            });
        }


    }]);
})(angular);
