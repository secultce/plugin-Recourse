const recourse = {
    getConfigSwalSend: (recourseTextareaId, recourseAttachmentsId, recourseText = '') => {
        return {
            title: "Escrever o Recurso",
            html: `
                <div style="display: grid" id="contextRecourse">
                    <label to="${recourseTextareaId}">Envie todos os destaques do seu recurso por escrito uma vez em um único campo</label>
                    <textarea
                        id="${recourseTextareaId}"
                        placeholder="Mensagem para a banca avaliadora"
                        class="swal2-textarea froala-editor" 
                        style="margin: 5px; display: none;">${recourseText}</textarea>
                    <div id="froala-container"></div> 
                    <input id="${recourseAttachmentsId}" type="file" multiple max="2" class="swal2-file">
                </div>`,
            showCancelButton: true,
            confirmButtonText: 'Enviar recurso',
            cancelButtonText: 'Sair',
            showLoaderOnConfirm: true,
            width: 800,
            padding: "5em",
            customClass: {
                confirmButton: "btn-success-rec",
                cancelButton: "btn-warning-rec"
            },
            didOpen: () => {
                // Inicializa o Froala quando o modal é aberto
                const textarea = document.getElementById(recourseTextareaId);
                const initialContent = textarea.value;

                // Inicializa o Froala Editor
                new FroalaEditor('#froala-container', {
                    // Configurações básicas do Froala
                    heightMin: 200,
                    toolbarButtons: [
                        'bold', 'italic', 'underline', 'paragraphFormat',
                        'align', 'formatOL', 'formatUL', 'insertLink', 'textColor'
                    ],
                    colorsText: [
                        '#61BD6D',
                        '#1ABC9C',
                        '#54ACD2',
                        '#ec5353',
                        'REMOVE'
                    ],
                    events: {
                        initialized: function () {
                            this.html.set(initialContent);
                        },
                        contentChanged: function () {
                            // Atualiza o textarea oculto com o conteúdo do Froala
                            textarea.value = this.html.get();
                        }
                    }
                });
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

$(() => {
    const opportunity = MapasCulturais.entity?.object

    if (opportunity?.appealEnabled === 'Sim') $('#appeal-period-wrapper').removeClass('d-none')

    $('.opportunity-claim-box').remove(); // Removendo o botão existente no módulo de oportunidades

    $('#enabled-appeal-wrapper').on('change', event => {
        const opt = event.target.value
        const appealEnabled = opt === 'Sim' ? true : false

        if (appealEnabled) {
            $('#appeal-period-wrapper').removeClass('d-none')
            return
        }

        $('#appeal-period-wrapper').addClass('d-none')
    })

    var editor = new FroalaEditor('#contextRecourse');

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
                        showConfirmationSwal(res.message)
                    },
                    error: function (err) {
                        if (err.status === 403) {
                            showConfirmationSwal('Você não pode mais remover este arquivo. Período do recurso encerrado')
                            return
                        }

                        showSimpleSwal('Erro inesperado, tente novamente')
                    }
                });
            }
        });
    })
    // SETUP ÚNICO: registra o botão para o componente Quill
    EventDelegator.setup('openRecourse', openRecourse, 'quill-editor');

})

function showEditor() {
    Swal.fire({
        title: 'Editar recurso',
        html: '<textarea id="froala-editor"></textarea>',
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            return $('#froala-editor').froalaEditor('html.get');
        },
        didOpen: () => {
            // Inicializa o Froala quando o SweetAlert é aberto
            $('#froala-editor').froalaEditor({
                // Configurações do Froala
                toolbarButtons: ['bold', 'italic', 'underline', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'insertLink'],
                heightMin: 200
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const content = result.value;
            // Faça algo com o conteúdo
            console.log(content);
        }
    });
}

function showSimpleSwal(message) {
    Swal.fire({
        position: "top-center",
        title: message,
        showConfirmButton: true,
    });
}

function showConfirmationSwal(message) {
    Swal.fire({
        position: "top-center",
        title: message,
        showConfirmButton: true,
        allowOutsideClick: false
    }).then((res) => {
        if (res.isConfirmed) location.reload()
    })
}

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

async function openRecourse(entityId, buttonElement, selectId, extraData) {
    // Passe para QuillEditor via options
    const result = await QuillEditor.open({
        title: extraData.customTitle || 'Recurso Padrão',  // Usa data-custom-title ou fallback
        placeholder: extraData.customPlaceholder || 'Escreva seu recurso...',
        entityId: entityId,
        selectId: selectId,
        triggerButton: buttonElement
    });

    const opportunity = extraData.opp;
    const agentId = extraData.agent;
    const files = $("#edit-recourse-file-" + entityId)[0].files

    if (result.isConfirmed) {
        const content = result?.value?.conteudo;
        const dataForm = createBodyRequest(extraData.action, content, entityId, opportunity); // forma do corpo da requisição
        const requestForm = createFormData(dataForm, files); // cria a requisicao com arquivos
        console.log('createFormData', requestForm);
        const panelRecourse = MapasCulturais.createUrl('recursos/agent/' + agentId);

        const response = await fetch(MapasCulturais.baseURL + extraData.url, {
            method: 'POST',
            body: requestForm,
        })
        const data = await response.json();
        // @todo: Entender como fazer para saber se deu erro
        if (data) {
            Swal.fire({
                position: "top-center",
                title: data.message,
                html: "Acompanhe o andamento do recurso no seu Painel. <br /> " +
                    "<a href='" + panelRecourse + "' class='btn btn-default'>Ir p/ painel</a>",
                showConfirmButton: false,
            });
            // //Ocutando botão para não ter mais de um envio
            // $("#btn-recourse-" + registration).hide();
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
    }
}

function createFormData(data, files = []) {
    const formData = new FormData();
    // Verifica se data é um objeto válido
    if (typeof data !== 'object' || data === null) {
        console.warn('O parâmetro "data" deve ser um objeto. Ignorando adição de dados simples.');
    } else {
        // Adiciona dados simples, lidando com arrays para append múltiplo
        for (const [key, value] of Object.entries(data)) {
            if (Array.isArray(value)) {
                // Se for array, append cada item individualmente (ex: campos multi-valor)
                value.forEach(item => formData.append(key, item));
            } else {
                console.log(key, value)
                formData.append(key, value);
                console.log({ formData })
            }
        }
    }

    // Adiciona arquivos, nomeados por índice (0, 1, 2…)
    Array.from(files).forEach((file, index) => {
        formData.append(index.toString(), file);
    });
    return formData;
}

function createBodyRequest(action, content, entityId = null, opportunity = null) {
    return action === 'create'
        ? { 'registration': entityId, 'opportunity': opportunity, 'recourse': content }
        : action === 'update'
            ? { 'recourseId': entityId, 'recourseText': content }
            : {};
}