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

class Folder {
    const INBOX = 'Inbox';
    const SENT = 'Sent';
    const SPAM = 'Spam';

    private Imap $imap;
    private string $folder;
    private ?Folder $parentFolder;

    public function __construct( Imap $imap, string $folder, Folder $parentFolder = null ) {
        $this->imap = $imap;
        $this->folder = $folder;
        $this->parentFolder = $parentFolder;
    }

    public function sendFirst($number) {
        $smtp = new Smtp();
        $unansweredMsgNrs = $this->imap->searchMailbox( Mail::UNFLAGGED )->getMessageNumbers();
        // Get maximal $number Message Ids
        $msgNrs = array_slice($unansweredMsgNrs,0,$number);
        foreach ($msgNrs as $msgNr) {
            $set = $this->imap->fetchByMessageNr($msgNr, false);
            // parse $set with ezcMailParser
            $parser = new ezcMailParser();
            $mails = $parser->parseMail($set);
            foreach( $mails as $mail)
            {
                // Use the SMTP transport to send the created mail object
                $smtp->send( $mail );
            }
            $this->imap->setFlag($msgNr,Mail::FLAGGED);
        }

        $answeredMsgNrs = $this->imap->searchMailbox( Mail::FLAGGED )->getMessageNumbers();
        // Get maximal 10 Message Ids
        foreach ($answeredMsgNrs as $msgNr) {
            $this->imap->clearFlag($msgNr,Mail::FLAGGED);
            $this->imap->moveMessage($msgNr,$this->getSentFolder()->getFolderPath());
        }
        $this->imap->expunge();
    }

    public function getMails() {
        $this->selectFolder();
        return $this->imap->getMails();
    }

    private function moveMail(Mail $mail, Folder $folder) {
        $this->imap->moveMessage($mail->messageNr,$folder->getFolderPath());
    }

    private function selectFolder(): void {
        $this->imap->selectMailbox( $this->getCurrentFolder() );
        $this->imap->createMailboxIfNotExist();
    }

    private function getCurrentFolder(): string {
        return $this->getFolderPath().$this->folder;
    }

    private function getInboxFolder(): Folder {
        return new Folder($this->imap,self::INBOX,$this);
    }

    private function getSpamFolder(): Folder {
        return new Folder($this->imap,self::SPAM,$this);
    }

    private function getSentFolder(): Folder {
        return new Folder($this->imap,self::SENT,$this);
    }

    private function getFolderPath(): string {
        if ($this->parentFolder != null) {
            return $this->parentFolder->getFolderPath().'\\';
        } else {
            return '';
        }
    }

}