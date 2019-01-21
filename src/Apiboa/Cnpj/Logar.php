<?php

namespace Api\Boa\Cnpj;

use Api\Boa\Login;

class Logar extends Login {

    private $ecs, $encript;

    public function setEcs($ecs) {
        $this->ecs = $ecs;
    }

    public function getEcs() {
        return $this->ecs;
    }

    public function setEncript($en){
        $this->encript = $en;
    }

    public function getEncript() {
        return $this->encript;
    }

    public function logar() {
        $cookie2 = $this->getCookie();
        $usuario   = $this->getUsuario();
        $senha     = $this->getSenha();
        $proxy = $this->getProxy();
        $userAgent = $this->getAgent();
        $ecs = $this->getEcs();
        $nomeEncript = $this->getEncript();

        $url2 = 'https://consulta.bvsnet.com.br/menu.asp';
        $url3 = 'https://commercial.bvsnet.com.br/SegurancaWeb/login.jsp';

        $headers3 = array();
        $headers3[] = "User-Agent: {$userAgent}";
        $headers3[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers3[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers3[] = "Referer: https://consulta.bvsnet.com.br/menu.asp";
        $headers3[] = "Content-Type: application/x-www-form-urlencoded";
        $headers3[] = "Dnt: 1";
        $headers3[] = "Connection: keep-alive";
        $headers3[] = "Cookie: " . $cookie2;
        $headers3[] = "Upgrade-Insecure-Requests: 1";

        $post3   = "ecs={$ecs}&nomeEncript={$nomeEncript}&type=empresarialGold&url=..%2FEmpresarialWeb%2Flogin.jsp&cod_servico=&operador=";
        $ver3    = $this->curl($url3, null, $post3, true, $url2, false, $proxy, $headers3);
        $cookie3 = $this->getCookies($ver3);
        $urlfrm4 = $this->corta($ver3, 'name="url" value="', '"');
        $urlfrm4 = urlencode($urlfrm4);
        //----------------------------------------------------------------//

        $url4  = 'https://commercial.bvsnet.com.br/SegurancaWeb/j_security_check';
        $post4 = "url={$urlfrm4}&j_username={$usuario}&j_password={$senha}";

        $cookies = $cookie2 . '; ' . $cookie3;

        $headers4 = array();
        $headers4[] = "User-Agent: {$userAgent}";
        $headers4[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers4[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers4[] = "Referer: https://commercial.bvsnet.com.br/SegurancaWeb/login.jsp";
        $headers4[] = "Content-Type: application/x-www-form-urlencoded";
        $headers4[] = "Dnt: 1";
        $headers4[] = "Connection: keep-alive";
        $headers4[] = "Cookie: " . $cookies;
        $headers4[] = "Upgrade-Insecure-Requests: 1";

        $ver4    = $this->curl($url4, $cookie3, $post4, true, $url3, false, $proxy, $headers4);
        $cookie4 = $this->getCookies($ver4);
        //------------------------------------------------------------------//

        $url5    = 'https://commercial.bvsnet.com.br/SegurancaWeb/';
        $cookies = $cookie3 . '; ' . $cookie4;

        $headers5   = array();
        $headers5[] = "User-Agent: {$userAgent}";
        $headers5[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers5[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers5[] = "Referer: https://commercial.bvsnet.com.br/SegurancaWeb/login.jsp";
        $headers5[] = "Dnt: 1";
        $headers5[] = "Connection: keep-alive";
        $headers5[] = "Cookie: ".$cookies;
        $headers5[] = "Upgrade-Insecure-Requests: 1";

        $ver5  = $this->curl($url5, null, null, true, $url3, false, $proxy, $headers5);

        $url6  = trim(rtrim($this->corta($ver5, 'ocation: ', "\n")));
        
        if(!stristr($url6, 'bvsnet.com.br')){
            return 'invalida';
        }

        $headers6   = array();
        $headers6[] = "User-Agent: {$userAgent}";
        $headers6[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers6[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers6[] = "Referer: https://commercial.bvsnet.com.br/SegurancaWeb/login.jsp";
        $headers6[] = "Dnt: 1";
        $headers6[] = "Connection: keep-alive";
        $headers6[] = "Cookie: ".$cookies;
        $headers6[] = "Upgrade-Insecure-Requests: 1";

        $ver6  = $this->curl($url6, null, null, true, $url3, false, $proxy, $headers6);

        $url7       = "https://commercial.bvsnet.com.br/EmpresarialWeb/j_security_check?type=empresarialGold";
        $headers7   = array();
        $headers7[] = "User-Agent: {$userAgent}";
        $headers7[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers7[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers7[] = "Referer: {$url6}";
        $headers7[] = "Content-Type: application/x-www-form-urlencoded";
        $headers7[] = "Dnt: 1";
        $headers7[] = "Connection: keep-alive";
        $headers7[] = "Cookie: ".$cookies;
        $headers7[] = "Upgrade-Insecure-Requests: 1";

        $post6 = "j_username={$usuario}&j_password={$senha}&origem=null&lk_click=null&cod_servico=&operador=&bt_cheque_popup=null&ecs={$ecs}";
        $ver7       = $this->curl($url7, null, $post6, true, $url6, false, $proxy, $headers7);

        $cookie7 = $this->getCookies($ver7); //cookie novos..
        $cookies = $cookie3 . '; '.$cookie7; 

        $url8       = "https://commercial.bvsnet.com.br/EmpresarialWeb/";
        $headers8   = array();
        $headers8[] = "User-Agent: {$userAgent}";
        $headers8[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers8[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers8[] = "Referer: {$url6}";
        $headers8[] = "Content-Type: application/x-www-form-urlencoded";
        $headers8[] = "Dnt: 1";
        $headers8[] = "Connection: keep-alive";
        $headers8[] = "Cookie: ".$cookies;
        $headers8[] = "Upgrade-Insecure-Requests: 1";
        $post8      = "j_username={$usuario}&j_password={$senha}&origem=null&lk_click=null&cod_servico=&operador=&bt_cheque_popup=null&ecs={$ecs}";
        $ver8       = $this->curl($url8, null, $post8, true, $url6, false, $proxy, $headers8);

        $url9       = "https://commercial.bvsnet.com.br/EmpresarialWeb/relatorio/empresarial/gold";
        $headers9   = array();
        $headers9[] = "User-Agent: {$userAgent}";
        $headers9[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers9[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers9[] = "Referer: {$url6}";
        $headers9[] = "Dnt: 1";
        $headers9[] = "Connection: keep-alive";
        $headers9[] = "Cookie: ". $cookies;
        $headers9[] = "Upgrade-Insecure-Requests: 1";

        $ver9  = $this->curl($url9, null, null, true, $url6, false, $proxy, $headers9);

        if(stristr($ver9, 'mpresarial Gold</h2>')){
            //$this->setCookie($cookies);
            return $cookies;
        }else{
            return false;
        }
    }
}
