<?php
namespace Concrete\Package\Xmailer\Controller\SinglePage\Dashboard\Xmailer;
use Concrete\Core\Page\Controller\DashboardPageController;
use Config;
use Loader;

class Settings extends DashboardPageController {
	private $ssl_options = array( 
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
	);
	public function view() {
		$this->set('config', Config::get('xmailer'));
		$this->set('imap', Config::get('xmailer.imap'));
		$this->set('smtp', Config::get('xmailer.smtp'));
		$this->set('ssl_options', $this->ssl_options);
	}
	public function submit() {
		$vt = Loader::Helper('validation/strings');
		$vn = Loader::Helper('validation/numbers');

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
			Config::save('xmailer.imap.host', $this->post('imap_host'));
			Config::save('xmailer.imap.user', $this->post('imap_user'));
			if ($vt->notempty($this->post('imap_password'))) {
				Config::save('xmailer.imap.password', $this->post('imap_password'));
			}
			Config::save('xmailer.imap.ssl', $this->post('imap_ssl'));
			Config::save('xmailer.imap.port', $this->post('imap_port'));

			Config::save('xmailer.smtp.host', $this->post('smtp_host'));
			Config::save('xmailer.smtp.user', $this->post('smtp_user'));
			if ($vt->notempty($this->post('smtp_password'))) {
				Config::save('xmailer.smtp.password', $this->post('smtp_password'));
			}
			Config::save('xmailer.smtp.ssl', $this->post('smtp_ssl'));
			Config::save('xmailer.smtp.port', $this->post('smtp_port'));
			
			Config::save('xmailer.spam', ($this->post('spam') == 1));
			Config::save('xmailer.replyto', ($this->post('replyto') == 1));
			Config::save('xmailer.allow', preg_split("/[\s,;]+/", $this->post('allow')));
			
			$this->set('success', t('Settings Saved.'));
		}
		$this->view();
	}
}