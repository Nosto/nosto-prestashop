<?php

/**
 * Helper class for managing the tab added to the admin section.
 */
class NostoTaggingAdminTab
{
	/**
	 * @return bool
	 */
	public static function install()
	{
		if (_PS_VERSION_ < '1.5')
			return true;

		$tab = new Tab();
		$tab->active = 1;
		$tab->class_name = 'AdminNosto';
		$tab->name = array();
		foreach (Language::getLanguages(true) as $lang)
			$tab->name[$lang['id_lang']] = 'Test';
		$tab->id_parent = (int)Tab::getIdFromClassName('AdminAdmin');
		$tab->module = 'nostotagging';
		return $tab->add();
	}

	/**
	 * @return bool
	 */
	public static function uninstall()
	{
		if (_PS_VERSION_ < '1.5')
			return true;

		$id_tab = (int)Tab::getIdFromClassName('AdminNosto');
		if (!$id_tab)
			return false;

		$tab = new Tab($id_tab);
		return $tab->delete();
	}
} 