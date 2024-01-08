// A $( document ).ready() block.

$(function () {
    console.log('.opportunity-claim-box ready');
    $('.opportunity-claim-box').remove();//Removendo o botão existente no modulo de oportunidades

    $( "#resourceOptions" ).change(function() {
        var opt =  $( "#resourceOptions" ).val();
        console.log({opt})
        if (opt == '0'){
            $('#insertData').show();
            //$('#resourceOptions option[value=0]').attr('selected','selected');
            claimDisabled(opt);
        }else{
            $('#insertData').removeClass('visible');
            claimDisabled(opt);
            $('#insertData').hide();
        }
    });
    $( "#btn-open-recourse-user" ).on( "click", function() {
        console.log('btn-open-recourse-user0');




    } );

});

function claimDisabled(opt)
{
    $.ajax({
        type: "POST",
        url: MapasCulturais.baseURL+'recursos/disabledResource',
        data: {id:MapasCulturais.entity.id, claimDisabled: opt},
        dataType: "json",
        success: function (response) {
            console.log(response);
        }
    });
}

function sendRecourse(registration, opportunity)
{
    var ctxUser = '';
    console.log({ctxUser})
    var idCtxUser = 'context-recourse-user-' + registration;
    var agent = MapasCulturais.userProfile.id;
    console.log(registration, agent);



    Swal.fire({
        title: "Escrever o Recurso",
        input: "textarea",
        inputLabel: "Envie todos os destaques do seu recurso por escrito uma vez em um único campo",
        inputPlaceholder: "Mensagem para a banca",
        inputAttributes: {
            "aria-label": "Type your message here",
            "id": idCtxUser
        },
        showCancelButton: true,
        confirmButtonText: 'Enviar recurso',
        cancelButtonText: 'Sair',
        showLoaderOnConfirm: true,
        preConfirm: async (login) => {
            console.log(login);
            ctxUser = login;
            if(ctxUser == ''){
                Swal.fire({
                    position: "top-center",
                    // icon: "success",
                    title: "Precisa preencher o campo de recurso",
                    showConfirmButton: true,
                    timer: 2000
                });
                return false;
            }

        },
        allowOutsideClick: false
    }).then((result) => {
        console.log({result});

        console.log({ctxUser})
        if(ctxUser !== '') {
            var send = {
                registration,
                opportunity,
                agent,
                recourse: ctxUser
            };

            $.ajax({
                method: "POST",
                url: MapasCulturais.baseURL + 'recursos/sendRecourse',
                data: send,
                success: function(res) {
                    Swal.fire({
                        position: "top-center",
                        // icon: "success",
                        title: res.message,
                        html: "Acompanhe o andamento do recurso no seu Painel. <br /> " +
                            "<a href='#' class='btn btn-default'>Ir p/ painel</a> <a href='#' class='btn btn-info'>Sair</a> ",
                        showConfirmButton: false,
                        // timer: 1500
                    });
                    //Ocutando botão para não ter mais de um envio
                    $("#btn-recourse-"+registration).hide();

                },
                error: function() {
                    Swal.fire({
                        position: "top-center",
                        // icon: "success",
                        title: 'Ops! Ocorreu um erro inesperado',
                        html: "Acompanhe o andamento do recurso no seu Painel. <br /> " +
                            "<a href='#' class='btn btn-default'>Ir p/ painel</a> <a href='#' class='btn btn-info'>Sair</a> ",
                        showConfirmButton: false,
                        // timer: 1500
                    });
                }
            });

        }
    });
}

