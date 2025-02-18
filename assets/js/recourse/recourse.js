const recourse = {
    getConfigSwalSend: (recourseTextareaId, recourseAttachmentsId, recourseText = '') => {
        return {
            title: "Escrever o Recurso",
            html: `<div style="display: grid">
                <label to="${recourseTextareaId}">Envie todos os destaques do seu recurso por escrito uma vez em um único campo</label>
                <textarea
                    id=${recourseTextareaId}
                    placeholder="Mensagem para a banca avaliadora"
                    class="swal2-textarea"
                    style="margin: 5px">${recourseText}</textarea>
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

                if (recourseText === '') {
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
        }
    }
}

// A $( document ).ready() block.
$(function () {
    $('.opportunity-claim-box').remove();//Removendo o botão existente no modulo de oportunidades

    $("#recourseOptions").change(function () {
        var opt = $("#recourseOptions").val();
        if (opt == '0') {
            $('#insertData').show();
            //$('#resourceOptions option[value=0]').attr('selected','selected');
            claimDisabled(opt);
        } else {
            $('#insertData').removeClass('visible');
            claimDisabled(opt);
            $('#insertData').hide();
        }
    });

    $('[delete-recourse-file-btn]').on('click', event => {
        Swal.fire({
            title: "Remover arquivo",
            text: "Você tem certeza que deseja remover este arquivo do recurso?",
            showConfirmButton: true,
            showCloseButton: false,
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sim',
            customClass: {
                confirmButton: "btn-success-rec",
                cancelButton: "btn-warning-rec"
            }
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: MapasCulturais.baseURL + 'recursos/deleteFile',
                    data: { fileId: event.currentTarget.dataset.fileId },
                    dataType: "json",
                    success: function (res) {
                        Swal.fire({
                            position: "top-center",
                            title: res.message,
                            showConfirmButton: true,
                            allowOutsideClick: false
                        }).then((res) => {
                            if (res.isConfirmed) location.reload()
                        });
                    },
                    error: function () {
                        Swal.fire({
                            position: "top-center",
                            title: 'Erro inesperado, tente novamente',
                            showConfirmButton: true,
                        });
                    }
                });
            }
        });
    })
});

function claimDisabled(opt) {
    $.ajax({
        type: "POST",
        url: MapasCulturais.baseURL + 'recursos/disabledRecourse',
        data: { id: MapasCulturais.entity.id, claimDisabled: opt },
        dataType: "json",
        success: function (response) {
            console.log(response);
        }
    });
}

function sendRecourse(registration, opportunity, agentId) {
    const recourseTextareaId = 'context-recourse-user-' + registration;
    const recourseAttachmentsId = 'attachment-recourse-user-' + registration;

    Swal.fire(recourse.getConfigSwalSend(recourseTextareaId, recourseAttachmentsId)).then(async content => {
        const [recourseText, files] = content.value;

        const formData = new FormData();
        formData.append('registration', registration)
        formData.append('opportunity', opportunity)
        formData.append('recourse', recourseText)
        Array.from(files).forEach((file, index) => {
            formData.append(index, file)
        })

        const panelRecourse = MapasCulturais.createUrl('recursos/agent/' + agentId);
        const response = await fetch(MapasCulturais.baseURL + 'recursos/sendRecourse', {
            method: 'POST',
            body: formData,
        })
        const data = await response.json()

        // @todo: Entender como fazer para saber se deu erro
        if (data) {
            Swal.fire({
                position: "top-center",
                title: data.message,
                html: "Acompanhe o andamento do recurso no seu Painel. <br /> " +
                    "<a href='" + panelRecourse + "' class='btn btn-default'>Ir p/ painel</a>",
                showConfirmButton: false,
            });
            //Ocutando botão para não ter mais de um envio
            $("#btn-recourse-" + registration).hide();
        }
        if (!data) {
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

function editRecourse(recourseId, recourseText) {

    const recourseTextareaId = 'edit-recourse-textarea-' + recourseId;
    const recourseAttachmentsId = 'edit-recourse-file-' + recourseId;

    Swal.fire(recourse.getConfigSwalSend(recourseTextareaId, recourseAttachmentsId, recourseText)).then(async content => {
        const [recourseText, files] = content.value;

        const formData = new FormData();
        formData.append('recourseId', recourseId)
        formData.append('recourseText', recourseText)
        Array.from(files).forEach((file, index) => {
            formData.append(index, file)
        })

        const response = await fetch(MapasCulturais.baseURL + 'recursos/updateRecourse', {
            method: 'POST',
            body: formData,
        })

        if (response.ok) {
            const data = await response.json()

            Swal.fire({
                position: "top-center",
                title: data.message,
                showConfirmButton: true,
                allowOutsideClick: false
            }).then((res) => {
                if (res.isConfirmed) location.reload()
            });
        } else {
            Swal.fire({
                position: "top-center",
                title: 'Erro inesperado, tente novamente',
                showConfirmButton: true,
            });
        }
    });
}
