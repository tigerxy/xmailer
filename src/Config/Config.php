<?php

namespace Xmailer\Config;

class Config extends AbstractConfig
{
    public MailingLists $mailingLists;

    public function __construct()
    {
        $this->mailingLists = new MailingLists();
        $this->mailingLists->readFromConfig();
    }

    public static function getSpam(): bool
    {
        return self::get('spam');
    }

    public static function getReplyTo(): bool
    {
        return self::get('replyTo');
    }

    public static function getAllow(): array
    {
        return self::get('allow');
    }

    public static function getAddPageName(): bool
    {
        return self::get('addPageName');
    }

    public static function getAmountSendPerRun(): int
    {
        return 10;
    }

    public static function getUserAttribute()
    {
        return self::get('userAttribute');
    }

    public static function getFooter()
    {
        return self::get('footer');
    }

    public static function getFooterHtml()
    {
        return self::get('footer.html');
    }

    public static function getFooterPlain()
    {
        return self::get('footer.plain');
    }

    public static function getLists()
    {
        return self::get('lists');
    }

    public static function setSpam(bool $val)
    {
        self::set('spam', $val);
    }

    public static function setReplyTo(bool $val)
    {
        self::set('replyTo', $val);
    }

    public static function setAddPageName(bool $val)
    {
        self::set('addPageName', $val);
    }

    public static function setAllow(array $val)
    {
        self::set('allow', $val);
    }

    public static function setFooter(array $footer)
    {
        self::set('footer', $footer);
    }

    public static function setUserAttribute(string $attribute)
    {
        self::set('userAttribute', $attribute);
    }

    public static function setLists(array $lists)
    {
        self::set('lists', $lists);
    }

    public static function allSslOptions(): array
    {
        return [
            "imap" => ImapConfig::ssl_options,
            "smtp" => SmtpConfig::ssl_options,
        ];
    }
}