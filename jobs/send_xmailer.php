<?php
namespace Concrete\Package\Xmailer\Job;

use \Job as AbstractJob;
use \Concrete\Core\User\Group\Group as Group;
use \Concrete\Core\User\UserList as UserList;
use \Concrete\Core\User\UserInfo as UserInfo;
use Zend\Mail\AddressList;
use Zend\Mail\Message as Message;
use Zend\Mail\Headers as Headers;
use \Concrete\Core\Mail\Service as MailService;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Mail\Storage\Imap as StorageImap;
use Loader;
use Config;

class SendXmailer extends AbstractJob {
    public function getJobName() {
        return t( 'SendMailinglist' );
    }

    public function getJobDescription() {
        return t( 'Sending the mails to subscribers of Mailinglist' );
    }

    public function run() {
        $transport = MailService::getMailerObject()['transport'];

        $config = Config::get( 'xmailer.imap' );
        $confVars = array( 'host'     => $config['host'],
        'user'     => $config['user'],
        'password' => $config['password'] );
        $mail = new StorageImap( $confVars );
        $confVars = array( 'host'     => $config['host'],
        'port' => 465,
        'user'     => $config['user'],
        'password' => $config['password'] );
        $smtp = new SimpleRawSMTP( $confVars );
        $root = $mail->getCurrentFolder();

        $queueFolder = $root.'.Queue';
        $inboxFolder = $queueFolder.'.Inbox';
        $sentFolder = $queueFolder.'.Sent';
        $errorFolder = $queueFolder.'.Error';

        $importantFolders = [$inboxFolder, $queueFolder, $sentFolder];

        // Check if folders exist otherwise create it
        foreach ( $importantFolders as $folder ) {
            try {
                $mail->getFolders( $folder );
            } catch( \Zend\Mail\Storage\Exception\InvalidArgumentException $e ) {
                $mail->createFolder( $folder );
            }
        }

        $mail->selectFolder( $inboxFolder );
        $mailSent = 0;
        $mailNotSent = 0;
        for ( $i = 0; $i <= $mail->countMessages() && $i<10 && $mail->countMessages() > 0;
        $i++ ) {
            try {
                $message = $mail->getMessage( 1 );
                $smtp->sendMail( $message->getHeaderField("From"), $message->getHeaderField("To"), $mail->getRawHeader( 1 ).$mail->getRawContent( 1 ) );
                $mail->moveMessage( 1, $sentFolder );
                $mailSent++;
            } catch ( \Exception | \Zend\Mail\Exception\RuntimeException $ex ) {
                $mail->moveMessage( 1, $errorFolder );
                $mailNotSent++;
            }
        }

        if ($mailSent + $mailNotSent > 0) {
            return "Sent $mailSent with success and $mailNotSent with Error.";
        } else {
            return 0;
        }
    }
}
