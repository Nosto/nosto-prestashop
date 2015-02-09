<?php

class NostoTaggingHelperAccount
{
	/**
	 * @param NostoAccount $account
	 * @param null|int $id_lang the ID of the language to set the account name for.
	 * @return bool
	 */
	public function save(NostoAccount $account, $id_lang)
	{
		/** @var NostoTaggingHelperConfig $helper_config */
		$helper_config = Nosto::helper('nosto_tagging/config');
		$success = $helper_config->saveAccountName($account->getName(), $id_lang);
		if ($success)
			foreach ($account->tokens as $token)
				$success = $success && $helper_config->saveToken($token->name, $token->value, $id_lang);
		return $success;
	}

	/**
	 * @param NostoAccount $account
	 * @param int $id_lang the ID of the language model to delete the account for.
	 * @param null|int $id_shop_group the ID of the shop context.
	 * @param null|int $id_shop the ID of the shop.
	 * @return bool
	 */
	public function delete(NostoAccount $account, $id_lang, $id_shop_group = null, $id_shop = null)
	{
		/** @var NostoTaggingHelperConfig $helper_config */
		$helper_config = Nosto::helper('nosto_tagging/config');
		$success = $helper_config->deleteAllFromContext($id_lang, $id_shop_group, $id_shop);
		if ($success)
		{
			$token = $account->getApiToken('sso');
			if ($token)
				try
				{
					$account->delete();
				}
				catch (NostoException $e)
				{
					Nosto::helper('nosto_tagging/logger')->error(
						__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(),
						$e->getCode()
					);
				}
		}
		return $success;
	}

	/**
	 * @param null|int $lang_id the ID of the language.
	 * @param null|int $id_shop_group the ID of the shop context.
	 * @param null|int $id_shop the ID of the shop.
	 * @return NostoAccount|null
	 */
	public function find($lang_id = null, $id_shop_group = null, $id_shop = null)
	{
		/** @var NostoTaggingHelperConfig $helper_config */
		$helper_config = Nosto::helper('nosto_tagging/config');
		$account_name = $helper_config->getAccountName($lang_id, $id_shop_group, $id_shop);
		if (!empty($account_name))
		{
			$account = new NostoAccount();
			$account->name = $account_name;

			$tokens = array();
			foreach (NostoApiToken::$tokenNames as $token_name)
			{
				$token_value = $helper_config->getToken($token_name, $lang_id, $id_shop_group, $id_shop);
				if (!empty($token_value))
					$tokens[$token_name] = $token_value;
			}

			if (!empty($tokens))
				foreach ($tokens as $name => $value)
				{
					$token = new NostoApiToken();
					$token->name = $name;
					$token->value = $value;
					$account->tokens[] = $token;
				}
			return $account;
		}
		return null;
	}

	/**
	 * @param null|int $lang_id the ID of the language.
	 * @param null|int $id_shop_group the ID of the shop context.
	 * @param null|int $id_shop the ID of the shop.
	 * @return bool
	 */
	public function existsAndIsConnected($lang_id = null, $id_shop_group = null, $id_shop = null)
	{
		$account = $this->find($lang_id, $id_shop_group, $id_shop);
		return ($account !== null && $account->isConnectedToNosto());
	}
}
