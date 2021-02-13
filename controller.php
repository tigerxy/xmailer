<?php
namespace Concrete\Package\Xmailer;

defined('C5_EXECUTE') or die(_("Access Denied."));

use \Concrete\Core\Package\Package;
use \Concrete\Core\Page\Single as SinglePage;
use \Concrete\Core\Job\Job as Job;
//use \Concrete\Core\Mail\Importer\MailImporter as MailImporter;
//use \Concrete\Core\Block\BlockType\BlockType;

class Controller extends Package
{

    protected $pkgHandle = 'xmailer';
    protected $appVersionRequired = '8.5.4';
    protected $pkgVersion = '2.2';
    protected $pkgAutoloaderRegistries = array(
        'src/' => '\Xmailer'
    );

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
        $this->setupAutoloader();
        $pkg = parent::install();
        //BlockType::installBlockTypeFromPackage('Mailer', $pkg);
        Job::installByPackage('process_xmailer', $pkg);
        Job::installByPackage('send_xmailer', $pkg);
        //MailImporter::add(array('miHandle' => 'xmailer'), $pkg);
        //SinglePage::add('/dashboard/xmailer', $pkg);
        SinglePage::add('/dashboard/xmailer/mailboxes', $pkg);
        SinglePage::add('/dashboard/xmailer/mailboxes/user', $pkg);
        SinglePage::add('/dashboard/xmailer/mailboxes/group', $pkg);
        SinglePage::add('/dashboard/xmailer/mailboxes/mailbox', $pkg);
        SinglePage::add('/dashboard/xmailer/settings', $pkg);
    }

    public function upgrade() {
        parent::upgrade();
        $pkg = Package::getByHandle('xmailer');
        Job::installByPackage('process_xmailer', $pkg);
        Job::installByPackage('send_xmailer', $pkg);
        //MailImporter::remove(array('miHandle' => 'xmailer'), $pkg);

    }

    // public function uninstall()
    // {
    //     parent::uninstall();
    //     $db = \Database::connection();
    //     $db->query('drop table xMailer');
    // }

    /**
     * Initialize the autoloader when the system boots up.
     */
    public function on_start()
    {
        $this->setupAutoloader();
    }

    /**
     * Configure the autoloader
     */
    private function setupAutoloader()
    {
        if (file_exists($this->getPackagePath() . '/vendor')) {
            require_once $this->getPackagePath() . '/vendor/autoload.php';
        }
    }
}
?>