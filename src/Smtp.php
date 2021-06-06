<?php

namespace Xmailer;

use Xmailer\Config\ImapConfig;
use Xmailer\Config\SmtpConfig;
use ezcMailSmtpTransport;
use ezcMailSmtpTransportOptions;

class Smtp extends ezcMailSmtpTransport
{
    private ezcMailSmtpTransportOptions $smtp_conn_options;

    public function __construct()
    {
        $this->smtp_conn_options = new ezcMailSmtpTransportOptions();
        $this->smtp_conn_options->preferredAuthMethod = ezcMailSmtpTransport::AUTH_AUTO;
        $this->smtp_conn_options->connectionType = ImapConfig::getSSL();
        parent::__construct(
            ImapConfig::getHost(),
            ImapConfig::getUser(),
            ImapConfig::getPass(),
            ImapConfig::getPort(),
            $this->smtp_conn_options
        );
    }
}
