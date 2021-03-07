<?php

namespace Xmailer\Config;

use ezcMailAddress;

class Mailinglist
{
    public ezcMailAddress $address;
    public String $mailbox;
    public Int $grpid;
    /**
     * @var array(ezcMailAddress)
     */
    private $members;
    public function __construct($config)
    {
        $this->address = new ezcMailAddress($config['email'], $config['name']);
        $this->mailbox = $config['mailbox'];
        $this->grpid = $config['grpId'];

        // TODO: Reading Members from Database with grpId
        $this->members = [
            new ezcMailAddress("roland1@fakemail.com", "Roland Greim"),
            new ezcMailAddress("roland2@fakemail.com", "Roland Greim"),
            new ezcMailAddress("roland3@fakemail.com", "Roland Greim"),
        ];
    }
    /**
     * @return array(ezcMailAddress)
     */
    public function getMemberEmailAdresses()
    {
        return $this->members;
    }
    public function isEmailAdressOfThisList(ezcMailAddress $email): Bool
    {
        return $email->email == $this->email;
    }
    public function isMemberOfList(ezcMailAddress $email): Bool
    {
        foreach ($this->members as $user) {
            if ($user->email == $email->email) {
                return true;
            }
        }
        return false;
    }
    public function jsonSerialize()
    {
        return [
            'name' => $this->address->name,
            'email' => $this->address->email,
            'mailbox' => $this->mailbox,
            'grpId' => $this->grpid
        ];
    }
}