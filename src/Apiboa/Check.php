<?php

namespace Api\Boa;
use Api\Boa\utils\Util;

class Check
{
	public $cookie;
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

    public function run(){

        $Util      = new Util();

        $cookies   = $this->getCookie();
        $proxy     = $this->getProxy();
        $userAgent = $this->userAgent;

        $url9       = "https://consumer.bvsnet.com.br/FamiliaConsumer/relatorio/pessoal/gold";
        $headers9   = array();
        $headers9[] = "User-Agent: {$userAgent}";
        $headers9[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers9[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers9[] = "Referer: {$url6}";
        $headers9[] = "Dnt: 1";
        $headers9[] = "Connection: keep-alive";
        $headers9[] = "Cookie: ". $cookies;
        $headers9[] = "Upgrade-Insecure-Requests: 1";

        $ver9  = $Util->curl($url9, null, null, true, $url9, false, $proxy, $headers9, 5);

        if(stristr($ver9, 'Pessoal Gold</h2>')){
        	return true;
        }elseif(stristr($ver9, 'cation: https://consumer.bvsnet.com.')){
            return false;
        }else{
            return 'rede';
        }

    }
}
