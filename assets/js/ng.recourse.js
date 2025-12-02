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
                                text: data.message,
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
                    if (status === 403) {
                        Swal.fire({
                            title: "Ops!",
                            text: err.message,
                        });
                        return false;
                    }
                    
                    Swal.fire({
                        title: "Ops!",
                        icon: 'error',
                        text: 'Ocorreu um erro inesperado'
                    });
                });
            }
        };
    }]);

    // Controlador da interface
    module.controller('RecourseController', ['$scope', 'RecourseService', '$sce', function ($scope, RecourseService, $sce) {
        $scope.data = {
            recourses: []
        };
        $scope.veriftRecourses = true;
        $scope.textVerifyRecourses = 'Verificando recursos . . . ';
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

                        recourse.recourseSend = moment(recourse.recourseSend?.date).format('DD/MM/YYYY HH:mm');
                        recourse.recourseDateReply = recourse.recourseDateReply?.date ? moment(recourse.recourseDateReply.date).format('DD/MM/YYYY HH:mm') : null;
                        recourse.recourseStatus = $scope.getSituation(recourse.recourseStatus);

                        if (recourse.recourseReply === null || recourse.recourseReply === '') {
                            $scope.countNotReply++;
                        }

                        recourse.recourseText = $sce.trustAsHtml(recourse.recourseText);

                        $scope.data.recourses.push(recourse);
                    });


                    if($scope.data.recourses.length > 0 ) {
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

        // Cria um DOM temporário para remover tags, contar caracteres e cortar com HTML limpo
        $scope.trustHtmlPreview = function(html) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const text = tempDiv.textContent || tempDiv.innerText || '';

            const shortText = text.length > 100 ? text.substring(0, 100) + '...' : text;
            return $sce.trustAsHtml(shortText);
        };
        // Verifica se tem mais de 100 caracteres
        $scope.hasMoreThan100 = function(html) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const text = tempDiv.textContent || tempDiv.innerText || '';
            return text.length > 100;
        };

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

        const froalaEditor = {
            instance: null,
            getSettings(recourseReply) {
                return {
                    heightMin: 200,
                    toolbarButtons: ['bold', 'italic', 'underline', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'insertLink', 'textColor'],
                    colorsText: ['#61BD6D', '#1ABC9C', '#54ACD2', '#ec5353', 'REMOVE'],
                    events: {
                        initialized: function () {
                            this.html.set(recourseReply || '')
                        },
                    },
                }
            },
        }

        $scope.replyRecourse = function (recourse) {
            // Caso o agente que clicou não foi o mesmo que respondeu ao recurso, então é bloqueado para responder
            const replyAgent = recourse.replyAgent
            if ((replyAgent !== null) && replyAgent.id !== MapasCulturais.userProfile.id) {
                Swal.fire({
                    title: "Ops!",
                    text: `Este recurso já foi respondido pelo parecerista ${replyAgent.name}`,
                })
                return false
            }

            Swal.fire({
                title: "Responder Recurso",
                html: htmlReplyRecourse(),
                width: 800,
                padding: "1em 5em",
                showCancelButton: true,
                confirmButtonText: 'Salvar resposta',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: "btn-success-rec",
                    cancelButton: "btn-warning-rec",
                },
                didOpen: () => {
                    // froalaEditor.instance = new FroalaEditor("#reply-textarea", froalaEditor.getSettings(recourse.recourseReply))

                    const status = recourse.status
                    const currentGrade = recourse.registration.consolidatedResult

                    $('[status-recourse]').val(status)

                    $('[status-recourse]').on('change', function () {
                        const selectedValue = $(this).val()
                        // Status 1 e 8 são 'deferido' e 'deferido parcialmente', respectivamente
                        if (MapasCulturais.entity.evaluationMethodConfiguration.type === 'technical' && (selectedValue == '1' || selectedValue == '8')) {
                            $('[grade-wrapper]').removeClass('d-none')
                            $('[current-grade]').text(currentGrade)
                            $('[new-grade]').val(recourse.replyResult)
                        } else {
                            $('[grade-wrapper]').addClass('d-none')
                        }
                    })

                    $('[status-recourse]').trigger('change')
                },
            }).then((res) => {
                if (res.isConfirmed) {
                    $scope.sendReplyRecourse(
                        recourse.id,
                        $('[status-recourse]').val() || '0',
                        // froalaEditor.instance.html.get(),
                        $('[new-grade]').val()
                    );
                }
            })
        }

        function htmlReplyRecourse() {
            return `
                <p style="margin-bottom: 1em;">Digite a resposta do recurso e selecione a situação</p>
                <textarea id="reply-textarea"></textarea>
                <label style="float: left;">Situação:</label>
                <select status-recourse class="form-control">
                    <option value="0" disabled selected>-- Selecione a situação --</option>
                    <option value="1">Deferido</option>
                    <option value="8">Deferido parcialmente</option>
                    <option value="-9">Indeferido</option>
                </select>
                <div class="form-group d-none" grade-wrapper>
                    <label style="float: left;">Nova nota:</label>
                    <input type="text" new-grade class="form-control" placeholder="Digite a nova nota">
                    <p class="badge badge-info current-grade">A nota atual é: <span current-grade></span></p>
                </div>
            `
        }

        $scope.sendReplyRecourse = function (id, status, reply, replyResult) {
            RecourseService.sendReply(id, status, reply, replyResult);
        };

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
                        $scope.dialogSecult('Resposta', reply);
                    }else{
                        $scope.dialogSecult('Ops!', 'Você não tem permissão para visualizar a resposta');
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

        $scope.infoUserRecourse = function (text) {
            Swal.fire({
                html: text,
                customClass: {
                    htmlContainer: 'modal-read-recourse'
                },
            });
        };

        $scope.dialogSecult = function (title, text, icon = '', footer = '') {
            Swal.fire({
                title: title,
                html: text,
                icon: icon,
                width: 850,
                footer: footer,
                showCancelButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                customClass: {
                    htmlContainer: 'modal-read-recourse'
                },
            });
        };
    }]);
})(angular);
