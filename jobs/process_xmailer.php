<?php

namespace Concrete\Package\Xmailer\Job;

use Concrete\Core\Job\Job;
use Xmailer\Imap;
use Xmailer\Imap\Mail;
use Xmailer\Imap\Folder;
use \ezcMail;

// FIXME: Replace these:
use Concrete\Core\Support\Facade\Config;

class ProcessXmailer extends Job
{
    private $listname = '';

    public function getJobName()
    {
        return t('ProcessMailinglist');
    }

    public function getJobDescription()
    {
        return t('Processing the mails for Mailinglist');
    }

    public function run()
    {
        echo '<pre>';
        $conn = new Imap();
        $rootFolder = new Folder($conn, 'Inbox');

        $mails = $rootFolder->getMails();
        //print_r($mails);

        foreach ($mails as $mail) {
            print_r($mail->getAllRecivers());
        }

        /*while ( $conn->next() ) {
            $mail = $conn->getMail();
            $from = $mail->from;
            $to_all = array_merge( $mail->to, $mail->cc, $mail->bcc );
            var_dump( $from, $to_all );
        }*/
        echo '</pre>';
        die();
    }

    private function formatSubject(Mail $mail): Mail
    {
        if (Config::get('xmailer.addpagename')) {
            $mail->subject = '[' . Config::get('concrete.site') . '] ' . $mail->subject;
        }
        return $mail;
    }
}
