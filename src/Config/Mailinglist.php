<?php
namespace Xmailer\Config;
use ezcMailAddress;

class Mailinglist {
    private Array $users;
    public String $name;
    public String $email;
    public Int $grpid;
    public function __construct($config)
    {
        $this->name = $config['name'];
        $this->email = $config['email'];
        $this->grpid = $config['grpid'];
    }
    public function getEmailAdress(): ezcMailAddress {
        return new ezcMailAddress($this->email,$this->name);
    }
    public function getGroupId(): Int {
        return $this->grpId;
    }
    public function getMemberEmailAdresses(): Array {
        return $this->users;
    }
    public function isEmailAdressOfThisList(ezcMailAddress $email): Bool {
        return $email->email == $this->email;
    }
    public function isMemberOfList(ezcMailAddress $email): Bool {
        foreach ($this->users as $user) {
            if ($user->email == $email->email) {
                return true;
            }
        }
        return false;
    }
}