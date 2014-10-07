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
		if (($access_token = Tools::getValue('access_token')) !== false)
		{
			$client = new NostoTaggingOAuth2Client();
			$client->setAccessToken($access_token);
			$this->module->exchangeDataWithNosto($client);
			$this->redirectToModuleAdmin();
		}
		elseif (($error = Tools::getValue('error')) !== false)
		{
			$message_parts = array($error);
			if (($error_reason = Tools::getValue('error_reason')) !== false)
				$message_parts[] = $error_reason;
			if (($error_description = Tools::getValue('error_description')) !== false)
				$message_parts[] = $error_description;

			NostoTaggingLogger::log(
				__CLASS__.'::'.__FUNCTION__.implode(' - ', $message_parts),
				NostoTaggingLogger::LOG_SEVERITY_ERROR,
				200
			);
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
		$admin_url = $this->module->getAdminUrl();
		if (!empty($admin_url))
			header('Location: '.$admin_url);
		else
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
