<?php
namespace Concrete\Package\Xmailer\Controller\SinglePage\Dashboard\Xmailer\Mailboxes;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\User\UserList;
use Loader;

class User extends DashboardPageController {
	public function view($bID = null) {
		if($bID == null) {
			$u["bID"] = 0;
			$this->set('user', $u);
		} else {
			$this->set('user', userMailboxes::getUserById($bID));
		}
		$this->set('allUsers', userMailboxes::getAllUsers());
	}
	public function submit() {
		$vt = Loader::helper('validation/strings');
		$vn = Loader::Helper('validation/numbers');
		
		if ($this->post('bID') != "" && !$vn->integer($this->post('bID'))) {
			$this->error->add(t('Invalid ID'));
		}
		if (!$vn->integer($this->post('uID'))) {
			$this->error->add(t('No users selected.'));
		}
		if (!$vt->notempty($this->post('email'))) {
			$this->error->add(t('Invalid email address.'));
		}
		
		if (!$this->error->has()) {
			if($this->post('bID') == 0) {
				userMailboxes::add($this->post('uID'), $this->post('email'));
				$this->set('success', t('User added.'));
			} else {
				userMailboxes::update($this->post('bID'), $this->post('uID'), $this->post('email'));
				$this->set('success', t('User updated.'));
			}
		}
		
		$this->view($this->post('bID'));
	}
}

class userMailboxes {
	public static function getUsers() {
		$db = \Database::connection();
		return $db->GetAll("SELECT x.*, u.uName FROM xMailer x, Users u WHERE u.uID = x.uID AND x.uID > 0");
	}
	public static function getAllUsers() {
		$db = \Database::connection();
		return $db->GetAll("SELECT uID, uName FROM Users");
	}
	public static function getUserById($bID) {
		$db = \Database::connection();
		settype($bID, 'integer');
		return $db->GetRow("SELECT x.*, u.uName FROM xMailer x, Users u WHERE u.uID = x.uID AND x.bID = $bID");
	}
	public static function add($uID, $email) {
		$db = \Database::connection();
		$db->Query("INSERT INTO xMailer (uID, email) VALUES ('$uID', '$email')");
	}
	public static function update($bID, $uID, $email) {
		$db = \Database::connection();
		$db->Query("UPDATE xMailer SET uID=$uID, email='$email' WHERE bID = $bID");
	}
}