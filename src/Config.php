<?php
namespace Xmailer;

use Xmailer\Config\Imap;
use Xmailer\Config\Smtp;
use Xmailer\Config\AbstractConfig as AbstractConfig;

class Config extends AbstractConfig {
    public Imap $imap;
    public Smtp $smtp;
    public function __construct() {
        $this->imap = new Imap();
        $this->smtp = new Smtp();
    }
    public function getSpam(): Bool {
        return $this->getVal('spam');
    }
    public function getReplyTo(): Bool {
        return $this->getVal('replyto');
    }
    public function getAllow(): Array {
        return $this->getVal('allow');
    }
    public function getAddPageName(): Bool {
        return $this->getVal('addpagename');
    }
    public function getAmountSendPerRun(): Int {
        return 10;
    }
    public function setSpam(Bool $val) {
        $this->setVal('spam',$val);
    }
    public function setReplyTo(Bool $val) {
        $this->setVal('replyto',$val);
    }
    public function setAllow(Bool $val) {
        $this->setVal('allow',$val);
    }
    public function setAddPageName(Bool $val) {
        $this->setVal('addpagename',$val);
    }
    public function allSslOptionsToJson() {
        return json_encode(array(
            "imap" => $this->imap->ssl_options->toArray(),
            "smtp" => $this->smtp->ssl_options->toArray(),
        ));
    }
}