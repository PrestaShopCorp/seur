{*
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
*}
{if $reembolso_cargo|count}
	{if $modulo=="seurcashondelivery"}
<br />
<form id="seurcashondelivery_concept_form" method="POST" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}">
	<fieldset id="cashondeliveryseur">
		<legend>{l s='Concept' mod='seurcashondelivery'}</legend>
		<span style="font-weight: bold; font-size: 14px;">{l s='Cash on delivery by SEUR' mod='seurcashondelivery'}</span><br />
		<br />
		{l s='Quantity:' mod='seurcashondelivery'} <b>{$reembolso_cargo|escape:'htmlall':'UTF-8'}<sup>*</sup></b><br />
		<p style="font-size:10px;margin:0;padding:0;"><sup>*</sup> {l s='This fee is included in delivery price.' mod='seurcashondelivery'}</p><br />
		<p>
			<label>{l s='Total Paid' mod='seur'}</label>
			{if $printed}{$total_paid_seurcashondelivery|escape:'htmlall':'UTF-8'}{else}<input id="input_total_paid" style="width:100px;" type="text" name="total_paid_seurcashondelivery" value="{$total_paid_seurcashondelivery|floatval}" size="2" />{/if}
		</p>
		{if !$printed}<p><input class="btn btn-primary" type="submit" name="total_paid_submit_seurcashondelivery" value="{l s='Update' mod='seurcashondelivery'}" /></p>{/if}
		{if $save_total_paid_seurcashondelivery_OK}<p style="font-size:10px;margin:0;padding:0; color:#71b238;">{l s='Changes have been saved successfully.' mod='seurcashondelivery'}</p>{/if}		
	</fieldset>
</form>				
	{/if}
{/if}

<script type="text/javascript">
	$(document).ready(function()
	{ldelim}
		$('#input_total_paid').focusout(function(){ldelim}
			var input_total_paid = $('#input_total_paid').val();
			input_total_paid = input_total_paid.replace('´', '.');
			input_total_paid = input_total_paid.replace(',', '.');
			input_total_paid = parseFloat(input_total_paid);
			$('#input_total_paid').val(input_total_paid);
		{rdelim});
	{rdelim});
</script>
		
{*
{if $reembolso_cargo|count}
	{if $modulo=="seurcashondelivery"}
<br />
<fieldset id="cashondeliveryseur">
	<legend>{l s='Concept' mod='seurcashondelivery'}</legend>
	<span style="font-weight: bold; font-size: 14px;">{l s='Cash on delivery by SEUR' mod='seurcashondelivery'}</span><br />
	<br />
	{l s='Quantity:' mod='seurcashondelivery'} <sup>*</sup><b><span class="codfee_label">{$reembolso_cargo|escape:'htmlall':'UTF-8'}</span><input style="width: 150px; display:none;" type="text" id="codfee_value" value="{$reembolso_cargo|escape:'htmlall':'UTF-8'}" name="codfee_value" /></b><span><button class="edit_codfee">{l s='Edit' mod='seurcashondelivery'}</button><button class="save_codfee" style="display:none;">{l s='Save' mod='seurcashondelivery'}</button></span><br />
	<p style="font-size:10px;margin:0;padding:0;"><sup>*</sup> {l s='This fee is included in delivery price.' mod='seurcashondelivery'}</p><br />
</fieldset>
	{/if}
{/if}

<script type="text/javascript">
	$(document).ready(function()
	{ldelim}
				$('#input_total_paid').keypress(function(){ldelim}
					alert('now');
			{rdelim});	
				$('.edit_codfee').click(function(){ldelim}
					$('.edit_codfee,.codfee_label').hide();
					$('.save_codfee,#codfee_value').show();
			{rdelim});
						$('.save_codfee').click(function()
			{ldelim}
				params = {ldelim}{rdelim};
				params['id_order'] = {Tools::getValue('id_order', 0)};
				params['codfee'] = $('#codfee_value').val();
				$.ajax({ldelim}
					 url: '/modules/seur/ajax/saveCodFee.php',
					data: params,
					type: 'POST',
					async: true,
					dataType: 'json',
					success: function (jsonData)
					{ldelim}
							if (typeof(jsonData.result) != 'undefined' && jsonData.result == 'OK')
							{ldelim}
								$('.codfee_label').html($('#codfee_value').val());
								$('#codfee_value,.save_codfee').hide();
								$('.edit_codfee,.codfee_label').show();
								location.reload();
								
							{rdelim}
					{rdelim},
					error: function(jqXHR, exception)
					{ldelim} 
						
					{rdelim},
				{rdelim});
			
			{rdelim});		
	
	{rdelim});


</script>*}