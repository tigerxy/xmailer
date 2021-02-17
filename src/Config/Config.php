<?php

namespace Xmailer\Config;

use \JsonSerializable;
/*
use Xmailer\Config\Imap;
use Xmailer\Config\Smtp;
use Xmailer\Config\AbstractConfig as AbstractConfig;
*/

class Config extends AbstractConfig implements JsonSerializable
{
    public Imap $imap;
    public Smtp $smtp;
    public Mailinglists $mailinglists;
    public function __construct()
    {
        $this->imap = new Imap();
        $this->smtp = new Smtp();
        $this->mailinglists = new Mailinglists();
        $this->mailinglists->readFromConfig();
    }
    public function getSpam(): Bool
    {
        return $this->getVal('spam');
    }
    public function getReplyTo(): Bool
    {
        return $this->getVal('replyto');
    }
    public function getAllow(): array
    {
        return $this->getVal('allow');
    }
    public function getAddPageName(): Bool
    {
        return $this->getVal('addpagename');
    }
    public function getAmountSendPerRun(): Int
    {
        return 10;
    }
    public function setSpam(Bool $val)
    {
        $this->setVal('spam', $val);
    }
    public function setReplyTo(Bool $val)
    {
        $this->setVal('replyto', $val);
    }
    public function setAllow(Bool $val)
    {
        $this->setVal('allow', $val);
    }
    public function setAddPageName(Bool $val)
    {
        $this->setVal('addpagename', $val);
    }
    public function allSslOptionsToJson()
    {
        return [
            "imap" => $this->imap->ssl_options->toArray(),
            "smtp" => $this->smtp->ssl_options->toArray(),
        ];
    }
    public function jsonSerialize($hidePassword = True)
    {
        return [
            'spam' => $this->getSpam(),
            'replyto' => $this->getReplyTo(),
            'addpagename' => $this->getAddPageName(),
            'imap' => $this->imap->jsonSerialize($hidePassword),
            'smtp' => $this->smtp->jsonSerialize($hidePassword),
            'allow' => $this->getAllow(),
            'lists' => array_map(function ($list) {
                return $list->jsonSerialize();
            }, iterator_to_array($this->mailinglists))
        ];
    }
}