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
        const urlBase = MapasCulturais.baseURL
        return {
            getRecourseAll: function (opportunityId) {
                return $http.get(urlBase + 'recursos/todos/' + opportunityId)
                    .success(data => data)
                    .error(function (data, status) {
                        console.error(status)
                        console.error(data)
                    });
            },
            sendReply: function (id, status, reply, replyResult = null) {
                const data = {
                    entityId: id,
                    status,
                    reply,
                    replyResult,
                };

                return $http.post(urlBase + 'recursos/responder', data)
                    .success(function (response, status) {
                        if(status === 202){
                            Swal.fire({
                                title: "Sucesso!",
                                text: response.message,
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                timer: 1500,
                                timerProgressBar: true,
                                customClass: {
                                    popup: 'colored-toast',
                                },
                            });
                            setTimeout(function () {
                                window.location.reload();
                            }, 1500);
                        }
                    })
                    .error(function (data, status) {
                        if(status === 403) {
                            Swal.fire({
                                title: "Ops!",
                                text: 'Este recurso já foi respondido por outro parecerista.',
                            });
                            return false;
                        }

                        console.error(status)
                        console.error(data)
                        Swal.fire({
                            title: "Ops!",
                            icon: 'error',
                            text: data.message,
                            footer: data.errorMessage,
                        });
                    });
            },
            getRegistration: function (registration) {
                return $http.get(urlBase + 'recursos/registration/'+ registration)
                    .success(function (data, status) {
                        return data
                    })
                    .error(function (data, status) {
                        console.error(status)
                        console.error(data)
                    });
            },
            verifyView: function (entity) {
                return $http.post(urlBase + 'recursos/verifyPermission', entity)
            },
            publish: function (opportunity){
                var dataPublish = {
                    opportunity
                };
                return $http.post(urlBase + 'recursos/publish', dataPublish).
                success(function (data, status){
                    console.log({data});
                    console.log({status});
                }).error(function (err, status) {
                    // console.log({err});
                    console.log({status});
                    if(status !== 200){
                        Swal.fire({
                            title: "Ops!",
                            icon: 'error',
                            text: 'Ocorreu um erro inesperado'
                        });
                    }
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
        $scope.currentGrade =  '';
        $scope.newGrade = '';
        $scope.reply = '';
        $scope.countNotReply = 0;
        $scope.isPublish = false;
        //Configuração de recurso (Admin)
        $scope.modelSelectConfigurationRecourse = false;
        $scope.divSelectConfiguration = false;

        if(MapasCulturais.hasOwnProperty('entity')){
            const recoursesAll = RecourseService.getRecourseAll(MapasCulturais.entity.id)
                .success(data => {
                    data.forEach(recourse => {
                        //Se tiver um publicado o botão no front é desabilitado
                        if (recourse.replyPublish) {
                            $scope.isPublish = true;
                        }

                        recourse.recourseSend = moment(recourse.recourseSend?.date).format('DD/MM/YYYY hh:mm');
                        recourse.recourseDateReply = recourse.recourseDateReply?.date ? moment(recourse.recourseDateReply.date).format('DD/MM/YYYY hh:mm') : null;
                        recourse.recourseStatus = $scope.getSituation(recourse.recourseStatus);

                        if (recourse.recourseReply === null || recourse.recourseReply === '') {
                            $scope.countNotReply++;
                        }

                        $scope.data.recourses.push(recourse);
                    });


                    if($scope.data.recourses.length > 0 ) {
                        $scope.tableRecourse = true;
                        $scope.veriftRecourses = false;
                        $scope.textVerifyRecourses = '';
                    }
                    if($scope.data.recourses.length === 0) {
                        $scope.textVerifyRecourses = 'Não existem recursos para essa oportunidade'
                    }
                    // you returned no value here!
                })
                .error((error, status) => {
                    console.error(status);
                    console.error(error);
                    $scope.textVerifyRecourses = 'Não existem recursos para essa oportunidade';
                });
        }


        $scope.getSituation = function (situation) {
            let statusSituation = '';

                switch (situation) {
                    case '0':
                        statusSituation = 'Aberto';
                        break;
                    case '1':
                        statusSituation = 'Deferido';
                        break;
                    case '8':
                        statusSituation = 'Deferido parcialmente';
                        break;
                    case '-9':
                        statusSituation = 'Indeferido';
                        break;
                }

            return statusSituation;
        };

        $scope.replyRecourse = function (id, registration, agent, text, send, status, replyAgent, reply, replyResult) {
            //Caso o agente que clicou não foi o mesmo que respondeu ao recurso, então é bloqueado para responder
            if ((replyAgent !== null) && replyAgent.id !== MapasCulturais.userProfile.id) {
                Swal.fire({
                    title: "Ops!",
                    text: `Este recurso já foi respondido pelo parecerista ${replyAgent.name}`,
                });
                return false;
            }
            $scope.tableRecourse = false
            $scope.divReplyRecourse = true
            $scope.recourseAdmin = {
                idRecourse: id,
                registration,
                agent,
                recourseText: text,
                recourseSend: send,
                status,
                replyAgent,
                reply,
                replyResult,
            }

            registration = RecourseService.getRegistration(registration)
            registration
                .then(res => {
                    $scope.currentGrade = res.data.resultConsolidate
                })
                .catch(err => {
                    console.error(err)
                });
        }

        $scope.changeSituation = function () {
            var registration = '';
            if($scope.recourseAdmin.status == '1' || $scope.recourseAdmin.status == '8'){
                registration =  RecourseService.getRegistration($scope.recourseAdmin.registration)
                registration
                    .then(res => {
                        $scope.currentGrade = res.data.resultConsolidate
                    })
                    .catch(err => {
                        console.error(err)
                    });
            }
        };

        $scope.sendReplyRecourse = function (id, status, reply, replyResult) {
            const response = RecourseService.sendReply(id, status, reply, replyResult);
            console.log(response);
        };

        $scope.backRecourse = function () {
            $scope.tableRecourse = true
            $scope.divReplyRecourse = false
        }

        $scope.redirectRegistration = function (registration) {
           return MapasCulturais.createUrl('inscricao', registration);
        };

        $scope.verifyView= function(recourseReply)
        {
           var reply = '';
           if(recourseReply == ''){
               reply = 'Recurso ainda não foi respondido!'
           }else{
               reply = recourseReply;
           }
            RecourseService.verifyView(MapasCulturais.entity)
                .then(res => JSON.parse(JSON.stringify(res)).data)
                .then(res => {
                    console.log({res})
                    if(res) {
                        $scope.dialogSecult(0, 'Resposta', reply);
                    }else{
                        $scope.dialogSecult(0, 'Ops!', 'Você não tem permissão para visualizar a resposta');
                    }
                })
                .catch(function(err) {
                    console.log({err})
                });
        };

        $scope.selectConfigurationRecourse = function() {
            console.log($scope.modelSelectConfigurationRecourse);
        };

        $scope.clickPublish = function(id) {
            Swal.fire({
                title: "Publicar recursos?",
                text: "Verifique se todos os recursos foram respondido. Pois ao confirmar essa ação não poderá ser " +
                    "revertida.",
                icon: "warning",
                showCancelButton: true,
                customClass: {
                    confirmButton: "btn-success-rec",
                    cancelButton: "btn-warning-rec"
                },
                confirmButtonText: "Sim, Publicar",
                cancelButtonText: "Não, sair",
            }).then((result) => {

                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Aguarde...',
                        text: 'Todos os recursos estão sendo publicados, aguarde a conclusão.',
                        imageUrl: MapasCulturais.spinnerUrl,
                        imageHeight: 30,
                        showConfirmButton: false,
                    });
                    RecourseService.publish(id)
                    .then(res => JSON.parse(JSON.stringify(res)).data)
                    .then(res => {
                        Swal.close();
                        if(res.status == 200){
                            Swal.fire({
                                title: 'Sucesso',
                                text: 'Todos os recursos foram publicados',
                                timer: 1500
                            });
                            setTimeout(function () {
                                window.location.reload();
                            }, 1500);
                        }
                    });
                }else{
                    Swal.close();
                }
            });
        };

        $scope.infoUserRecourse = function(text) {
            Swal.fire({
                html:text,
            });
        };
        $scope.dialogSecult = function (id, title, text, icon = '', footer = '') {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                width: 850,
                footer: footer,
                // showDenyButton: true,
                showCancelButton: false,
                // denyButtonText: `Sair`,
                // confirmButtonText: "Responder",
                allowOutsideClick: false,
                allowEscapeKey: false,
            });
        };



    }]);
})(angular);
