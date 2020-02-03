<?php

use \Slim\Slim;
use \Zware\Model\Chaves;
use \Zware\Model\Empresas;
use \Zware\Pagina;
use \Zware\PaginaInicial;
use \Zware\Model\User;
use \Zware\Model\Tickets;
use \Zware\Model\Rotinas;
use \Zware\Model\Files;
use \Zware\Model\Funcoes;
use \Zware\DB\MySql;
use \Zware\Model\ConfigHandler;



$app->get('/tickets(/)', function(){
	User::verifyLogin("Location: /");
	$user = new User();
	$user->loadCookie();
	$dadosUsuario = User::retornaDadosDaSession();
	$tickets = Tickets::GeraTickets($dadosUsuario['idusuario']);

	$dadosUsuario['tickets'] = $tickets;

	User::verificaAcesso("tickets", $dadosUsuario, "Tickets");
	exit;
});

$app->get('/novo_ticket(/)', function(){
	User::verifyLogin("Location: /");
	$user = new User();
	$user->loadCookie();
	$dadosUsuario = User::retornaDadosDaSession();
	$tickets = Tickets::GeraTickets($dadosUsuario['idusuario']);

	$json = Tickets::GeraArvoreEmpresas($dadosUsuario['idusuario']);
	echo($json);
	exit;

	$dadosUsuario['tickets'] = $tickets;

	User::verificaAcesso("criar_ticket", $dadosUsuario, "Criar ticket");
	exit;
});



?>
