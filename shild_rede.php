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
use Api\Boa\utils\Curl;

require(dirname(__FILE__).'/vendor/autoload.php');
require(__DIR__. "/config.php");


$user     = $sv_config['user'];
$password = $sv_config['passwd'];
$mysqlsv  = $sv_config['mysqlsv'];
$mysqlbd  = $sv_config['dbname'];
$dsn      = "mysql:host={$mysqlsv};dbname={$mysqlbd}";
$keyapi   = $sv_config['key_proxyrotator'];

$database = new Nette\Database\Connection($dsn, $user, $password);

function getProxy($max=5, $keyapi) {

	$url_api = "http://falcon.proxyrotator.com:51337/?apiKey={$keyapi}&country=br";

	$Curl = new Curl();
	$Curl->setTimeout(3);
	for($x=0; $x <= $max; $x++) {
		$Curl->add($url_api);
	}
	$listpr   = $Curl->run();
	$listprok = $listpr['body'];
	if(count($listprok) < 1) {
		return [];
	}

	$lista_nova = [];

	foreach($listprok as $pr){
		if(stristr($pr, 'proxy"')){
			$pr = json_decode($pr);
			if(isset($pr->proxy)){
				$lista_nova[] = $pr->proxy;
			}		
		}
	}

	return $lista_nova;
}

function checkProxy($proxy) {

	$url  = 'https://www.bvsnet.com.br';
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING  => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT   => 3,
	  CURLOPT_CONNECTTIMEOUT => 3,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_PROXY => $proxy
	));

	$response = curl_exec($curl);
	$err      = curl_error($curl);

	curl_close($curl);

	if ($err) {
		return false;
	} else {
		
		if(stristr($response, 'ACSP')){
			return true;
		}else{
			return false;
		}
	}
}

$result = $database->query('SELECT * FROM redes where status = ? LIMIT 500', true);
$count_ativos   = 0;
$count_inativos = 0;
$count_total    = $result->getRowCount();

foreach ($result as $row) {

	$proxy  = $row->proxy;
	$start  = $row->start;
	$ativo  = $row->ativo;
	$status = $row->status;

	$check = checkProxy($proxy);

	if($check === true){

		$payload = [];
		$payload['update'] = date("Y-m-d H:i:s");
		$payload['ativo']  = true;
		$payload['status'] = true;

		$result = $database->query('UPDATE redes SET', $payload , 'WHERE proxy = ?', $proxy);
		$count_ativos++;
	}else{

		$payload = [];
		$payload['update'] = date("Y-m-d H:i:s");
		$payload['ativo']  = false;
		$payload['status'] = true;

		$result = $database->query('UPDATE redes SET', $payload , 'WHERE proxy = ?', $proxy);
		$count_inativos++;
	}
}

if($count_ativos < 50) {
	$nproxys = getProxy(5, $keyapi);
	if(count($nproxys) > 0){

		foreach($nproxys as $nnpr) {
			if(stristr($nnpr, ':')){
				$proxy = $nnpr;
				$now   = date("Y-m-d H:i:s");

				$database->query('INSERT INTO redes ?', [
				    'proxy' => $proxy,
				    'start' => $now,
				    'update'=> $now,
				    'ativo' => false,
				    'status'=> true
				]);

				$id = $database->getInsertId();

				if($id > 0) {
					echo "--->{$proxy} - inserido em: $now\n";
				}else{
					echo "--->{$proxy} - ERROR ao inserido em: $now\n";
				}
			}
		}

	}
}

echo "
--------------------------------
---> :: STATUS REDE ::
---> Total: {$count_total}
---> Ativos: {$count_ativos}
---> Inativos: {$count_inativos}
\n";









