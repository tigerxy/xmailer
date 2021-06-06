<?php

namespace Xmailer;

use Exception;
use ezcBaseFileNotFoundException;
use ezcBasePropertyNotFoundException;
use ezcBaseValueException;
use ezcMailTransportException;
use Xmailer\Config\ImapConfig;
use Xmailer\Imap\Mail;
use Xmailer\Imap\Mailbox;
use ezcMailImapTransport;
use ezcMailImapTransportOptions;
use ezcMailParser;
use ezcMailParserSet;
use ezcMailParserOptions;

class Imap extends ezcMailImapTransport
{
    private ezcMailImapTransportOptions $imap_conn_options;
    private array $availableMailboxes;

    public function __construct()
    {
        $this->imap_conn_options = new ezcMailImapTransportOptions();
        $this->imap_conn_options->ssl = ImapConfig::useSSL();
        $this->imap_conn_options->uidReferencing = true;

        parent::__construct(
            ImapConfig::getHost(),
            ImapConfig::getPort(),
            $this->imap_conn_options
        );

        $this->authenticate(
            ImapConfig::getUser(),
            ImapConfig::getPass()
        );
    }

    /**
     * @param Mailbox $mbox
     * @return Mail[]
     * @throws ConnectionError
     * @throws ParserError
     */
    public function getMails(Mailbox $mbox): array
    {
        try {
            $set = $this->fetchAll();
        } catch (ezcMailTransportException $e) {
            throw new ConnectionError("Could not fetch mails of {$mbox->getMailboxPath()} from server");
        }
        try {
            $mails = $this->parseMailSet($set);
        } catch (ParserError $e) {
            throw new ParserError("Could not parse mails of {$mbox->getMailboxPath()}");
        }
        $numbers = $set->getMessageNumbers();
        foreach (array_combine($numbers, $mails) as $number => $mail) {
            $mail->mailbox = $mbox;
            $mail->messageNr = $number;
        }
        return $mails;
    }

    /**
     * @param ezcMailParserSet $set
     * @return Mail[]
     * @throws ConnectionError
     * @throws ParserError
     */
    private function parseMailSet(ezcMailParserSet $set): array
    {
        $options = new ezcMailParserOptions();
        $options->mailClass = Mail::class;
        try {
            $parser = new ezcMailParser($options);
        } catch (ezcBasePropertyNotFoundException | ezcBaseValueException $e) {
            throw new ConnectionError("Could not create parser");
        }
        try {
            return $parser->parseMail($set);
        } catch (ezcBaseFileNotFoundException $e) {
            throw new ParserError("Could not parse mail");
        }
    }

    /**
     * @param $messageNr
     * @param $destMailbox
     * @throws ConnectionError
     */
    public function moveMessage($messageNr, $destMailbox)
    {
        try {
            $this->copyMessages($messageNr, $destMailbox);
        } catch (ezcMailTransportException $e) {
            throw new ConnectionError("Could not not copy message ($messageNr) to $destMailbox");
        }
        try {
            $this->delete($messageNr);
        } catch (ezcMailTransportException $e) {
            throw new ConnectionError("Could not flag message ($messageNr) as DELETED");
        }
    }

    private function turnOnUidReferencing($on)
    {
        $this->imap_conn_options->uidReferencing = $on;
    }

    private function updateMailboxes()
    {
        $this->availableMailboxes = $this->listMailboxes();
    }

    /**
     * @throws ConnectionError
     */
    public function createMailboxIfNotExist($mbox = null)
    {
        if (!$mbox) {
            $mbox = $this->selectedMailbox;
        }
        $this->updateMailboxes();
        // Check if mailboxes exist otherwise create it
        if ($mbox != "Inbox" && !in_array($mbox, $this->availableMailboxes)) {
            try {
                $this->createMailbox($mbox);
            } catch (ezcMailTransportException $e) {
                throw new ConnectionError("Could not create Mailbox $mbox");
            }
        }
    }

    /**
     * @throws ConnectionError
     */
    public function expunge()
    {
        try {
            parent::expunge();
        } catch (ezcMailTransportException $e) {
            throw new ConnectionError("Could not clean up and delete mails flagged as DELETED");
        }
    }
}