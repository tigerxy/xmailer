<?php

namespace Xmailer\Config;

use JsonSerializable;
/*
use Xmailer\Config\Imap;
use Xmailer\Config\Smtp;
use Xmailer\Config\AbstractConfig as AbstractConfig;
*/

class Config extends AbstractConfig implements JsonSerializable
{
    public ImapConfig $imap;
    public SmtpConfig $smtp;
    public MailingLists $mailingLists;
    public function __construct()
    {
        $this->imap = new ImapConfig();
        $this->smtp = new SmtpConfig();
        $this->mailingLists = new MailingLists();
        $this->mailingLists->readFromConfig();
    }
    public function getSpam(): Bool
    {
        return $this->getVal('spam');
    }
    public function getReplyTo(): Bool
    {
        return $this->getVal('replyTo');
    }
    public function getAllow(): array
    {
        return $this->getVal('allow');
    }
    public function getAddPageName(): Bool
    {
        return $this->getVal('addPageName');
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
        $this->setVal('replyTo', $val);
    }
    public function setAllow(Bool $val)
    {
        $this->setVal('allow', $val);
    }
    public function setAddPageName(Bool $val)
    {
        $this->setVal('addPageName', $val);
    }
    public function allSslOptionsToJson(): array
    {
        return [
            "imap" => $this->imap->ssl_options->toArray(),
            "smtp" => $this->smtp->ssl_options->toArray(),
        ];
    }
    public function jsonSerialize($hidePassword = True): array
    {
        return [
            'spam' => $this->getSpam(),
            'replyTo' => $this->getReplyTo(),
            'addPageName' => $this->getAddPageName(),
            'imap' => $this->imap->jsonSerialize($hidePassword),
            'smtp' => $this->smtp->jsonSerialize($hidePassword),
            'allow' => $this->getAllow(),
            'lists' => array_map(function ($list) {
                return $list->jsonSerialize();
            }, $this->mailingLists->toArray())
        ];
    }
}