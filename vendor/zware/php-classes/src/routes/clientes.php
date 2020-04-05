<?php

use \Slim\Slim;
use \Zware\Model\Chaves;
use \Zware\Pagina;
use \Zware\Model\Clientes;
use \Zware\PaginaInicial;
use \Zware\Model\User;
use \Zware\Model\Funcoes;
use \Zware\DB\MySql;
use \Zware\Model\ConfigHandler;

$app->get('/cadastro_cliente', function () {

	$user = new User();
	$user->loadCookie();
	$dadosUsuario = User::retornaDadosDaSession();
	$dadosUsuario['rotas'] = Clientes::ListaRotasPorEmail("rossano.oliveira@dadobier.com.br");
	$dadosUsuario['formasPagto'] = Clientes::ListarFormasPagto();
	$dadosUsuario['condPagto'] = Clientes::ListarCondPagto();
	$dadosUsuario['segmentos'] = Clientes::ListarSegmentos();


	User::verificaAcesso("cadastrar_cliente", $dadosUsuario, "cadastro_cliente");
	exit;
});

$app->get('/cadastros', function () {

	$user = new User();
	$user->loadCookie();
	$dadosUsuario = User::retornaDadosDaSession();



	User::verificaAcesso("cadastrar_cliente", $dadosUsuario, "cadastro_cliente");
	exit;
});

?>
