<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_3_1($module)
{
	if(!Configuration::get('SEUR_NACIONAL_SERVICE'))
		Configuration::updateValue('SEUR_NACIONAL_SERVICE', '031');
	
	if(!Configuration::get('SEUR_NACIONAL_PRODUCT'))
		Configuration::updateValue('SEUR_NACIONAL_PRODUCT', '002');
	
	if(!Configuration::get('SEUR_INTERNACIONAL_SERVICE'))
		Configuration::updateValue('SEUR_INTERNACIONAL_SERVICE', '077');
	
	if(!Configuration::get('SEUR_INTERNACIONAL_PRODUCT'))
		Configuration::updateValue('SEUR_INTERNACIONAL_PRODUCT', '070');
	
	uninstallSeurCashOnDelivery31();
	installSeurCashOnDelivery31();
    
	return $module;
}

function uninstallSeurCashOnDelivery31()
{	
	if ($module = Module::getInstanceByName('seurcashondelivery'))
	{
		$module_dir = _PS_MODULE_DIR_.str_replace(array('.', '/', '\\'), array('', '', ''), $module->name);
		recursiveDeleteOnDisk31($module_dir);
	}
		
	return true;
}

function installSeurCashOnDelivery31()
{
	if (moveFiles31())
		return true;
		
	return false;
}

function moveFiles31()
{
	if (!is_dir(_PS_MODULE_DIR_.'seurcashondelivery'))
	{
		$module_dir = _PS_MODULE_DIR_.str_replace(array('.', '/', '\\'), array('', '', ''), 'seurcashondelivery');
		recursiveDeleteOnDisk31($module_dir);
	}
	$dir = _PS_MODULE_DIR_.'seur/install/seurcashondelivery';
	if (!is_dir($dir))
		return false;

	copyDirectory31($dir, _PS_MODULE_DIR_.'seurcashondelivery');

	return true;
}

function copyDirectory31($source, $target)
{
	if (!is_dir($source))
	{
		copy($source, $target);
		return null;
	}

	@mkdir($target);
	chmod($target, 0755);
	$d = dir($source);
	$nav_folders = array('.', '..');
	while (false !== ($file_entry = $d->read() ))
	{
		if (in_array($file_entry, $nav_folders))
			continue;

		$s = "$source/$file_entry";
		$t = "$target/$file_entry";
		copyDirectory31($s, $t);
	}
	$d->close();
}

function recursiveDeleteOnDisk31($dir)
{
	if (strpos(realpath($dir), realpath(_PS_MODULE_DIR_)) === false)
		return;
	if (is_dir($dir))
	{
		$objects = scandir($dir);
		foreach ($objects as $object)
			if ($object != '.' && $object != '..')
			{
				if (filetype($dir.'/'.$object) == 'dir')
					recursiveDeleteOnDisk31($dir.'/'.$object);
				else
					unlink($dir.'/'.$object);
			}
		reset($objects);
		rmdir($dir);
	}
}

?>
