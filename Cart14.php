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

if (!class_exists('SeurLib'))
        include(_PS_MODULE_DIR_.'seur/classes/SeurLib.php');

class Cart extends CartCore
{
	
	function getOrderShippingCost($id_carrier = NULL, $useTax = true)
	{
        $seurCarriers = SeurLib::getSeurCarriers();
        $isInCarriers = false;
        foreach($seurCarriers as $carrier)
        {
            if(in_array($id_carrier, $carrier)){
                $isInCarriers = true;
                break;
            }
                    
        }
        if ($id_carrier == NULL || !$isInCarriers)
                return parent::getOrderShippingCost($id_carrier, $useTax);
                
		global $defaultCountry;

		if ($this->isVirtualCart())
			return 0;

		// Checking discounts in cart
		$products = $this->getProducts();
		$discounts = $this->getDiscounts(true);
		if ($discounts)
			foreach ($discounts AS $id_discount)
				if ($id_discount['id_discount_type'] == 3)
				{
					if ($id_discount['minimal'] > 0)
					{
						$total_cart = 0;

						$categories = Discount::getCategories((int)($id_discount['id_discount']));
						if (sizeof($categories))
							foreach($products AS $product)
								if (Product::idIsOnCategoryId((int)($product['id_product']), $categories))
									$total_cart += $product['total_wt'];

						if ($total_cart >= $id_discount['minimal'])
							return 0;
					}
					else
						return 0;
				}

		// Order total in default currency without fees
		$order_total = $this->getOrderTotal(true, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING);

		// Start with shipping cost at 0
		$shipping_cost = 0;

		// If no product added, return 0
		if ($order_total <= 0 AND !(int)(self::getNbProducts($this->id)))
			return $shipping_cost;

		// Get id zone
		if (isset($this->id_address_delivery)
			AND $this->id_address_delivery
			AND Customer::customerHasAddress($this->id_customer, $this->id_address_delivery))
			$id_zone = Address::getZoneById((int)($this->id_address_delivery));
		else
		{
			// This method can be called from the backend, and $defaultCountry won't be defined
			if (!Validate::isLoadedObject($defaultCountry))
				$defaultCountry = new Country(Configuration::get('PS_COUNTRY_DEFAULT'), Configuration::get('PS_LANG_DEFAULT'));
			$id_zone = (int)$defaultCountry->id_zone;
		}

		// If no carrier, select default one
		if (!$id_carrier)
			$id_carrier = $this->id_carrier;

		if ($id_carrier && !$this->isCarrierInRange($id_carrier, $id_zone))
			$id_carrier = '';

		if (empty($id_carrier) && $this->isCarrierInRange(Configuration::get('PS_CARRIER_DEFAULT'), $id_zone))
				$id_carrier = (int)(Configuration::get('PS_CARRIER_DEFAULT'));

		if (empty($id_carrier))
		{
			if ((int)($this->id_customer))
			{
				$customer = new Customer((int)($this->id_customer));
				$result = Carrier::getCarriers((int)(Configuration::get('PS_LANG_DEFAULT')), true, false, (int)($id_zone), $customer->getGroups());
				unset($customer);
			}
			else
				$result = Carrier::getCarriers((int)(Configuration::get('PS_LANG_DEFAULT')), true, false, (int)($id_zone));

			foreach ($result AS $k => $row)
			{
				if ($row['id_carrier'] == Configuration::get('PS_CARRIER_DEFAULT'))
					continue;

				if (!isset(self::$_carriers[$row['id_carrier']]))
					self::$_carriers[$row['id_carrier']] = new Carrier((int)($row['id_carrier']));

				$carrier = self::$_carriers[$row['id_carrier']];

				// Get only carriers that are compliant with shipping method
				if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT AND $carrier->getMaxDeliveryPriceByWeight($id_zone) === false)
				OR ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE AND $carrier->getMaxDeliveryPriceByPrice($id_zone) === false))
				{
					unset($result[$k]);
					continue ;
				}

