<?php
namespace Xmailer\Connection;
use ezcMailParser;

class Server_old {
    private Imap $imap;
    private Smtp $smtp;
    private Array $mails;

    public function __construct($folder='')
    {
        $this->imap = new Imap($folder);
        $this->smtp = new Smtp();
    }

    public function selectFolder($folder) {
        $this->imap->selectFolder($folder);
        $this->mails = null;
    }

    public function sendFirst($number) {
        $this->imap->sendFirst($number);
    }

    public function next() {
        if($this->mails == null) {
            $this->mails = array_keys($this->imap->listMessages());
        } else {
            array_shift($this->mails);
        }
        return count($this->mails) > 0;
    }

    public function getMail() {
        var_dump($this->mails);
        $set = $this->imap->fetchByMessageNr($this->mails[0], false);
        $parser = new ezcMailParser();
        return $parser->parseMail($set)[0];
    }
}
?>