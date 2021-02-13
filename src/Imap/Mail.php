<?php

namespace Xmailer\Imap;

use Xmailer\Config\Mailinglist;
use Xmailer\Config\Mailinglists;
use ezcMail;
use ezcMailAddress;

class Mail extends ezcMail
{
    const ALL = 'ALL';
    const ANSWERED = 'ANSWERED';
    const DELETED = 'DELETED';
    const DRAFT = 'DRAFT';
    const FLAGGED = 'FLAGGED';
    const NEW = 'NEW';
    const OLD = 'OLD';
    const RECENT = 'RECENT';
    const SEEN = 'SEEN';
    const UNANSWERED = 'UNANSWERED';
    const UNDELETED = 'UNDELETED';
    const UNDRAFT = 'UNDRAFT';
    const UNFLAGGED = 'UNFLAGGED';
    const UNRECENT = 'UNRECENT';
    const UNSEEN = 'UNSEEN';

    public string $folder = '';
    public int $messageNr = 0;
    public Mailinglists $assignedMailinglists;

    /**
     * @return array<ezcMailAddress>
     */
    public function getAllRecivers(): array
    {
        return array_merge($this->to, $this->cc, $this->bcc);
    }

    public function matchMailToMailinglists(Mailinglists $lists): void
    {
        $recvs = $this->getAllRecivers();
        $listsArray = iterator_to_array($lists);
        $matches = $this->array_inner_join($listsArray, $recvs, function (Mailinglist $list, ezcMailAddress $recv) {
            return $recv->email == $list->address->email;
        });
        $this->assignedMailinglists = new Mailinglists($matches);
    }

    private function array_inner_join(array $a, array $b, callable $func): array
    {
        $ret = array();
        foreach ($a as $ai) {
            foreach ($b as $bi) {
                if ($func($ai, $bi)) {
                    array_push($ret, $ai);
                }
            }
        }
        return $ret;
    }

    public function cleanHeaders(): void
    {
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();

        $this->to = array_map(function (Mailinglist $list) {
            return $list->address;
        }, iterator_to_array($this->assignedMailinglists));
    }
}