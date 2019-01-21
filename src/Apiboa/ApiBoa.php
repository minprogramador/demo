<?php

namespace Api\Boa;
use Api\Boa\utils\Util;

abstract class ApiBoa extends Util {

    public $cookie  = null;
    public $usuario = null;
    public $senha   = null;
    public $proxy   = null;
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

    public function getAgent() {
    	return $this->userAgent;
    }

    public function setAgent($ag) {
        $this->userAgent = $ag;
    }

    public function getCookie() {
        return $this->cookie;
    }

    public function setCookie($cc) {
        $this->cookie = $cc;
    }

}