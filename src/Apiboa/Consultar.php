<?php

namespace Api\Boa;
use Api\Boa\utils\Util;

class Consultar
{
	public $cookie, $cpf;
	public $proxy;
    public $userAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0';

    public function setCookie($cc) {
    	$this->cookie = $cc;
    }

    public function getCookie() {
    	return $this->cookie;
    }

    public function setProxy($pr) {
    	$this->proxy = $pr;
    }

    public function getProxy() {
    	return $this->proxy;
    }

    public function setCpf($cpf) {
    	$this->cpf = $cpf;
    }

    public function getCpf() {
    	return $this->cpf;
    }

    public function run(){

        $Util      = new Util();
        $cpf = $this->getCpf();
        $cookies   = $this->getCookie();
        $proxy     = $this->getProxy();
        $userAgent = $this->userAgent;

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
        $result = $Util->curl($url, null, $post, true, null, false, $proxy, $headers);

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
