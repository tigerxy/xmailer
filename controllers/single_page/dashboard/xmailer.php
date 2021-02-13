<?php

namespace Concrete\Package\Xmailer\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;

class Xmailer extends DashboardPageController
{
	public function view()
	{
		$this->redirect("dashboard/xmailer/mailboxes");
	}
}
