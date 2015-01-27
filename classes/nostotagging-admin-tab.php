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
 * Helper class for managing the tab added to the admin section.
 */
class NostoTaggingAdminTab
{
	/**
	 * Installs the Admin Tab in PS backend.
	 * Only for PS >= 1.5.
	 *
	 * @return bool
	 */
	public static function install()
	{
		if (_PS_VERSION_ < '1.5')
			return true;

		$tab = new Tab();
		$tab->active = 1;
		$tab->class_name = 'AdminParentNosto';
		$tab->name = array();
		foreach (Language::getLanguages(true) as $lang)
		{
			switch ($lang['iso_code'])
			{
				case 'de':
					$tab->name[$lang['id_lang']] = 'Personalisierung';
					break;

				case 'fr':
					$tab->name[$lang['id_lang']] = 'Personnalisation';
					break;

				case 'es':
					$tab->name[$lang['id_lang']] = 'Personalización';
					break;

				default:
					$tab->name[$lang['id_lang']] = 'Personalization';
					break;
			}
		}
		$tab->id_parent = 0;
		$tab->module = 'nostotagging';
		$added = $tab->add();

		if ($added)
		{
			$tab = new Tab();
			$tab->active = 1;
			$tab->class_name = 'AdminNosto';
			$tab->name = array();
			foreach (Language::getLanguages(true) as $lang)
			{
				switch ($lang['iso_code'])
				{
					case 'de':
						$tab->name[$lang['id_lang']] = 'Home';
						break;

					case 'fr':
						$tab->name[$lang['id_lang']] = 'Home';
						break;

					case 'es':
						$tab->name[$lang['id_lang']] = 'Home';
						break;

					default:
						$tab->name[$lang['id_lang']] = 'Home';
						break;
				}
			}
			$tab->id_parent = (int)Tab::getIdFromClassName('AdminParentNosto');
			$tab->module = 'nostotagging';
			$added = $tab->add();
		}

		return $added;
	}

	/**
	 * Uninstalls the Admin Tab from PS backend.
	 * Only for PS >= 1.5.
	 *
	 * @return bool
	 */
	public static function uninstall()
	{
		if (_PS_VERSION_ < '1.5')
			return true;

		foreach (array('AdminParentNosto', 'AdminNosto') as $tab_name) {
			$id_tab = (int)Tab::getIdFromClassName($tab_name);
			if ($id_tab)
			{
				$tab = new Tab($id_tab);
				$tab->delete();
			}
		}

		return true;
	}
} 