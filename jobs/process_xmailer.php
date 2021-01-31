<?php
namespace Concrete\Package\Xmailer\Job;

use \Job as AbstractJob;
use \Concrete\Core\User\Group\Group as Group;
use \Concrete\Core\User\UserList as UserList;
use \Concrete\Core\User\UserInfo as UserInfo;
use Zend\Mail\AddressList;
use Zend\Mail\Message;
use Zend\Mail\Headers;
use Zend\Mail\Storage as Storage;
use \Concrete\Core\Mail\Service as MailService;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Mail\Storage\Imap as StorageImap;
use Loader;
use Config;

class ProcessXmailer extends AbstractJob {
    private const LISTNAME = '[CVJM-Forchheim]';

    public function getJobName() {
        return t( 'ProcessMailinglist' );
    }

    public function getJobDescription() {
        return t( 'Processing the mails for Mailinglist' );
    }

    public function run() {
        $debugMsg = '';
        $transport = MailService::getMailerObject()['transport'];

        $config = Config::get( 'xmailer.imap' );
        $mailbox = new StorageImap( array( 'host'     => $config['host'],
        'user'     => $config['user'],
        'password' => $config['password'] ) );

        $root = $mailbox->getCurrentFolder();
        $processFolder = $root.'.Process';
        // Step1: filtering and sorting mails in inbox
        $notValidFolder = $processFolder.'.NotValid';
        $spamFolder = $processFolder.'.Spam';
        $queryFolder = $processFolder.'.Querys';
        $noSpamFolder = $processFolder.'.NoSpam';
        // Step2: move ready to send mails to waiting folder
        $waitFolder = $processFolder.'.Waiting';
        // Step3: automated or manual moving waiting to read folder
        $readyFolder = $processFolder.'.ReadyToSend';
        // Step4: processing mails for sending and move to finish
        $finishFolder = $processFolder.'.Finish';

        $queueFolder = $root.'.Queue';
        $inboxFolder = $queueFolder.'.Inbox';
        $sentFolder = $queueFolder.'.Sent';

        $importantFolders = [$processFolder, $notValidFolder, $spamFolder, $noSpamFolder, $queryFolder, $waitFolder, $readyFolder, $finishFolder, $inboxFolder, $queueFolder, $sentFolder];

        // Check if folders exist otherwise create it
        foreach ( $importantFolders as $folder ) {
            try {
                $mailbox->getFolders( $folder );
            } catch( \Zend\Mail\Storage\Exception\InvalidArgumentException $e ) {
                $mailbox->createFolder( $folder );
            }
        }

        $message = null;

        // Process INBOX
        for ( $i = 0; $i < $mailbox->countMessages();
        $i++ ) {
            try {
                $message = $mailbox->getMessage( 1 );
                
                $h = $message->getHeaders();
                $from = $message->getHeader( 'from' )->getAddressList();
                $to = $message->getHeader( 'to' )->getAddressList();
                $subject = $message->getHeader( 'subject' );
                
                if ( !$this->isValidSender( $from->current() ) ) {
                    $mailbox->moveMessage( 1, $spamFolder );
                    $this->log( $debugMsg, $subject->getFieldValue(), $spamFolder );
                } elseif ( strtolower( $subject->getFieldValue() ) == 'who' ) {
                    $mailbox->moveMessage( 1, $queryFolder );
                    $this->log( $debugMsg, $subject->getFieldValue(), $queryFolder );
                } else {
                    $draftMail = new Message();
                    $draftMail->setTo( 'roland@stempelgreim.de' );
                    $draftMail->setSubject( $this->addListnameToSubject( 'New Mail is waiting '.$subject->getFieldValue() ) );
                    $draftMail->setFrom( 'notify@cvjm-forchheim.de' ); // $to->current() );
                    $body = 'Bitte gib mich frei QAQ!'.PHP_EOL.'My id is: '.$mailbox->getUniqueId( 1 );
                    $draftMail->setBody( $body );

                    $mailbox->appendMessage( $draftMail->toString(), $inboxFolder );

                    $mailbox->moveMessage( 1, $waitFolder );
                    $this->log( $debugMsg, $subject->getFieldValue(), $waitFolder );
                }
            } catch( \Zend\Mail\Exception\RuntimeException | \Zend\Mail\Exception\InvalidArgumentException $ex ) {
                $mailbox->moveMessage( 1, $notValidFolder );
                $this->log( $debugMsg, 'Not valid message: '.$ex->getMessage(), $notValidFolder );
            }
        }

        // Process $noSpamFolder
        $mailbox->selectFolder( $noSpamFolder );
        for ( $i = 0; $i < $mailbox->countMessages();
        $i++ ) {
            $message = $mailbox->getMessage( 1 );
            $subject = $message->getHeader( 'subject' );

            $mailbox->moveMessage( 1, $waitFolder );
            $this->log( $debugMsg, $subject->getFieldValue(), $waitFolder );
        }
        // Process $queryFolder
        $mailbox->selectFolder( $queryFolder );
        for ( $i = 0; $i < $mailbox->countMessages();
        $i++ ) {
            $message = $mailbox->getMessage( 1 );

            $from = $message->getHeader( 'from' )->getAddressList();
            $to = $message->getHeader( 'to' )->getAddressList();
            $subject = $message->getHeader( 'subject' );

            $sendTo = $this->getEmailAdresses( $to->current() );

            $draftMail = new Message();
            $draftMail->setTo( $from );
            $draftMail->setSubject( $this->addListnameToSubject( 'Members of list '.$to->current()->toString() ) );
            $draftMail->setFrom( $to->current() );
            $body = '';
            do {
                $body .= $sendTo->current()->toString().PHP_EOL;
            }
            while( $sendTo->next() );
            $draftMail->setBody( $body );

            $mailbox->appendMessage( $draftMail->toString(), $inboxFolder );
            $mailbox->moveMessage( 1, $finishFolder );
        }

        // Process $readyFolder
        $mailbox->selectFolder( $readyFolder );
        for ( $i = 0; $i < $mailbox->countMessages();
        $i++ ) {
            $message = $mailbox->getMessage( 1 );

            $h = $message->getHeaders();
            $from = $message->getHeader( 'from' )->getAddressList();
            $to = $message->getHeader( 'to' )->getAddressList();
            $subject = $message->getHeader( 'subject' )->getFieldValue();
            $subject = $this->addListnameToSubject( $subject );

            $addHeaders = new Headers();
            $additionalHeaders = ['MIME-Version', 'Content-Type', 'Content-Transfer-Encoding'];
            foreach ( $additionalHeaders as $headerKey ) {
                if ( $h->has( $headerKey ) )
                $addHeaders->addHeader( $h->get( $headerKey ) );
            }

            $draftMail = new Message();
            $draftMail->setBody( $message->getContent() );
            if ( Config::get( 'xmailer.replyto' ) ) {
                $draftMail->setReplyTo( $from );
            }
            $draftMail->setSubject($subject);

            $h = $draftMail->getHeaders();
            $h->addHeaders( $addHeaders );

            //Queue Mail in $inboxFolder Folder
            do {
                $sendTo = $this->getEmailAdresses( $to->current() );
                do {
                    $draftMail->setFrom( $to->current() );
                    $draftMail->setTo( $sendTo->current() );
                    $mailbox->appendMessage( $draftMail->toString(), $inboxFolder );
                }
                while ( $sendTo->next() );
            }
            while ( $to->next() );

            $mailbox->moveMessage( 1, $finishFolder );
            $this->log( $debugMsg, $subject->getFieldValue(), $finishFolder );
        }

        return $debugMsg;
    }

