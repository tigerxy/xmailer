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
        $matches = $this->array_inner_join($lists->toArray(), $receivers, function (MailingList $list, ezcMailAddress $recv) {
            return $recv->email == $list->address->email;
        });
        $this->assignedMailingLists = new MailingLists($matches);
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
        // TODO: Ignore if user is whitelisted
        $this->assignedMailingLists = new MailingLists(array_filter($this->assignedMailingLists->toArray(), function (MailingList $list) {
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
    public function moveToFinishElseSpam(Mailbox $finish, Mailbox $spam): Mail
    {
        if ($this->assignedMailingLists->empty()) {
            return $this->moveTo($spam);
        } else {
            return $this->moveTo($finish);
        }
    }

    /**
     * @throws ConnectionError
     */
    public function moveTo(Mailbox $mbox): Mail
    {
        $this->mailbox->moveMail($this, $mbox);
        return $this;
    }

    public function addPageNameToSubject(): Mail
    {
        $config = new Config();
        if ($config->getAddPageName()) {
            $pageName = Config::getConfig()->get('concrete.site');
            $this->subject = '[' . $pageName . '] ' . $this->subject;
        }
        return $this;
    }

    public function addFooter(): Mail
    {
        $context = new ezcMailPartWalkContext(function (ezcMailPartWalkContext $context, ezcMailText $mailPart) {
            if ($mailPart->subType == "html") {
                $mailPart->text .= "<br/><br/>" . Config::getConfig()->get('xmailer.footer.html');
            }
            if ($mailPart->subType == "plain") {
                $mailPart->text .= PHP_EOL . PHP_EOL . Config::getConfig()->get('xmailer.footer.plain');
            }
        });
        $this->walkParts($context, $this);
        return $this;
    }

    public function debug(string $msg): Mail
    {
        echo '\n\n' . $msg;
        var_dump($this);
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
        $this->setHeader('reply-to', $replyTo);
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