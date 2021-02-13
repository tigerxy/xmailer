<?php

namespace Concrete\Package\Xmailer\Controller\SinglePage\Dashboard\Xmailer\Mailboxes;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\User\Group\GroupList;

// FIXME: Replace these:
use Concrete\Core\Support\Facade\Database;
use Concrete\Core\Legacy\Loader;

class Group extends DashboardPageController
{
	public function view($bID = null)
	{
		if ($bID == null) {
			$g["bID"] = 0;
			$this->set('group', $g);
		} else {
			$this->set('group', groupMailboxes::getGroupById($bID));
		}
		$this->set('allGroups', groupMailboxes::getAllGroups());
	}
	public function submit()
	{
		$vt = Loader::helper('validation/strings');
		$vn = Loader::Helper('validation/numbers');

		if ($this->post('bID') != "" && !$vn->integer($this->post('bID'))) {
			$this->error->add(t('Invalid ID'));
		}
		if (!$vn->integer($this->post('gID'))) {
			$this->error->add(t('No users or groups selected.'));
		}
		if (!$vt->notempty($this->post('email'))) {
			$this->error->add(t('Invalid email address.'));
		}

		if (!$this->error->has()) {
			if ($this->post('bID') == 0) {
				groupMailboxes::add($this->post('gID'), $this->post('email'));
				$this->set('success', t('Group added successfully'));
			} else {
				groupMailboxes::update($this->post('bID'), $this->post('gID'), $this->post('email'));
				$this->set('success', t('Group update successfully'));
			}
		}

		$this->view($this->post('bID'));
	}
}

class groupMailboxes
{
	public static function getGroups()
	{
		$db = Database::connection();
		return $db->GetAll("SELECT x.*, g.gName FROM xMailer x, Groups g WHERE g.gID = x.gID AND x.gID > 0");
	}
	public static function getAllGroups()
	{
		$db = Database::connection();
		return $db->GetAll("SELECT gID, gName FROM Groups");
	}
	public static function getGroupById($bID)
	{
		$db = Database::connection();
		settype($bID, 'integer');
		return $db->GetRow("SELECT x.*, g.gName FROM xMailer x, Groups g WHERE g.gID = x.gID AND x.bID = $bID");
	}
	public static function add($gID, $email)
	{
		$db = Database::connection();
		$db->Query("INSERT INTO xMailer (gID, email) VALUES ('$gID', '$email')");
	}
	public static function update($bID, $gID, $email)
	{
		$db = Database::connection();
		$db->Query("UPDATE xMailer SET gID=$gID, email='$email' WHERE bID = $bID");
	}
}
