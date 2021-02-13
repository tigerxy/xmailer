<?php
namespace Xmailer\Config;
use Xmailer\Config\Ssl\Options;
use Xmailer\Config\Ssl\Option;

class Imap extends AbstractServer {
    public function __construct() {
        parent::__construct();
        $this->ssl_options = new Options(
            new Option($this,"tcp","Plain",143),
            new Option($this,"ssl","SSL/TLS",993)
        );
    }
    protected String $sub_path = '.imap';
}