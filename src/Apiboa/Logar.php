<?php

namespace Api\Boa;
use Api\Boa\utils\Util;

class Logar
{
	public $cookie;
	public $usuario, $senha, $proxy;
    public $userAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0';

    public function setUsuario($user) {
        $this->usuario = $user;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function setSenha($sen){
        $this->senha = $sen;
    }

    public function getSenha() {
        return $this->senha;
    }

    public function setProxy($proxy) {
        $this->proxy = $proxy;
    }

    public function getProxy() {
        return $this->proxy;
    }

    public function run()
    {
        $usuario   = $this->getUsuario();
        $senha     = $this->getSenha();
        $proxy     = $this->getProxy();
        $userAgent = $this->userAgent;
        
        $Util = new Util();

        $url1 = "https://www.bvsnet.com.br/cgi-bin/db2www/NETPO101.mbr/Equifax?lk_codig={$usuario}&lk_senha={$senha}&lu_lunif=&lu_ludtm=&lu_eqfax=";
        $headers1 = array(
            "Connection: keep-alive",
            "Upgrade-Insecure-Requests: 1",
        );

        $ver1 = $Util->curl($url1, null, null, true, null, false, $proxy, $headers1);

        if(strlen($ver1) < 100){
            return 'rede';
        }
        //-----------------------------------------------------------------//
        $url2 = 'https://consulta.bvsnet.com.br/menu.asp';

        $headers2 = array();
        $headers2[] = "User-Agent: {$userAgent}";
        $headers2[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers2[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers2[] = "Referer: https://www.bvsnet.com.br/cgi-bin/db2www/NETPO101.mbr/Equifax?lk_codig={$usuario}&lk_senha={$senha}&lu_lunif=&lu_ludtm=&lu_eqfax=";
        $headers2[] = "Content-Type: application/x-www-form-urlencoded";
        $headers2[] = "Dnt: 1";
        $headers2[] = "Connection: keep-alive";
        $headers2[] = "Upgrade-Insecure-Requests: 1";

        $post2   = "name={$usuario}&senha={$senha}&redir=https%3A%2F%2Fwww.bvsnet.com.br%2Fcgi-bin%2Fdb2www%2FNETPO101.mbr%2FloginSI";
        $ver2    = $Util->curl($url2, null, $post2, true, $url1, false, $proxy, $headers2);
        

        if(stristr($ver2, 'IDENTIFICACAO%20INVALIDA')){
            return 'invalida';
        }elseif(!stristr($ver2, 'Produtos Boa Vista</li')){
            return false;
        }


        $cookie2 = $Util->getCookies($ver2);

        $ecs         = $Util->corta($ver2, 'id="ecs" value="', '"');
        $nomeEncript = $Util->corta($ver2, 'id="nomeEncript" value="', '"');

        //----------------------------------------------------------------//

        $url3 = 'https://consumer.bvsnet.com.br/SegurancaWeb/login.jsp';

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

        $post3   = "ecs={$ecs}&nomeEncript={$nomeEncript}&type=22&url=..%2FFamiliaConsumer%2Flogin.jsp&cod_servico=&operador=";
        $ver3    = $Util->curl($url3, null, $post3, true, $url2, false, $proxy, $headers3);
        $cookie3 = $Util->getCookies($ver3);
        $urlfrm4 = $Util->corta($ver3, 'name="url" value="', '"');
        $urlfrm4 = urlencode($urlfrm4);

        //----------------------------------------------------------------//

        $url4  = 'https://consumer.bvsnet.com.br/SegurancaWeb/j_security_check';
        $post4 = "url={$urlfrm4}&j_username={$usuario}&j_password={$senha}";

        $cookies = $cookie2 . '; ' . $cookie3;

        $headers4 = array();
        $headers4[] = "User-Agent: {$userAgent}";
        $headers4[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers4[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers4[] = "Referer: https://consumer.bvsnet.com.br/SegurancaWeb/login.jsp";
        $headers4[] = "Content-Type: application/x-www-form-urlencoded";
        $headers4[] = "Dnt: 1";
        $headers4[] = "Connection: keep-alive";
        $headers4[] = "Cookie: " . $cookies;
        $headers4[] = "Upgrade-Insecure-Requests: 1";

        $ver4    = $Util->curl($url4, $cookie3, $post4, true, $url3, false, $proxy, $headers4);
        $cookie4 = $Util->getCookies($ver4);
        //------------------------------------------------------------------//

        $url5    = 'https://consumer.bvsnet.com.br/SegurancaWeb/';
        $cookies = $cookie3 . '; ' . $cookie4;

        $headers5   = array();
        $headers5[] = "User-Agent: {$userAgent}";
        $headers5[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers5[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers5[] = "Referer: https://consumer.bvsnet.com.br/SegurancaWeb/login.jsp";
        $headers5[] = "Dnt: 1";
        $headers5[] = "Connection: keep-alive";
        $headers5[] = "Cookie: ".$cookies;
        $headers5[] = "Upgrade-Insecure-Requests: 1";

        $ver5  = $Util->curl($url5, null, null, true, $url3, false, $proxy, $headers5);

        $url6  = trim(rtrim($Util->corta($ver5, 'ocation: ', "\n")));
        if(!stristr($url6, 'bvsnet.com.br')){
            return 'invalida';
        }

        $headers6   = array();
        $headers6[] = "User-Agent: {$userAgent}";
        $headers6[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers6[] = "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3";
        $headers6[] = "Referer: https://consumer.bvsnet.com.br/SegurancaWeb/login.jsp";
        $headers6[] = "Dnt: 1";
        $headers6[] = "Connection: keep-alive";
        $headers6[] = "Cookie: ".$cookies;
        $headers6[] = "Upgrade-Insecure-Requests: 1";

        $ver6  = $Util->curl($url6, null, null, true, $url3, false, $proxy, $headers6);

        $url7       = "https://consumer.bvsnet.com.br/FamiliaConsumer/j_security_check?type=22";
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
        $post6      = "j_username={$usuario}&j_password={$senha}&origem=null&lk_click=null&cod_servico=&operador=&bt_cheque_popup=null&ecs={$ecs}";
        $ver7       = $Util->curl($url7, null, $post6, true, $url6, false, $proxy, $headers7);

        $cookie7 = $Util->getCookies($ver7); //cookie novos..
        $cookies = $cookie3 . '; '.$cookie7; 

        $url8       = "https://consumer.bvsnet.com.br/FamiliaConsumer/";
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
        $ver8       = $Util->curl($url8, null, $post8, true, $url6, false, $proxy, $headers8);

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

        $ver9  = $Util->curl($url9, null, null, true, $url6, false, $proxy, $headers9);

        if(stristr($ver9, 'Pessoal Gold</h2>')){
            return $cookies;
        }else{
            return false;
        }
    }
}
