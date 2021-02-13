<?php
namespace Concrete\Package\Xmailer\Job;

use Concrete\Core\Job\Job;
use Xmailer\Imap;
use Xmailer\Config;

class SendXmailer extends Job {
    public function getJobName() {
        return t( 'SendMailinglist' );
    }

    public function getJobDescription() {
        return t( 'Sending the mails to subscribers of Mailinglist' );
    }

    public function run() {
        $config = new Config();

        $conn = new Imap('Queue');
        $conn->sendFirst($config->getAmountSendPerRun());
    }
}