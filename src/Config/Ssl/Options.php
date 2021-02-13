<?php

namespace Xmailer\Config\Ssl;

use Xmailer\Config\AbstractServer as AbstractServer;
use IteratorIterator;
use ArrayIterator;

class Options extends IteratorIterator
{
    public function __construct(Option ...$options)
    {
        parent::__construct(new ArrayIterator($options));
    }
    public function current(): Option
    {
        return parent::current();
    }
    public function selected(): Option
    {
        foreach ($this as $option) {
            if ($option->isSelected()) {
                return $option;
            }
        }
        return iterator_to_array(parent::getInnerIterator())[0];
    }
    public function toArray(): array
    {
        return iterator_to_array(parent::getInnerIterator());
    }
}
