<?php

namespace Xmailer\Config;

use Concrete\Core\Support\Facade\Application;

abstract class AbstractConfig
{
    protected String $path = 'xmailer';
    //public function __construct() {}
    public static function getConfig() {
        $app = Application::getFacadeApplication();
        return $app->make('config');
    }

    protected function getVal(String $name)
    {
        $config = self::getConfig();
        return $config->get($this->path . '.' . $name);
    }
    protected function setVal(String $name, $val)
    {
        $config = self::getConfig();
        $config->save($this->path . '.' . $name, $val);
    }
}