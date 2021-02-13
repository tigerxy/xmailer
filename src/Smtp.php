<?php
namespace Xmailer;
use Xmailer\Config\Smtp as SmtpConfig;
use ezcMailSmtpTransport;
use ezcMailSmtpTransportOptions;

class Smtp extends ezcMailSmtpTransport {
    private ezcMailSmtpTransportOptions $smtp_conn_options;

    public function __construct()
    {
        $config = new SmtpConfig();

        $this->smtp_conn_options = new ezcMailSmtpTransportOptions();
        $this->smtp_conn_options->preferredAuthMethod = ezcMailSmtpTransport::AUTH_AUTO;
        $this->smtp_conn_options->connectionType = $config->getSSL();
        parent::__construct( 
            $config->getHost(), 
            $config->getUser(), 
            $config->getPass(), 
            $config->getPort(), 
            $this->smtp_conn_options );
    }
}