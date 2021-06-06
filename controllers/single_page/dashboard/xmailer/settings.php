<?php

namespace Concrete\Package\Xmailer\Controller\SinglePage\Dashboard\Xmailer;

use Concrete\Core\Attribute\Category\UserCategory;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\User\Group\Group;
use Concrete\Core\User\Group\GroupList;
use Xmailer\Config\Config;

// FIXME: Replace these:
use Concrete\Core\Legacy\Loader;
use Concrete\Core\Support\Facade\Application as Core;

class Settings extends DashboardPageController
{
    public function view()
    {

        $this->requireAsset('javascript', 'xmailer-settings-form');

        $groupList = new GroupList();
        $groups = array_map(function (Group $group) {
            return array(
                "id" => intval($group->getGroupID()),
                "name" => $group->getGroupName(),
            );
        }, $groupList->getResults());

        //$us = new UserCategory();
        //$userAttributes = $us->getAttributeKeyRepository()->findAll();

        $this->set('settings', Config::getAll());
        $this->set('sslOptions', Config::allSslOptions());
        $this->set('userAttributes', ['a', 'mailingliste', 'c']);
        $this->set('groups', $groups);
    }

    public function submit()
    {
        $encryptor = Core::make("helper/encryption");
        $vt = Loader::Helper('validation/strings');
        $vn = Loader::Helper('validation/numbers');
        $config = new Config();

        if (!$vt->notempty($this->post('imap_host'))) {
            $this->error->add(t('IMAP Host is not ok.'));
        }
        if (!$vt->notempty($this->post('imap_user'))) {
            $this->error->add(t('IMAP User is not ok.'));
        }
        if ($this->post('imap_port') != "" && !$vn->integer($this->post('imap_port'))) {
            $this->error->add(t('IMAP Port is not ok.'));
        }
        if (!$vt->notempty($this->post('smtp_host'))) {
            $this->error->add(t('SMTP Host is not ok.'));
        }
        if (!$vt->notempty($this->post('smtp_user'))) {
            $this->error->add(t('SMTP User is not ok.'));
        }
        if ($this->post('smtp_port') != "" && !$vn->integer($this->post('smtp_port'))) {
            $this->error->add(t('SMTP Port is not ok.'));
        }
        if (!$this->error->has()) {
            foreach (array('imap' => $config->imap, 'smtp' => $config->smtp) as $name => $conf) {
                $conf->setHost($this->post($name . '_host'));
                $conf->setUser($this->post($name . '_user'));
                if ($vt->notempty($this->post($name . '_password'))) {
                    $conf->setPass($encryptor->encrypt($this->post($name . '_password')));
                }
                $conf->setSSL($this->post($name . '_ssl'));
                if ($vt->notempty($this->post($name . '_port'))) {
                    $conf->setPort($this->post($name . '_port'));
                } else {
                    $conf->setPort(0);
                }
            }

            $lists = $this->post('lists');
            if (empty($lists)) $lists = [];
            $config->setLists($lists);

            $allow = $this->post('allow');
            if (empty($allow)) $allow = [];
            $config->setAllow($allow);

            $config->setSpam((bool) $this->post('spam'));
            $config->setReplyTo((bool) $this->post('replyTo'));
            $config->setAddPageName((bool) $this->post('addPageName'));

            $config->setFooter($this->post('footer'));
            $config->setUserAttribute($this->post('userAttribute'));

            $this->set('success', t('Settings Saved.'));
        }
        $this->view();
    }
}