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

require_once(_PS_MODULE_DIR_.'seur/AdminSeur.php');

function upgrade_module_1_2_0($module)
{
	 
	$fields = DB::getInstance()->executeS('DESCRIBE '._DB_PREFIX_.'seur_order');
	if (is_array($fields))
		foreach($fields as $field)
			$field_installed[] = $field['Field'];
	
	
	if (!in_array('printed_pdf',$field_installed))
	{
		$sql='ALTER TABLE '._DB_PREFIX_.'seur_order ADD printed_pdf int(1) NOT NULL DEFAULT 1';
		Db::getInstance()->execute($sql);
	}
    
	if (!in_array('printed_label',$field_installed))
	{
		$sql='ALTER TABLE '._DB_PREFIX_.'seur_order ADD printed_label int(1) NOT NULL DEFAULT 1';
		Db::getInstance()->execute($sql);
	} 
	if (!in_array('codfee',$field_installed))
	{
		$sql='ALTER TABLE '._DB_PREFIX_.'seur_order ADD codfee decimal(13,6)';
		Db::getInstance()->execute($sql);
	}
    if (!in_array('id_address_delivery',$field_installed))
	{
		$sql='ALTER TABLE '._DB_PREFIX_.'seur_order ADD id_address_delivery int(11) NOT NULL';
		Db::getInstance()->execute($sql);
	}
    if (!in_array('total_paid',$field_installed))
	{
		$sql='ALTER TABLE '._DB_PREFIX_.'seur_order ADD total_paid decimal(20,6) NOT NULL DEFAULT 0';
		Db::getInstance()->execute($sql);
	}	
	
	AdminSeur::uninstallSeurCashOnDelivery();
	AdminSeur::installSeurCashOnDelivery();
    
	return $module;
}

?>
