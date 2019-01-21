<?php

namespace Api\Boa\Cpf;

use Api\Boa\ApiBoa;

class Check extends ApiBoa {

    public function check() {

        $cookies   = $this->getCookie();
        $proxy     = $this->getProxy();
        $userAgent = $this->getAgent();

        $url9       = "https://consumer.bvsnet.com.br/FamiliaConsumer/relatorio/pessoal/gold";

        $headers9   = array();
        $headers9[] = "User-Agent: {$userAgent}";
        $headers9[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers9[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers9[] = "Referer: {$url9}";
        $headers9[] = "Dnt: 1";
        $headers9[] = "Connection: keep-alive";
        $headers9[] = "Cookie: ". $cookies;
        $headers9[] = "Upgrade-Insecure-Requests: 1";

        $ver9  = $this->curl($url9, null, null, true, $url9, false, $proxy, $headers9, 5);

        if(stristr($ver9, 'Pessoal Gold</h2>')){
        	return true;
        }elseif(stristr($ver9, 'cation: https://consumer.bvsnet.com.')){
            return false;
        }else{
            return 'rede';
        }
    }
}
