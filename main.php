<?php

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

use React\EventLoop\Timer\Timer;
use \React\Http\Request;
use \React\Http\Response;
use \React\EventLoop\Factory;
use \Voidcontext\Arc\Reactor\App;
use \Voidcontext\Arc\Reactor\Server\Adapter\ReactHttpServer;

use React\Promise\Deferred;
use React\ChildProcess\Process;

use \KHR\React\Curl\Curl;
use \KHR\React\Curl\Exception;

require __DIR__ . "/vendor/autoload.php";
require(__DIR__. "/config.php");

$startrun = date("Y-m-d H:i:s");
$loop = Factory::create();
$curl = new Curl($loop);
$server = new ReactHttpServer($loop);

$curl->client->enableHeaders();

$app = new App($server, [
    'port' => 5555
    ,
]);

$connection = new React\MySQL\Connection($loop, [
    'dbname' => $sv_config['dbname'],
    'user'   => $sv_config['user'],
    'passwd' => $sv_config['passwd'],
    'host'   => $sv_config['mysqlsv']
]);

$connection->connect(function () {});

function runPayload($payload) {
	global $loop;
	$result = '';
	$deferred = new Deferred();
	$process  = new Process($payload);

	$process->start($loop);

	$process->stdout->on('data', function ($chunk) use (&$result) {
		$result .= $chunk;
	});
	
	$process->on('error', function($e) use($deferred) {
		$deferred->reject($e);
	});

	$process->on('exit', function ($code, $term) use(&$result, $deferred) {

		$deferred->resolve($result);

	});

	return $deferred->promise();
}

$loop->addPeriodicTimer(45, function(Timer $timer) {

	echo date("Y-m-d H:i:s")." - verifica cookie e proxys..\n";

	$payload = "php shild.php";
	runPayload($payload)
		->then(function ($value) {

			print_r($value);
			echo date("Y-m-d H:i:s")." - FIMMM verifica cookie e proxys..\n";
	    },
	    function ($reason) {
	    	echo "\ndeu error...\n";
	    }
	);

});

//1800

$loop->addPeriodicTimer(1800, function(Timer $timer) {

	echo date("Y-m-d H:i:s")." ~ ..::verifica cache de proxys::..\n";

	$payload = "php shild_rede.php";
	runPayload($payload)
		->then(function ($value) {

			print_r($value);
			echo date("Y-m-d H:i:s")." ~ ..:: FIMM verifica cache de proxys::..\n";
	    },
	    function ($reason) {
	    	echo "\ndeu error...\n";
	    }
	);
		
});

$app->get('/consulta/{doc}', function (Request $request, Response $response) use($connection, $loop) {
	$token = 'demo';
	$querys = $request->getQuery();
	if(isset($querys['type'])) {
		$type = $querys['type'];
		if($type == 'json') {
			$type = 'json';
		}
	}else{
		$type = 'html';
	}

	$verurl = $request->getPath();
	$verurl = explode("consulta/", $verurl);

	if(isset($verurl[1])) {

		$doc = $verurl[1];

		if(strlen($doc) === 11) {
			$cpf = $doc;
		}elseif(strlen($doc) === 14) {
			$cnpj = $doc;
		}else{
			$results = ['msg' => 'doc invalido.'];
		}
	}else{
		$results = ['msg' => 'doc invalido.'];		
	}

	if(!isset($results)) {

		if(isset($cpf)){

			$payload = "php gambi.php a=$token t=$type doc=".$cpf;

			runPayload($payload)
				->then(function ($value) use($response){
					if(strlen($value) < 40) {
						$value = 'Opss, deu erro ao consultar...';						
					}
					$response->writeHead(200, ["Content-Type" => "text/html"]);
					$response->write($value);
					$response->end();
			    },
			    function ($reason) use($response) {

					$response->writeHead(200, ["Content-Type" => "text/html"]);
					$response->write('Opss, deu erro ao consultar....');
					$response->end();
			    }
			);

		}elseif(isset($cnpj)) {
			$results = ['msg' => 'consulta cnpj'];
			$response->writeHead(200, ["Content-Type" => "application/json"]);
			$response->write(json_encode($results));
			$response->end();
		}else{
			$results = ['msg' => 'vc fez alguma merda.'];
			$results = ['msg' => 'consulta cnpj'];
			$response->writeHead(200, ["Content-Type" => "application/json"]);
			$response->write(json_encode($results));
			$response->end();
		}
	}

});


$app->get('/', function (Request $request, Response $response) use($connection, $loop, &$startrun) {

	$connection->query('select * from contas', function ($command, $conn) use ($request, $loop, $response, &$startrun) {
	    if ($command->hasError()) {

	        $error = $command->getError();
	    
	    } else {

	        $results = $command->resultRows;

			$tudook = array_filter($results, function($elem) {
				if(strlen($elem['proxy']) > 5 and strlen($elem['cookie']) > 10 and $elem['status'] == true) {
					return $elem;
				}
			});

			$tudoruim = array_filter($results, function($elem) {
				if($elem['status'] == false) {
					return $elem;
				}
			});

			$penden_proxy = array_filter($results, function($elem) {
				if(strlen($elem['proxy']) < 5 and $elem['status'] == true) {
					return $elem;
				}
			});

			$penden_sessao = array_filter($results, function($elem) {
				if(strlen($elem['cookie']) < 10 and $elem['status'] == true) {
					return $elem;
				}
			});
	    }

		$results = [
			'total'    => count($results),
			'ativos'   => count($tudook),
			'inativos' => count($tudoruim),
			'pendente rede'   => count($penden_proxy),
			'pendente sessao' => count($penden_sessao),
			'status' => true,
			'start'  => $startrun
		];

		$conn->query('select * from redes', function ($command1, $conn1) use ($request, $loop, $response, &$startrun, $results) {

			if ($command1->hasError()) {
				$error = $command1->getError();
			} else {
				$results1 = $command1->resultRows;
			}

			$tudook_rede = array_filter($results1, function($elem) {
				if(strlen($elem['proxy']) > 5 and $elem['ativo'] == true and $elem['status'] == true) {
					return $elem;
				}
			});

			$tudoruim_rede = array_filter($results1, function($elem) {
				if($elem['ativo'] == false) {
					return $elem;
				}
			});
			$results['rede total'] = count($results1);
			$results['rede on']    = count($tudook_rede);
			$results['rede off']   = count($tudoruim_rede);
			$results['rede max']   = 50;

            $payload = "php shild_info.php";
            runPayload($payload)
				->then(function ($value) use ($request, $loop, $response, &$startrun, $results) {
					$value = json_decode($value, true);
                    $results['serverinfo'] = $value;
                    $response->writeHead(200, ["Content-Type" => "application/json"]);
                    $response->write(json_encode($results));
					$response->end();
                },
                function ($reason) use($response) use ($results) {
                	$response->writeHead(200, ["Content-Type" => "application/json"]);
                    $response->write(json_encode($results));
                    $response->end();
                }
            );

		});
	});

});


$app->run();
$loop->run();
