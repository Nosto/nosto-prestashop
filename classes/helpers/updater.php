<?php
/**
 * 2013-2015 Nosto Solutions Ltd
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
 * @copyright 2013-2015 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for upgrading the module by running new upgrade scripts.
 * Prestashop 1.4. does not have any mechanism for auto-upgrade of modules and Prestashop < 1.5.4.0 has a bug that
 * causes the upgrade scripts to never run.
 * The upgrade scripts are ran 'silently' and does not output anything to the user.
 */
class NostoTaggingHelperUpdater
{
	/**
	 * @var string the updater only runs upgrade scripts >= to this version.
	 *
	 * This is needed as otherwise the updater would run all found upgrade scripts the first time the module is updated,
	 * as there would be no `installed version` written to the config yet.
	 */
	protected static $from_version = '2.1.0';

	/**
	 * Checks if the module has new upgrade scripts and applies them.
	 * These scripts located in the modules `upgrade` directory, with versions above the current installed version.
	 *
	 * @param Module $module the module to check and apply the updates for.
	 */
	public function checkForUpdates($module)
	{
		if (!Module::isInstalled($module->name))
			return;

		// Prestashop 1.4 does not have any auto-update mechanism.
		// Prestashop < 1.5.4.0 has a bug that causes the auto-update mechanism fail.
		if (version_compare(_PS_VERSION_, '1.5.4.0', '<'))
		{
			// If the module is already updated to the latest version, don't continue.
			$installed_version = (string)Nosto::helper('nosto_tagging/config')->getInstalledVersion();
			if (version_compare($installed_version, $module->version, '='))
				return;

			foreach ($this->findUpgradeScripts($module) as $script)
				if (file_exists($script['file']) && is_readable($script['file']))
				{
					// Run the script and update the currently installed module version so future updates can work.
					include_once $script['file'];
					call_user_func($script['upgrade_function'], $module);
				}

			// Always update the installed version so that we can check it during the next requests in order
			// to avoid reading the file system for upgrade script all the time.
			Nosto::helper('nosto_tagging/config')->saveInstalledVersion($module->version);
		}

		// Prestashop >= 1.5.4.0 handles the auto-update mechanism.
	}

	/**
	 * Reads the file system and finds any new upgrade scripts that can be applied for the module.
	 * These scripts located in the modules `upgrade` directory, with versions above the current installed version.
	 *
	 * @param Module $module the module to find the upgrade files for.
	 * @return array the list of upgrade scripts.
	 */
	protected function findUpgradeScripts($module)
	{
		$scripts = array();
		$path = _PS_MODULE_DIR_.$module->name.'/upgrade/';
		$installed_version = (string)Nosto::helper('nosto_tagging/config')->getInstalledVersion();
		$new_version = $module->version;

		if (file_exists($path) && ($files = scandir($path)))
			foreach ($files as $file)
				if (!in_array($file, array('.', '..', 'index.php')))
				{
					$parts = explode('-', $file);
					$script_version = isset($parts[1]) ? basename($parts[1], '.php') : '';
					if (count($parts) == 2
						&& !empty($script_version)
						&& version_compare($script_version, self::$from_version, '>=')
						&& version_compare($script_version, $new_version, '<=')
						&& version_compare($script_version, $installed_version, '>'))
					{
						$scripts[] = array(
							'file' => $path.$file,
							'version' => $script_version,
							'upgrade_function' => 'upgrade_module_'.str_replace('.', '_', $script_version)
						);
					}
				}

		usort($scripts, array('NostoTaggingUpdater', 'sortUpgradeScriptsByVersion'));
		return $scripts;
	}

	/**
	 * Sorts the found upgrade scripts by their versions in asc order.
	 *
	 * @param array $a first version.
	 * @param array $b second version.
	 * @return mixed -1 if first version is lower than second, 0 if equal, and 1 if second is lower.
	 */
	public static function sortUpgradeScriptsByVersion($a, $b)
	{
		return version_compare($a['version'], $b['version']);
	}
}
