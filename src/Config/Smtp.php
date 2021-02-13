<?php
namespace Xmailer\Config;
use Xmailer\Config\Ssl\Options;
use Xmailer\Config\Ssl\Option;

class Smtp extends AbstractServer {
    public function __construct() {
        parent::__construct();
        $this->ssl_options = new Options(
            new Option($this,"tcp","Plain",25),
            new Option($this,"ssl","SSL",465),
            new Option($this,"sslv2","SSLv2",465),
            new Option($this,"sslv3","SSLv3",465),
            new Option($this,"tls","TLS",465)
        );
    }
    protected String $sub_path = '.smtp';
}