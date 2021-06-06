<?php

namespace Xmailer\Config;

use Concrete\Core\Support\Facade\Application;

abstract class AbstractConfig
{
    const path = 'xmailer';

    public static function getConfig()
    {
        $app = Application::getFacadeApplication();
        return $app->make('config');
    }

    public static function get(string $name, string $path = self::path)
    {
        return self::getConfig()->get($path . "." . $name);
    }

    public static function getAll()
    {
        return self::getConfig()->get(self::path);
    }

    public static function set(string $name, $val, string $path = self::path)
    {
        self::getConfig()->save($path . "." . $name, $val);
    }
}