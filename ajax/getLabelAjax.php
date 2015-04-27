<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');


$token = Tools::getValue('token');
$admin_token = Tools::getAdminToken('AdminSeur'.(int)Tab::getIdFromClassName('AdminSeur').(int)Tools::getValue('id_employee'));



$module_instance = Module::getInstanceByName('seur');

try
{
	$id = Tools::getValue('id', null);
	$reference = Tools::getValue('reference', null);
	$start_date = Tools::getValue('start_date_order', null);
	$end_date = Tools::getValue('end_date_order', null);
	$name = Tools::getValue('name', null);
	if($name!=null)
	   $name = rtrim(ltrim($name));
	$address = Tools::getValue('address', null);
	$postcode = Tools::getValue('postcode', null);
	$city = Tools::getValue('city', null);
	$country = Tools::getValue('country', null);
	$state = Tools::getValue('state', null);
	$content = '';
								$orders = getAllOrders($id, $reference, $start_date, $end_date, $name, $address, $postcode, $city, $state, $country);
								if (is_array($orders) && !empty($orders))
								{
										foreach($orders as $order)
										$content  .= '<tr><td>'.$order['id_order'].'</td><td>'.$order['reference'].'</td><td>'.date('d-m-Y H:i:s', strtotime($order['date_add'])).'</td><td>'.$order['firstname'].' '.$order['lastname'].' </td><td>'.$order['address1'].' '.$order['address2'].' </td><td>'.$order['postcode'].' </td><td>'.$order['city'].' </td><td>'.$order['state'].' </td><td>'.$order['country'].' </td><td><input type="checkbox" name="id_orders[]" value="'.$order['id_order'].'"/></td><td></td></tr>';	
								
									 	$content  .= '<tr><td colspan="10"></td><td colspan="1"><input class="button btnTab" type="submit"  value="Imprimir etiqueta"></td></tr>';	
								}
								else
								{
									$content  .= '<tr><td colspan="11">'.$module_instance->l('Not results found').'</td></tr>';
									// .getQuery($id, $reference, $start_date, $end_date, $name, $address, $postal_code, $city, $state_name, $country).'</td></tr>';	
								}
					
  	
}
catch (PrestaShopException $e)
{
					$content  .= '<tr><td colspan="11">'.$module_instance ->l('Error to extract data, please try later').$e->getMessage().'</td></tr>';	
					
}

$out = array();
$out['response'] = $content;
echo Tools::jsonEncode($out);
die();
	
	function getAllOrders($id = null, $reference = null, $date_start = null, $date_end = null, $name = null, $address = null, $postal_code = null, $city  = null, $state_name = null, $country = null)
	{
		$sql = getQuery($id, $reference, $date_start, $date_end, $name, $address, $postal_code, $city, $state_name, $country);
		
		return DB::getInstance()->executeS($sql);
	
	}
	function getQuery($id = null, $reference = null, $date_start = null, $date_end = null, $name = null, $address = null, $postal_code = null, $city  = null, $state_name = null, $country = null)
	{
		$context = Context::getContext();
		$sql = 'SELECT o.reference,o.id_order,o.date_add,o.reference, a.firstname, a.lastname, a.address1, a.address2, a.postcode, a.city, cl.name as country, s.name as state FROM '._DB_PREFIX_.'orders o INNER JOIN '._DB_PREFIX_.'seur_order so ON so.id_order  = o.id_order  INNER JOIN '._DB_PREFIX_.'address a ON o.id_address_delivery = a.id_address INNER JOIN '._DB_PREFIX_.'country_lang cl ON cl.id_country = a.id_country AND cl.id_lang ='.(int)$context->language->id.' LEFT JOIN '._DB_PREFIX_.'state s ON s.id_state = a.id_state ';
		$where = array();
		if ((int)$id > 0)
			$where[] = ' o.id_order ='.(int)$id.' ';
		
		if (trim($reference) != '')
			$where[] = ' o.reference like \'%'.pSQL($reference).'%\' ';
		
		
		if (trim($date_start) != '' && trim($date_end) != '' )
			$where[] = ' o.date_add between \''.date('y-m-d', strtotime(pSQL($date_start))).'\' AND \''.date('y-m-d', strtotime(pSQL($date_end)." +1 days")).'\' ';
			
		if (trim($date_start) != '' && trim($date_end) == '' )
			$where[] = ' o.date_add >= \''.date('y-m-d', strtotime(pSQL($date_start))).'\'  ';
			
		if (trim($date_start) == '' && trim($date_end) != '' )
			$where[] = ' o.date_add <= \''.date('y-m-d', strtotime(pSQL($date_end))).'\'  ';
			
		if (trim($name) != ''  )
		{
			$terms = explode(' ',$name);
			$firstname_where = implode('%\'  OR a.firstname like \'%', $terms);	
			$lastname_where = implode('%\'  OR a.lastname like \'%',$terms);	
			$where[] = ' ( a.firstname like \'%'.pSQL($firstname_where).'%\'  OR a.lastname like \'%'.pSQL($lastname_where).'%\'  )  ';
		}
		
		if (trim($address) != ''  )
			$where[] = ' ( a.address1 like \'%'.pSQL($address).'%\'  OR a.address2 like \'%'.pSQL($address).'%\'  )  ';
		
		
		if (trim($postal_code) != ''  )
			$where[] = ' a.postcode like \'%'.pSQL($postal_code).'%\'   ';
		
		if (trim($city) != ''  )
			$where[] = ' a.city like \'%'.pSQL($city).'%\'   ';
		
		if (trim($state_name) != ''  )
			$where[] = ' s.name like \'%'.pSQL($state_name).'%\'   ';
		
		if (trim($country) != ''  )
			$where[] = ' cl.name like \'%'.pSQL($country).'%\'   ';
		
		if(!empty($where))
			$sql.= ' WHERE '.implode(' AND ', $where);
		
		$sql.= ' ORDER BY o.id_order DESC';
			
		return $sql;
	}