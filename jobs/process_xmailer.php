<?php
namespace Concrete\Package\Xmailer\Job;

use \Job as AbstractJob;
use \Concrete\Core\User\UserList as UserList;
use \Concrete\Core\User\UserInfo as UserInfo;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Mail\Storage\Imap as StorageImap;
use Loader;
use Config;

class ProcessXmailer extends AbstractJob
{

    public function getJobName()
    {
        return t("Mailinglist");
    }

    public function getJobDescription()
    {
        return t("Mailinglist");
    }

    public function run()
    {
        $config = Config::get('xmailer.imap');
        $mail = new StorageImap(array('host'     => $config['host'],
                                       'user'     => $config['user'],
                                       'password' => $config['password']));
        //$mail = new StorageImap(Config::get('xmailer.imap'));
                                        
        if ($mail->countMessages() > 0) {
            $message = $mail->getMessage(1);
            if ($message->getHeaders()->get("from") != null)
                $from = $message->getHeaders()->get("from")->toString();
            else
                $from = "";
            if ($message->getHeaders()->get("to") != null)
                $to = $message->getHeaders()->get("to")->toString();
            else
                $to = "";
            preg_match("/[\w._%+-]+@[\w.-]+.[\w]{2,}/", $from, $fromEmails);
            preg_match("/[\w._%+-]+@[\w.-]+.[\w]{2,}/", $to, $toEmails);
            
            $sendTo = $this->getEmailAdresses($toEmails);
            
            if ($this->isValidSender($fromEmails[0])) {
                foreach ($sendTo as $mailadress) {
                    $fwd = new Message();
                    $fwd->setBody($message->getContent());
                    $h = $message->getHeaders();
                    
                    $h->removeHeader('From');
                    $h->removeHeader('To');
                    $h->removeHeader('Reply-To');
                    
                    $fwd->setHeaders($h);
                    $fwd->addFrom($mailadress['from']);
                    $fwd->addTo($mailadress['to']);
                    if (Config::get('xmailer.replyto'))
                        $fwd->addReplyTo($fromEmails[0]);
                    
                    $transport = new SendmailTransport();
                    $transport->send($fwd);
                }
                $mail->moveMessage(1, "INBOX.Sent");
            } else {
                $mail->moveMessage(1, "INBOX.Spam");
            }
            return t("%d email sent to %d People.", 1, count($sendTo));
        }
        return t("No emails sent.");
    }
    
    private function isValidSender($fromAdress) {
        if (Config::get('xmailer.spam'))
            return $this->getIsEmailInSystem($fromAdress) != null;
        return true;
    }
    
    private function getIsEmailInSystem($email) {
        //array_push($Adresses, Config::get('xmailer.allow'));
        $grp = new UserList();
        $grp->filterByKeywords($email);
        return $grp->getTotalResults() > 0;
    }
    
    private function getEmailAdresses($toAdresses) {
        $Adresses = [];
		$db = \Database::connection();
		//$mailads = $db->GetAll("SELECT u.uEmail as email FROM xMailer x LEFT JOIN UserGroups ug ON x.gID = ug.gID LEFT JOIN Users u ON x.uID = u.uID OR ug.uID = u.uID WHERE x.email = '".$toAdresses."' GROUP BY u.uEmail");
        foreach($toAdresses as $toAdress) {
            $query = $db->GetRow("SELECT * FROM xMailer WHERE email = '$toAdress';");
            if($query['gID'] != 0) {
                foreach($this->getEmailAdressesByGroupId($query['gID']) as $adress)
                    array_push($Adresses, array(
                        "from" => $toAdress,
                        "to" => $adress
                    ));
            }
            if($query['uID'] != 0) {
                //array_push($Adresses, $this->getEmailAdressesByUserId($query['uID']));
            }
        }
        return $Adresses;
    }
    
    private function getEmailAdressesByGroupId($gId) {
        $db = \Database::connection();
        $grp = new UserList();
        $grp->filterByGroupID($gId);
        $list = $grp->getResultIDs();
        $mailads = array();
        foreach ($list as $uID) {
            array_push($mailads, $db->fetchColumn('SELECT uEmail FROM Users WHERE uID = ?', [$uID]));
        }
        return $mailads;
    }
    
    /*private function getEmailAdressesByUserId($uId) {
        //veraltet
        $info = new UserInfo();
        $user = $info->getByID($uId);
        return $user->uEmail;
    }*/
}