    private function isValidSender( $fromAdress ) {
        $grp = new UserList();
        $grp->filterByKeywords( $fromAdress->getEmail() );

        $pagination = $grp->getPagination();

        return $pagination->getTotalResults() > 0;
    }

    private function getEmailAdresses( $toAdress ) {
        $db = \Database::connection();
        $query = $db->GetRow( "SELECT * FROM xMailer WHERE email = '".$toAdress->getEmail()."';" );
        if ( $query['gID'] != 0 ) {
            $Adresses = $this->getEmailAdressesByGroupId( $query['gID'] );
        }
        return $Adresses;
    }

    private function getEmailAdressesByGroupId( $gId ) {
        $grp = Group::getByID( $gId );
        $user_list = new UserList();
        $user_list->includeInactiveUsers();
        $user_list->ignorePermissions();
        $user_list->filterByGroup( $grp );
        $user_list->filterByAttribute( 'mailingliste', true );
        $user_list->sortByUserName();
        $adresses = new AddressList();
        foreach ( $user_list->getResults() as $user ) {
            $adresses->add( $user->getUserEmail(), $this->formatName( $user->getUserName() ) );
        }
        return $adresses;
    }

    private function formatName( $name ) {
        return preg_replace('/(?<!^)[A-Z]/m', ' \0', $name);
    }

    private function log( &$msg, $name, $folder ) {
        $msg .= "'".$name."' moved to '".$folder."', ";
    }

    private function addListnameToSubject( $subject ) {
        return self::LISTNAME.' '.str_replace( self::LISTNAME.' ', '', $subject );
    }
}
