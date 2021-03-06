<?php

namespace Xmailer\Imap;

use ezcMailPartWalkContext;
use ezcMailText;
use Xmailer\Config\Config;
use Xmailer\Config\MailingList;
use Xmailer\Config\MailingLists;
use ezcMail;
use ezcMailAddress;
use ezcMailTools;
use Xmailer\ConfigError;
use Xmailer\ConnectionError;


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
    public MailingLists $assignedMailingLists;

    private bool $isInvalid = false;
    private bool $isSpam = false;
    private bool $debug = false;

    public function __construct()
    {
        parent::__construct();
        $this->appendExcludeHeaders([
            'Authentication-Results',
            'Delivered-To',
            'Received-SPF',
            'Received',
            'User-Agent',
            'DKIM-Signature',
            'Content-Type',
            'Content-Transfer-Encoding',
            'Bcc',
            'Cc',
            'X-Spam-Checker-Version',
            'X-Spam-Level',
            'X-Spam-Status',
            'X-Original-To',
            'X-SecureMailgate-Identity',
            'X-Originating-IP',
            'X-SecureMailgate-Domain',
            'X-SecureMailgate-Username',
            'X-SecureMailgate-Outgoing-Class',
            'X-SecureMailgate-Outgoing-Evidence',
            'X-Recommended-Action',
            'X-Filter-ID',
            'X-Report-Abuse-To',
            'X-Rspamd-Queue-Id',
            'X-Spamd-Result',
            'X-Rspamd-Server',
            'X-NC-CID',
            'X-PPP-Message-ID',
            'X-PPP-Vhost',
        ]);
    }

    public function getAllReceivers(): array
    {
        return array_merge($this->to, $this->cc, $this->bcc);
    }

    public function setReceivingMailingList(MailingList $list): Mail
    {
        $this->assignedMailingLists = new MailingLists([$list]);
        return $this;
    }

    public function findReceivingMailingLists(MailingLists $lists): Mail
    {
        $receivers = $this->getAllReceivers();
        if (empty($receivers)) {
            $this->isInvalid = true;
            return $this;
        }
        $matchingMailingLists = $this->array_inner_join($lists->toArray(), $receivers, function (MailingList $list, ezcMailAddress $receiver) {
            return $list->isReceiver($receiver);
        });

        if (empty($matchingMailingLists)) {
            $this->isSpam = true;
            return $this;
        }

        $this->assignedMailingLists = new MailingLists($matchingMailingLists);
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

    /**
     * @throws ConfigError
     */
    public function isSenderMemberOrWhitelisted(): Mail
    {
        if (empty($this->from))
            $this->isInvalid = true;

        if ($this->isSpamOrInvalid() || $this->senderIsWhitelisted())
            return $this;

        $this->assignedMailingLists = new MailingLists(array_filter($this->assignedMailingLists->toArray(), function (MailingList $list) {
            return $list->isMemberOfList($this->from);
        }));

        if (empty($this->assignedMailingLists->toArray()))
            $this->isSpam = true;

        return $this;
    }

    private function senderIsWhitelisted(): bool
    {
        return in_array($this->from->email, Config::getAllow());
    }

    public function cleanHeaders(): Mail
    {
        if ($this->isSpamOrInvalid())
            return $this;

        $this->to = array();
        $this->cc = array();
        $this->bcc = array();

        foreach ($this->assignedMailingLists as $list) {
            $this->addTo($list->address);
        }
        $this->updateHeaders();
        return $this;
    }

    /**
     * @throws ConfigError
     * @throws ConnectionError
     */
    public function appendForEachMailingListMemberTo(Mailbox $mbox): Mail
    {
        if ($this->isSpamOrInvalid())
            return $this;

        $backup_to = $this->getTo();
        foreach ($this->assignedMailingLists as $list) {
            $this->setReplyTo($this->getFrom());
            $this->setFrom($list->address);
            foreach ($list->getEmailAddressesOfMembers() as $receiver) {
                $this->setTo([$receiver]);
                $mbox->queueMailForSending($this);
            }
        }
        $this->setTo($backup_to);
        return $this;
    }

    private function updateHeaders(): void
    {
        $this->setHeader("To", ezcMailTools::composeEmailAddresses($this->to));
        $this->setHeader("Cc", '');
        $this->setHeader("Bcc", '');
    }

    public function getToString(): string
    {
        return ezcMailTools::composeEmailAddresses($this->to);
    }

    /**
     * @throws ConnectionError
     */
    public function moveToFolderFinishSpamInvalid(Mailbox $finish, Mailbox $spam, Mailbox $invalid): Mail
    {
        if ($this->isSpam)
            return $this->moveTo($spam);
        elseif ($this->isInvalid)
            return $this->moveTo($invalid);
        else
            return $this->moveTo($finish);
    }

    /**
     * @throws ConnectionError
     */
    private function moveTo(Mailbox $mbox): Mail
    {
        if ($this->debug) return $this;
        $this->mailbox->moveMail($this, $mbox);
        return $this;
    }

    public function addPageNameToSubject(): Mail
    {
        if ($this->isSpamOrInvalid())
            return $this;

        if (Config::getAddPageName()) {
            $pageName = Config::get('site','concrete');
            $this->subject = '[' . $pageName . '] ' . $this->subject;
        }
        return $this;
    }

    public function addFooter(): Mail
    {
        if ($this->isSpamOrInvalid())
            return $this;

        $context = new ezcMailPartWalkContext(function (ezcMailPartWalkContext $context, $mailPart) {
            if ($mailPart instanceof ezcMailText) {
                if ($mailPart->subType == "html") {
                    $mailPart->text .= "<br/><br/>" . Config::getFooterHtml();
                }
                if ($mailPart->subType == "plain") {
                    $mailPart->text .= PHP_EOL . PHP_EOL . Config::getFooterPlain();
                }
            }
        });
        $this->walkParts($context, $this);
        return $this;
    }

    private function getTo(): array
    {
        return $this->to;
    }

    private function setTo($address): void
    {
        $this->to = $address;
    }

    private function setReplyTo(ezcMailAddress $address = null): void
    {
        $replyTo = '';
        if ($address != null) {
            $replyTo = $address->__toString();
        }
        $this->setHeader('Reply-To', $replyTo);
    }

    private function getFrom(): ezcMailAddress
    {
        return $this->from;
    }

    private function setFrom(ezcMailAddress $address): void
    {
        $this->from = $address;
    }

    private function isSpamOrInvalid(): bool
    {
        return $this->isSpam || $this->isInvalid;
    }
}