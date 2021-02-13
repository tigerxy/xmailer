<?php

namespace Xmailer\Config;

use Concrete\Core\Support\Facade\Config as C5Config;

abstract class AbstractConfig
{
    protected String $path = 'xmailer';
    //public function __construct() {}
    protected function getVal(String $name)
    {
        return C5Config::get($this->path . '.' . $name);
    }
    protected function setVal(String $name, Mixed $val)
    {
        return C5Config::save($this->path . '.' . $name, $val);
    }
}
