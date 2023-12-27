// A $( document ).ready() block.

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