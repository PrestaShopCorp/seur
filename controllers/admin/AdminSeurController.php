<?php
/*
* 2007-2013 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @version  Release: 0.4.4
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) exit;

class AdminSeurController extends AdminController{

		public $output = '';

		/*
		public function setMedia(){
			parent::setMedia();
			Tools::addCSS(_PS_CSS_DIR_.'jquery.fancybox-1.3.4.css', 'screen');
			Tools::addJS(array(_PS_JS_DIR_.'jquery/jquery.fancybox-1.3.4.js', _PS_JS_DIR_.'jquery/jquery.serialScroll-1.2.2-min.js', _THEME_JS_DIR_.'product.js'));
			if (Configuration::get('PS_DISPLAY_JQZOOM') == 1) {
				Tools::addCSS(_PS_CSS_DIR_.'jqzoom.css', 'screen');
				Tools::addJS(_PS_JS_DIR_.'jquery/jquery.jqzoom.js');
			}
		}
		*/

        public function initContent()
        {
                $this->display = 'view';
            
                if (Tools::getValue('verDetalle'))
                {
                    $response = Expedition::getExpeditions($_POST);
                    $this->tpl_view_vars = array('datos' => $this->displayFormDeliveries($response,true));
                }
                elseif (Tools::getValue('createPickup'))
                {
                    $error_response = Pickup::createPickup();
                    if (!empty($error_response))
                        $this->tpl_view_vars = array('datos' => $this->displayFormDeliveries(null,null,$error_response));
                    else
                        $this->tpl_view_vars = array('datos' => $this->displayFormDeliveries());
                }
                elseif (Tools::getValue('submitFilter'))
                {
                    if (Tools::getValue('reference_number'))
                        $_POST['reference_number'] = str_pad((string)Tools::getValue('reference_number'), 6, '0', STR_PAD_LEFT);
                    $response = Expedition::getExpeditions($_POST);
                    $this->tpl_view_vars = array('datos' => $this->displayFormDeliveries($response,false));
                }
                else
                    $this->tpl_view_vars = array('datos' => $this->displayFormDeliveries());
            
                parent::initContent();
        }

        public function renderView()
        {
                    $helper = new HelperView($this);
                    $helper->base_folder = _PS_MODULE_DIR_.'seur/views/templates/admin/';
                    $helper->base_tpl = 'AdminSeur.tpl';

                    $this->setHelperDisplay($helper);
                    $helper->tpl_vars = $this->tpl_view_vars;
                    if (!is_null($this->base_tpl_view))
                            $helper->base_tpl = $this->base_tpl_view;
                    $view = $helper->generateView();

                    return $view;
        }


        public function displayFormDeliveries($response = null, $detail = null, $error = null)
        {
                
                $token = Tools::getValue('token');
                
                $back = Tools::safeOutput($_SERVER['REQUEST_URI']);
                
                $seur_order_states = array(
                    '' => $this->l('All'),
                    '1' => $this->l('Delivered'),
                    '2' => $this->l('In transit'),
                    '3' => $this->l('Incidents fixable by customer'),
                    '4' => $this->l('Incident management SEUR'),
                    '5' => $this->l('Returned'),
                    '6' => $this->l('Sinister'),
                    '7' => $this->l('Canceled')
                );
                
                if(empty($_POST))
                {
                        $before_days = 1;
                        $delivery_valuend_data = date('d-m-Y');
                        $start_data = mktime( 0,0,0, date('m', time()), date('d', time()) - $before_days, date('Y', time()) );
                        $start_data = date('d-m-Y',$start_data);
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
            
                /* 
                 * tabs ini 
                 */
                
                $ps_version = 'ps'.(version_compare(_PS_VERSION_, "1.5", ">=") > 1.4 ? '5' : '4');
				
                $img_dir = __PS_BASE_URI__.'modules/seur/img/';
                
                if (!empty($error))
                {
                        $this->content .= SeurLib::displayErrors ($error);
                }
                
                if (Tools::getValue('error'))
                {
                        $this->content .= SeurLib::displayErrors (Tools::getValue('codigo').' => '.Tools::getValue('error'));
                }
                
                $this->content .= "
                		<div id='contenttab'>
                		<fieldset>
                            <legend>
                                <img src='$img_dir/logonew.png' />
                         	</legend>
                        <div id='seur_module' class='$ps_version'>
                            <ul class='configuration_menu'>
                                <li class='button btnTab".($tab_view == 'deliveries' ? ' active' : '' )."' tab='deliveries'>
                                    <img src='$img_dir/config.png' alt=".$this->l('Shipments')." title=".$this->l('Shipments')." />
                                    ".$this->l('Shipments')."
                                </li>
                                <li class='button btnTab".($tab_view == 'packing_list' ? ' active' : '' )."' tab='packing_list'>
                                    <img src='$img_dir/manifest.png' alt='".$this->l('Packing List')."' title='".$this->l('Packing List')."' />
                                    ".$this->l('Packing List')."
                                </li>
                                <li class='button btnTab".($tab_view == 'pickups' ? ' active' : '' )."' tab='pickups'>
                                    <img src='$img_dir/recogidas.png' alt='".$this->l('Pickups')."' title='".$this->l('Pickups')."' />
                                    ".$this->l('Pickups')."
                                </li>
                            </ul>
                            <ul class='configuration_tabs'>
                                <li id='deliveries'".( $tab_view == 'deliveries' ? ' class="default"' : '' ).">
                                    <form action='index.php?controller=AdminSeur&submitFilter=1&token=$token' method='post'>
                                        <table id='deliveriesTable' class='table' cellpadding='0' cellspacing='0'>
                                            <thead>
                                                <tr> 
                                                    <th>".$this->l('Reference number')."</th>
                                                    <th>".$this->l('Expedition number')."</th>
                                                    <th>".$this->l('Start date')."</th>
                                                    <th>".$this->l('End date')."</th>
                                                    <th colspan='5'>".$this->l('Estate')."</th>
                                                </tr>
                                                <tr class='filtros'>
                                                    <td><input type='text' name='reference_number' value='' autocomplete='off' /></td>
                                                    <td><input type='text' name='expedition_number' value='' autocomplete='off' /></td>
                                                    <td><input type='text' name='start_date' id='start_date' value=".$start_data." autocomplete='off' /></td>
                                                    <td><input type='text' name='end_date' id='end_date' class='datepicker' value=".$delivery_valuend_data." autocomplete='off' /></td>
                                                    <td colspan='4'>
                                                        <select id='order_state' name='order_state' value='' autocomplete='off'>";
                                                        foreach ($seur_order_states as $key => $seur_order_state)
                                                            $this->content .= "<option value='$key'>$seur_order_state</option>";
                                      $this->content .= "</select>
                                                    </td>
                                                    <td>
                                                        <input type='submit' value=".$this->l('Filter')." name='submitFilter' class='filter' />
                                                    </td>
                                                </tr>
                                             </thead>";

                if ($response == true && $detail == null)
                {

                        $string_xml = htmlspecialchars_decode($response->out);
                        $string_xml = str_replace("&", "&amp; ", $string_xml);
                        $xml = simplexml_load_string($string_xml);

                        if ($xml->DESCRIPCION)
                                echo SeurLib::displayErrors($xml->DESCRIPCION);
                        else
                        {
                                if ($xml->attributes()->NUM[0] != 0)
                                {
                                    foreach ( $xml->EXPEDICION as $delivery)
                                    {
                                            $headers = array(
                                                    'order' => $this->l('Order/Reference'),
                                                    'expedition' => $this->l('Expedition'),
                                                    'name' => $this->l('Name'),
                                                    'description' => $this->l('Description'),
                                                    'date' => $this->l('Date'),
                                                    'delivery' => $this->l('Delivery'),
                                                    'details' => $this->l('Details')
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
                                    {
                                            $this->content .= '<th '.($key == 'delivery' || $key == 'details' ? 'colspan="2"' : '' ).'>'.$header.'</th>';
                                    }
                                    $this->content .= '</tr>';
                                    $line = 1;
                                    $countryTo = '';
                                    foreach ( $deliveries_data as $delivery_data )
                                    {
                                            $this->content .= '<tr '.(($line % 2 != 0) ? 'class="alternate"' : '').'>';
                                            $delivery_dataedition_number = ""; 
                                            $delivered = false;
                                            foreach ( $delivery_data as $key => $delivery_value )
                                            {
                                                    if( $key == 'Expedicion') 
                                                            $delivery_number = $delivery_value;
                                                    $this->content .= '<td class='.$key.' '.($key =='EXPEDICION' || $key =='Detalles' ? 'colspan="2"' : '' ).'>'.( !in_array($key, $headersOcultas) ? $delivery_value : '' );
                                                    $delivery_dataedition_number = ( $key == 'EXPEDICION' ? $delivery_value : '' );

                                                    if( $key == 'Descripcion' && $delivery_value == 'ENTREGA EFECTUADA' ) 
                                                            $delivered = TRUE;
                                                    
                                                    if( $key == 'EXPEDICION' AND ($countryTo == 'ES' OR $countryTo == '-' OR $countryTo == '') AND $delivered )
                                                    {
                                                            $this->content .= '
                                                                    <a href="../modules/seur/ajax/createDeliveryNote.php?back='.$back.'&token='.Tools::getValue('token').'&expedition_number='.$delivery_value.'&token='.$token.'&id_employee='.$this->context->cookie->id_employee.'">
                                                                        <img src="'.$img_dir.'/png_ico.png" alt="'.$this->l('Delivery').'" title="'.$this->l('Delivery').'" />
                                                                    </a>
                                                                    <!--a class="verDetalles" href="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'&verDetalle=1&token='.$token.'&expedition_number='.$delivery_value.'&id_employee='.$this->context->cookie->id_employee.'"-->';
                                                    }                
                                                    if( $key == 'Detalles')
                                                            $this->content .= '
                                                                    <a class="verDetalles" href="'.__PS_BASE_URI__.'modules/seur/ajax/getExpeditionAjax.php?expedition_number='.$delivery_number.'&token='.$token.'&id_employee='.$this->context->cookie->id_employee.'">
                                                                        <img src="'.$img_dir.'/details.png" alt="'.$this->l('See details').'" title="'.$this->l('See details').'" />
                                                                    </a>';
                                                    $this->content .= '</td>';

                                           }
                                           $this->content .= '</tr>';
                                           $line++;
                                    }
                                    
                                }
                                else
                                {
                                        $this->content .= SeurLib::displayErrors($this->l('No results.'));
                                }
                                $this->content .= ' </tbody>';
                        }
                }
                $this->content .= '
                                </table>
                            </form>
                        </li>
                        <li id="packing_list"'.( $tab_view == 'packing_list' ? '  class="default"' : '' ).'>
                            <table class="table" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th>'.$this->l('Download today packing list').'</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <a href="../modules/seur/ajax/createPackingList.php?back='.$back.'&token='.Tools::getValue('token').'&id_employee='.$this->context->cookie->id_employee.'" target="_blank">
                                            <img src="'.$img_dir.'/ico_descargar.png" alt="'.$this->l('Packing List').'" />'.$this->l('Download').'</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </li>
                ';

                $this->content .= '
                        <li id="pickups"'.( $tab_view == 'pickups' ? 'class="default"' : '' ).'>
                            <table class="table" cellspacing="0">
                                <thead>';

                $pickup_data = Pickup::getLastPickup();
                
                $steady_pickup = false;
                
                if ($pickup_data)
                {
                        $pickup_date = explode(' ',$pickup_data['date']);
                }
                
                if (SeurLib::getConfigurationField('pickup') == 1)
                {
                        $steady_pickup = true;
                }
                
                if ( !empty($pickup_data) && strtotime( date('Y-m-d') ) == strtotime($pickup_date[0]) && !$steady_pickup)
                {
                        $this->content .= '
                                    <tr>
                                        <th>'.$this->l('Localizer').'</th>
                                        <th colspan="2">'.$this->l('Date').'</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr >
                                       <td>'.$pickup_data['localizer'].'</td>
                                       <td>'.$pickup_data['date'].'</td>
                                    </tr>
                                </tbody>';
                }
                elseif ( (int)date('H') < (int)(14) && !$steady_pickup)//(int)(14) hora para la pickup
                {
                        $this->content .= '
                                    <tr>
                                        <td class="createpickup">
                                            <a href="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'&createPickup=1">'.$this->l('Create pickup').'</a>
                                        </td>
                                    </tr>
                        ';
                }
                elseif ($steady_pickup)
                {
                        $this->content .= '
                                    <tr>
                                        <th>'.$this->l('Fixed pickup.').'</th>
                                    </tr>
                        ';
                }
                elseif ( (int)date('H') >= (int)(14) )
                {
                
                        $this->content .= '<tbody>
                                                <tr>
                                                    <td>
                                                        <p><img src="../img/admin/help2.png" /> 
                                                           '.$this->l('14H is past, to create a pickup please contact SEUR on 902101010 or via '). '
                                                        </p>
                                                        <p><a href="http://www.seur.com" target="_blank">www.seur.com</a></p>
                                                        <p>'.$this->l('Thank you.').'</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                        ';
                }
                $this->content .= '
                            </thead>
                        </table>
                </li>
            </ul>
          </div>
          </fieldset>

          <div>
          ';

        }

}