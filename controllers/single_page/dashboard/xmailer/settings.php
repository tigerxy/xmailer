<?php
namespace Concrete\Package\Xmailer\Controller\SinglePage\Dashboard\Xmailer;
use Concrete\Core\Page\Controller\DashboardPageController;
use Config;
use Loader;

class Settings extends DashboardPageController {
	public function view() {
		$this->set('config', Config::get('xmailer'));
		$this->set('imap', Config::get('xmailer.imap'));
	}
	public function submit() {
		$vt = Loader::Helper('validation/strings');
		$vn = Loader::Helper('validation/numbers');
		
		if (!$vt->notempty($this->post('host'))) {
			$this->error->add(t('IMAP Host is not ok.'));
		}
		if (!$vt->notempty($this->post('user'))) {
			$this->error->add(t('IMAP User is not ok.'));
		}
		if ($this->post('port') != "" && !$vn->integer($this->post('port'))) {
			$this->error->add(t('IMAP Port is not ok.'));
		}
		if (!$this->error->has()) {
			Config::save('xmailer.imap.host', $this->post('host'));
			Config::save('xmailer.imap.user', $this->post('user'));
			if ($vt->notempty($this->post('password'))) {
				Config::save('xmailer.imap.password', $this->post('password'));
			}
			Config::save('xmailer.imap.ssl', $this->post('ssl'));
			Config::save('xmailer.imap.port', $this->post('port'));
			
			Config::save('xmailer.spam', ($this->post('spam') == 1));
			Config::save('xmailer.replyto', ($this->post('replyto') == 1));
			Config::save('xmailer.allow', preg_split("/[\s,;]+/", $this->post('allow')));
			
			$this->set('success', t('Settings Saved.'));
		}
		$this->view();
	}
}