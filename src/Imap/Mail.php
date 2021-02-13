<?php
namespace Xmailer\Imap;
use ezcMail;

class Mail extends ezcMail {
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
}