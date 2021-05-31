<?php

namespace Xmailer\Imap;

use ezcBaseFileNotFoundException;
use ezcMailNoSuchMessageException;
use ezcMailTransportException;
use Xmailer\Imap;
use Xmailer\ParserError;
use Xmailer\Smtp;
use ezcMailParser;
use Xmailer\ConnectionError;

class Mailbox
{
    const INBOX = 'Inbox';
    const SENT = 'Sent';

    private Imap $imap;
    private string $mailbox;
    private ?Mailbox $parentMailbox;

    /**
     * @throws ConnectionError
     */
    public function __construct(Imap $imap, string $mailbox, Mailbox $parentMailbox = null)
    {
        $this->imap = $imap;
        $this->mailbox = $mailbox;
        $this->parentMailbox = $parentMailbox;
        $this->imap->createMailboxIfNotExist($this->getMailboxPath());
    }

    /**
     * @param $number
     * @throws ConnectionError
     * @throws ParserError
     */
    public function sendFirst($number)
    {
        $this->getInboxMailbox()->selectMailbox();
        $smtp = new Smtp();
        try {
            $unansweredMsgNrs = $this->imap->searchMailbox(Mail::UNFLAGGED)->getMessageNumbers();
        } catch (ezcMailTransportException $e) {
            throw new ConnectionError("Could not search on server");
        }
        // Get maximal $number Message Ids
        $msgNrs = array_slice($unansweredMsgNrs, 0, $number);
        foreach ($msgNrs as $msgNr) {
            try {
                $set = $this->imap->fetchByMessageNr($msgNr, false);
            } catch (ezcMailNoSuchMessageException | ezcMailTransportException $e) {
                throw new ConnectionError("Could not fetch mail ($msgNr) on server");
            }
            // parse $set with ezcMailParser
            $parser = new ezcMailParser();
            try {
                $mails = $parser->parseMail($set);
            } catch (ezcBaseFileNotFoundException $e) {
                throw new ParserError("Could not parse mail");
            }
            foreach ($mails as $mail) {
                // Use the SMTP transport to send the created mail object
                $mail->appendExcludeHeaders([
                    'Return-Path',
                    'Content-Type',
                    'Content-Transfer-Encoding',
                ]);
                try {
                    $smtp->send($mail);
                } catch (ezcMailTransportException $e) {
                    throw new ConnectionError("Could not send mail");
                }
            }
            try {
                $this->imap->setFlag($msgNr, Mail::FLAGGED);
            } catch (ezcMailTransportException $e) {
                throw new ConnectionError("Could not set flag FLAGGED for mail");
            }
        }

        try {
            $answeredMsgNrs = $this->imap->searchMailbox(Mail::FLAGGED)->getMessageNumbers();
        } catch (ezcMailTransportException $e) {
            throw new ConnectionError("Could not search on server");
        }
        // Get maximal 10 Message Ids
        foreach ($answeredMsgNrs as $msgNr) {
            try {
                $this->imap->clearFlag($msgNr, Mail::FLAGGED);
            } catch (ezcMailTransportException $e) {
                throw new ConnectionError("Could not clear flag FLAGGED for mail");
            }
            $this->imap->moveMessage($msgNr, $this->getSentMailbox()->getMailboxPath());
        }
        $this->imap->expunge();
    }

    /**
     * @return Mail[]
     * @throws ConnectionError
     * @throws ParserError
     */
    public function getMails(): array
    {
        $this->selectMailbox();
        return $this->imap->getMails($this);
    }

    /**
     * @throws ConnectionError
     */
    public function moveMail(Mail $mail, Mailbox $mailbox)
    {
        $this->selectMailbox();
        $this->imap->moveMessage($mail->messageNr, $mailbox->getMailboxPath());
    }

    /**
     * @throws ConnectionError
     */
    private function selectMailbox(): void
    {
        $this->imap->selectMailbox($this->getMailboxPath());
        $this->imap->createMailboxIfNotExist();
    }

    /**
     * @throws ConnectionError
     */
    private function getInboxMailbox(): Mailbox
    {
        return new Mailbox($this->imap, self::INBOX, $this);
    }

    /**
     * @throws ConnectionError
     */
    private function getSentMailbox(): Mailbox
    {
        return new Mailbox($this->imap, self::SENT, $this);
    }

    public function getMailboxPath(): string
    {
        $parentPath = $this->parentMailbox ? $this->parentMailbox->getMailboxPath() . "/" : '';
        return $parentPath . $this->mailbox;
    }

    /**
     * @throws ConnectionError
     */
    public function queueMailForSending(Mail $mail)
    {
        $this->selectMailbox();
        $mbox = $this->getInboxMailbox()->getMailboxPath();
        try {
            $this->imap->append($mbox, $mail->generate());
        } catch (ezcMailTransportException $e) {
            throw new ConnectionError("Could not queue mail $mail->messageId");
        }
    }
}