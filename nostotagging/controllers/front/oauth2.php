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
		$language_id = (int)Tools::getValue('language_id');
		if (($code = Tools::getValue('code')) !== false)
		{
			$params = array();
			if (!empty($language_id))
				$params['language_id'] = $language_id;

			// todo: what if language not set, use context??

			// The user accepted the authorization request.
			// The authorization server responded with a code that can be used to exchange for the access token.
			$client = new NostoTaggingOAuth2Client();
			$client->setRedirectUrl($this->module->getOAuth2ControllerUrl($params));
			if (($token = $client->authenticate($code)) !== false)
				if($this->module->exchangeDataWithNosto($token, $language_id))
				{
					$msg = $this->module->l('Account %s successfully connected to Nosto.');
					$msg = sprintf($msg, $token->merchant_name);
					$this->module->setAdminFlashMessage('success', $msg);
					$this->redirectToModuleAdmin($language_id);
				}
			$msg = $this->module->l('Account could not be connected to Nosto. Please contact Nosto support.');
			$this->module->setAdminFlashMessage('error', $msg);
			$this->redirectToModuleAdmin($language_id);
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
			$msg = $this->module->l('Account could not be connected to Nosto. You rejected the connection request.');
			$this->module->setAdminFlashMessage('error', $msg);
			$this->redirectToModuleAdmin($language_id);
		}
		$this->notFound();
	}

	/**
	 * Redirects the user to the module admin url if the current user is logged in as an admin in the back office.
	 * If the url cannot be found or user is not admin, then show the 404 page.
	 *
	 * @param int|null $language_id the ID of the language model to show the module admin page for.
	 */
	protected function redirectToModuleAdmin($language_id = null)
	{
		// The admin url is only used if user is logged in as admin in back office, so that we do not expose it.
		// The OAuth2 request cycle is initiated from the back office, so the user should still be logged in.
		$admin_url = '';
		if ($this->module->isUserAdmin())
			$admin_url = NostoTaggingConfig::read(NostoTaggingConfig::ADMIN_URL);
		if (!empty($admin_url))
		{
			if (!empty($language_id))
			{
				$parsed_url = parse_url($admin_url);
				$scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
				$host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
				$port = isset($parsed_url['port']) ? ':'.$parsed_url['port'] : '';
				$path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
				if (isset($parsed_url['query']))
					$query = '?'.$parsed_url['query'].'&language_id='.$language_id;
				else
					$query = '?language_id='.$language_id;
				$admin_url = $scheme.$host.$port.$path.$query;
			}

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
