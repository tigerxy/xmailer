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
    private const USER_ATTRIBUTE_FOR_FILTER = "mailingliste";
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

        $user_list = new UserList();
        $user_list->ignorePermissions();
        $user_list->includeInactiveUsers();
        // TODO: Move attribute "mailingliste" to Config
        $attribute = self::USER_ATTRIBUTE_FOR_FILTER;
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

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->address->name,
            'email' => $this->address->email,
            'mailbox' => $this->mailbox,
            'grpId' => $this->groupId
        ];
    }
}