				// If out-of-range behavior carrier is set on "Desactivate carrier"
				if ($row['range_behavior'])
				{
					// Get only carriers that have a range compatible with cart
					if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT AND (!Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $this->getTotalWeight(), $id_zone)))
					OR ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE AND (!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, (int)($this->id_currency)))))
					{
						unset($result[$k]);
						continue ;
					}
				}

				if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
					$shipping = $carrier->getDeliveryPriceByWeight($this->getTotalWeight(), $id_zone);
				else
					$shipping = $carrier->getDeliveryPriceByPrice($order_total, $id_zone, (int)($this->id_currency));
				
				if (!isset($minShippingPrice))
					$minShippingPrice = $shipping;

				if ($shipping <= $minShippingPrice)
				{
					$id_carrier = (int)($row['id_carrier']);
					$minShippingPrice = $shipping;
				}
			}
		}

		if (empty($id_carrier))
			$id_carrier = Configuration::get('PS_CARRIER_DEFAULT');

		if (!isset(self::$_carriers[$id_carrier]))
			self::$_carriers[$id_carrier] = new Carrier((int)($id_carrier), Configuration::get('PS_LANG_DEFAULT'));
		$carrier = self::$_carriers[$id_carrier];
		if (!Validate::isLoadedObject($carrier))
			die(Tools::displayError('Fatal error: "no default carrier"'));
		if (!$carrier->active)
			return $shipping_cost;

		// Free fees if free carrier
		if ($carrier->is_free == 1)
			return 0;

		// Select carrier tax
		if ($useTax AND !Tax::excludeTaxeOption())
			 $carrierTax = Tax::getCarrierTaxRate((int)$carrier->id, (int)$this->{Configuration::get('PS_TAX_ADDRESS_TYPE')});

		$configuration = Configuration::getMultiple(array('SEUR_FREE_PRICE', 'PS_SHIPPING_HANDLING', 'PS_SHIPPING_METHOD', 'SEUR_FREE_WEIGTH'));
		// Free fees
		$free_fees_price = 0;
		if (isset($configuration['SEUR_FREE_PRICE']))
			$free_fees_price = Tools::convertPrice((float)($configuration['SEUR_FREE_PRICE']), Currency::getCurrencyInstance((int)($this->id_currency)));
		$orderTotalwithDiscounts = $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
		if ($orderTotalwithDiscounts >= (float)($free_fees_price) AND (float)($free_fees_price) > 0)
			return $shipping_cost;
		if (isset($configuration['SEUR_FREE_WEIGTH']) AND $this->getTotalWeight() >= (float)($configuration['SEUR_FREE_WEIGTH']) AND (float)($configuration['SEUR_FREE_WEIGTH']) > 0)
			return $shipping_cost;

			// Get shipping cost using correct method
			if ($carrier->range_behavior)
			{
				// Get id zone
				if (
					isset($this->id_address_delivery)
					AND $this->id_address_delivery
					AND Customer::customerHasAddress($this->id_customer, $this->id_address_delivery)
				)
					$id_zone = Address::getZoneById((int)($this->id_address_delivery));
				else
					$id_zone = (int)$defaultCountry->id_zone;
				if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT AND (!Carrier::checkDeliveryPriceByWeight($carrier->id, $this->getTotalWeight(), $id_zone)))
						OR ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE AND (!Carrier::checkDeliveryPriceByPrice($carrier->id, $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, (int)($this->id_currency)))))
						$shipping_cost += 0;
					else {
							if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
								$shipping_cost += $carrier->getDeliveryPriceByWeight($this->getTotalWeight(), $id_zone);
							else // by price
								$shipping_cost += $carrier->getDeliveryPriceByPrice($order_total, $id_zone, (int)($this->id_currency));
						 }
			}
			else
			{
				if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
					$shipping_cost += $carrier->getDeliveryPriceByWeight($this->getTotalWeight(), $id_zone);
				else
					$shipping_cost += $carrier->getDeliveryPriceByPrice($order_total, $id_zone, (int)($this->id_currency));

			}
		// Adding handling charges
		if (isset($configuration['PS_SHIPPING_HANDLING']) AND $carrier->shipping_handling)
			$shipping_cost += (float)($configuration['PS_SHIPPING_HANDLING']);

		// Additional Shipping Cost per product
		foreach($products AS $product)
			$shipping_cost += $product['additional_shipping_cost'] * $product['cart_quantity'];

		$shipping_cost = Tools::convertPrice($shipping_cost, Currency::getCurrencyInstance((int)($this->id_currency)));

		//get external shipping cost from module
		if ($carrier->shipping_external)
		{
			$moduleName = $carrier->external_module_name;
			$module = Module::getInstanceByName($moduleName);
	
			if (Validate::isLoadedObject($module))
			{
				if (array_key_exists('id_carrier', $module))
					$module->id_carrier = $carrier->id;		
				if ($carrier->need_range)
					$shipping_cost = $module->getOrderShippingCost($this, $shipping_cost);
				else
					$shipping_cost = $module->getOrderShippingCostExternal($this);
	
				// Check if carrier is available
				if ($shipping_cost === false)
					return false;
			}
			else
				return false;
		}

		// Apply tax
		if (isset($carrierTax))
			$shipping_cost *= 1 + ($carrierTax / 100);

		return (float)(Tools::ps_round((float)($shipping_cost), 2));
	}

	
	function getSummaryDetails()
	{
		$cookie = $this->context->cookie; // rnavarro: ??
		/*
		if(is_object($this->context){
			$cookie = $this->context->cookie;
		}
		else{
			$context = Context::getContext();
			$cookie = $context->cookie;
		}
		*/
		$delivery = new Address((int)($this->id_address_delivery));
		$invoice = new Address((int)($this->id_address_invoice));
		
		// New layout system with personalization fields
		if (version_compare(_PS_VERSION_, "1.5", "<"))
        {
            $formattedAddresses['invoice'] = $this->getFormattedLayoutData($invoice);
            $formattedAddresses['delivery'] = $this->getFormattedLayoutData($delivery);
        }
        else
        {
            $formattedAddresses['invoice'] = AddressFormat::getFormattedLayoutData($invoice);
            $formattedAddresses['delivery'] = AddressFormat::getFormattedLayoutData($delivery);
        }
		
		$total_tax = $this->getOrderTotal() - $this->getOrderTotal(false);

		if ($total_tax < 0)
			$total_tax = 0;

		$total_free_ship = 0;
		if ($free_ship = Tools::convertPrice((float)(Configuration::get('SEUR_FREE_PRICE')), new Currency((int)($this->id_currency))))
		{
			$discounts = $this->getDiscounts();
			$total_free_ship =  $free_ship - ($this->getOrderTotal(true, Cart::ONLY_PRODUCTS) + $this->getOrderTotal(true, Cart::ONLY_DISCOUNTS));
			foreach ($discounts as $discount)
				if ($discount['id_discount_type'] == 3)
				{
					$total_free_ship = 0;
					break;
				}
		}
		return array(
			'delivery' => $delivery,
			'delivery_state' => State::getNameById($delivery->id_state),
			'invoice' => $invoice,
			'invoice_state' => State::getNameById($invoice->id_state),
			'formattedAddresses' => $formattedAddresses,
			'carrier' => new Carrier((int)($this->id_carrier), $cookie->id_lang),
			'products' => $this->getProducts(false),
			'discounts' => $this->getDiscounts(false, true),
			'is_virtual_cart' => (int)$this->isVirtualCart(),
			'total_discounts' => $this->getOrderTotal(true, Cart::ONLY_DISCOUNTS),
			'total_discounts_tax_exc' => $this->getOrderTotal(false, Cart::ONLY_DISCOUNTS),
			'total_wrapping' => $this->getOrderTotal(true, Cart::ONLY_WRAPPING),
			'total_wrapping_tax_exc' => $this->getOrderTotal(false, Cart::ONLY_WRAPPING),
			'total_shipping' => $this->getOrderShippingCost(),
			'total_shipping_tax_exc' => $this->getOrderShippingCost(NULL, false),
			'total_products_wt' => $this->getOrderTotal(true, Cart::ONLY_PRODUCTS),
			'total_products' => $this->getOrderTotal(false, Cart::ONLY_PRODUCTS),
			'total_price' => $this->getOrderTotal(),
			'total_tax' => $total_tax,
			'total_price_without_tax' => $this->getOrderTotal(false),
			'free_ship' => $total_free_ship);
	}
    
    /*
	** Return a data array containing ordered, formatedValue and object fields
	*/
	function getFormattedLayoutData($address)
	{
		$layoutData = array();

		if ($address && $address instanceof Address)
		{
			$layoutData['ordered'] = AddressFormat::getOrderedAddressFields((int)$address->id_country);
			$layoutData['formated'] = AddressFormat::getFormattedAddressFieldsValues($address, $layoutData['ordered']);
			$layoutData['object'] = array();

			$reflect = new ReflectionObject($address);
			$public_properties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
			foreach ($public_properties as $property)
				if (isset($address->{$property->getName()}))
					$layoutData['object'][$property->getName()] = $address->{$property->getName()};
		}
		return $layoutData;
	}

	
}