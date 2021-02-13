<?php
namespace Concrete\Package\Xmailer\Job;

use Concrete\Core\Job\Job;
use Concrete\Core\Support\Facade\Config;
use Xmailer\Imap;
use Xmailer\Imap\Mail;
use Xmailer\Imap\Folder;
use \ezcMail;

class ProcessXmailer extends Job {
    private $listname = '';

    public function getJobName() {
        return t( 'ProcessMailinglist' );
    }

    public function getJobDescription() {
        return t( 'Processing the mails for Mailinglist' );
    }

    public function run() {
        $conn = new Imap();
        $rootFolder = new Folder($conn,'Inbox');
        
        $mails = $rootFolder->getMails();
        echo '<pre>';
        print_r($mails);
        echo '</pre>';

        /*while ( $conn->next() ) {
            $mail = $conn->getMail();
            $from = $mail->from;
            $to_all = array_merge( $mail->to, $mail->cc, $mail->bcc );
            var_dump( $from, $to_all );
        }*/
        die();
    }

    /**
    * @return array<ezcMailAddress>
    */

    private function getAllRecivers( Mail $mail ): array {
        return array_merge( $mail->to, $mail->cc, $mail->bcc );
    }

    private function formatSubject( Mail $mail ): Mail {
        if ( Config::get( 'xmailer.addpagename' ) ) {
            $mail->setSubject( '['.Config::get( 'concrete.site' ).'] '.$mail->getSubject() );
        }
        return $mail;
    }

}