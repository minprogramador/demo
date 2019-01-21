<?php

namespace Api\Boa\Cnpj;

use Api\Boa\ApiBoa;

class Consulta extends ApiBoa {

	public $cnpj;

    public function setCnpj($cnpj) {
    	$this->cnpj = $cnpj;
    }

    public function getCnpj() {
    	return $this->cnpj;
    }

    public function consultar() {

        $doc       = $this->getCnpj();
        $cookies   = $this->getCookie();
        $proxy     = $this->getProxy();
        $userAgent = $this->getAgent();

        $url       = "https://commercial.bvsnet.com.br/EmpresarialWeb/relatorio/empresarial/gold/consulta";
        $headers   = array();
        $headers[] = "User-Agent: {$userAgent}";
        $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers[] = "Referer: https://commercial.bvsnet.com.br/EmpresarialWeb/relatorio/empresarial/gold";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        $headers[] = "Dnt: 1";
        $headers[] = "Connection: keep-alive";
        $headers[] = "Cookie: ". $cookies;
        $headers[] = "Upgrade-Insecure-Requests: 1";

        $post   = "op=consultar&consulta=on&cnpjs%5B0%5D={$doc}&hasScoreAtacadista=on&hasFaturamentoPresumido=on&cnpjs%5B0%5D=&cnpjs%5B1%5D=&cnpjs%5B2%5D=&cnpjs%5B3%5D=&cnpjs%5B4%5D=&paginaUnica=false&nomeCliente=&comboUf=";
        $result = $this->curl($url, null, $post, true, null, false, $proxy, $headers);

        if(stristr($result, '>INFORMA')){

            return $result;
        }else{
            return false;
        }
    }
}
