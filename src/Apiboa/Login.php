<?php

namespace Api\Boa;

class Login extends ApiBoa {

    public function preLogin() {

        $usuario   = $this->getUsuario();
        $senha     = $this->getSenha();
        $proxy     = $this->getProxy();
        $userAgent = $this->getAgent();

        $url1 = "https://www.bvsnet.com.br/cgi-bin/db2www/NETPO101.mbr/Equifax?lk_codig={$usuario}&lk_senha={$senha}&lu_lunif=&lu_ludtm=&lu_eqfax=";

        $headers1 = array(
            "Connection: keep-alive",
            "Upgrade-Insecure-Requests: 1",
        );

        $ver1 = $this->curl($url1, null, null, true, null, false, $proxy, $headers1);

        if(strlen($ver1) < 100){
            return 'rede';
        }

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
        $ver2    = $this->curl($url2, null, $post2, true, $url1, false, $proxy, $headers2);
        
        if(stristr($ver2, 'IDENTIFICACAO%20INVALIDA')){
            return 'invalida';
        }elseif(!stristr($ver2, 'Produtos Boa Vista</li')){
            return false;
        }

        $cookie      = $this->getCookies($ver2);
        $ecs         = $this->corta($ver2, 'id="ecs" value="', '"');
        $nomeEncript = $this->corta($ver2, 'id="nomeEncript" value="', '"');
        
        return ['cookie' => $cookie, 'ecs' => $ecs, 'encript' => $nomeEncript];
    }



}