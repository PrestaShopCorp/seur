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

$useSSL = false;
if(isset($_SERVER["HTTPS"]) && in_array($_SERVER["HTTPS"], array("on", "ON"))){ $useSSL = true; }
// die("useSSL: ".(int)$useSSL);
include('../../../config/config.inc.php');
include('../../../init.php');

if (!class_exists('SeurLib')) include(_PS_MODULE_DIR_ . 'seur/classes/SeurLib.php');
if (!class_exists('Pickup')) include(_PS_MODULE_DIR_ . 'seur/classes/Pickup.php');

$order_data = (array) $_GET;

if (Tools::getValue('token') != Tools::getAdminToken('AdminOrders' . (int) (Tab::getIdFromClassName('AdminOrders')) . (int) (Tools::getValue('id_employee'))))
    Tools::display404Error();

try {
	//*
	$sc_options = array(
		// "soap_version" => SOAP_1_1, 
		"connection_timeout" => 30 
	);
	$soap_client = new SoapClient((string)Configuration::get('SEUR_URLWS_ET'), $sc_options);
	//*/
	// $soap_client = new SoapClient((string) Configuration::get('SEUR_URLWS_ET'));

    $merchant_data = SeurLib::getMerchantData();

    $notification = SeurLib::getConfigurationField('notification_advice');
    $advice_checkbox = SeurLib::getConfigurationField('advice_checkbox');
    $distribution_checkbox = SeurLib::getConfigurationField('distribution_checkbox');

    $servicio = Configuration::get('SEUR_NACIONAL_SERVICE');
    $producto = Configuration::get('SEUR_NACIONAL_PRODUCT');
    $mercancia = false;
    $claveReembolso = '';
    $valorReembolso = '';

    if (SeurLib::getConfigurationField('international_orders') == 1 && ($order_data['iso'] != 'ES' && $order_data['iso'] != 'PT' && $order_data['iso'] != 'AD')) {
        $servicio = Configuration::get('SEUR_INTERNACIONAL_SERVICE');
        $producto = Configuration::get('SEUR_INTERNACIONAL_PRODUCT');
        $mercancia = true;
        $order_data['total_bultos'] = 1;
    }
    if (isset($order_data['reembolso']) && ($order_data['iso'] == 'ES' || $order_data['iso'] == 'PT' || $order_data['iso'] == 'AD')) {
        $claveReembolso = 'f';
        $valorReembolso = (float) $order_data['reembolso'];
    }
    if (isset($order_data['cod_centro']) && ($order_data['iso'] == 'ES' || $order_data['iso'] == 'PT' || $order_data['iso'] == 'AD')) {
        $servicio = 1;
        $producto = 48;
    }

    $pesoTotal = $order_data['total_kilos'];
    $totalBultos = $order_data['total_bultos'];
    $pesoBulto = $pesoTotal / $totalBultos;

    if ($pesoBulto < 1.0) {//1kg
        $pesoBulto = 1.0;
        $pesoTotal = $totalBultos;
    }

    $cont = 0;

    $xml = '<?xml version="1.0" encoding="ISO-8859-1"?><root><exp>';
    for ($i = 0; $i <= $totalBultos - 1; $i++) {
        $cont++;
        $xml .= '
                        <bulto>
                            <ci>' . $merchant_data['cit'] . '</ci>
                            <nif>' . $merchant_data['nif_dni'] . '</nif>
                            <ccc>' . $merchant_data['ccc'] . '</ccc>
                            <servicio>' . $servicio . '</servicio>
                            <producto>' . $producto . '</producto>
                ';
        if ($mercancia)
            $xml .= '
                                <id_mercancia>382</id_mercancia>
                        ';
        $xml .= '
                            <cod_centro></cod_centro>
                            <total_bultos>' . $totalBultos . '</total_bultos>
                            <total_kilos>' . $pesoTotal . '</total_kilos>
                            <pesoBulto>' . $pesoBulto . '</pesoBulto>
                            <observaciones>' . $order_data['info_adicional'] . '</observaciones>
                            <referencia_expedicion>' . $order_data['pedido'] . '</referencia_expedicion>
                            <ref_bulto>' . $order_data['pedido'] . str_pad((string) ((int) $i + 1), 3, '0', STR_PAD_LEFT) . '</ref_bulto>
                            <clavePortes>F</clavePortes>
                            <clavePod></clavePod>
                ';
        $xml .= '   
                            <claveReembolso>' . $claveReembolso . '</claveReembolso>
                            <valorReembolso>' . $valorReembolso . '</valorReembolso>
                            <libroControl></libroControl>
                            <nombre_consignatario>' . $order_data['name'] . '</nombre_consignatario>
                            <direccion_consignatario>' . $order_data['direccion_consignatario'] . '</direccion_consignatario>
                            <tipoVia_consignatario>CL</tipoVia_consignatario>
                            <tNumVia_consignatario>N</tNumVia_consignatario>
                            <numVia_consignatario>.</numVia_consignatario>
                            <escalera_consignatario>.</escalera_consignatario>
                            <piso_consignatario>.</piso_consignatario>
                            <puerta_consignatario>.</puerta_consignatario>
                            <poblacion_consignatario>' . $order_data['consignee_town'] . '</poblacion_consignatario>
                        ';
        if (!empty($order_data['codPostal_consignatario']))
            $xml .= '
                                <codPostal_consignatario>' . $order_data['codPostal_consignatario'] . '</codPostal_consignatario>
                        ';
        $xml .= '
                            <pais_consignatario>' . $order_data['iso'] . '</pais_consignatario>
                            <codigo_pais_origen>' . $order_data['iso_merchant'] . '</codigo_pais_origen>
                            <email_consignatario>' . $order_data['email_consignatario'] . '</email_consignatario>
                            <sms_consignatario>' . ((int) $notification ? $order_data['movil'] : "" ) . '</sms_consignatario>
                            <test_sms>' . ((int) $notification ? 'S' : 'N') . '</test_sms>
                            <test_preaviso>' . ((int) $advice_checkbox ? 'S' : 'N') . '</test_preaviso>
                            <test_reparto>' . ((int) $distribution_checkbox ? 'S' : 'N') . '</test_reparto>
                            <test_email>' . ((int) $notification ? 'N' : 'S') . '</test_email>
                            <eci>N</eci>
                            <et>N</et>
                            <telefono_consignatario>' . $order_data['telefono_consignatario'] . '</telefono_consignatario>
                            <atencion_de>' . $order_data['companyia'] . '</atencion_de>
                            </bulto>
                        ';
    }
    $xml .= '
                </exp></root>
        ';

    $xml_name = $merchant_data['franchise'] . '_' . $merchant_data['cit'] . '_' . date('dmYHi') . '.xml';

    $dir = _PS_MODULE_DIR_ . 'seur/files/deliveries_xml/';
    ini_set('allow_url_fopen', 1);
    if (!$file = fopen($dir . $xml_name, 'w+'))
        echo 'No se ha podido abrir el fichero';
    else {
        fwrite($file, $xml);
        fclose($file);
        chmod($dir . $xml_name, 0777);
    }

    ini_set('allow_url_fopen', 0);
    $label_name = $order_data['pedido'];
    $make_pickup = false;
    $auto = false;
    $pickup_data = Pickup::getLastPickup();

    if (!empty($pickup_data)) {
        $pickup_data = $pickup_data[0];
        $datepickup = explode(" ", $pickup_data['date']);
        $datepickup = $datepickup[0];

        if (strtotime(date("Y-m-d")) != strtotime($datepickup))
            $make_pickup = true;
        if (SeurLib::getConfigurationField('pickup') == 0)
            $auto = true;
    }

    $data = array(
        'in0' => $merchant_data['user'],
        'in1' => $merchant_data['pass'],
        'in2' => 'ZEBRA',
        'in3' => 'LP2844-Z',
        'in4' => '2C',
        'in5' => $xml,
        'in6' => $xml_name,
        'in7' => $merchant_data['nif_dni'],
        'in8' => $merchant_data['franchise'],
        'in9' => '-1',
        'in10' => 'prestashop',
    );
    $response = $soap_client->impresionIntegracionConECBWS($data);
    if ($response->out == 'ERROR')
        echo 'ERROR';

    elseif ($response->out->mensaje != 'OK')
        echo $response->out->mensaje;
    else {
        ini_set("allow_url_fopen", 1);
        if ($file = fopen(_PS_MODULE_DIR_ . 'seur/files/deliveries_labels/' . $label_name . '.txt', 'w+')) {
            fwrite($file, (string) $response->out->traza);
            fclose($file);
        }
        ini_set('allow_url_fopen', 0);
        // TO_REVIEW SeurLib::setOrderData($order_data['pedido'],$totalBultos,'zebra');
        if ($make_pickup && $auto)
            Pickup::createPickup();
        echo 1;
    }
} catch (SoapFault $fault) {
    $url = Tools::getValue('back') . '&token=' . Tools::getValue('token') . '&codigo=Error&error=' . $fault->getMessage();
	Tools::redirect($url);
}