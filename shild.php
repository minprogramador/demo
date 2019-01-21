<?php

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

use Api\Boa\Cnpj\Check as cnpjCheck;
use Api\Boa\Cpf\Check as cpfCheck;

use Api\Boa\Cnpj\Logar as cnpjLogar;
use Api\Boa\Cpf\Logar as cpfLogar;


use Nette\Database\Context;
//use Api\Boa\Logar;
//use Api\Boa\Check;
//use Api\Boa\Consultar;
//use Api\Boa\utils\Util;

require(dirname(__FILE__).'/vendor/autoload.php');
require(__DIR__. "/config.php");

$user     = $sv_config['user'];
$password = $sv_config['passwd'];
$mysqlsv  = $sv_config['mysqlsv'];
$mysqlbd  = $sv_config['dbname'];
$dsn      = "mysql:host={$mysqlsv};dbname={$mysqlbd}";
$database = new Nette\Database\Connection($dsn, $user, $password);

function getProxy() {

	global $database;

	$query   = 'select * from redes where `update` < NOW() and `ativo`=true  ORDER BY `update` desc limit 10;';

	$result = $database->fetchAll($query);
	$result = json_encode($result);
	$result = json_decode($result, true);
	$rand_keys = array_rand($result, 1);
	$vaipr = $result[$rand_keys];
	$vaipr = $vaipr['proxy'];

	if(stristr($vaipr, ':')){
		return $vaipr;
	}else{
		return false;
	}
}

$result = $database->query('SELECT * FROM contas where status = ?', true);

