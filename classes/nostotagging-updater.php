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
 * Helper class for upgrading the module by running new upgrade scripts.
 * Prestashop 1.4. does not have any mechanism for auto-upgrade of modules and Prestashop < 1.5.4.0 has a bug that
 * causes the upgrade files to never be ran.
 * The upgrade scripts are ran 'silently' and does not output anything to the user.
 */
class NostoTaggingUpdater
{
	/**
	 * @var string the updater only runs upgrade scripts >= to this version.
	 *
	 * This is needed as otherwise the updater would run all found upgrade script the first time as there would be no
	 * `installed version` written to the config yet.
	 */
	protected static $from_version = '2.1.0';

	/**
	 * Checks if the currently attached module has upgrade scripts and applies them.
	 * These scripts located in the modules `upgrade` directory, with versions above the current installed version.
	 *
	 * @param Module $module the module to check and apply the updates for.
	 * @return bool true if updates are found, false otherwise.
	 * @throws Exception
	 */
	public static function checkForUpdates($module)
	{
		if (!Module::isInstalled($module->name))
			return false;

		// Prestashop 1.4 does not have any auto-update mechanism.
		// Prestashop < 1.5.4.0 has a bug that causes the auto-update mechanism fail.
		if (version_compare(_PS_VERSION_, '1.5.4.0', '<'))
		{
			$upgrade_files = self::findUpgradeFiles($module);
			foreach ($upgrade_files as $upgrade_file) {
				if (file_exists($upgrade_file['file']) && is_readable($upgrade_file['file']))
				{
					include_once $upgrade_file['file'];
					if (call_user_func($upgrade_file['upgrade_function'], $module))
					{
						// We store our own version of the modules `database_version` number which tells us which
						// version is currently installed.
						NostoTaggingConfig::write(
							NostoTaggingConfig::INSTALLED_VERSION, $upgrade_file['version'], null, true
						);
					}
				}
			}
		}
		// Prestashop >= 1.5.4.0 handles the auto-update mechanism.
		return false;
	}

	/**
	 * Reads the file system and finds any upgrade scripts that can be applied for the module.
	 * These scripts located in the modules `upgrade` directory, with versions above the current installed version.
	 *
	 * @param Module $module the module to find the upgrade files for.
	 * @return array the list of upgrade file info.
	 */
	protected static function findUpgradeFiles($module)
	{
		$upgrade_files = array();
		$upgrade_path = _PS_MODULE_DIR_.$module->name.'/upgrade/';
		// We store our own version of the modules `database_version` number which tells us which version
		// is currently installed.
		$installed_version = (string)NostoTaggingConfig::read(NostoTaggingConfig::INSTALLED_VERSION);

		if (file_exists($upgrade_path) && ($files = scandir($upgrade_path)))
			foreach ($files as $file)
				if (!in_array($file, array('.', '..', 'index.php')))
				{
					$tab = explode('-', $file);
					$file_version = isset($tab[1]) ? basename($tab[1], '.php') : '';
					if (count($tab) == 2
						&& !empty($file_version)
						&& version_compare($file_version, self::$from_version, '>=')
						&& (version_compare($file_version, $module->version, '<=')
							&& version_compare($file_version, $installed_version, '>')))
					{
						$upgrade_files[] = array(
							'file' => $upgrade_path.$file,
							'version' => $file_version,
							'upgrade_function' => 'upgrade_module_'.str_replace('.', '_', $file_version));
					}
				}

		usort($upgrade_files, array('NostoTaggingUpdater', 'sortUpgradeFilesByVersion'));

		return $upgrade_files;
	}

	/**
	 * Sorts the found upgrade file info versions in asc order.
	 *
	 * @param array $a first version.
	 * @param array $b second version.
	 * @return mixed -1 if first version is lower than second, 0 if equal, and 1 if second is lower.
	 */
	public static function sortUpgradeFilesByVersion($a, $b)
	{
		return version_compare($a['version'], $b['version']);
	}
}
