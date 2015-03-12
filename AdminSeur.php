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

if (!defined('_PS_VERSION_'))
	exit;

class AdminSeur extends AdminTab {

	const FILENAME = 'AdminSeur';

	public $output = '';
	public $content = '';
	private $module_instance;
	private $ps14_tab = '&tab=AdminSeur';
	private $current_controller = 'AdminSeur';
	private $ps14 = true;

	public $module_enabled_and_configured = false;

	public function __construct($ps14 = true)
	{
		$this->table = 'seur_configuration';
		$this->className = 'seur_configuration';

		$this->lang = true;
		$this->edit = true;
		$this->delete = true;

		$this->context = Context::getContext();
		$this->module_instance = Module::getInstanceByName('seur');
		if (!$ps14)
		{
			$this->ps14 = false;
			$this->ps14_tab = '';
			$this->current_controller = 'AdminSeur15';
		}

		if (!isset($this->fields_list))
			$this->fields_list = array();

		if (Configuration::get('SEUR_Configured'))
			$this->module_enabled_and_configured = true;

		parent::__construct();
	}

	public function displayModuleConfigurationWarning()
	{
		if ($this->ps14)
			$this->context->smarty->assign('ps_14', true);

		if (!version_compare(_PS_VERSION_, '1.6', '<'))
			$this->context->smarty->assign('ps_16', true);

		$this->context->smarty->assign(array(
			'seur_warning_message' => $this->module_instance->l('Please, first configure your SEUR module as a merchant.', self::FILENAME),
			'module_instance' => $this->module_instance
		));
		$this->content = $this->context->smarty->fetch(_PS_MODULE_DIR_.'seur/views/templates/admin/warning_message.tpl');
	}

	public function display()
	{
		if (!$this->module_enabled_and_configured)
			$this->displayModuleConfigurationWarning();
		else
			$this->initContent();

		echo $this->content;
	}

	public function initContent()
	{
		$this->display = 'view';
		if (Tools::getValue('verDetalle'))
		{
			$response = Expedition::getExpeditions($this->getExpeditionData());
			$this->tpl_view_vars = array('datos' => $this->displayFormDeliveries($response, true));
		}
		elseif (Tools::getValue('createPickup'))
		{
			$error_response = Pickup::createPickup();

			if (!empty($error_response))
				$this->tpl_view_vars = array('datos' => $this->displayFormDeliveries(null, null, $error_response));
			else
				$this->tpl_view_vars = array('datos' => $this->displayFormDeliveries());
		}
		elseif (Tools::getValue('submitFilter'))
		{
			$response = Expedition::getExpeditions($this->getExpeditionData());
			$this->tpl_view_vars = array('datos' => $this->displayFormDeliveries($response, false));
		}
		elseif (Tools::getValue('generateLabel'))
		{
			$this->printLabels(Tools::getValue('id_orders',array()), 'txt');
			die();			
		}
	}

	public function getExpeditionData()
	{
		$expedition_data = array();

		Tools::safePostVars();

		if (Tools::isSubmit('start_date'))
			$expedition_data['start_date'] = Tools::getValue('start_date');

		if (Tools::isSubmit('end_date'))
			$expedition_data['end_date'] = Tools::getValue('end_date');

		if (Tools::isSubmit('expedition_number'))
			$expedition_data['expedition_number'] = Tools::getValue('expedition_number');

		if ((Tools::isSubmit('reference_number')) && (Tools::getValue('reference_number')) > 0)
			$expedition_data['reference_number'] = sprintf('%06d', Tools::getValue('reference_number'));
		else
			$expedition_data['reference_number'] = '';

		if (Tools::isSubmit('order_state'))
			$expedition_data['order_state'] = Tools::getValue('order_state');

		return $expedition_data;
	}