foreach ($result as $row) {
	$tipo    = $row->tipo;
    $usuario = $row->usuario;
    $senha   = $row->senha;
    $proxy   = $row->proxy;
    $cookie  = $row->cookie;
    $start   = $row->start;
    $cicles  = $row->cicles;

    $cnt_cookie = $row->count_cookie;
    $cnt_proxy  = $row->count_proxy;

	if(strlen($proxy) < 5) {
		$proxy = getProxy();
		$nnproxy = true;
	}else{
		$nnproxy = false;
	}

	if(strlen($cookie) > 5) {

		echo "::START CHECK COOKIE::\n";

		if($tipo == 1) {
			$cpfcon = new cpfCheck();
			$cpfcon->setCookie($cookie);
			$cpfcon->setProxy($proxy);
			$run = $cpfcon->check();
		}elseif($tipo == 2) {
			$cnpjcon = new cnpjCheck();
			$cnpjcon->setCookie($cookie);
			$cnpjcon->setProxy($proxy);
			$run = $cnpjcon->check();
		}else {
			die('tipo invalido');
		}

		echo "\n\n------> 83 $run ------> \n";


		if($run === true){
			// cookie ok, atualiza ciclo e update.
			echo "\n\n[$cookie] - $proxy\n\n";
			echo "\n##### Cookie online, ciclo ok ($cicles) >>>> $tipo ~ ", $usuario, PHP_EOL;
			
			$payload = [];
			if($nnproxy === true) {
				$payload['count_proxy'] = ($cnt_proxy + 1);
			}

			$payload['proxy'] = $proxy;
			$payload['cookie'] = $cookie;
			$payload['cicles'] = ($cicles + 1);
			$payload['update'] = date("Y-m-d H:i:s");

			$result = $database->query('UPDATE contas SET', $payload , 'WHERE usuario = ?', $usuario);

		}elseif($run == 'rede'){

			$payload = [
				'proxy'  => '',
				'cookie' => $cookie,
				'update' => date("Y-m-d H:i:s")
			];

			$result = $database->query('UPDATE contas SET', $payload , 'WHERE usuario = ?', $usuario);

			echo "\n############Rede off >>> $tipo ~ ", $usuario, PHP_EOL;

		}else{
			//cookie ruim, atualiza cookie, update, status.
			echo "\n############# Cookie offline >>>> $usuario\n";
			$result = $database->query('UPDATE contas SET', [

				'proxy'  => $proxy,
				'cookie' => '',
			    'update' => date("Y-m-d H:i:s"),
			    'status' => true
			], 'WHERE usuario = ?', $usuario);

		}

		echo "::END CHECK COOKIE::\n";
	}else{

		echo "::START COLETA COOKIE::\n";

		if(strlen($proxy) < 7){
			echo "::SEM PROXY, CONTINUA FILA...::\n";
			continue;
		}
		echo ":: entrou na linha 129, logando: $tipo ~ $usuario ---- $proxy::\n";
		if($tipo == 1) {
			$Logar = new cpfLogar();
			$Logar->setProxy($proxy);
			$Logar->setUsuario($usuario);
			$Logar->setSenha($senha);
			$prlogin = $Logar->preLogin();

			if(is_array($prlogin)) {
				
				$Logar->setCookie($prlogin['cookie']);
				$Logar->setEcs($prlogin['ecs']);
				$Logar->setEncript($prlogin['encript']);

				$cookie = $Logar->logar();
			}else{
				$cookie = false;
			}

		}
		elseif($tipo == 2) {

			$Logarcnpj = new cnpjLogar();
			$Logarcnpj->setProxy($proxy);
			$Logarcnpj->setUsuario($usuario);
			$Logarcnpj->setSenha($senha);
			$prlogincnpj = $Logarcnpj->preLogin();

			if(is_array($prlogincnpj)) {
				
				$Logarcnpj->setCookie($prlogincnpj['cookie']);
				$Logarcnpj->setEcs($prlogincnpj['ecs']);
				$Logarcnpj->setEncript($prlogincnpj['encript']);

				$cookie = $Logarcnpj->logar();
			}else{
				$cookie = false;
			}

		}

		if($cookie == 'rede') {
			//rede ruim..., tenta relogar com novo proxy.

			echo "\n::REDE RUIM, ATUALIZA REGISTRO E CONTINUAR LOOP.::\n";
			$result = $database->query('UPDATE contas SET', [
				'proxy'  => '',
				'cookie' => '',
			    'update' => date("Y-m-d H:i:s"),
			    'status' => true
			], 'WHERE usuario = ?', $usuario);

			echo "start=proxyoff::{$usuario}::{$proxy}::{$tipo}=end";
			continue;
		}elseif($cookie == 'invalida'){

			$result = $database->query('UPDATE contas SET', [
			    'proxy' => '',
			    'cookie' => '',
			    'status' => false
			], 'WHERE usuario = ?', $usuario);

			echo "start=contaoff::{$usuario}::{$proxy}::{$tipo}=end";
			continue;
		}elseif($cookie === false){

			$result = $database->query('UPDATE contas SET', [
			    'proxy' => '',
			    'update' => date("Y-m-d H:i;s"),
			    'status' => true
			], 'WHERE usuario = ?', $usuario);


			echo "deu false, trocar de proxy ?", PHP_EOL;

		}
		elseif(strlen($cookie) > 15) {

			$payload = [];
			if($nnproxy === true) {
				$payload['count_proxy'] = ($cnt_proxy + 1);
			}

			//zera os ciclos, guardar logs????....

			$payload['proxy']  = $proxy;
			$payload['cookie'] = $cookie;
			$payload['cicles'] = '0';
			$payload['update'] = date("Y-m-d H:i:s");
			$payload['status'] = true;
			$payload['count_cookie'] = ($cnt_cookie + 1);

			$result = $database->query('UPDATE contas SET', $payload , 'WHERE usuario = ?', $usuario);

			echo "salvou cookie > ", $usuario;

		}else{
			echo "\n############# debug linha 107\n";
			echo "start=false::{$usuario}::{$proxy}::{$tipo}=end";	
			die;

		}

	}

	if(isset($result)) {
		echo "### final linha 199....##\n\n";
		//print_r($result);
	}

}












