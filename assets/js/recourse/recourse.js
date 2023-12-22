// A $( document ).ready() block.
$( document ).ready(function() {
    console.log( "ready!" );
});

$(function () {
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
    //Salva a configuração dos recursos
    $("#buttonSendData").click(function (e) {
        e.preventDefault();
        var form = $("#resourceFormData").serialize();
        form += '&opportunity='+MapasCulturais.entity.id;
        console.log(form);
        $.ajax({
            type: "POST",
            url: MapasCulturais.baseURL+'recursos/opportunityEnabled',
            data: form,
            dataType: "json",
            success: function (response) {
                console.log(response);
            }
        });
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