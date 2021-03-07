<?php

namespace Xmailer\Imap;

use Xmailer\Imap;
use Xmailer\Smtp;
use Xmailer\ImapMail;
use ezcMailImapTransport;
use ezcMailImapTransportOptions;
use ezcMailParser;
use ezcMailParserSet;
use ezcMailParserOptions;

class Mailbox
{
    const INBOX = 'Inbox';
    const SENT = 'Sent';
    const SPAM = 'Spam';

    private Imap $imap;
    private string $mailbox;
    private ?Mailbox $parentMailbox;

    public function __construct(Imap $imap, string $mailbox, Mailbox $parentMailbox = null)
    {
        $this->imap = $imap;
        $this->mailbox = $mailbox;
        $this->parentMailbox = $parentMailbox;
        $this->imap->createMailboxIfNotExist($this->getMailboxPath());
    }

    public function sendFirst($number)
    {
        $this->getInboxMailbox()->selectMailbox();
        $smtp = new Smtp();
        $unansweredMsgNrs = $this->imap->searchMailbox(Mail::UNFLAGGED)->getMessageNumbers();
        // Get maximal $number Message Ids
        $msgNrs = array_slice((array) $unansweredMsgNrs, 0, $number);
        foreach ($msgNrs as $msgNr) {
            $set = $this->imap->fetchByMessageNr($msgNr, false);
            // parse $set with ezcMailParser
            $parser = new ezcMailParser();
            $mails = $parser->parseMail($set);
            foreach ($mails as $mail) {
                // Use the SMTP transport to send the created mail object
                $smtp->send($mail);
            }
            $this->imap->setFlag($msgNr, Mail::FLAGGED);
        }

        $answeredMsgNrs = $this->imap->searchMailbox(Mail::FLAGGED)->getMessageNumbers();
        // Get maximal 10 Message Ids
        foreach ($answeredMsgNrs as $msgNr) {
            $this->imap->clearFlag($msgNr, Mail::FLAGGED);
            $this->imap->moveMessage($msgNr, $this->getSentMailbox()->getMailboxPath());
        }
        $this->imap->expunge();
    }

    public function getMails()
    {
        $this->selectMailbox();
        return $this->imap->getMails($this);
    }

    public function moveMail(Mail $mail, Mailbox $mailbox)
    {
        $this->selectMailbox();
        $this->imap->moveMessage($mail->messageNr, $mailbox->getMailboxPath());
    }

    private function selectMailbox(): void
    {
        $this->imap->selectMailbox($this->getMailboxPath());
        $this->imap->createMailboxIfNotExist();
    }

    private function getInboxMailbox(): Mailbox
    {
        return new Mailbox($this->imap, self::INBOX, $this);
    }

    private function getSpamMailbox(): Mailbox
    {
        return new Mailbox($this->imap, self::SPAM, $this);
    }

    private function getSentMailbox(): Mailbox
    {
        return new Mailbox($this->imap, self::SENT, $this);
    }

    private function getMailboxPath(): string
    {
        $parentPath = $this->parentMailbox ? $this->parentMailbox->getMailboxPath() . "/" : '';
        return $parentPath . $this->mailbox;
    }

    public function appendMail(Mail $mail)
    {
        $this->selectMailbox();
        $mbox = $this->getInboxMailbox()->getMailboxPath();
        $this->imap->append($mbox, $mail->generate());
    }
}