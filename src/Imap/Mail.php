<?php

namespace Xmailer\Imap;

use Xmailer\Config\Mailinglist;
use Xmailer\Config\Mailinglists;
use ezcMail;
use ezcMailAddress;
use ezcMailTools;

class Mail extends ezcMail
{
    const ALL = 'ALL';
    const ANSWERED = 'ANSWERED';
    const DELETED = 'DELETED';
    const DRAFT = 'DRAFT';
    const FLAGGED = 'FLAGGED';
    const NEW = 'NEW';
    const OLD = 'OLD';
    const RECENT = 'RECENT';
    const SEEN = 'SEEN';
    const UNANSWERED = 'UNANSWERED';
    const UNDELETED = 'UNDELETED';
    const UNDRAFT = 'UNDRAFT';
    const UNFLAGGED = 'UNFLAGGED';
    const UNRECENT = 'UNRECENT';
    const UNSEEN = 'UNSEEN';

    public string $mailbox = '';
    public int $messageNr = 0;
    public Mailinglists $assignedMailinglists;

    public function __construct()
    {
        parent::__construct();
        $this->appendExcludeHeaders([
            'Authentication-Results',
            'Delivered-To',
            'Received-SPF',
            'Received',
            'User-Agent',
            'X-bounce-key',
            'X-HE-SMSGID',
            'X-NC-CID',
            'X-Original-To',
            'X-Spam-Checker-Version',
            'X-Spam-Level',
            'X-Spam-Status',
        ]);
    }
    /**
     * @return array<ezcMailAddress>
     */
    public function getAllRecivers(): array
    {
        return array_merge($this->to, $this->cc, $this->bcc);
    }

    public function matchMailToMailinglists(Mailinglists $lists): void
    {
        $recvs = $this->getAllRecivers();
        $listsArray = iterator_to_array($lists);
        $matches = $this->array_inner_join($listsArray, $recvs, function (Mailinglist $list, ezcMailAddress $recv) {
            return $recv->email == $list->address->email;
        });
        $this->assignedMailinglists = new Mailinglists($matches);
    }

    private function array_inner_join(array $a, array $b, callable $func): array
    {
        $ret = array();
        foreach ($a as $ai) {
            foreach ($b as $bi) {
                if ($func($ai, $bi)) {
                    array_push($ret, $ai);
                }
            }
        }
        return $ret;
    }

    public function cleanHeaders(): void
    {
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();

        foreach ($this->assignedMailinglists as $list) {
            $this->addTo($list->address);
        }
        $this->updateHeaders();
        /*$this->to = array_map(function (Mailinglist $list) {
            return $list->address;
        }, iterator_to_array($this->assignedMailinglists));*/
    }

    private function updateHeaders(): void
    {
        // foreach ([
        //     'to' => $this->to,
        //     'cc' => $this->cc,
        //     'bcc' => $this->bcc,
        // ] as $key => $value) {
        //     /*$this->setHeader($key, array_reduce((array) $value, function (?string $str, ezcMailAddress $mail) {
        //         return $str ? ',' . $mail->__toString() : $mail->__toString();
        //     }));*/
        //     var_dump(ezcMailTools::composeEmailAddresses((array) $value));
        // }
        $this->setHeader("To", ezcMailTools::composeEmailAddresses((array) $this->to));
        $this->setHeader("Cc", '');
        $this->setHeader("Bcc", '');
    }

    public function getToString(): string
    {
        return (string) ezcMailTools::composeEmailAddresses((array) $this->to);
    }

    public function myGenerateHeaders()
    {
        // set our headers first.
        //$this->to = new ezcMailAddress()'$this->getToString()';
        //var_dump($this->headers["To"]);

        /*$this->setHeader('Subject', $this->subject, $this->subjectCharset);

        $this->setHeader('MIME-Version', '1.0');
        $this->setHeader('User-Agent', 'Apache Zeta Components');
        $this->setHeader('Date', date('r'));
        $idhost = $this->from != null && $this->from->email != '' ? $this->from->email : 'localhost';
        if (is_null($this->messageId)) {
            $this->setHeader('Message-Id', '<' . ezcMailTools::generateMessageId($idhost) . '>');
        } else {
            $this->setHeader('Message-Id', $this->messageID);
        }

        // if we have a body part, include the headers of the body
        if (is_subclass_of($this->body, "ezcMailPart")) {
            return parent::generateHeaders() . $this->body->generateHeaders();
        }*/
        //parent::headers = $this->headers;
        foreach ($this->headers as $key => $value) {
            parent::setHeader($key, $value);
        }
        return parent::generateHeaders();
    }
}