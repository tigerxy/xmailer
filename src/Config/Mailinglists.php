<?php

namespace Xmailer\Config;

use ArrayIterator;
use Concrete\Core\Support\Facade\Config as C5Config;

class Mailinglists extends ArrayIterator
{
    public function readFromConfig(): void
    {
        if ($this->empty()) {
            $config = C5Config::get('xmailer.lists');
            foreach ($config as $list) {
                $this->append(new Mailinglist($list));
            }
        }
    }
    public function current(): Mailinglist
    {
        return parent::current();
    }
    public function offsetGet($offset): Mailinglist
    {
        return parent::offsetGet($offset);
    }
    // public function append(Mailinglist $value): void
    // {
    //     parent::append($value);
    // }
    public function empty(): bool
    {
        return $this->count() == 0;
    }
    public function first(): Mailinglist
    {
        return $this[0];
    }
}