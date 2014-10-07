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
			$client->setClientSecret(''); // todo
			$client->setRedirectUrl($this->module->getOAuth2ControllerUrl());
			if (($token = $client->authenticate($code)) !== false)
			{
				if($this->module->exchangeDataWithNosto($token))
					$this->redirectToModuleAdmin(array(
						'success_message' => $this->module->l('Account was successfully authorized.')
					));
			}
			$this->redirectToModuleAdmin(array(
				'error_message' => $this->module->l('Account could not be authorized. Please contact Nosto support.')
			));
		}
		$this->notFound();
	}

	/**
	 * Redirects the user to the module admin url.
	 * If the url cannot be found, then show the 404 page.
	 *
	 * @param array $extra_params any extra GET params to pass to the admin page.
	 */
	protected function redirectToModuleAdmin(array $extra_params = array())
	{
		$admin_url = $this->module->getAdminUrl();
		if (!empty($admin_url))
		{
			if (!empty($extra_params))
			{
				$admin_url_parts = parse_url($admin_url);
				$admin_url_parts['query'] = (isset($admin_url_parts['query']) ? $admin_url_parts['query'].'&' : '')
					.http_build_query($extra_params);
				$admin_url = ((isset($admin_url_parts['scheme'])) ? $admin_url_parts['scheme'] . '://' : '')
					.((isset($admin_url_parts['host'])) ? $admin_url_parts['host'] : '')
					.((isset($admin_url_parts['port'])) ? ':'.$admin_url_parts['port'] : '')
					.((isset($admin_url_parts['path'])) ? $admin_url_parts['path'] : '')
					.((isset($admin_url_parts['query'])) ? '?'.$admin_url_parts['query'] : '')
					.((isset($admin_url_parts['fragment'])) ? '#'.$admin_url_parts['fragment'] : '');
			}
			header('Location: '.$admin_url);
		}
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
