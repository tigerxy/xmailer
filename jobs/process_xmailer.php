<?php

namespace Concrete\Package\Xmailer\Job;

use Concrete\Core\Job\Job;
use Xmailer\Config\MailingLists;
use Xmailer\ConfigError;
use Xmailer\Imap;
use Xmailer\Imap\Mailbox;
use Xmailer\ConnectionError;
use Xmailer\ParserError;


class ProcessXmailer extends Job
{
    private const INBOX = 'INBOX';
    private const PROCESS = 'Process';
    private const FINISH = 'Finish';
    private const QUEUE = 'Queue';
    private const LIST = 'List';
    private const SPAM = 'Spam';

    public function getJobName(): string
    {
        return t('ProcessMailinglist');
    }

    public function getJobDescription(): string
    {
        return t('Processing the mails for Mailinglist');
    }

    /**
     * @throws ConfigError
     * @throws ConnectionError
     * @throws ParserError
     */
    public function run()
    {
        $lists = new MailingLists();
        $lists->readFromConfig();
        $conn = new Imap();

        $rootMailbox = new Mailbox($conn, self::INBOX);
        $processMailbox = new Mailbox($conn, self::PROCESS, $rootMailbox);
        $finishMailbox = new Mailbox($conn, self::FINISH, $processMailbox);
        $spamMailbox = new Mailbox($conn, self::SPAM, $processMailbox);
        $queueMailbox = new Mailbox($conn, self::QUEUE, $rootMailbox);
        $toListMailbox = new Mailbox($conn, self::LIST, $processMailbox);

        $mails = $rootMailbox->getMails();

        foreach ($mails as $mail) {
            $mail
                ->findReceivingMailingLists($lists)
                ->isSenderMemberOrWhitelisted()
                ->cleanHeaders()
                ->addPageNameToSubject()
                ->addFooter()
                ->appendForEachMailingListMemberTo($queueMailbox)
                ->moveToFinishElseSpam($finishMailbox, $spamMailbox);
        }
        $conn->expunge();

        foreach ($lists as $list) {
            $listMailbox = new Mailbox($conn, $list->mailbox, $toListMailbox);
            $mails = $listMailbox->getMails();
            foreach ($mails as $mail) {
                $mail
                    ->setReceivingMailingList($list)
                    ->cleanHeaders()
                    ->addPageNameToSubject()
                    ->addFooter()
                    ->appendForEachMailingListMemberTo($queueMailbox)
                    ->moveToFinishElseSpam($finishMailbox, $spamMailbox);
            }
            $conn->expunge();
        }

    }
}