<?php

namespace Xmailer\Config;

class ImapConfig extends AbstractConfig
{
    const sub_path = "imap.";
    public const ssl_options = [
        [
            "id" => "tcp",
            "description" => "Plain",
            "port" => 143
        ], [
            "id" => "ssl",
            "description" => "SSL/TLS",
            "port" => 993
        ]
    ];

    public static function useSSL(): bool
    {
        return self::get(self::sub_path . 'ssl') == 'ssl';
    }

    public static function getHost(): string
    {
        return self::get(self::sub_path . 'imap.host');
    }

    public static function getUser(): string
    {
        return self::get(self::sub_path . 'user');
    }

    public static function getPass(): string
    {
        return self::get(self::sub_path . 'password');
    }

    public static function getPort(): int
    {
        return self::get(self::sub_path . 'port');
    }

    public static function getSSL(): string
    {
        return self::get(self::sub_path . 'ssl');
    }

    public static function setHost(string $val)
    {
        self::set(self::sub_path . 'host', $val);
    }

    public static function setUser(string $val)
    {
        self::set(self::sub_path . 'user', $val);
    }

    public static function setPass(string $val)
    {
        if ($val != '') {
            self::set(self::sub_path . 'password', $val);
        }
    }

    public static function setPort(int $val)
    {
        self::set(self::sub_path . 'port', $val);
    }

    public static function setSSL(string $val)
    {
        self::set(self::sub_path . 'ssl', $val);
    }
}
