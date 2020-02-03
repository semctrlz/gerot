<?php
session_start();
require_once("vendor/autoload.php");

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


$app = new Slim();

$app->config('debug', true);

require_once("functions.php");
require_once("vendor/zware/php-classes/src/routes/pagina.php");
require_once("vendor/zware/php-classes/src/routes/tickets.php");
require_once("vendor/zware/php-classes/src/routes/empresas.php");


$app->run();
