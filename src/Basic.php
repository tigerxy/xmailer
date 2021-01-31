<?php
use Concrete\Core\User\UserList;
use Concrete\Core\User\UserInfo;
use Concrete\Core\User\Group\Group;
use Concrete\Core\User\Group\GroupList;
namespace Concrete\Package\Xmailer\Src;

class Basic
{
	public function getUsers() {
		$db = Loader::db();
		return $db->GetAll("SELECT x.*, u.uName FROM xMailer x, Users u WHERE u.uID = x.uID AND x.uID > 0 ORDER BY u.uName");
	}
	public function getGroups() {
		$db = Loader::db();
		return $db->GetAll("SELECT x.*, g.gName FROM xMailer x, Groups g WHERE g.gID = x.gID AND x.gID > 0 ORDER BY g.gName");
	}
    
    public function isValidSender($fromAdress) {
        return $this->getIsEmailInSystem($fromAdress) != null;
    }
    
    public function getIsEmailInSystem($email) {
        $info = new UserInfo();
        $user = $info->getByEmail($email);
        return $user != null;
    }
    
    public function getEmailAdresses($toAdresses) {
        $Adresses = [];
		$db = Loader::db();
		//$mailads = $db->GetAll("SELECT u.uEmail as email FROM xMailer x LEFT JOIN UserGroups ug ON x.gID = ug.gID LEFT JOIN Users u ON x.uID = u.uID OR ug.uID = u.uID WHERE x.email = '".$toAdresses."' GROUP BY u.uEmail");
        foreach($toAdresses as $toAdress) {
            $query = $db->GetRow("SELECT * FROM xMailer WHERE email = '$toAdress';");
            if($query['gID'] != 0) {
                array_push($Adresses, $this->getEmailAdressesByGroupId($query['gID']));
            }
            if($query['uID'] != 0) {
                array_push($Adresses, $this->getEmailAdressByUserId($query['uID']));
            }
        }
        return $Adresses;
    }
    
    private function getEmailAdressesByGroupId($gId) {
        $grp = new UserList();
        $grp->filterByGroupID($gId);
        $list = $grp->getResultIDs();
        $mailads = [];
        foreach ($list as $uId) {
            array_push($mailads, $this->getEmailAdressByUserId($uId));
        }
        return $mailads;
    }
    
    private function getEmailAdressByUserId($uId) {
        $info = new UserInfo();
        $user = $info->getByID($uId);
        return $user->uEmail;
    }
}

class UserMailboxes {
    function __construct($bID) {
        $this->bID = $bID;
		$db = Loader::db();
        $query = $db->GetRow("SELECT * FROM xMailer WHERE bID = $bID;");
        $this->uID = $query['uID'];
        $this->email = $query['email'];
    }
    function getMailboxAdress() {
        return $this->email;
    }
    function getUserEmail() {
        $info = new UserInfo();
        $user = $info->getByID($this->uID);
        return $user->uEmail;
    }
    function getUserName() {
        $info = new UserInfo();
        $user = $info->getByID($this->uID);
        return $user->uName;
    }
}

class GroupMailboxes {
    function __construct($bID) {
        $this->bID = $bID;
		$db = Loader::db();
        $query = $db->GetRow("SELECT * FROM xMailer WHERE bID = $bID;");
        $this->gID = $query['gID'];
        $this->email = $query['email'];
    }
    function getGroupName() {
        $info = new Group();
        $grp = $info->getByID($this->gID);
        return $grp->uName;
    }
    
}
?>