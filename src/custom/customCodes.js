
//Página inicial para gerenciamento da rotina

$(document).ready(function(){
	// updating the view with notifications using ajax
	function load_unseen_notification(view = '')
	{
		$.ajax({
			url: '\\classhandler.php',
			method: 'POST',
			data: {
					poperacao: "notificacoes"
			},
			dataType: "json",
			success: function(data) {
				if(data.status == "success"){

					var dadosRet = JSON.parse(data.retorno);

					var r_total_not = dadosRet['quantTotal'];



					if(r_total_not > 0){
						$('#notificacao').text(r_total_not);
						var notific = 'Notificações';
						if(r_total_not == 1){
							notific = 'Notificação'
						}

						$('#pageNotification').text(r_total_not+" "+notific);

						$('#notificationContents').text("");

						var numero = 0;
						var novo = "";


						for (i = 0; i < dadosRet['dados'].length; i++) {

							numero = dadosRet['dados'][i]['quant'];

							if(dadosRet['dados'][i]['tipo'] == 'c'){
								if(numero == 1){
									novo = "novo convite";
								}else{
									novo = "novos convites";""
								}
								$("#notificationContents").append(`<a href='/notificacoes?t=c&nr=v' class='dropdown-item'><i class='fas fa-sign-in-alt mr-2'></i> ${dadosRet['dados'][i]['quant']} ${novo}<span class='float-right text-muted text-sm'>${dadosRet['dados'][i]['dif']}</span></a>`);
							}else if(dadosRet['dados'][i]['tipo'] == 'a'){
								if(numero == 1){
									novo = "nova amizade";
								}else{
									novo = "novas amizades";""
								}
								$("#notificationContents").append(`<a href='/notificacoes?t=a&nr=v' class='dropdown-item'><i class='fas fa-users mr-2'></i> ${dadosRet['dados'][i]['quant']} ${novo}<span class='float-right text-muted text-sm'>${dadosRet['dados'][i]['dif']}</span></a>`);
							}else if(dadosRet['dados'][i]['tipo'] == 'd'){
								if(numero == 1){
									novo = "nova demanda";
								}else{
									novo = "novas demandas";""
								}
								$("#notificationContents").append(`<a href='/notificacoes?t=d&nr=v' class='dropdown-item'><i class='fas fa-calendar-plus mr-2'></i> ${dadosRet['dados'][i]['quant']} ${novo}<span class='float-right text-muted text-sm'>${dadosRet['dados'][i]['dif']}</span></a>`);
							}else if(dadosRet['dados'][i]['tipo'] == 's'){
								if(numero == 1){
									novo = "nova notificação";
								}else{
									novo = "novas notificações";""
								}
								$("#notificationContents").append(`<a href='/notificacoes?t=s&nr=v' class='dropdown-item'><i class='fas fa-info mr-2'></i> ${dadosRet['dados'][i]['quant']} ${novo}<span class='float-right text-muted text-sm'>${dadosRet['dados'][i]['dif']}</span></a>`);
							}
						}


						// <a href="#" class="dropdown-item">
            // <i class="fas fa-envelope mr-2"></i> 4 new messages
            // <span class="float-right text-muted text-sm">3 mins</span>
          	// </a>



					}else{
						$('#notificacao').text("");
						$('#notificationContents').text("");
						$('#pageNotification').text("0 Notificações")
					}
				}
			},
			error: function(data) {
				console.log(data);
			}

		});

		setTimeout(load_unseen_notification, 10000);
	}

	load_unseen_notification();
});


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
        url: '\\classhandler.php',
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

function MenuTicket(objeto){
	var ticket = objeto.closest('.ticket');



	var cartao = null;
	for (var i = 0; i < ticket.childNodes.length; i++) {
			if (ticket.childNodes[i].className == "cartao-ticket") {
				notes = ticket.childNodes[i];
				break;
			}
	}


	// var tabela = objeto.closest('.ticket').next("#cartaoTicket");

	// var menu =  ticket.closest('.cartao-ticket-menu');
	// tabela.display = 'none';

	// if(tabela.classList.contains('menu-ativo')){
	// 	menu.display = 'none';

	// alert('tem');
	// }else{
	// 	menu.display = 'block';
	// 	alert('não tem')
	// }
}

function ocultaMenu(object){

	var menu = object;
	var ticket = menu.closest('.ticket');
	var painel = ticket.childNodes[1];

	menu.style.display = "none";
}

function exibeMenu(object){
	var menu = object;
	var ticket = menu.closest('.ticket');
	var painel = ticket.childNodes[1];

	menu.style.display = "block";

}


$('.fas.fa-ellipsis-h').click(function(o){
	o.preventDefault();

	ticket = $(this).closest('.ticket')[0];
	var painel = $(this).closest('.cartao-ticket')[0]
	var menu =null;

	for (var i = 0; i < ticket.childNodes.length; i++)
	{
			if (ticket.childNodes[i].className == "cartao-ticket-menu")
			{
				menu = ticket.childNodes[i];
				break;
			}
	}
	menu.style.display = "block";

});

//Clicar no botão de fav
$(".botaoFav").click(function(event){
	event.preventDefault();

	var id = $(this).data("id");
	var estrela = event.target;

	//Verificar se o favorito está ativo

	var ativo = estrela.classList.contains('active');

	if(ativo){
		estrela.classList.remove('active');
	}else{
		estrela.classList.add('active');
	}

	FavTicket(id, !ativo)

});

function FavTicket(id, ativa){
	if(ativa){
		//define como favorito
	}else{
		//remove de favorito
	}
}

$(".botaoPin").click(function(event){
	event.preventDefault();

	var id = $(this).data("id");
	var pin = event.target;

	//Verificar se o favorito está ativo

	var ativo = pin.classList.contains('active');

	if(ativo){
		pin.classList.remove('active');
	}else{
		pin.classList.add('active');
	}

	PinTicket(id, !ativo)

});

function PinTicket(id, ativa){
	if(ativa){
		//define como fixo
	}else{
		//remove de fixo
	}
}

function DataValida(data, igualOuPosteriorAHoje = false)
	{
		var dataDigitada = data.trim();

		//a data deve ter pelo menos 8 caracteres (no caso de dd/mm/aa) e
		//no máximo 10 caracteres (no caso de dd/mm/aaaa)
		if(dataDigitada.length < 6 || dataDigitada.length >10){
			return false;
		}

		var partes = dataDigitada.split('/',3);

		if(partes.length != 3){
			return false;
		}

		if(isNaN(parseInt(partes[0]))|| isNaN(parseInt(partes[1] - 1)) || isNaN(parseInt(partes[2]))){
			return false;
		}

		//separo em partes
		var dia = parseInt(partes[0]);
		var mes = parseInt(partes[1] - 1);
		var ano = parseInt(partes[2]);

		if(ano <= 50){
			ano+=2000;
		}else if (ano<=99){
			ano+=1900;
		}

		// Verifica o range de ano
		if(ano < 1000 || ano > 3000){
			return false;
		}

		// Converte para milissegundos
    mSeconds = (new Date(ano, mes, dia)).getTime();
    // Inicializa o objeto data com os milissegundos calculados
    objDate = new Date();
    objDate.setTime(mSeconds);
    // Compara a data inserida e as partes do objeto data()
		// se houver alguma diferença retorna data invalida
		if (objDate.getFullYear() !== ano ||
        objDate.getMonth() !== mes ||
        objDate.getDate() !== dia) {
        return false;
		}

		if(igualOuPosteriorAHoje && objDate < new Date()){
			return false;
		}

		return true;
	}
