<?php

class NostoTaggingHelperAccount
{
	const NOSTOTAGGING_CONFIG_BASE = 'NOSTOTAGGING_API_TOKEN_';

	/**
	 * @param NostoAccount $account
	 * @param null|int $id_lang the ID of the language to set the account name for.
	 * @return bool
	 */
	public function save(NostoAccount $account, $id_lang)
	{
		$success = NostoTaggingConfig::write(NostoTaggingConfig::ACCOUNT_NAME, $account->getName(), $id_lang);
		if ($success)
			foreach ($account->tokens as $token)
				$success = $success && $this->saveToken($token, $id_lang);
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
		$success = NostoTaggingConfig::deleteAllFromContext($id_lang, $id_shop_group, $id_shop);
		if ($success)
		{
			$token = $account->getToken('sso');
			if ($token)
				try
				{
					$account->delete();
				}
				catch (NostoException $e)
				{
					NostoTaggingLogger::log(
						__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(),
						NostoTaggingLogger::LOG_SEVERITY_ERROR,
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
		$account_name = NostoTaggingConfig::read(NostoTaggingConfig::ACCOUNT_NAME, $lang_id, $id_shop_group, $id_shop);
		if (!empty($account_name))
		{
			$account = new NostoAccount();
			$account->name = $account_name;
			$tokens = array(); // todo: load tokens from config.
			if (is_array($tokens) && !empty($tokens))
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

	protected function saveToken(NostoApiToken $token, $id_lang)
	{
		return NostoTaggingConfig::write($this->getTokenConfigKey($token->name), $token->value, $id_lang);
	}

	protected function getTokenConfigKey($name)
	{
		return self::NOSTOTAGGING_CONFIG_BASE.Tools::strtoupper($name);
	}
}
