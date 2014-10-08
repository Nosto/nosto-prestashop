<?php

/**
 * @property NostoTagging $module
 */
class NostoTaggingOauth2ModuleFrontController extends ModuleFrontController
{
	/**
	 * @inheritdoc
	 */
	public function initContent()
	{
		if (($code = Tools::getValue('code')) !== false)
		{
			$client = new NostoTaggingOAuth2Client();
			$client->setClientId($this->module->getAccountName());
			$client->setClientSecret($this->module->getClientSecret());
			$client->setRedirectUrl($this->module->getOAuth2ControllerUrl());
			if (($token = $client->authenticate($code)) !== false)
				if($this->module->exchangeDataWithNosto($token))
				{
					$this->module->setAdminFlashMessage('success', $this->module->l('Account successfully authenticated with Nosto.'));
					$this->redirectToModuleAdmin();
				}
			$this->module->setAdminFlashMessage('error', $this->module->l('Account could not be authenticated. Please contact Nosto support.'));
			$this->redirectToModuleAdmin();
		}
		elseif (($error = Tools::getValue('error')) !== false)
		{
			// todo: handle the reject error.
		}
		$this->notFound();
	}

	/**
	 * Redirects the user to the module admin url if the current user is logged in as an admin in the back office.
	 * If the url cannot be found or user is not admin, then show the 404 page.
	 */
	protected function redirectToModuleAdmin()
	{
		// The admin url is only returned if user is logged in as admin in back office,
		// so that we do not expose it to outsiders.
		// The OAuth2 request cycle is initiated from the back office, so the user should still be logged in.
		$admin_url = $this->module->getAdminUrl();
		if (!empty($admin_url))
		{
			header('Location: '.$admin_url);
			die;
		}
		$this->notFound();
	}

	/**
	 * Shows the 404 page to the user.
	 */
	protected function notFound()
	{
		Controller::getController('PageNotFoundController')->run();
	}
}
