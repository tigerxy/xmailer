<?php

namespace Concrete\Package\Xmailer\Job;

use Concrete\Core\Job\Job;
use Xmailer\Config\Mailinglists;
use Xmailer\Config\Mailinglist;
use Xmailer\Imap;
use Xmailer\Imap\Mail;
use Xmailer\Imap\Mailbox;
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
        $lists = new Mailinglists();
        $lists->readFromConfig();
        $conn = new Imap();
        $rootMailbox = new Mailbox($conn, 'INBOX');
        $processMailbox = new Mailbox($conn, 'Process', $rootMailbox);
        $finishMailbox = new Mailbox($conn, 'Finish', $processMailbox);
        $queueMailbox = new Mailbox($conn, 'Queue', $rootMailbox);

        $mails = $rootMailbox->getMails();

        foreach ($mails as $mail) {
            $mail->findReceivingMailinglists($lists)
                ->isSenderMemberOfMailinglist()
                ->cleanHeaders()
                ->addPagenameToSubject()
                ->appendForEachMailinglistmemberTo($queueMailbox)
                ->moveTo($finishMailbox);
        }
    }
}