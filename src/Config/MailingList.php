<?php

namespace Xmailer\Config;

use Concrete\Core\User\Group\Group;
use Concrete\Core\User\UserInfo;
use Concrete\Core\User\UserList;
use Exception;
use ezcMailAddress;
use Xmailer\ConfigError;

class MailingList
{
    public ezcMailAddress $address;
    public string $mailbox;
    public int $groupId;

    public function __construct($config)
    {
        $this->address = new ezcMailAddress($config['email'], $config['name']);
        $this->mailbox = $config['mailbox'];
        $this->groupId = $config['grpId'];
    }

    /**
     * @return ezcMailAddress[]
     * @throws ConfigError
     */
    public function getEmailAddressesOfMembers(): array
    {
        $group = Group::getByID($this->groupId);

        if (empty($group))
            throw new ConfigError("Cannot find groupId($this->groupId)");

        $user_list = new UserList();
        $user_list->ignorePermissions();
        $user_list->includeInactiveUsers();
        $attribute = (new Config())->getUserAttribute();
        try {
            $user_list->filterByAttribute($attribute, true);
        } catch (Exception $e) {
            throw new ConfigError("User attribute $attribute in configuration is not valid");
        }
        $user_list->filterByGroup($group);

        return array_map(function (UserInfo $member): ezcMailAddress {
            return new ezcMailAddress($member->getUserEmail(), $member->getUserName());
        }, $user_list->getResults());
    }

    public function isReceiver(ezcMailAddress $email) : bool
    {
        return $this->address->email == $email->email;
    }

    /**
     * @throws ConfigError
     */
    public function isMemberOfList(ezcMailAddress $email): bool
    {
        foreach ($this->getEmailAddressesOfMembers() as $member) {
            if ($member->email == $email->email) {
                return true;
            }
        }
        return false;
    }
}