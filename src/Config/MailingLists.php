<?php

namespace Xmailer\Config;

use ArrayIterator;

class MailingLists extends ArrayIterator
{
    public function readFromConfig(): void
    {
        if ($this->empty()) {
            foreach (Config::getLists() as $list) {
                $this->append(new MailingList($list));
            }
        }
    }
    public function current(): MailingList
    {
        return parent::current();
    }
    public function offsetGet($offset): MailingList
    {
        return parent::offsetGet($offset);
    }
    public function empty(): bool
    {
        return $this->count() == 0;
    }
    public function first(): MailingList
    {
        return $this[0];
    }
    public function toArray(): array
    {
        return iterator_to_array($this);
    }
}