
//Página inicial para gerenciamento da rotina

$(document).on('mouseenter', 'td.text-center', function () {       
    $(this).find(":button").show();
    $(this).find(".badge").hide();
}).on('mouseleave', 'td.text-center', function () {
    $(this).find(":button").hide();
    $(this).find(".badge").show();
});

//Exibir botão editar ao passar o mouse na foto do perfil -> para isso coloque o botão e a foto dentro de um div com o id fotoPerfil
$(document).on('mouseenter', '#fotoPerfil', function () {        
    $(this).find(":button").fadeIn(200);
    
}).on('mouseleave', '#fotoPerfil', function () {
    $(this).find(":button").fadeOut(200);
    
});

$('#addGerente').click(function(){

    

});


//Clicar no botão de Submit no Modal Edita Foto
$('#modalEditaFotoSubmit').click(function(){
    $('#modalformFoto').submit();
});

//Clicar no botão de alterarSenha
$('#modalAlterarSenhaSubmit').click(function(e){    
    $('#modalAlterarSenhaForm').submit();   
    //$('#modalFormAlterarSenha').submit();
});

//Função que altera o estado de uma tarefa em um dia
$("td.text-center :button").click(function(){
    
    var tarefa = $(this).data("tarefa");
    var data = $(this).data('data');
    var result = $(this).data('result');

    var td = $(this).closest('td');
    
    var bad = td.children('span');
    
    //Caso clique no botão info deve abrir o modal para inserir as observações
    if(result != "info")
    {
        bad.removeClass('badge-danger');
        bad.removeClass('badge-warning');
        bad.removeClass('badge-success');
        bad.removeClass('badge-info');

        if(result == "ok"){        
            bad.addClass('badge-success');
            bad.text('Ok');
        }else if(result == "atrazo"){
            bad.addClass('badge-warning');        
            bad.text('Atrazo');
        }
        else if(result == "na"){
            bad.addClass('badge-info');        
            bad.text('N/A');
        }
        else{
            bad.addClass('badge-danger');
            bad.text('Não');
        }
        //Update no banco via ajax
        $.ajax({
            url: 'userhandler.php',
            method: 'POST',
            data: {
                idtarefa: tarefa,
                data: data,
                resultado: result,
                operacao: "alterar"
            },        
            dataType: "json",    
            success: function(data) {
                var usuario = data.users;
                
                
                if (data.status != 'success') {
                    //alert(data.status);
                }
            }
        });
        
    }
        
});

$("#perfilSalvar").click(function(e){    
    e.preventDefault();

    var nome = $("#inputNome").val();
    var sobrenome = $("#inputSobrenome").val();
    var apelido = $("#inputApelido").val();
    var nascimento = $("#datemask").val();
    var educacao = $("#inputEducacao").val();
    var habilidades = $("#inputHabilidades").val();
    var sobre = $("#inputBio").val();
    var idusuario = $("#inputIdUsuario").val();
    var operacao = "alteraUsuario";

    //alert("Nome = "+nome+" Sobrenome = " + sobrenome + " Apelido = " + apelido + " Educação = "+educacao+ " Habilidade = " + habilidades + " Sobre = " + sobre + " idusuario = " +  idusuario + " Operacao = "+operacao);
   
    $.ajax({
        url: 'classhandler.php',
        method: 'POST',
        data: {
            pnome: nome,
            psobrenome: sobrenome,
            papelido: apelido,
            nascimento: nascimento,
            peducacao: educacao,
            phabilidades: habilidades,
            psobre: sobre,
            pidusuario: idusuario,
            poperacao: operacao
        },        
        dataType: "json",    
        success: function(data) {  
                      
            if (data.status != 'sucesso') 
            {
                alert(data.retorno);                
            }
            else
            {
                alert("Dados salvos com sucesso!");
            }
        }
    });   
        
});
//Corrige conflito do Jquery com o Bootstrap no menu de usuario


$(":input").inputmask();

function verificarApelido() {
    var input = $("#inputApelido");
    var btSalvar = $("#perfilSalvar");
    var x = input.val();
    var apelidoTratado = x.toLowerCase();
    var usuario = $("#inputIdUsuario").val();

    $.ajax({
        url: 'classhandler.php',
        method: 'POST',
        data: {            
            idusuario: usuario,
            apelido: apelidoTratado,            
            poperacao: "verificarAPelido"
        },        
        dataType: "json",    
        success: function(data) {       

            input.removeClass('is-invalid');
            input.removeClass('is-valid');
            btSalvar.removeAttr('disabled');
            
            if(data.status == 'livre')
            {
                input.addClass('is-valid');
            }
            else if(data.status == 'ocupado')
            {
                input.addClass('is-invalid');
                btSalvar.attr('disabled', 'true');
            }

        }
    });
}

