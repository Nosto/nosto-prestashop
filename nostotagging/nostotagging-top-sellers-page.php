<?php

/**
 * Helper class for managing the "Top Sellers" CMS page.
 *
 * CURRENTLY NOT IN USE.
 */
class NostoTaggingTopSellersPage
{
	const NOSTOTAGGING_CONFIG_KEY_TOP_SELLERS_CMS_ID = 'NOSTOTAGGING_TOP_SELLERS_CMS_ID';

	/**
	 * Page meta title.
	 *
	 * @var string
	 */
	protected static $meta_title = 'Top Sellers';

	/**
	 * Page URL rewrite.
	 *
	 * @var string
	 */
	protected static $link_rewrite = 'top-sellers';

	/**
	 * Page content.
	 *
	 * @var string
	 */
	protected static $content = '<div class="nosto_element" id="nosto-page-top-sellers">&nbsp;</div>';

	/**
	 * Creates a new CMS page for the "Top Sellers" page.
	 * The page contains only a nosto element.
	 *
	 * @return bool
	 */
	public static function addPage()
	{
		if (self::getPage() === null)
		{
			$cms = new CMS();
			$cms->id_cms_category = 1;
			$cms->active = 1;

			$multi_lang_fields = array(
				'meta_title' => self::$meta_title,
				'link_rewrite' => self::$link_rewrite,
				'content' => self::$content,
			);
			$languages = Language::getLanguages(false);

			foreach ($multi_lang_fields as $field => $item)
			{
				$data = array();
				foreach ($languages as $language)
					$data[(int)$language['id_lang']] = $item;
				$cms->{$field} = $data;
			}

			if ($cms->add())
			{
				Configuration::updateGlobalValue(self::NOSTOTAGGING_CONFIG_KEY_TOP_SELLERS_CMS_ID, $cms->id);

				self::addPageToShops($cms->id);
				self::addPageToMenu($cms->id);

				return true;
			}
		}

		return false;
	}

	/**
	 * Deletes the "Top Sellers" CMS page and menu item from all shops.
	 *
	 * @return bool
	 */
	public static function deletePage()
	{
		$cms = self::getPage();
		if ($cms instanceof CMS)
		{
			if (Shop::isFeatureActive())
				$cms->id_shop_list = Shop::getCompleteListOfShopsID();

			if (!$cms->delete())
				return false;

			self::deletePageFromMenu($cms->id, true);
		}

		return Configuration::deleteByName(self::NOSTOTAGGING_CONFIG_KEY_TOP_SELLERS_CMS_ID);
	}

	/**
	 * Enables the "Top Sellers" CMS page for current context shops.
	 */
	public static function enablePage()
	{
		$cms = self::getPage();

		if ($cms instanceof CMS)
		{
			if (Shop::isFeatureActive() && Shop::isTableAssociated('cms'))
				self::addPageToShops($cms->id);
			elseif (!$cms->active)
			{
				$cms->active = 1;
				$cms->save();
			}

			self::addPageToMenu($cms->id);
		}
	}

	/**
	 * Disables the "Top Sellers" CMS page for current context shops.
	 */
	public static function disablePage()
	{
		$cms = self::getPage();

		if ($cms instanceof CMS)
		{
			if (Shop::isFeatureActive() && Shop::isTableAssociated('cms'))
			{
				$shop_ids = Shop::getContextListShopID();
				if (!empty($shop_ids))
				{
					$where = '`id_cms` = '.(int)$cms->id.'
                               AND id_shop IN ('.implode(', ', $shop_ids).')';
					Db::getInstance()->delete('cms_shop', $where);
				}
			}
			elseif ($cms->active)
			{
				$cms->active = 0;
				$cms->save();
			}

			self::deletePageFromMenu($cms->id);
		}
	}

	/**
	 * Getter for the "Top Sellers" CMS page.
	 *
	 * @return CMS|null
	 */
	protected static function getPage()
	{
		$cms_id = Configuration::getGlobalValue(self::NOSTOTAGGING_CONFIG_KEY_TOP_SELLERS_CMS_ID);
		if (ctype_digit((string)$cms_id))
		{
			$cms = new CMS($cms_id);
			if ((int)$cms->id === (int)$cms_id)
				return $cms;
		}

		return null;
	}

	/**
	 * Adds the "Top Sellers" CMS page to current context shops.
	 *
	 * @param int $cms_id
	 */
	protected static function addPageToShops($cms_id)
	{
		if (Shop::isFeatureActive() && Shop::isTableAssociated('cms'))
		{
			$shop_ids = Shop::getContextListShopID();
			$insert = array();
			foreach ($shop_ids as $shop_id)
				$insert[] = array(
					'id_cms' => (int)$cms_id,
					'id_shop' => (int)$shop_id,
				);

			Db::getInstance()->insert('cms_shop', $insert, false, true, Db::INSERT_IGNORE);
		}
	}

