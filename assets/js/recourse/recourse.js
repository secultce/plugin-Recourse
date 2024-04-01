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
    const recourseTextareaId = 'context-recourse-user-' + registration;
    const recourseAttachmentsId = 'attachment-recourse-user-' + registration;
    const agent = MapasCulturais.userProfile.id;

    Swal.fire({
        title: "Escrever o Recurso",
        html: `<div style="display: grid">
            <label to="${recourseTextareaId}">Envie todos os destaques do seu recurso por escrito uma vez em um único campo</label>
            <textarea
                id=${recourseTextareaId}
                aria-label="Type your message here"
                placeholder="Mensagem para a banca avaliadora"
                class="swal2-textarea"
                style="margin: 5px"></textarea>
            <input
                id=${recourseAttachmentsId}
                type="file"
                multiple
                max="2"
                class="swal2-file">
        </div>`,
        showCancelButton: true,
        confirmButtonText: 'Enviar recurso',
        cancelButtonText: 'Sair',
        showLoaderOnConfirm: true,
        customClass: {
            confirmButton: "btn-success-rec",
            cancelButton: "btn-warning-rec"
        },
        preConfirm: async () => {
            const recourseText = document.getElementById(recourseTextareaId).value;

            if(recourseText === ''){
                Swal.fire({
                    position: "top-center",
                    title: "Precisa preencher o campo de recurso",
                    showConfirmButton: true,
                    timer: 2000
                });
                return false;
            }

            return [
                recourseText,
                document.getElementById(recourseAttachmentsId).files,
            ];
        },
        allowOutsideClick: false
    }).then(async content => {
        const [recourseText, files] = content.value;

        const formData = new FormData();
        formData.append('registration', registration)
        formData.append('opportunity', opportunity)
        formData.append('agent', agent)
        formData.append('recourse', recourseText)
        Array.from(files).forEach((file, index) => {
            formData.append(index,  file)
        })

        const panelRecourse = MapasCulturais.createUrl('recursos/agent/'+agent);
        const response = await fetch(MapasCulturais.baseURL + 'recursos/sendRecourse', {
            method: 'POST',
            body: formData,
        })
        const data = await response.json()

        // @todo: Entender como fazer para saber se deu erro
        if(data) {
            Swal.fire({
                position: "top-center",
                title: data.message,
                html: "Acompanhe o andamento do recurso no seu Painel. <br /> " +
                    "<a href='"+panelRecourse+"' class='btn btn-default'>Ir p/ painel</a>",
                showConfirmButton: false,
            });
            //Ocutando botão para não ter mais de um envio
            $("#btn-recourse-"+registration).hide();
        }
        if(!data) {
            Swal.fire({
                position: "top-center",
                title: 'Ops! Ocorreu um erro inesperado',
                html: "Acompanhe o andamento do recurso no seu Painel. <br /> " +
                    "<a href='#' class='btn btn-default'>Ir p/ painel</a> <a href='#' class='btn btn-info'>Sair</a> ",
                showConfirmButton: false,
            })
        }
    });
}

