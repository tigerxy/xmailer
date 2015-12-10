<?php
namespace Concrete\Package\Xmailer;

defined('C5_EXECUTE') or die(_("Access Denied."));

use \Concrete\Core\Package\Package;
use \Concrete\Core\Page\Single as SinglePage;
use \Concrete\Core\Job\Job as Job;
//use \Concrete\Core\Block\BlockType\BlockType;

class Controller extends Package
{

    protected $pkgHandle = 'xmailer';
    protected $appVersionRequired = '5.7.4.2';
    protected $pkgVersion = '1.0';

    public function getPackageDescription()
    {
        return t('xMailer to send emails to usergroupes and users.');
    }

    public function getPackageName()
    {
        return t('xMailer');
    }

    public function install()
    {
        $pkg = parent::install();
        //BlockType::installBlockTypeFromPackage('Mailer', $pkg);
        Job::installByPackage('process_xmailer', $pkg);
        SinglePage::add('/dashboard/xmailer', $pkg);
        SinglePage::add('/dashboard/xmailer/mailboxes', $pkg);
        SinglePage::add('/dashboard/xmailer/mailboxes/user', $pkg);
        SinglePage::add('/dashboard/xmailer/mailboxes/group', $pkg);
        SinglePage::add('/dashboard/xmailer/settings', $pkg);
    }
    // public function uninstall()
    // {
    //     parent::uninstall();
    //     $db = \Database::connection();
    //     $db->query('drop table xMailer');
    // }
}
?>