	public function displayFormDeliveries($response = null, $detail = null, $error = null)
	{
		$token = Tools::getValue('token');
		$back = Tools::safeOutput($_SERVER['REQUEST_URI']);

		$seur_order_states = array(
			'' => $this->module_instance->l('All', self::FILENAME),
			'1' => $this->module_instance->l('Delivered', self::FILENAME),
			'2' => $this->module_instance->l('In transit', self::FILENAME),
			'3' => $this->module_instance->l('Incidents fixable by customer', self::FILENAME),
			'4' => $this->module_instance->l('Incident management SEUR', self::FILENAME),
			'5' => $this->module_instance->l('Returned', self::FILENAME),
			'6' => $this->module_instance->l('Sinister', self::FILENAME),
			'7' => $this->module_instance->l('Canceled', self::FILENAME)
		);

		Tools::safePostVars();

		if (empty($_POST))
		{
			$delivery_valuend_data = date('d-m-Y');
			$start_data = strtotime('-1 day', strtotime(date('Y-m-d')));
			$start_data = date('d-m-Y', $start_data);
		}
		else
		{
			$start_data = Tools::getValue('start_date');
			$delivery_valuend_data = Tools::getValue('end_date');
		}

		if ($response == null && $detail == null)
			$tab_view = 'deliveries';
		elseif ($response == true && $detail == null)
			$tab_view = 'deliveries';
		elseif ($response == true && $detail == true)
			$tab_view = 'deliveries';

		$ps_version = 'ps'.(version_compare(_PS_VERSION_, '1.5', '>=') > 1.4 ? '5' : '4');
		$img_dir = __PS_BASE_URI__.'modules/seur/img/';

		if (!empty($error))
			$this->content .= $this->module_instance->displayError($error);

		if (Tools::getValue('error'))
			$this->content .= $this->module_instance->displayError(Tools::getValue('codigo').' => '.Tools::getValue('error'));

		$this->content .= "<div id='contenttab'>";

		if(_PS_VERSION_ > '1.5')
		{
			$this->content .= "<script>
				$( document ).ready(function() {
					$('#submitFilter').click(function(){
						document.formfilter.submit();
					});
				});
			</script>";
		}
		
		$this->content .= "<fieldset>
				<legend>
					<img src='$img_dir/logonew.png' />
			 	</legend>
				<div id='seur_module' class='$ps_version'>
					<ul class='configuration_menu'>
					  ".((!SeurLib::getConfigurationField('print_type'))?"<li class='button btnTab".($tab_view == 'labels' ? ' active' : '' )."' tab='label'>
							<img src='$img_dir/config.png' alt=".$this->module_instance->l('Label', self::FILENAME).' title='.$this->module_instance->l('Label', self::FILENAME).' />
							'.$this->module_instance->l('Label', self::FILENAME)."
						</li>":"")."
						
						<li class='button btnTab".($tab_view == 'deliveries' ? ' active' : '' )."' tab='deliveries'>
							<img src='$img_dir/config.png' alt=".$this->module_instance->l('Shipments', self::FILENAME).' title='.$this->module_instance->l('Shipments', self::FILENAME).' />
							'.$this->module_instance->l('Shipments', self::FILENAME)."
						</li>
						<li class='button btnTab".($tab_view == 'packing_list' ? ' active' : '' )."' tab='packing_list'>
							<img src='$img_dir/manifest.png' alt='".$this->module_instance->l('Packing List', self::FILENAME)."' title='".$this->module_instance->l('Packing List', self::FILENAME)."' />
							".$this->module_instance->l('Packing List', self::FILENAME)."
						</li>
						<li class='button btnTab".($tab_view == 'pickups' ? ' active' : '' )."' tab='pickups'>
							<img src='$img_dir/recogidas.png' alt='".$this->module_instance->l('Pickups', self::FILENAME)."' title='".$this->module_instance->l('Pickups', self::FILENAME)."' />
							".$this->module_instance->l('Pickups', self::FILENAME)."
						</li>
					</ul>
					<ul class='configuration_tabs'>
						<li id='deliveries'".($tab_view == 'deliveries' ? ' class="default"' : '').">
							<form action='index.php?controller=".$this->current_controller.'&submitFilter=1&token='.$token.$this->ps14_tab."' method='post' id='formfilter' name='formfilter'>
								<table id='deliveriesTable' class='table' cellpadding='0' cellspacing='0'>
									<thead>
										<tr> 
											<th>".$this->module_instance->l('Reference number', self::FILENAME).'</th>
											<th>'.$this->module_instance->l('Expedition number', self::FILENAME).'</th>
											<th>'.$this->module_instance->l('Start date', self::FILENAME).'</th>
											<th>'.$this->module_instance->l('End date', self::FILENAME)."</th>
											<th colspan='5'>".$this->module_instance->l('Estate', self::FILENAME)."</th>
										</tr>
										<tr class='filtros'>
											<td><input class='ps14_input' type='text' name='reference_number' value='' autocomplete='off' /></td>
											<td><input class='ps14_input' type='text' name='expedition_number' value='' autocomplete='off' /></td>
											<td><input class='ps14_input' type='text' name='start_date' id='start_date' autocomplete='off' value='".$start_data."'/></td>
											<td><input class='ps14_input' type='text' name='end_date' id='end_date' class='datepicker' autocomplete='off' value='".$delivery_valuend_data."'/></td>
											<td colspan='4'>
												<select id='order_state' name='order_state' value='' autocomplete='off'>";
												foreach ($seur_order_states as $key => $seur_order_state)
													$this->content .= "<option value='$key'>$seur_order_state</option>";
												$this->content .= "</select>
											</td>
											<td>
												<input type='submit' value=".$this->module_instance->l('Filter', self::FILENAME)." name='submitFilter' id='submitFilter' class='filter' />
											</td>
										</tr>
									</thead>";
		if (($response == true) && ($detail == null))
		{
			$string_xml = htmlspecialchars_decode($response->out);
			$string_xml = str_replace('&', '&amp; ', $string_xml);
			$xml = simplexml_load_string($string_xml);

			if ($xml->DESCRIPCION)
				$this->content .= $this->module_instance->displayError($xml->DESCRIPCION);
			else
			{
				if ($xml->attributes()->NUM[0] != 0)
				{
					$deliveries_data = array();

					foreach ($xml->EXPEDICION as $delivery)
					{
						$headers = array(
							'order' => $this->module_instance->l('Order/Reference', self::FILENAME),
							'expedition' => $this->module_instance->l('Expedition', self::FILENAME),
							'name' => $this->module_instance->l('Name'),
							'description' => $this->module_instance->l('Description', self::FILENAME),
							'date' => $this->module_instance->l('Date', self::FILENAME),
							'delivery' => $this->module_instance->l('Delivery', self::FILENAME),
							'details' => $this->module_instance->l('Details', self::FILENAME)
							);

						$headersOcultas = array('EXPEDICION','DESTINA_PAIS' => (string)$delivery->DESTINA_PAIS);

						$deliveries_data[] = array(
							'Pedido/Referencia' => (string)$delivery->REMITE_REF,
							'Expedicion' => (string)$delivery->EXPEDICION_NUM,
							'Nombre' => (string)$delivery->DESTINA_NOMBRE,
							'Descripcion' => (string)$delivery->DESCRIPCION_PARA_CLIENTE,
							'date' => (string)$delivery->FECHA_CAPTURA,
							'EXPEDICION' => (string)$delivery->EXPEDICION_NUM,
							'Detalles' => '',
						);
					}

					$this->content .= "<tbody>
						<tr class='bold'>";

					foreach ($headers as $key => $header)
						$this->content .= '<th '.($key == 'delivery' || $key == 'details' ? 'colspan="2"' : '' ).'>'.$header.'</th>';

					$this->content .= '</tr>';
					$line = 1;
					$countryTo = '';

					foreach ($deliveries_data as $delivery_data)
					{
						$this->content .= '<tr '.(($line % 2 != 0) ? 'class="alternate"' : '').'>';
						$delivered = false;

						foreach ($delivery_data as $key => $delivery_value)
						{
							if ($key == 'Expedicion')
								$delivery_number = $delivery_value;

							$this->content .= '<td class='.$key.' '.($key == 'EXPEDICION' || $key == 'Detalles' ? 'colspan="2"' : '' ).'>'.( !in_array($key, $headersOcultas) ? $delivery_value : '' );

							if ($key == 'Descripcion' && $delivery_value == 'ENTREGA EFECTUADA')
								$delivered = true;

							if ($key == 'EXPEDICION' && ($countryTo == 'ES' || $countryTo == '-' || $countryTo == '') && $delivered)
							{
								$this->content .= '<a href="../modules/seur/ajax/createDeliveryNote.php?back='.$back.'&token='.Tools::getValue('token').'&expedition_number='.$delivery_value.'&token='.$token.'&id_employee='.(int)$this->context->cookie->id_employee.'">
										<img src="'.$img_dir.'/png_ico.png" alt="'.$this->module_instance->l('Delivery', self::FILENAME).'" title="'.$this->module_instance->l('Delivery', self::FILENAME).'" />
									</a>
									<!--a class="verDetalles" href="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'&verDetalle=1&token='.$token.'&expedition_number='.$delivery_value.'&id_employee='.(int)$this->context->cookie->id_employee.'"-->';
							}

							if ($key == 'Detalles')
								$this->content .= '<a class="verDetalles" href="'.__PS_BASE_URI__.'modules/seur/ajax/getExpeditionAjax.php?expedition_number='.$delivery_number.'&token='.$token.'&id_employee='.(int)$this->context->cookie->id_employee.'">
										<img src="'.$img_dir.'/details.png" alt="'.$this->module_instance->l('See details', self::FILENAME).'" title="'.$this->module_instance->l('See details', self::FILENAME).'" />
									</a>';
							$this->content .= '</td>';

						}
						$this->content .= '</tr>';
						$line++;
					}
				}
				else
					$this->content .= $this->module_instance->displayError($this->module_instance->l('No results.', self::FILENAME));

				$this->content .= ' </tbody>';
			}
		}

		$this->content .= '</table>
				</form>
			</li>
			<li id="packing_list"'.( $tab_view == 'packing_list' ? '  class="default"' : '' ).'>
				<table class="table" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th>'.$this->module_instance->l('Download today packing list', self::FILENAME).'</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<a href="../modules/seur/ajax/createPackingList.php?back='.$back.'&token='.Tools::getValue('token').'&id_employee='.$this->context->cookie->id_employee.'" target="_blank">
							<img src="'.$img_dir.'/ico_descargar.png" alt="'.$this->module_instance->l('Packing List', self::FILENAME).'" />'.$this->module_instance->l('Download', self::FILENAME).'</a>
						</td>
					</tr>
				</tbody>
				</table>
			</li>';

		$this->content .= '<li id="pickups"'.( $tab_view == 'pickups' ? 'class="default"' : '' ).'>
			<table class="table" cellspacing="0">
			<thead>';

		$pickup_data = Pickup::getLastPickup();
		$steady_pickup = false;

		if ($pickup_data)
			$pickup_date = explode(' ', $pickup_data['date']);

		if (SeurLib::getConfigurationField('pickup') == 1)
			$steady_pickup = true;

		if (!empty($pickup_data) && strtotime(date('Y-m-d')) == strtotime($pickup_date[0]) && !$steady_pickup)
		{
			$this->content .= '<tr>
						<th>'.$this->module_instance->l('Localizer', self::FILENAME).'</th>
						<th colspan="2">'.$this->module_instance->l('Date', self::FILENAME).'</th>
					</tr>
				</thead>
				<tbody>
					<tr >
					   <td>'.$pickup_data['localizer'].'</td>
					   <td>'.$pickup_data['date'].'</td>
					</tr>
				</tbody>';
		}
		elseif ((int)date('H') < 14 && !$steady_pickup)
		{
			$this->content .= '<tr>
					<td class="createpickup">
						<a href="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'&createPickup=1">'.$this->module_instance->l('Create pickup', self::FILENAME).'</a>
					</td>
					</tr>';
		}
		elseif ($steady_pickup)
		{
			$this->content .= '<tr>
					<th>'.$this->module_instance->l('Fixed pickup.', self::FILENAME).'</th>
					</tr>';
		}
		elseif ((int)date('H') >= 14)
		{
			$this->content .= '<tbody>
						<tr>
							<td>
							<p><img src="../img/admin/help2.png" /> 
							   '.$this->module_instance->l('14H is past, to create a pickup please contact SEUR on 902101010 or via ', self::FILENAME).'
							</p>
							<p><a href="http://www.seur.com" target="_blank">www.seur.com</a></p>
							<p>'.$this->module_instance->l('Thank you.', self::FILENAME).'</p>
							</td>
						</tr>
						</tbody>';
		}
		$start_date = Tools::getValue('start_date_order','');
		$end_date = Tools::getValue('end_date_order','');
		$reference = Tools::getValue('reference','');
		$ID = Tools::getValue('ID','');
		$firstname = Tools::getValue('firstname','');
		$lastname = Tools::getValue('lastname','');
		$address = Tools::getValue('address','');
		$postcode = Tools::getValue('postcode','');
		$city = Tools::getValue('city','');
		$state = Tools::getValue('state','');
		$country = Tools::getValue('country','');


		$this->content .= '</thead>
					</table>
				</li>
				<li id="label" '.( $tab_view == 'label' ? 'class="default"' : '' ).'>
							<form action="index.php?controller='.$this->current_controller.'&generateLabel=1&token='.$token.$this->ps14_tab.'" method="post" target="_blank">
					<!--<form action="index.php?controller='.$this->current_controller.'&submitFilter=1&token='.$token.$this->ps14_tab.'" method="post" id="formfilter" name="formfilter">
						-->
						<table id="labelTable" class="table" cellpadding="0" cellspacing="0" >
							<thead>
								<tr>
											<th>'.$this->module_instance->l('ID', self::FILENAME).'</th>
											<th>'.$this->module_instance->l('Reference number', self::FILENAME).'</th>
											<th>'.$this->module_instance->l('Start Date', self::FILENAME).' / '.$this->module_instance->l('End Date', self::FILENAME).'</th>
											<th>'.$this->module_instance->l('Name', self::FILENAME).'</th>
											<th>'.$this->module_instance->l('Address', self::FILENAME).'</th>
											<th>'.$this->module_instance->l('Postal code', self::FILENAME).'</th>
											<th>'.$this->module_instance->l('City', self::FILENAME).'</th>
											<th>'.$this->module_instance->l('State', self::FILENAME).'</th>
											<th>'.$this->module_instance->l('Country', self::FILENAME).'</th>
											<th>'.$this->module_instance->l('Printed label', self::FILENAME).'</th>
											<th></th>
								</tr>
								<tr class="filtros">
											<td><input class="ps14_input" type="text" name="id" value="'.$ID.'" autocomplete="off" /></td>
											<td><input class="ps14_input" type="text" name="reference" value="'.$reference.'" autocomplete="off" /></td>
											<td style="min-width:160px;"><span>'.$this->module_instance->l('Desde', self::FILENAME).'</span>&nbsp;<input class="datepicker" type="text" name="start_date_order" id="start_date_order" autocomplete="off" value="'.$start_date.'"/><br><span>'.$this->module_instance->l('Hasta', self::FILENAME).'</span>&nbsp;&nbsp;<input class="datepicker" type="text" name="end_date_order" id="end_date_order" autocomplete="off" value="'.$end_date.'"/></td>
											<td><input class="ps14_input" type="text" name="name" id="firstname" autocomplete="off" value="'.$firstname.' '.$lastname.'"/></td>
											<td><input class="ps14_input" type="text" name="address" id="address" autocomplete="off" value="'.$address.'"/></td>
											<td><input class="ps14_input" type="text" name="postcode" id="postcode" autocomplete="off" value="'.$postcode.'"/></td>
											<td><input class="ps14_input" type="text" name="city" id="city" autocomplete="off" value="'.$city.'"/></td>
											<td><input class="ps14_input" type="text" name="state" id="state" autocomplete="off" value="'.$state.'"/></td>
											<td><input class="ps14_input" type="text" name="country" id="couuntry" autocomplete="off" value="'.$country.'"/></td>
											<td><input  type="checkbox"   id="printed_label" autocomplete="off" value=""/></td>
											<td><a class="filter" id="labelsFilter">'.$this->module_instance->l('Filtrar', self::FILENAME).'</a></td>
								</tr>
							</thead>
							<tbody>';
								$orders = $this->getAllOrders();
								if (is_array($orders))
								{
									foreach($orders as $order)
										$this->content .= '<tr><td>'.$order['id_order'].'</td><td>'.$order['reference'].'</td><td>'.date('d-m-Y H:i:s', strtotime($order['date_add'])).'</td><td>'.$order['firstname'].' '.$order['lastname'].' </td><td>'.$order['address1'].' '.$order['address2'].' </td><td>'.$order['postcode'].' </td><td>'.$order['city'].' </td><td>'.$order['state'].' </td><td>'.$order['country'].' </td><td><input type="checkbox" name="id_orders[]" value="'.$order['id_order'].'"/></td><td></td></tr>';	
								
									 	$this->content .= '<tr><td colspan="10"></td><td colspan="1"><input  class="button btnTab" type="submit"  value="Imprimir etiqueta"></td></tr>';	
								}
						$this->content .= '</tbody>
						</table>
						<script type="text/javascript">
							$(document).ready(function(){
								
									$(\'#printed_label\').live(\'click\',function(){
											if($(this).is(\':checked\'))
											{
												$(\'input[name^=id_orders]\').attr(\'checked\',\'checked\');
											}
											else
											{
												$(\'input[name^=id_orders]\').removeAttr(\'checked\');
											}
										
									
									});
								'.(version_compare(_PS_VERSION_, '1.6', '<')?'$(\'#start_date_order,#end_date_order,\').datepicker({ showAnim : \'slideDown\', dateFormat : \'dd-mm-yy\' , \'onSelect\': function(dateText, inst){$(this).trigger(\'change\')}});':'').'	
								
							
								$(\'#labelsFilter\').click(function(){
											params = {};
										  $.each($(\'#labelTable .filtros input[type=text]\'), function(key, item){
												params[item.name] = item.value;
										  });
										  $.ajax(
												{
													url: \''._MODULE_DIR_.'seur/ajax/getLabelAjax.php\',
													data: params,
													type: \'POST\',
													async: true,
													dataType: \'json\',
													success: function (jsonData)
													{
														$(\'#labelTable tbody\').html(jsonData.response);
													},
											});
								});							
							});
						</script>
					</form>
					</li>
					</ul>
				</div>
	  </fieldset>

	  </div>
				';

	}
	public function getAllOrders($id = null, $reference = null, $date_start = null, $date_end = null, $name = null, $address = null, $postal_code = null, $city  = null, $state_name = null, $country = null)
	{
		$sql = 'SELECT o.reference,o.id_order,o.date_add,o.reference, a.firstname, a.lastname, a.address1, a.address2, a.postcode, a.city, cl.name as country, s.name as state FROM '._DB_PREFIX_.'orders o INNER JOIN '._DB_PREFIX_.'seur_order so ON so.id_order  = o.id_order  INNER JOIN '._DB_PREFIX_.'address a ON o.id_address_delivery = a.id_address INNER JOIN '._DB_PREFIX_.'country_lang cl ON cl.id_country = a.id_country AND cl.id_lang ='.$this->context->language->id.' LEFT JOIN '._DB_PREFIX_.'state s ON s.id_state = a.id_state ';
		$where = array();
		if ((int)$id > 0)
			$where[] = ' o.id_order ='.$id.' ';
		
		if (trim($reference) != '')
			$where[] = ' o.reference like \''.$reference.'\' ';
		
		
		if (trim($date_start) != '' && trim($date_end) != '' )
			$where[] = ' o.date_add between \''.$date_start.'\' AND   \''.$date_end.'\' ';
			
		if (trim($date_start) != '' && trim($date_end) == '' )
			$where[] = ' o.date_add >= \''.$date_start.'\'  ';
			
		if (trim($date_start) == '' && trim($date_end) != '' )
			$where[] = ' o.date_add <= \''.$date_start.'\'  ';
			
		if (trim($name) != ''  )
			$where[] = ' ( a.firstname like = \'%'.$name.'%\'  OR a.lastname like = \'%'.$name.'%\'  )  ';
		
		if (trim($address) != ''  )
			$where[] = ' ( a.address1 like = \'%'.$address.'%\'  OR a.address2 like = \'%'.$address.'%\'  )  ';
		
		
		if (trim($postal_code) != ''  )
			$where[] = ' a.post_code like = \'%'.$postal_code.'%\'   ';
		
		if (trim($city) != ''  )
			$where[] = ' a.city like = \'%'.$city.'%\'   ';
		
		if (trim($state_name) != ''  )
			$where[] = ' a.state like = \'%'.$state_name.'%\'   ';
		
		if (trim($country) != ''  )
			$where[] = ' a.country like = \'%'.$country.'%\'   ';
		
		if(!empty($where))
			$sql.= ' WHERE '.implode(' AND ', $where);
			
		$sql.= ' ORDER BY o.id_order DESC';
		
		return DB::getInstance()->executeS($sql);
	
	}
	public function printLabels($id_orders = array(), $type = 'txt')
	{
		try
		{
				ob_end_clean();
				header('Content-type: text/plain');
				header('Content-Disposition: attachment; filename=seur_labels-'.date('Y-m-d h:i:s', strtotime('now')).'.txt');
				header('Content-Transfer-Encoding: binary');
				header('Accept-Ranges: bytes');
		if(!is_array($id_orders)) $id_orders = (array) $id_orders;
		$directory = _PS_MODULE_DIR_.'seur/files/deliveries_labels/';
		if ($type == 'txt')
		{

				if(is_array($id_orders))
				foreach ($id_orders as $id_order)
				{
					$name = sprintf('%06d', (int)$id_order);
					if (!file_exists($directory.$name.'.txt') || !($fp = Tools::file_get_contents($directory.$name.'.txt')))
					{
									
							$data_label = $this->getLabelData($id_order);
							if (is_array($data_label))
							{
							
								// if ($this->isPrinted((int)$id_order, true))
								// {
								// $success = true;
								// }
								// else
								// {
								// echo $name;
								$success = Label::createLabels($data_label, 'zebra');
								// }
							
								if ($success === true)
								{
								
									// if ($this->setAsPrinted((int)$id_order, true))
											// $this->printLabel((int)array($id_order), 'txt');
									$this->setAsPrinted((int)$id_order, true);		
								}
						
						}
					
					}
				}
			  if(is_array($id_orders))
				foreach ($id_orders as $id_order)
				{
					$name = sprintf('%06d', (int)$id_order);
			
					if (file_exists($directory.$name.'.txt') && ($fp = Tools::file_get_contents($directory.$name.'.txt')))
					{
						
						echo $fp;				
					}
				}
		}
		elseif ($type == 'pdf')
		{
			if (file_exists($directory.$name.'.pdf') && ($fp = Tools::file_get_contents($directory.$name.'.pdf')))
			{
				ob_end_clean();
				header('Content-type: application/pdf');
				header('Content-Disposition: inline; filename='.$name.'.pdf');
				header('Content-Transfer-Encoding: binary');
				header('Accept-Ranges: bytes');

				echo $fp;
			}
		}
		
		}catch(Exception $e)
		{
		   echo $e->getMessage();
		
		}
		exit;	
		$this->context->smarty->assign('error', $this->l('Document was already printed, but is missing in module directory'));
	}
		private function isPrinted($id_order, $label = false)
	{
		$field = $label ? 'printed_label' : 'printed_pdf';

		return DB::getInstance()->getValue('
			SELECT `'.bqSQL($field).'`
			FROM `'._DB_PREFIX_.'seur_order`
			WHERE `id_order` = "'.(int)$id_order.'"
		');
	}
	
	private function setAsPrinted($id_order, $label = false)
	{
		$field = $label ? 'printed_label' : 'printed_pdf';

		return DB::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'seur_order`
			SET `'.bqSQL($field).'` = 1
			WHERE `id_order` = "'.(int)$id_order.'"
		');
	}
	private function getLabelData($id_order)
	{
					$label_data = false;
					$cookie = $this->context->cookie;
					$order = new Order($id_order);
					$customer = new Customer($order->id_customer);
					$address_delivery = new Address($order->id_address_delivery);
					
					$carrier_pos = SeurLib::getSeurCarrier('SEP');
					$datospos = '';
					$products = $order->getProductsDetail();
					$order_weigth = 0;
					foreach ($products as $product)
							$order_weigth += (float)$product['product_weight'] * (float)$product['product_quantity'];

					$order_weigth = ($order_weigth < 1.0 ? 1.0 : (float)$order_weigth);
					$name = $address_delivery->firstname.' '.$address_delivery->lastname;
					$direccion = $address_delivery->address1.' '.$address_delivery->address2;
					$newcountry = new Country((int)$address_delivery->id_country, (int)$cookie->id_lang);
					$iso_merchant = SeurLib::getMerchantField('country');
					$iso_country = Country::getIsoById((int)$address_delivery->id_country);
						if ($iso_country == 'PT')
						{
							$post_code = explode(' ', $address_delivery->postcode);
							$post_code = $post_code[0];
						}
						else
							$post_code = $address_delivery->postcode;

						$international_orders = SeurLib::getConfigurationField('international_orders');
						$date_calculate = strtotime('-14 day', strtotime(date('Y-m-d')));
						$date_display = date('Y-m-d H:m:i', $date_calculate);
				
					if ((!$international_orders && !($iso_country == 'ES' || $iso_country == 'PT' || $iso_country == 'AD')))	
					{
						return false;
					}
					
					$order_data = SeurLib::getSeurOrder((int)$order->id);
					$response_post_code = Town::getTowns($post_code);
					$order_weigth = ((float)$order_weigth != $order_data['peso_bultos'] ? (float)$order_data['peso_bultos'] : (float)$order_weigth);
				
					if ((int)$order->id_carrier == $carrier_pos['id'])
					{
						$datospos = SeurLib::getOrderPos((int)$order->id_cart);
						if (!empty($datospos))
						{
							$label_data = array(
								'pedido' => sprintf('%06d', (int)$order->id),
								'total_bultos' => $order_data['numero_bultos'],
								'total_kilos' => (float)$order_weigth,
								'direccion_consignatario' => $direccion,
								'consignee_town' => $datospos['city'],
								'codPostal_consignatario' => $datospos['postal_code'],
								'telefono_consignatario' => (!empty($address_delivery->phone_mobile) ? $address_delivery->phone_mobile : $address_delivery->phone),
								'movil' => $address_delivery->phone_mobile,
								'name' => $name,
								'companyia' => $datospos['company'],
								'email_consignatario' => Validate::isLoadedObject($customer) ? $customer->email : '',
								'dni' => $address_delivery->dni,
								'info_adicional' => $info_adicional_str,
								'country' => $newcountry->name,
								'iso' => $newcountry->iso_code,
								'cod_centro' => $datospos['id_seur_pos'],
								'iso_merchant' => $iso_merchant
							);
							$rate_data['cod_centro'] = $datospos['id_seur_pos'];
						}
					}
					else
					{
						$label_data = array(
						'pedido' => sprintf('%06d', (int)$order->id),
						'total_bultos' => $order_data['numero_bultos'],
						'total_kilos' => (float)$order_weigth,
						'direccion_consignatario' => $direccion,
						'consignee_town' => $address_delivery->city,
						'codPostal_consignatario' => $post_code,
						'telefono_consignatario' => (!empty($address_delivery->phone_mobile) ? $address_delivery->phone_mobile : $address_delivery->phone),
						'movil' => $address_delivery->phone_mobile,
						'name' => $name,
						'companyia' => (!empty($address_delivery->company) ? $address_delivery->company : ''),
						'email_consignatario' => Validate::isLoadedObject($customer) ? $customer->email : '',
						'dni' => $address_delivery->dni,
						'info_adicional' => $info_adicional_str,
						'country' => $newcountry->name,
						'iso' => $newcountry->iso_code,
						'iso_merchant' => $iso_merchant,
						'admin_dir' => utf8_encode(_PS_ADMIN_DIR_),
						'id_employee' => $cookie->id_employee,
						'token' => Tools::getAdminTokenLite('AdminOrders'),
						'back' => $back
					);
					
					}
					if (strcmp($order->module, 'seurcashondelivery') == 0)
					{
						$label_data['reembolso'] = (float)$order_data['total_paid'];
					}
			
					return $label_data;
	
	
	}
	
	
	
}