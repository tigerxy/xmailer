<?php

namespace Xmailer\Config;

use Xmailer\Config\Ssl\Options;

abstract class AbstractServer extends AbstractConfig
{
    public Options $ssl_options;
    protected String $sub_path;
    public function __construct()
    {
        $this->path = $this->path . $this->sub_path;
    }
    public function getHost(): String
    {
        return $this->getVal('host');
    }
    public function getUser(): String
    {
        return $this->getVal('user');
    }
    public function getPass(): String
    {
        return $this->getVal('password');
    }
    public function getPort(): Int
    {
        return $this->getVal('port');
    }
    public function getSSL(): String
    {
        return $this->getVal('ssl');
    }
    public function setHost(String $val)
    {
        $this->setVal('host', $val);
    }
    public function setUser(String $val)
    {
        $this->setVal('user', $val);
    }
    public function setPass(String $val)
    {
        $this->setVal('password', $val);
    }
    public function setPort(Int $val)
    {
        $this->setVal('port', $val);
    }
    public function setSSL(String $val)
    {
        $this->setVal('ssl', $val);
    }
}
