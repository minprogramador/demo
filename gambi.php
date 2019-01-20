<?php

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

use Nette\Database\Context;
use Api\Boa\Logar;
use Api\Boa\Check;
use Api\Boa\Consultar;
use Api\Boa\utils\Util;
use Api\Boa\Filtro;

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
		if(stristr($arv, 't=')){
			$tipo = str_replace('t=', '', $arv);
		}
		if(stristr($arv, 'a=')){
			$token = str_replace('a=', '', $arv);
		}
	}
}else{
	die('nada a fazer.');	
}

if(!$cpf) {
	die('nada a fazer.');
}

if(!$tipo) {
	$tipo = 'html';
}

if(!$token) {
	die;
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
	$resdebug = base64_encode($res);
	$timeout = '';

	if(stristr($res, 'ES CONFIDENCIAIS')) {

		$limpa   = new Filtro();
		$resjson = $limpa->json($res);
		$resjson = json_encode($resjson);

		$status  = true;
		$query   = "INSERT INTO `historico` (`conta`, `proxy`, `token`, `doc`, `retorno`, `timeout`, `data`, `status`) VALUES ('".$usuario."', '".$proxy."', '".$token."', '".$cpf."', '".$resjson."', '".$timeout."', NOW(), '".$status."');";

		$database->query($query);

		if ($tipo == 'json') {
			$resokk = $resjson;
		}else{
			$resokk = $res;
		}
		echo $resokk;
		break;
		
	}
	elseif($res === false) {

		$query   = "INSERT INTO `historico` (`conta`, `proxy`, `token`, `doc`, `retorno`, `timeout`, `data`, `status`) VALUES ('".$usuario."', '".$proxy."', '".$token."', '".$cpf."', 'cookie invalido. retorno debug > ".$resdebug."', '".$timeout."', NOW(), false);";
		$database->query($query);
		$database->query("update contas set `cookie`='';");

	}elseif($res === true) {

		$query   = "INSERT INTO `historico` (`conta`, `proxy`, `token`, `doc`, `retorno`, `timeout`, `data`, `status`) VALUES ('".$usuario."', '".$proxy."', '".$token."', '".$cpf."', 'erro ao consultar, cookie ok! retorno debug > ".$resdebug."', '".$timeout."', NOW(), false);";
		$database->query($query);
		$result = ['msg'=> 'erro ao consultar, cookie ok !', 'status' => true];

	}
	elseif($res == 'rede'){

		$query   = "INSERT INTO `historico` (`conta`, `proxy`, `token`, `doc`, `retorno`, `timeout`, `data`, `status`) VALUES ('".$usuario."', '".$proxy."', '".$token."', '".$cpf."', 'erro ao consultar, proxy invalido! retorno debug > ".$resdebug."', '".$timeout."', NOW(), false);";
		$database->query($query);		
		$database->query("update contas set `proxy`='';");

	}else{

		$query   = "INSERT INTO `historico` (`conta`, `proxy`, `token`, `doc`, `retorno`, `timeout`, `data`, `status`) VALUES ('".$usuario."', '".$proxy."', '".$token."', '".$cpf."', 'erro indefinido.... retorno debug > ".$resdebug."', '".$timeout."', NOW(), false);";
		$database->query($query);		
//		die("\n#Error linha 110--->\n");

	}

	continue;
}








