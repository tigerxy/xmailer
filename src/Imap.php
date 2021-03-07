<?php

namespace Xmailer;

use Xmailer\Config\Imap as ConfigImap;
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
        $this->imapConnect();
    }

    private function imapConnect()
    {
        $config = new ConfigImap();
        // Create a new IMAP transport with an SSL connection (default port is 993,
        // you can specify a different one using the second parameter of the constructor).
        $this->imap_conn_options = new ezcMailImapTransportOptions();
        //$imap_options->uidReferencing = false;
        $this->imap_conn_options->ssl = $config->useSSL();

        // Create a new IMAP transport object by specifying the server name
        parent::__construct(
            $config->getHost(),
            $config->getPort(),
            $this->imap_conn_options
        );

        // Authenticate to the IMAP server
        $this->authenticate(
            $config->getUser(),
            $config->getPass()
        );
    }

    /**
     * @return array<Mail>
     */

    public function getMails(Mailbox $mbox): array
    {
        $set = $this->fetchAll();
        $mails = $this->parseMailSet($set);
        $numbers = $set->getMessageNumbers();
        foreach (array_combine($numbers, $mails) as $number => $mail) {
            $mail->mailbox = $mbox;
            $mail->messageNr = $number;
        }
        return $mails;
    }

    /**
     * @return array<Mail>
     */

    private function parseMailSet(ezcMailParserSet $set): array
    {
        $options = new ezcMailParserOptions();
        $options->mailClass = Mail::class;
        $parser = new ezcMailParser($options);
        return $parser->parseMail($set);
    }

    public function moveMessage($messageNr, $destMailbox)
    {
        $this->copyMessages($messageNr, $destMailbox);
        $this->delete($messageNr);
        $this->expunge();
    }

    private function turnOnUidReferencing($on)
    {
        $this->imap_conn_options->uidReferencing = $on;
    }

    private function updateMailboxes()
    {
        $this->availableMailboxes = $this->listMailboxes();
    }

    public function createMailboxIfNotExist($mbox = null)
    {
        if (!$mbox) {
            $mbox = $this->selectedMailbox;
        }
        $this->updateMailboxes();
        // Check if mailboxes exist otherwise create it
        if ($mbox != "Inbox" && !in_array($mbox, $this->availableMailboxes)) {
            $this->createMailbox($mbox);
        }
    }

    private function isEmpty()
    {
        return !$this->isNotEmpty();
    }

    private function isNotEmpty()
    {
        // ignore Mails with DELETED tag
        return $this->count() > 0;
    }

    private function count()
    {
        return $this->countByFlag(Mail::UNDELETED);
    }
}