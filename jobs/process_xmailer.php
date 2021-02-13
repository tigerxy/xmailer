<?php

namespace Concrete\Package\Xmailer\Job;

use Concrete\Core\Job\Job;
use Xmailer\Config\Mailinglists;
use Xmailer\Config\Mailinglist;
use Xmailer\Imap;
use Xmailer\Imap\Mail;
use Xmailer\Imap\Folder;
use \ezcMail;
use \ezcMailAddress;

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
        $lists = new Mailinglists();
        $lists->readFromConfig();
        $conn = new Imap();
        $rootFolder = new Folder($conn, 'Inbox');

        $mails = $rootFolder->getMails();
        //print_r($mails);


        print_r(iterator_to_array($lists));
        foreach ($mails as $mail) {
            $mail->matchMailToMailinglists($lists);
            print_r($mail);
            $mail->cleanHeaders();
            print_r($mail);
        }

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