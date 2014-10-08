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
		// If this "is" a request from the Nosto oauth2 server.
		if (($code = Tools::getValue('code')) !== false)
		{
			$client = new NostoTaggingOAuth2Client();
			$client->setClientId($this->module->getAccountName());
			$client->setClientSecret($this->module->getClientSecret());
			$client->setRedirectUrl($this->module->getOAuth2ControllerUrl());
			if (($token = $client->authenticate($code)) !== false)
				if($this->module->exchangeDataWithNosto($token))
					$this->redirectToModuleAdmin(array(
						'messages' => array(
							'success' => $this->module->l('Account successfully authenticated with Nosto.')
						)
					));
			$this->redirectToModuleAdmin(array(
				'messages' => array(
					'error' => $this->module->l('Account could not be authenticated. Please contact Nosto support.')
				)
			));
		}
		$this->notFound();
	}

	/**
	 * Redirects the user to the module admin url if the current user is logged in as an admin in the back office.
	 * If the url cannot be found or user is not admin, then show the 404 page.
	 *
	 * @param array $data additional data to set in the admin cookie that can be read on the module admin page.
	 */
	protected function redirectToModuleAdmin(array $data = array())
	{
		// Check if user is logged in as admin in back office, so that we do not expose the the admin url to outsiders.
		// The OAuth2 request cycle is initiated from the back office, so the user should still be logged in.
		$cookie = new Cookie('psAdmin');
		if ((bool)$cookie->id_employee)
		{
			// Add data to the admin cookie so that we can show messages on admin page.
			$cookie->nostotagging = json_encode($data);
			$admin_url = $this->module->getAdminUrl();
			if (!empty($admin_url))
			{
				header('Location: '.$admin_url);
				die;
			}
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
