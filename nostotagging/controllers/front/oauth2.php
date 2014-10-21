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

		// todo: admin cookie not working.
		if ($this->module->isUserAdmin())
			$admin_url = NostoTaggingConfig::read(NostoTaggingConfig::ADMIN_URL);
		if (!empty($admin_url))
		{
			if (!empty($language_id))
			{
				$parsed_url = NostoTaggingHttpRequest::parseUrl($admin_url);
				$query_string = isset($parsed_url['query']) ? $parsed_url['query'] : '';
				$parsed_url['query'] = NostoTaggingHttpRequest::replaceQueryParam('language_id', $language_id, $query_string);
				$admin_url = NostoTaggingHttpRequest::buildUrl($parsed_url);
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
		if (_PS_VERSION_ < '1.5')
			Tools::display404Error();
		else
			Controller::getController('PageNotFoundController')->run();
	}
}
