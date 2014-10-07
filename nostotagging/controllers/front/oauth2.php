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
		// If this is a request from the Nosto oauth2 server.
		if (($code = Tools::getValue('code')) !== false)
		{
			$client = new NostoTaggingOAuth2Client();
			$client->setClientId($this->module->getAccountName());
			$client->setClientSecret($this->module->getClientSecret());
			$client->setRedirectUrl($this->module->getOAuth2ControllerUrl());
			if (($token = $client->authenticate($code)) !== false)
				$this->module->exchangeDataWithNosto($token);
			// todo: show general front end error page instead, because passing a error message to admin part is bad.
			$this->redirectToModuleAdmin();
		}
		$this->notFound();
	}

	/**
	 * Redirects the user to the module admin url.
	 * If the url cannot be found, then show the 404 page.
	 */
	protected function redirectToModuleAdmin()
	{
		// todo: check if the admin is logged in, otherwise just show a view so we do not expose the the admin url to outsiders.
		$admin_url = $this->module->getAdminUrl();
		if (!empty($admin_url))
			header('Location: '.$admin_url);
		else
			$this->notFound();
		die();
	}

	/**
	 * Shows the 404 page to the user.
	 */
	protected function notFound()
	{
		Controller::getController('PageNotFoundController')->run();
	}
}
