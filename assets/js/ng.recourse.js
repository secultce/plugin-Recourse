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
            sendReply: function (id, status, reply) {
                var data = {
                    entityId: id,
                    status,
                    reply
                };
                return $http.post(urlBase + 'recursos/responder', data).
                    success(function (data, status) {
                        console.log({ data })
                            // Swal.fire({
                            //     title: "Sucesso!",
                            //     text: data.message
                            // });
                    // setTimeout(() => {
                    //     window.location.reload()
                    // }, "2000");
                        // return data
                    }).
                    error(function (data, status) {
                        console.log(data)
                        // Swal.fire({
                        //     title: "Ops!",
                        //     icon: 'error',
                        //     text: data.message
                        // });
                    });
            },
            getRegistration: function (registration) {
                return $http.get(urlBase + 'recursos/registration/'+ registration).
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
        $scope.veriftRecourses = true;
        $scope.textVerifyRecourses = 'Verificando recursos . . . ';
        $scope.tableRecourse = true;
        $scope.divReplyRecourse = false
        $scope.recourseAdmin = {
            idRecourse: '',
            registration: '',
            agent: '',
            recourseText: '',
            recourseSend: '',
            status: ''
        };
        $scope.noteActual=  '';
        $scope.newNoteReply = '';
        $scope.reply = '';
        $scope.countNotReply = 0;

        var recoursesAll = RecourseService.getRecourseAll(MapasCulturais.entity.id)
            .then(res => JSON.parse(JSON.stringify(res)).data)
            .then(res => {
                for (i = 0; i < res.length; i++){
                    if(res[i]['recourseSend']){
                        res[i]['recourseSend'] = moment(res[i]['recourseSend'].date).format('DD/MM/YYYY hh:mm')
                    }
                    if(res[i]['recourseDateReply']){
                        res[i]['recourseDateReply'] = moment(res[i]['recourseDateReply'].date).format('DD/MM/YYYY hh:mm')
                    }
                    //Formatando o status de nome
                    if(res[i]['recourseStatus']) {
                        res[i]['recourseStatus'] =  $scope.getSituation(res[i]['recourseStatus']);
                    }

                    if(res[i]['recourseReply'] == null)
                    {
                        $scope.countNotReply++;
                        console.log('Dentro do If: ',$scope.countNotReply);
                    }
                }
                $scope.data.recourses = res;
                if(res.length > 0 )
                {
                    $scope.tableRecourse = true;
                    $scope.veriftRecourses = false;
                    $scope.textVerifyRecourses = '';
                }
                if(res.length == 0)
                {
                    $scope.textVerifyRecourses = 'Não existe recursos para essa oportunidade'
                }
                // you returned no value here!

            });

        $scope.getSituation = function (situation) {
            var statusStituation = '';

                switch (situation) {
                    case '0':
                        statusStituation = 'Aberto';
                        break;
                    case '1':
                        statusStituation = 'Deferido';
                        break;
                    case '-9':
                        statusStituation = 'Indeferido';
                        break;
                }

            return statusStituation;
        };

        $scope.replyRecourse = function (id, registration, agent, text, send, status, agentReply, reply) {
            //Caso o agente que clicou não foi o mesmo que respondeu ao recurso, então é bloqueado para responder
            if( (agentReply !== null) && agentReply !== MapasCulturais.userProfile.id){
                Swal.fire({
                    title: "Ops!",
                    text: 'Você não poderá responder a esse recurso ou visualizar a resposta'
                });
                return false;
            }
            $scope.tableRecourse = false
            $scope.divReplyRecourse = true
            $scope.recourseAdmin = {
                idRecourse: id,
                registration: registration,
                agent: agent,
                recourseText: text,
                recourseSend: send,
                status: status,
                agentReply: agentReply,
                reply: reply
            }
        }

        $scope.changeSituation = function () {
            var registration = '';
            if($scope.recourseAdmin.status == '1'){
                registration =  RecourseService.getRegistration($scope.recourseAdmin.registration)
                registration.then(res => {
                    console.log(res)
                    $scope.noteActual = res.data.resultConsolidate
                }).catch(err => {
                    console.log(err)
                })
            }
        }

        $scope.sendReplyRecourse = function (id, status, reply) {
            var reply = RecourseService.sendReply(id, status, reply);
            console.log({reply})
        }

        $scope.backRecourse = function () {
            $scope.tableRecourse = true
            $scope.divReplyRecourse = false
        }

        $scope.redirectRegistrarion = function (registration) {
           return MapasCulturais.createUrl('inscricao', registration);
        }


        $scope.dialogSecult = function (id, title, text, icon = '', footer = '') {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                width: 850,
                footer: footer,
                // input: "textarea",
                // html: `<label>Recurso Enviado:</label><br/>${text} <br/><hr/>
                // <label>Situação do Recurso:</label>
                // <select name="status" class="swal2-input" id="cars">
                //     <option value="">--Selecione--</option>
                //     <option value="deferido">Deferido</option>
                //     <option value="indeferido">Indeferido</option>
                // </select>
                // `,
                showDenyButton: true,
                showCancelButton: false,
                // denyButtonText: `Sair`,
                // confirmButtonText: "Responder",
                allowOutsideClick: false,
                allowEscapeKey: false,
                // preConfirm: (result) => {
                //     statusRecourse = document.getElementById("cars").value,
                //         RecourseService.sendReply(id, result, statusRecourse)
                //     return false; // Prevent confirmed
                // },
                // preDeny: () => {
                //     if (someLogic()) {
                //         return false; // Prevent denied
                //     }
                // },
            });
        }


    }]);
})(angular);
