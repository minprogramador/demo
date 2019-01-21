<?php

namespace Api\Boa\Cpf;

use Api\Boa\ApiBoa;

class Consulta extends ApiBoa {

	public $cpf;

    public function setCpf($cpf) {
    	$this->cpf = $cpf;
    }

    public function getCpf() {
    	return $this->cpf;
    }

    public function consultar() {

        $cpf       = $this->getCpf();
        $cookies   = $this->getCookie();
        $proxy     = $this->getProxy();
        $userAgent = $this->getAgent();

        $url       = "https://consumer.bvsnet.com.br/FamiliaConsumer/relatorio/pessoal/gold/consulta";
        $headers   = array();
        $headers[] = "User-Agent: {$userAgent}";
        $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers[] = "Referer: https://consumer.bvsnet.com.br/FamiliaConsumer/relatorio/pessoal/gold";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        $headers[] = "Dnt: 1";
        $headers[] = "Connection: keep-alive";
        $headers[] = "Cookie: ". $cookies;
        $headers[] = "Upgrade-Insecure-Requests: 1";

        $post   = "INCLUIR=1&tipoRelatorio=22&method=consultarCpfs&consulta=on&cpf1={$cpf}&score=on&cpf2=&cpf3=&cpf4=&cpf5=&cpf6=&nome=&dtNascimento=&uf=&cidade=";
        $result = $this->curl($url, null, $post, true, null, false, $proxy, $headers);

        if(stristr($result, 'ES FORNECIDAS</strong>')){
            return $result;
        }elseif(stristr($result, 'Pessoal Gold</h2>')){
            return true;
        }elseif(stristr($result, 'cation: https://consumer.bvsnet.com.')){
            return false;
        }else{
            return 'rede';
        }

    }

}
