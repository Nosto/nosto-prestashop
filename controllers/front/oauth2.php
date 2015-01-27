<?php
/**
 * 2013-2014 Nosto Solutions Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@nosto.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2013-2014 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

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
			$admin_url = NostoTaggingHttpRequest::replaceQueryParamsInUrl($query_params, $admin_url);
			Tools::redirect($admin_url, '');
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
