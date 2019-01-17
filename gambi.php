<?php

setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

use Nette\Database\Context;
use Api\Boa\Logar;
use Api\Boa\Check;
use Api\Boa\Consultar;
use Api\Boa\utils\Util;

require(dirname(__FILE__).'/vendor/autoload.php');
require(__DIR__. "/config.php");

$user     = $sv_config['user'];
$password = $sv_config['passwd'];
$mysqlsv  = $sv_config['mysqlsv'];
$mysqlbd  = $sv_config['dbname'];
$dsn      = "mysql:host={$mysqlsv};dbname={$mysqlbd}";

if(count($argv) > 1) {
	foreach($argv as $arv){
		if(stristr($arv, 'doc=')){
			$cpf = str_replace('doc=', '', $arv);
		}
	}
}else{
	die('nada a fazer.');	
}

if(!$cpf) {
	die('nada a fazer.');
}

$database = new Nette\Database\Connection($dsn, $user, $password);

$result = $database->query('select * from contas where LENGTH(`proxy`) > 6 and LENGTH(`cookie`) > 10 and `status`=true');

foreach ($result as $row) {
    $usuario = $row->usuario;
    $senha   = $row->senha;
    $proxy   = $row->proxy;
    $cookie  = $row->cookie;
    $start   = $row->start;
    $cicles  = $row->cicles;

    $cnt_cookie = $row->count_cookie;
    $cnt_proxy  = $row->count_proxy;

	if(strlen($proxy) < 5) {
		continue;
	}

	if(strlen($cookie) < 5) {
		continue;
	}

	$cons = new Consultar();
	$cons->setcookie($cookie);
	$cons->setProxy($proxy);
	$cons->setCpf($cpf);
	$res = $cons->run();
	if($res !== false) {
		echo $res;
		break;
	}else{
		continue;
	}
}
