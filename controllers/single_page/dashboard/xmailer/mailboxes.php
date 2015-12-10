<?php
namespace Concrete\Package\Xmailer\Controller\SinglePage\Dashboard\Xmailer;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\User\UserList;
use Concrete\Core\User\Group\GroupList;

class Mailboxes extends DashboardPageController {
	public function view() {
        $this->set('users', $this->getUsers());
        $this->set('groups', $this->getGroups());
	}
	private function getUsers() {
		$db = \Database::connection();
		return $db->GetAll("SELECT x.*, u.uName FROM xMailer x, Users u WHERE u.uID = x.uID AND x.uID > 0 ORDER BY u.uName");
	}
	private function getGroups() {
		$db = \Database::connection();
		return $db->GetAll("SELECT x.*, g.gName FROM xMailer x, Groups g WHERE g.gID = x.gID AND x.gID > 0 ORDER BY g.gName");
	}
}