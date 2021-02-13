<?php

namespace Concrete\Package\Xmailer\Controller\SinglePage\Dashboard\Xmailer;

use Concrete\Core\Page\Controller\DashboardPageController;
use Xmailer\Config;

// FIXME: Replace these:
use Concrete\Core\Legacy\Loader;
use Concrete\Core\Support\Facade\Application as Core;

class Settings extends DashboardPageController
{
	/*private $ssl_options = array( 
		"imap" => array(
			"tcp" => array("desc" => "Plain", "port" => 143),
			"ssl" => array("desc" => "SSL/TLS", "port" => 993)
		),
		"smtp" => array(
			"tcp" => array("desc" => "Plain", "port" => 25),
			"ssl" => array("desc" => "SSL", "port" => 465),
			"sslv2" => array("desc" => "SSLv2", "port" => 465),
			"sslv3" => array("desc" => "SSLv3", "port" => 465),
			"tls" => array("desc" => "TLS", "port" => 465)
		)
	);*/
	public function view()
	{
		$config = new Config();
		/*$this->set('config', Config::get('xmailer'));
		$this->set('imap', Config::get('xmailer.imap'));
		$this->set('smtp', Config::get('xmailer.smtp'));
		$this->set('ssl_options', $this->ssl_options);*/
		$this->set('config', $config);
		// $this->set('imap', $config->imap);
		// $this->set('smtp', $config->smtp);
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

			$config->setSpam($this->post('spam') == 1);
			$config->setReplyTo($this->post('replyto') == 1);
			$config->setAllow(preg_split("/[\s,;]+/", $this->post('allow')));
			$config->setAddPageName($this->post('addpagename') == 1);

			$this->set('success', t('Settings Saved.'));
		}
		$this->view();
	}
}
