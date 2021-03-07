<?php

namespace Xmailer\Imap;

use Xmailer\Config\Mailinglist;
use Xmailer\Config\Mailinglists;
use ezcMail;
use ezcMailAddress;
use ezcMailTools;
// FIXME: Replace these:
use Concrete\Core\Support\Facade\Config;

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

    public ?Mailbox $mailbox = null;
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

    public function findReceivingMailinglists(Mailinglists $lists): Mail
    {
        $recvs = $this->getAllRecivers();
        $matches = $this->array_inner_join($lists->toArray(), $recvs, function (Mailinglist $list, ezcMailAddress $recv) {
            return $recv->email == $list->address->email;
        });
        $this->assignedMailinglists = new Mailinglists($matches);
        return $this;
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

    public function isSenderMemberOfMailinglist(): Mail
    {
        $this->assignedMailinglists = new Mailinglists(array_filter($this->assignedMailinglists->toArray(), function (Mailinglist $list) {
            return $list->isMemberOfList($this->from);
        }));
        return $this;
    }

    public function cleanHeaders(): Mail
    {
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();

        $this->appendExcludeHeaders([
            'bcc',
            'cc',
        ]);

        foreach ($this->assignedMailinglists as $list) {
            $this->addTo($list->address);
        }
        $this->updateHeaders();
        /*$this->to = array_map(function (Mailinglist $list) {
            return $list->address;
        }, iterator_to_array($this->assignedMailinglists));*/
        return $this;
    }

    public function appendForEachMailinglistmemberTo(Mailbox $mbox)
    {
        $backup_to = $this->getTo();
        $backup_reply_to = $this->getReplyTo();
        foreach ($this->assignedMailinglists as $list) {
            $this->setReplyTo($this->getFrom());
            $this->setFrom($list->address);
            foreach ($list->getMemberEmailAdresses() as $reciver) {
                $this->setTo([$reciver]);
                $mbox->appendMail($this);
            }
        }
        $this->setTo($backup_to);
        return $this;
    }

    private function updateHeaders(): void
    {
        $this->setHeader("To", ezcMailTools::composeEmailAddresses((array) $this->to));
        $this->setHeader("Cc", '');
        $this->setHeader("Bcc", '');
    }

    public function getToString(): string
    {
        return (string) ezcMailTools::composeEmailAddresses((array) $this->to);
    }

    public function moveTo(Mailbox $mbox): Mail
    {
        $this->mailbox->moveMail($this, $mbox);
        return $this;
    }

    public function addPagenameToSubject(): Mail
    {
        if (Config::get('xmailer.addpagename')) {
            $this->subject = '[' . Config::get('concrete.site') . '] ' . $this->subject;
        }
        return $this;
    }

    /**
     * @return array(ezcMailAddress)
     */
    private function getTo()
    {
        return $this->to;
    }

    /**
     * @param array(ezcMailAddress) $address
     */
    private function setTo($address): void
    {
        $this->to = $address;
    }

    private function getReplyTo(): ezcMailAddress
    {
        return new ezcMailAddress($this->getHeader('reply-to'));
    }

    private function setReplyTo(ezcMailAddress $address = null): void
    {
        $replyto = '';
        if ($address != null) {
            $replyto = $address->__toString();
        }
        $this->setHeader('reply-to', $replyto);
    }

    private function getFrom(): ezcMailAddress
    {
        return $this->from;
    }

    private function setFrom(ezcMailAddress $address): void
    {
        $this->from = $address;
    }
}