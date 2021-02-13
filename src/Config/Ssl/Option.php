<?php
namespace Xmailer\Config\Ssl;
use Xmailer\Config\AbstractServer as AbstractServer;

class Option {
    private AbstractServer $config;
    public String $id;
    public String $description;
    public Int $port;
    public function __construct(AbstractServer $config, String $id, String $description, Int $port) {
        $this->config = $config;
        $this->id = $id;
        $this->description = $description;
        $this->port = $port;
    }
    public function getId(): String {
        return $this->id;
    }
    public function getDescription(): String {
        return $this->description;
    }
    public function getPort(): Int {
        return $this->port;
    }
    public function isSelected(): Bool {
        return $this->id == $this->config->getSSL();
    }
}