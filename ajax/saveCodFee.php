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
require_once(dirname(__FILE__).'/../classes/SeurLib.php');
require_once(dirname(__FILE__).'/../classes/Rate.php');

$id_order = Tools::getValue('id_order', 0);
$codfee = Tools::getValue('codfee',null);
if ($codfee)
	$codfee = str_replace(',','.',$codfee);
$out = array();
if($codfee!=null && $id_order &&saveCodFee($id_order,$codfee))
	$out['result'] = 'OK';
else
	$out['result'] = 'NOK';
echo Tools::jsonEncode($out);
// die();	

function saveCodFee($id_order, $codfee)
{
	if($codfee!=null && !$id_order) 
		return false;
	$context = Context::getContext();	
	$order = new Order($id_order);
	
	$query = new DBQuery();
	$query->select('codfee');
	$query->from('seur_order');
	$query->where('id_order = '.$id_order);
	$old_fee = DB::getInstance()->getValue($query->build());
	$old_fee_rate= Tools::ps_round(($old_fee - ($old_fee/((float)($order->carrier_tax_rate/100)+1))),2);
	$old_fee_net = Tools::ps_round(($old_fee - $old_fee_rate),2);
	$total_shipping_tax_excl = $order->total_shipping_tax_excl - $old_fee_net;
	$total_shipping_tax_incl = $order->total_shipping_tax_incl - $old_fee;
	
	$codfee_rate = Tools::ps_round(($codfee - ($codfee/((float)($order->carrier_tax_rate/100)+1))),2);
	$codfee_net = Tools::ps_round(($codfee - $codfee_rate),2);
	$order->total_shipping_tax_excl = $total_shipping_tax_excl + $codfee_net;
	$order->total_shipping_tax_incl = $total_shipping_tax_incl + $codfee;
	$order->total_shipping = $order->total_shipping_tax_incl;
		
	$order->total_paid = ($order->total_products_wt + $order->total_wrapping_tax_incl + $order->total_shipping_tax_incl) - $order->total_discounts_tax_incl;
	$order->total_paid_tax_excl = ($order->total_products + $order->total_wrapping_tax_excl + $order->total_shipping_tax_excl) - $order->total_discounts_tax_excl;
	$order->total_paid_tax_incl = ($order->total_products_wt + $order->total_wrapping_tax_incl + $order->total_shipping_tax_incl) - $order->total_discounts_tax_incl;
	$order->total_paid_real = ($order->total_products_wt + $order->total_wrapping_tax_incl + $order->total_shipping_tax_incl) - $order->total_discounts_tax_incl;
	if ($order->save())
	{
			if ($order->invoice_number)
			{
				$invoice = new OrderInvoice($order->invoice_number);
				$invoice->total_paid_tax_excl = $order->total_paid_tax_excl;
				$invoice->total_paid_tax_incl = $order->total_paid_tax_incl;
				$invoice->total_shipping_tax_incl = $order->total_shipping_tax_incl;
				$invoice->total_shipping_tax_excl = $order->total_shipping_tax_excl;
				$invoice->save();
				
				$params = array();
				$params['amount'] = $order->total_paid;
				DB::getInstance()->update('order_payment', $params, ' order_reference="'.$order->reference.'"');
			}
		$params = array();
		$params['shipping_cost_tax_excl'] = $order->total_shipping_tax_excl;
		$params['shipping_cost_tax_incl'] = $order->total_shipping_tax_incl;
		DB::getInstance()->update('order_carrier', $params, ' id_order='.$id_order.' AND id_carrier = '.$order->id_carrier);
		return DB::getInstance()->update('seur_order',array('codfee'=>($codfee)),' id_order = '.$id_order);
	}
	
	return false;
}

	