	/**
	 * Adds the "Top Sellers" CMS page as a menu item in the top menu.
	 *
	 * The menu is assumed to be implemented by the default "Blocktopmenu" module.
	 * If the module is not installed, then do nothing.
	 *
	 * The menu items are stored in the configuration table as a comma separated string.
	 * The "Top Sellers" page is added to the config for the current context shops.
	 *
	 * @param int $cms_id
	 */
	protected static function addPageToMenu($cms_id)
	{
		if (Module::isInstalled('Blocktopmenu'))
		{
			$menu_item = 'CMS'.(int)$cms_id;
			// Get all menu config rows where we need to add the page identifier.
			$config = self::getMenuConfig();

			foreach ($config as $item)
			{
				// Add page identifier if it does not already exist in this menu.
				$menu_items = Configuration::get('MOD_BLOCKTOPMENU_ITEMS', null,
					$item['id_shop_group'], $item['id_shop']);

				if (is_string($menu_items))
				{
					$menu_items = explode(',', $menu_items);
					if (!in_array($menu_item, $menu_items))
					{
						$menu_items[] = $menu_item;
						$menu_items = implode(',', $menu_items);
						Configuration::updateValue('MOD_BLOCKTOPMENU_ITEMS', $menu_items, false,
							$item['id_shop_group'], $item['id_shop']);
					}
				}
			}
		}
	}

	/**
	 * Deletes the "Top Sellers" CMS page from the top menu.
	 *
	 * The menu is assumed to be implemented by the default "Blocktopmenu" module.
	 * If the module is not installed, then do nothing.
	 *
	 * The menu items are stored in the configuration table as a comma separated string.
	 * The "Top Sellers" page is removed from the config for the current context shops.
	 *
	 * @param int $cms_id
	 * @param bool $all_shops
	 */
	protected static function deletePageFromMenu($cms_id, $all_shops = false)
	{
		if (Module::isInstalled('Blocktopmenu'))
		{
			$menu_item = 'CMS'.(int)$cms_id;
			// Get all menu config rows from where we need to remove the page identifier.
			$config = self::getMenuConfig($all_shops);

			foreach ($config as $item)
			{
				// Remove page identifier if it exists in this menu.
				$menu_items = Configuration::get('MOD_BLOCKTOPMENU_ITEMS', null,
					$item['id_shop_group'], $item['id_shop']);
				if (is_string($menu_items))
				{
					$menu_items = explode(',', $menu_items);
					$i = array_search($menu_item, $menu_items);
					if ($i !== false)
					{
						unset($menu_items[$i]);
						$menu_items = implode(',', $menu_items);
						Configuration::updateValue('MOD_BLOCKTOPMENU_ITEMS', $menu_items, false,
							$item['id_shop_group'], $item['id_shop']);
					}
				}
			}
		}
	}

	/**
	 * Gets the config items for the top menu that apply to the current context shops.
	 *
	 * The menu is assumed to be implemented by the default "Blocktopmenu" module.
	 * If the module is not installed, then return empty list.
	 *
	 * @param bool $all_shops
	 * @return array
	 */
	protected static function getMenuConfig($all_shops = false)
	{
		$config = array();

		if (Module::isInstalled('Blocktopmenu'))
		{
			// Default menu config that applies to the current shop context.
			$config[] = array(
				'id_shop_group' => null,
				'id_shop' => null,
			);

			if (Shop::isFeatureActive())
			{
				// If the context is "all shops" then we need to check all individual
				// shops and all shop groups, as they can override the global menu.
				$id_shop = Shop::getContextShopID(true);
				$id_shop_group = Shop::getContextShopGroupID(true);
				if ($all_shops || ($id_shop === null && $id_shop_group === null))
				{
					$shops = Shop::getShopsCollection();
					foreach ($shops as $shop)
						if (Configuration::hasKey('MOD_BLOCKTOPMENU_ITEMS', null, null, $shop->id))
							$config[] = array(
								'id_shop_group' => 0,
								'id_shop' => (int)$shop->id,
							);

					$shop_groups = ShopGroup::getShopGroups();
					foreach ($shop_groups as $shop_group)
						if (Configuration::hasKey('MOD_BLOCKTOPMENU_ITEMS', null, $shop_group->id, null))
							$config[] = array(
								'id_shop_group' => (int)$shop_group->id,
								'id_shop' => 0,
							);
				}
			}
		}

		return $config;
	}
}
