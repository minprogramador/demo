<?php

error_reporting(0);
ini_set('error_reporting', 0);

setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

use Nette\Database\Context;

use Api\Boa\Cnpj\Consulta as cnpjConsulta;
use Api\Boa\Cpf\Consulta as cpfConsulta;

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
		if(stristr($arv, '-d=')){
			$doc = str_replace('-d=', '', $arv);
		}
		if(stristr($arv, '-r=')){
			$tipores = str_replace('-r=', '', $arv);
		}
		if(stristr($arv, '-t=')){
			$tipo = str_replace('-t=', '', $arv);
		}
		if(stristr($arv, '-a=')){
			$token = str_replace('-a=', '', $arv);
		}
	}
}else{
	die('nada a fazer.');	
}

if(!$doc) {
	die('nada a fazer.');
}

if(!$tipores) {
	$tipo = 'html';
}

if(!$token) {
	die;
}

if($tipo == 'cpf'){
	$tipo = '1';
}else{
	$tipo = '2';
}

$database = new Nette\Database\Connection($dsn, $user, $password);

$result = $database->query('select * from contas where LENGTH(`proxy`) > 6 and LENGTH(`cookie`) > 10 and `status`=true and `tipo`='.$tipo);
foreach ($result as $row) {

    $usuario = $row->usuario;
    $senha   = $row->senha;
    $proxy   = $row->proxy;
    $cookie  = $row->cookie;
    $start   = $row->start;
    $cicles  = $row->cicles;
    $tipo    = $row->tipo;

    $cnt_cookie = $row->count_cookie;
    $cnt_proxy  = $row->count_proxy;

	if(strlen($proxy) < 5) {
		continue;
	}

	if(strlen($cookie) < 5) {
		continue;
	}

	if($tipo == 1) {

		$cpfcon = new cpfConsulta();
		$cpfcon->setCookie($cookie);
		$cpfcon->setProxy($proxy);
		$cpfcon->setCpf($doc);
		$res = $cpfcon->consultar();

	}elseif($tipo == 2) {

		$cnpjcon = new cnpjConsulta();
		$cnpjcon->setCookie($cookie);
		$cnpjcon->setProxy($proxy);
		$cnpjcon->setCnpj($doc);
		$res = $cnpjcon->consultar();
	}else{
		continue;
//		die('nao tem tipo...');
	}

	$resdebug = base64_encode($res);
	$timeout = '';

	if(stristr($res, 'ES CONFIDENCIAIS')) {

		if($tipo == 1){
			$limpa   = new Filtro();
			$resjson = $limpa->json($res);
			$resjson = json_encode($resjson);			
		}elseif($tipo == 2){
			$resjson = base64_encode($res);
		}

		$status  = true;
		$query   = "INSERT INTO `historico` (`conta`, `proxy`, `token`, `doc`, `retorno`, `timeout`, `data`, `status`, `tipo`) VALUES ('".$usuario."', '".$proxy."', '".$token."', '".$doc."', '".$resjson."', '".$timeout."', NOW(), '".$status."', '".$tipo."');";

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

		$query   = "INSERT INTO `historico` (`conta`, `proxy`, `token`, `doc`, `retorno`, `timeout`, `data`, `status`, `tipo`) VALUES ('".$usuario."', '".$proxy."', '".$token."', '".$doc."', 'cookie invalido. retorno debug > ".$resdebug."', '".$timeout."', NOW(), false, '".$tipo."');";
		$database->query($query);
		$database->query("update contas set `cookie`='';");

	}elseif($res === true) {

		$query   = "INSERT INTO `historico` (`conta`, `proxy`, `token`, `doc`, `retorno`, `timeout`, `data`, `status`, `tipo`) VALUES ('".$usuario."', '".$proxy."', '".$token."', '".$doc."', 'erro ao consultar, cookie ok! retorno debug > ".$resdebug."', '".$timeout."', NOW(), false, '".$tipo."');";
		$database->query($query);
		$result = ['msg'=> 'erro ao consultar, cookie ok !', 'status' => true];

	}
	elseif($res == 'rede'){

		$query   = "INSERT INTO `historico` (`conta`, `proxy`, `token`, `doc`, `retorno`, `timeout`, `data`, `status`, `tipo`) VALUES ('".$usuario."', '".$proxy."', '".$token."', '".$doc."', 'erro ao consultar, proxy invalido! retorno debug > ".$resdebug."', '".$timeout."', NOW(), false, '".$tipo."');";
		$database->query($query);		
		$database->query("update contas set `proxy`='';");

	}else{

		$query   = "INSERT INTO `historico` (`conta`, `proxy`, `token`, `doc`, `retorno`, `timeout`, `data`, `status`, `tipo`) VALUES ('".$usuario."', '".$proxy."', '".$token."', '".$doc."', 'erro indefinido.... retorno debug > ".$resdebug."', '".$timeout."', NOW(), false, '".$tipo."');";
		$database->query($query);		
//		die("\n#Error linha 110--->\n");

	}

	continue;
}








