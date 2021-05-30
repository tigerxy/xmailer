<?php

namespace Concrete\Package\Xmailer\Job;

use Concrete\Core\Job\Job;
use Xmailer\Imap;
use Xmailer\Imap\Mailbox;
use Xmailer\Config\Config;
use Xmailer\ConnectionError;
use Xmailer\ParserError;

class SendXmailer extends Job
{
    public function getJobName(): string
    {
        return t('SendMailinglist');
    }

    public function getJobDescription(): string
    {
        return t('Sending the mails to subscribers of Mailinglist');
    }

    /**
     * @throws ConnectionError
     * @throws ParserError
     */
    public function run()
    {
        $config = new Config();

        $conn = new Imap();
        $rootMailbox = new Mailbox($conn, 'INBOX');
        $queueMailbox = new Mailbox($conn, 'Queue', $rootMailbox);
        $queueMailbox->sendFirst($config->getAmountSendPerRun());
    }
}
