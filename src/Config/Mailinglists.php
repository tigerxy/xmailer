<?php
namespace Xmailer\Config;
use ArrayIterator;
use Config as C5Config;

class Mailinglists extends ArrayIterator
{
    public function __construct()
    {
        $config = C5Config::get( 'xmailer.lists' );
        $lists = array();
        foreach ($config as $list) {
            array_push($lists,new Mailinglist($list));
        }
        parent::__construct($lists);
    }
    public function current() : Mailinglist
    {
        return parent::current();
    }
    public function offsetGet($offset) : Mailinglist
    {
        return parent::offsetGet($offset);
    }
}