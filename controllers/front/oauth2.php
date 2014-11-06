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
		$language_id = (int)Tools::getValue('language_id', $this->module->getContext()->language->id);
		if (($code = Tools::getValue('code')) !== false)
		{
			// The user accepted the authorization request.
			// The authorization server responded with a code that can be used to exchange for the access token.
			$client = new NostoTaggingOAuth2Client();
			$client->setRedirectUrl($this->module->getOAuth2ControllerUrl(array('language_id' => $language_id)));
			if (($token = $client->authenticate($code)) !== false)
				if ($this->module->exchangeDataWithNosto($token, $language_id))
				{
					$msg = $this->module->l('Account %s successfully connected to Nosto.', 'oauth2');
					$msg = sprintf($msg, $token->merchant_name);
					$this->redirectToModuleAdmin(array(
						'language_id' => $language_id,
						'oauth_success' => $msg,
					));
				}
			$msg = $this->module->l('Account could not be connected to Nosto. Please contact Nosto support.', 'oauth2');
			$this->redirectToModuleAdmin(array(
				'language_id' => $language_id,
				'oauth_error' => $msg,
			));
		}
		elseif (($error = Tools::getValue('error')) !== false)
		{
			// The user rejected the authorization request.
			$message_parts = array($error);
			if (($error_reason = Tools::getValue('error')) !== false)
				$message_parts[] = $error_reason;
			if (($error_description = Tools::getValue('error')) !== false)
				$message_parts[] = $error_description;
			NostoTaggingLogger::log(
				__CLASS__.'::'.__FUNCTION__.' - '.implode(' - ', $message_parts),
				NostoTaggingLogger::LOG_SEVERITY_ERROR,
				200
			);
			$msg = $this->module->l('Account could not be connected to Nosto. You rejected the connection request.', 'oauth2');
			$this->redirectToModuleAdmin(array(
				'language_id' => $language_id,
				'oauth_error' => $msg,
			));
		}
		$this->notFound();
	}

	/**
	 * Redirects the user to the module admin url if the current user is logged in as an admin in the back office.
	 * If the url cannot be found, then show the 404 page.
	 *
	 * @param array $query_params
	 */
	protected function redirectToModuleAdmin(array $query_params)
	{
		$admin_url = NostoTaggingConfig::read(NostoTaggingConfig::ADMIN_URL);
		if (!empty($admin_url))
		{
			$parsed_url = NostoTaggingHttpRequest::parseUrl($admin_url);
			$query_string = isset($parsed_url['query']) ? $parsed_url['query'] : '';
			foreach ($query_params as $param => $value)
				$query_string = NostoTaggingHttpRequest::replaceQueryParam($param, $value, $query_string);
			$parsed_url['query'] = $query_string;
			$admin_url = NostoTaggingHttpRequest::buildUrl($parsed_url);
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
		if (_PS_VERSION_ < '1.5')
			Tools::display404Error();
		else
			Controller::getController('PageNotFoundController')->run();
	}
}
