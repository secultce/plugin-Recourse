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
    EventDelegator.setup('sendReply', sendReply, 'quill-editor');

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
        const dataForm = createBodyRequest(extraData.action, content, entityId, opportunity, 'recourse'); // forma do corpo da requisição
        const requestForm = createFormData(dataForm, files); // cria a requisicao com arquivos
        const panelRecourse = MapasCulturais.createUrl('recursos/agent/' + agentId);

        const response = await fetch(MapasCulturais.baseURL + extraData.url, {
            method: 'POST',
            body: requestForm,
        })
        const data = await response.json();
     
        if (!response.ok) {
            McMessages.error(
                'Ops! Ocorreu um erro inesperado', data && data.message? data.message : "Tente novamente mais tarde.",
            )
            return;
        }
        McMessages.custom(
            data && data.message? data.message : "Recurso enviado com sucesso.", 'success', "Acompanhe o andamento do recurso no seu Painel. <br /> " +
                "<a href='" + panelRecourse + "' class='btn btn-default'>Ir p/ painel</a>",
        )
    }
}


function createFormData(data, files = []) {
    const formData = new FormData();
    // Verifica se data é um objeto válido
    if (typeof data !== 'object' || data === null) {
        console.warn('O parâmetro "data" deve ser um objeto. Ignorando adição de dados simples.');
    } else {
        // Adiciona dados simples, lidando com arrays para append múltiplo
        setobject(data, formData);
    }
    // Adiciona arquivos, nomeados por índice (0, 1, 2…)
    Array.from(files).forEach((file, index) => {
        formData.append(index.toString(), file);
    });
    return formData;
}

function createBodyRequest(action, content, entityId = null, opportunity = null, entity) {

    if(entity == 'recourse'){
        return action === 'create'
            ? { 'registration': entityId, 'opportunity': opportunity, 'recourse': content }
            : action === 'update'
                ? { 'recourseId': entityId, 'recourseText': content }
                : {};
    }

    if(entity == 'replyRecourse'){
        return action === 'create'
            ? { 'entityId': entityId, 'reply': content }
            : action === 'update'
                ? { 'entityId': entityId, 'reply': content }
                : {};
    }

}

/**
 * Controla a visibilidade da div de nota baseado no status do recurso
 * @param {string} statusValue - Valor do select de status
 */
function handleRecourseStatusChange(statusValue) {
    const noteDiv = document.getElementById('valueNoteRecourse');

    if (!noteDiv) {
        console.warn('Elemento valueNoteRecourse não encontrado');
        return;
    }

    // Oculta a div quando o valor for -9 (Indeferido)
    // Mostra a div para outros valores (1 = Deferido, 8 = Deferido parcialmente)
    if (statusValue === '-9') {
        noteDiv.style.display = 'none';
    } else if (statusValue === '1' || statusValue === '8') {
        noteDiv.style.display = 'block';
    } else {
        // Para o valor 0 (-- Selecione a situação --), mantém oculto
        noteDiv.style.display = 'none';
    }
}

/**
 * Inicializa os event listeners para o select de status do recurso
 */
function initRecourseStatusListener() {
    const selectElement = document.querySelector('.selectRecourseSituacion');

    if (!selectElement) {
        console.warn('Select de status do recurso não encontrado');
        return;
    }

    // Remove listener anterior se existir para evitar duplicação
    selectElement.removeEventListener('change', onStatusChange);

    // Adiciona o listener
    selectElement.addEventListener('change', onStatusChange);

    // Inicializa a visibilidade com base no valor ATUAL do select (pré-selecionado)
    // Isso garante que, se extraData.status for 1 ou 8, a div #valueNoteRecourse seja exibida imediatamente
    handleRecourseStatusChange(selectElement.value);
}

/**
 * Handler do evento de mudança do select
 */
function onStatusChange(event) {
    handleRecourseStatusChange(event.target.value);
}

async function sendReply(entityId, buttonElement, selectId, extraData)
{
    const resultReply = extraData.note == '' ? 0.0 : extraData.note; // recebendo o valor da tabela

    const htmlSelect = `<label style="float: left;">Situação:</label>
                <select status-recourse="" name="status" class="form-control selectRecourseSituacion">
                    <option value="0" disabled="" >-- Selecione a situação --</option>
                    <option value="1" ${extraData.status == 1 ? 'selected' : ''}>Deferido</option>
                    <option value="8" ${extraData.status == 8 ? 'selected' : ''}>Deferido parcialmente</option>
                    <option value="-9" ${extraData.status == -9 ? 'selected' : ''}>Indeferido</option>
                </select>
                <div class="form-group" id="valueNoteRecourse" grade-wrapper="" style="display: none;">
                    <label style="float: left;">Nova nota:</label>
                    <input type="number" name="replyResult" value="${resultReply}" class="form-control" placeholder="Digite a nova nota">
                    <p class="badge badge-info current-grade">A nota atual é: <span current-grade="">${resultReply}</span></p>
                </div>`

    const result = await QuillEditor.open({
        title: extraData.customTitle || 'Responder recurso',
        placeholder: extraData.customPlaceholder || 'Escreva sua resposta para o recurso',
        entityId: entityId,
        selectId: selectId,
        triggerButton: buttonElement,
        html: htmlSelect,
        showFile: false,
        onOpen: () => {
            // Inicializa o listener do select após o modal abrir
            initRecourseStatusListener();
        }
    });
    if (result.isConfirmed) {
        const content = result?.value?.conteudo;
        const dataForm = createBodyRequest(extraData.action, content, entityId, null, 'replyRecourse'); // forma do corpo da requisição
        const requestForm = createFormData(dataForm, []); // cria a requisicao com arquivos
        // Complementando requestForm
        const request = setobject(result.value.customFields, requestForm);
        const response = await fetch(MapasCulturais.createUrl(extraData.url), {
            method: 'POST',
            body: request,
        })

        const data = await response.json();
        if (!response.ok) {
            McMessages.error(
                'Ops! Ocorreu um erro inesperado', data && data.message? data.message : "Tente novamente mais tarde.",
            )
            return;
        }
        McMessages.success(
            "Sucesso!",
            data && data.message? data.message : "Acompanhe o andamento do recurso no seu Painel. <br /> "
        )
        setTimeout(()=>{
            window.location.reload()
        },2000)
    }
}

function setobject(objectVal, request)
{
    // Adiciona dados simples, lidando com arrays para append múltiplo
    for (const [key, value] of Object.entries(objectVal)) {
        if (Array.isArray(value)) {
            // Se for array, append cada item individualmente (ex: campos multi-valor)
            value.forEach(item => request.append(key, item));
        } else {
            request.append(key, value);
        }
    }
    return request;
}
