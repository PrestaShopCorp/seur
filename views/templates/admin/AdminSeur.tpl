{**
* 2007-2015 PrestaShop
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
*  @copyright 2007-2015 PrestaShop SA
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div id="contenttab">

{if $ps15}		
<script type="text/javascript">
{literal}
				$( document ).ready(function() {
					$('#submitFilter').click(function(){
						document.formfilter.submit();
					});
				});

{/literal}
</script>
{/if}
		
			<fieldset>
				<legend>
					<img src="{$img_dir|escape:'htmlall':'UTF-8'}/logonew.png" />
			 	</legend>
				<div id="downloadmanual-seur">
					<a id="manual_download" href="/modules/seur/manual/seur_manual.pdf" target="_blank" >
						<img src="{$img_path|escape:'htmlall':'UTF-8'}ico_descargar.png" alt="{l s='Manual' mod='seur'}" /> {l s='Manual' mod='seur'}
					</a>
				</div>
				<div id="seur_module" class="{$ps_version|escape:'htmlall':'UTF-8'}">
					<ul class="configuration_menu">
						{if !$print_type}
						<li class="button btnTab {if $tab_view eq 'labels'} active {/if}" tab="label">
							<img src="{$img_dir|escape:'htmlall':'UTF-8'}/config.png" alt="{l s='Label' mod='seur'}" title="{l s='Label' mod='seur'}" />
							{l s='Label' mod='seur'}
						</li>
						{/if}
						<li class="button btnTab {if $tab_view eq 'deliveries'} active {/if}" tab="deliveries">
							<img src="{$img_dir|escape:'htmlall':'UTF-8'}config.png" alt="{l s='Shipments' mod='seur'}" title="{l s='Shipments' mod='seur'}" />
							{l s='Shipments' mod='seur'}
						</li>
						<li class="button btnTab {if $tab_view eq 'packing_list'} active {/if}" tab="packing_list">
							<img src="{$img_dir|escape:'htmlall':'UTF-8'}manifest.png" alt="{l s='Packing List' mod='seur'}" title="{l s='Packing List' mod='seur'}" />
							{l s='Packing List' mod='seur'}
						</li>
						<li class="button btnTab {if $tab_view eq 'pickups'} active {/if}" tab="pickups">
							<img src="{$img_dir|escape:'htmlall':'UTF-8'}recogidas.png" alt="{l s='Pickups' mod='seur'}" title="{l s='Pickups' mod='seur'}" />
							{l s='Pickups' mod='seur'}
						</li>
					</ul>
									
					<ul class="configuration_tabs">
						<li id="deliveries" {if $tab_view eq 'pickups'} class="active"  {/if}>
							<form action="index.php?controller={$current_controller|escape:'htmlall':'UTF-8'}&submitFilter=1&token={$token|escape:'htmlall':'UTF-8'}{$ps14_tab|escape:'htmlall':'UTF-8'}" method="post" id="formfilter" name="formfilter">
								<table id="deliveriesTable" class="table" cellpadding="0" cellspacing="0">
									<thead>
										<tr> 
											<th>{l s='Reference number' mod='seur'}</th>
											<th>{l s='Expedition number' mod='seur'}</th>
											<th>{l s='Start date' mod='seur'}</th>
											<th>{l s='End date' mod='seur'}</th>
											<th colspan="5">{l s='Estate' mod='seur'}</th>
										</tr>
										<tr class="filtros">
											<td><input class="ps14_input" type="text" name="reference_number" value="" autocomplete="off" /></td>
											<td><input class="ps14_input" type="text" name="expedition_number" value="" autocomplete="off" /></td>
											<td><input class="ps14_input" type="text" name="start_date" id="start_date" autocomplete="off" value="{$start_data|escape:'htmlall':'UTF-8'}"/></td>
											<td><input class="ps14_input" type="text" name="end_date" id="end_date" class="datepicker" autocomplete="off" value="{$delivery_valuend_data|escape:'htmlall':'UTF-8'}"/></td>
											<td colspan="4">
												<select id="order_state" name="order_state" value="" autocomplete="off">
												{foreach $seur_order_states as $key => $seur_order_state}
													<option value="{$key|escape:'htmlall':'UTF-8'}">{$seur_order_state|escape:'htmlall':'UTF-8'}</option>
												{/foreach}
												</select>
											</td>
											<td>
												<input type="submit" value="{l s='Filter' name='submitFilter' mod='seur'}" id="submitFilter" class="filter" />
											</td>
										</tr>
									</thead>
									{if $errors}									
										<div class="bootstrap">
											<div class="module_error alert alert-danger">
												<button data-dismiss="alert" class="close" type="button">×</button>
												{$errors|escape:'htmlall':'UTF-8'}
											</div>
										</div>
									{/if}
					<tbody>

						<tr class="bold">
					{if $headers}
					{foreach $headers as $key => $header}
						<th {if $key eq 'delivery' || $key eq 'details' } colspan="2" {/if} )>{$header|escape:'htmlall':'UTF-8'}</th>
					{/foreach}
					</tr>
					{/if}
					
					{if $deliveries_data}
					{foreach $deliveries_data item=delivery_data name='delivery_item'}
						<tr {if $smarty.foreach.delivery_item.iteration % 2 != 0} class="alternate" {/if} >
						{assign var='delivered' value=false}
						{if $delivery_data}
						{foreach $delivery_data as $key => $delivery_value}
							{if $key eq 'Expedicion'}
								{assign var='delivery_number' value=$delivery_value}
								
							{/if}
							<td class='{$key}' {if $key eq 'EXPEDICION' || $key eq 'Detalles'} colspan='2' {/if}> 
							{if !in_array($key, $headersOcultas)} {$delivery_value} {/if}
							
							{if $key eq 'Descripcion' && $delivery_value eq 'ENTREGA EFECTUADA'}
								{assign var='delivered' value=true}
							{/if}
							{if $key eq 'EXPEDICION' && ($countryTo eq 'ES' || $countryTo eq '-' || $countryTo eq '') && $delivered}
								<a href="/modules/seur/ajax/createDeliveryNote.php?back={$back|escape:'htmlall':'UTF-8'}&amp;token={Tools::getValue('token')}&expedition_number={$delivery_value|escape:'htmlall':'UTF-8'}&amp;token={$token|escape:'htmlall':'UTF-8'}&id_employee={$id_employee|escape:'htmlall':'UTF-8'}">
										<img src="{$img_dir|escape:'htmlall':'UTF-8'}/png_ico.png" alt="{l s='Delivery' mod='seur'}" title="{l s='Delivery' mod='seur'}" />
								</a>
							{/if}
							 {if $key eq 'Detalles'}
								<a class="verDetalles" href="/modules/seur/ajax/getExpeditionAjax.php?expedition_number={$delivery_number|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&id_employee={$id_employee|escape:'htmlall':'UTF-8'}">
										<img src="{$img_dir|escape:'htmlall':'UTF-8'}/details.png" alt="{l s='See details' mod='seur'}" title="{l s='See details' mod='seur'}" />
									</a>
							{/if}
							</td>
							
						{/foreach}
						{/if}
						</tr>
					
					{foreachelse}
					<div class="bootstrap">
						<div class="module_error alert alert-danger">
							<button data-dismiss="alert" class="close" type="button">×</button>
							{l s='No result' mod='seur'}
						</div>
					</div>
					{/foreach}
					{/if}			
				</tbody>
	
				</table>
				</form>
			</li>
			
			<li id="packing_list" {if $tab_view eq 'packing_list'} class="default" {/if}>
				<table class="table" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th>{l s='Download today packing list' mod='seur'}</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<a href="../modules/seur/ajax/createPackingList.php?back={$back|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&id_employee={$id_employee|escape:'htmlall':'UTF-8'}" target="_blank">
							<img src="{$img_dir|escape:'htmlall':'UTF-8'}/ico_descargar.png" alt="{l s='Packing List' mod='seur'}" />{l s='Download' mod='seur'}</a>
						</td>
					</tr>
				</tbody>
				</table>
			</li>

		<li id="pickups" {if $tab_view eq 'pickups'} class="default" {/if}>
			<table class="table" cellspacing="0">
				<thead>
			
					{if $pickup_data}
						{assign var=pickup_date value=" "|explode:$pickup_data['date']} 
					{/if}
					
								
					{if !empty($pickup_data) && strtotime(date('Y-m-d')) == strtotime($pickup_date[0]) && !$steady_pickup}
							<tr>
								<th>{l s='Localizer' mod='seur'}</th>
								<th colspan="2">{l s='Date' mod='seur'}</th>
							</tr>
						</thead>
						<tbody>
							<tr >
							   <td>{$pickup_data['localizer']}</td>
							   <td>{$pickup_data['date']}</td>
							</tr>
						</tbody>
					
					{elseif date('H')|intval < 14 && !$steady_pickup}
							<tr>
								<td class="createpickup">
									<a href="index.php?controller=AdminSeur15&createPickup=1&token={$token|escape:'htmlall':'UTF-8'}{$ps14_tab|escape:'htmlall':'UTF-8'}">{l s='Create pickup' mod='seur'}</a>
								</td>
							</tr>
					{elseif $steady_pickup}
							<tr>
								<th>{l s='Fixed pickup.' mod='seur'}</th>
							</tr>
					{elseif date('H')|intval >= 14}
					
						<tbody>
							<tr>
								<td>
								<p><img src="../img/admin/help2.png" /> 
								   {l s='14H is past, to create a pickup please contact SEUR on 902101010 or via' mod='seur'}
								</p>
								<p><a href="http://www.seur.com" target="_blank">www.seur.com</a></p>
								<p>{l s='Thank you.' mod='seur'}</p>
								</td>
							</tr>
						</tbody>
					{/if}

				</thead>
			</table>
				</li>
				<li id="label"  {if $tab_view eq 'label'} class="default" {/if}>
					<form action="index.php?controller=AdminSeur15&generateLabel=1&token={$token|escape:'htmlall':'UTF-8'}{$ps14_tab|escape:'htmlall':'UTF-8'}" method="post" target="_blank">
						<table id="labelTable" class="table" cellpadding="0" cellspacing="0" >
							<thead>
								<tr>
											<th>{l s='ID' mod='seur'}</th>
											<th>{l s='Reference number' mod='seur'}</th>
											<th>{l s='Start Date' mod='seur'} /  {l s='End Date' mod='seur'}</th>
											<th>{l s='Name' mod='seur'}</th>
											<th>{l s='Address' mod='seur'}</th>
											<th>{l s='Postal code' mod='seur'}</th>
											<th>{l s='City' mod='seur'}</th>
											<th>{l s='State' mod='seur'}</th>
											<th>{l s='Country' mod='seur'}</th>
											<th>{l s='Order state' mod='seur'}</th>
											<th>{l s='Printed label' mod='seur'}</th>
											<th></th>
								</tr>
								<tr class="filtros">
											<td><input class="ps14_input" type="text" name="id" value="{$ID|escape:'htmlall':'UTF-8'}" autocomplete="off" /></td>
											<td><input class="ps14_input" type="text" name="reference" value="{$reference|escape:'htmlall':'UTF-8'}" autocomplete="off" /></td>
											<td style="min-width:160px;"><span>{l s='Desde' mod='seur'}</span>&nbsp;<input class="datepicker" type="text" name="start_date_order" id="start_date_order" autocomplete="off" value="{$start_date}"/>
											<br>
											<span>
											{l s='Hasta'}</span>&nbsp;&nbsp;
											<input class="datepicker" type="text" name="end_date_order" id="end_date_order" autocomplete="off" value="{$end_date|escape:'htmlall':'UTF-8'}"/></td>
											<td><input class="ps14_input" type="text" name="name" id="firstname" autocomplete="off" value="{$firstname|escape:'htmlall':'UTF-8'} {$lastname|escape:'htmlall':'UTF-8'}"/></td>
											<td><input class="ps14_input" type="text" name="address" id="address" autocomplete="off" value="{$address|escape:'htmlall':'UTF-8'}"/></td>
											<td><input class="ps14_input" type="text" name="postcode" id="postcode" autocomplete="off" value="{$postcode|escape:'htmlall':'UTF-8'}"/></td>
											<td><input class="ps14_input" type="text" name="city" id="city" autocomplete="off" value="{$city|escape:'htmlall':'UTF-8'}"/></td>
											<td><input class="ps14_input" type="text" name="state" id="state" autocomplete="off" value="{$state|escape:'htmlall':'UTF-8'}"/></td>
											<td><input class="ps14_input" type="text" name="country" id="country" autocomplete="off" value="{$country|escape:'htmlall':'UTF-8'}"/></td>
											<td>
												<select id="order_state" name="order_state" value="" autocomplete="off">
													<option value=""></option>
												{foreach $ps_order_states item=ps_order_state}
													<option value="{$ps_order_state.id_order_state}" {if ($order_state == $ps_order_state.id_order_state)}selected="selected"{/if}>{$ps_order_state.name}</option>
												{/foreach}
												</select>
											</td>
											<td style="text-align:center;"><input  type="checkbox"   id="printed_label" autocomplete="off" value=""/></td>
											<td><a class="filter" id="labelsFilter">{l s='Filtrar' mod='seur'}</a></td>
								</tr>
							</thead>
					
							<tbody>
						
							</tbody>
						</table>
						<script type="text/javascript">
							$(document).ready(function(){
									{literal}
									$('#printed_label').live('click',function(){
											if($(this).is(':checked'))
											{
												$('input[name^=id_orders]').attr('checked','checked');
											}
											else
											{
												$('input[name^=id_orders]').removeAttr('checked');
											}
										
									
									});
										{/literal}
								{if isset($ps_16) && !$ps_16} 
								{literal}
									$('#start_date_order,#end_date_order').datepicker({ showAnim : 'slideDown', dateFormat : 'dd-mm-yy' , 'onSelect': function(dateText, inst){$(this).trigger('change')}});
								{/literal}
									{/if}	
								{literal}
							
								$('#labelsFilter').live('click', function(){
											params = {};
										  $.each($('#labelTable .filtros input[type=text]'), function(key, item){
												params[item.name] = item.value;
										  });
										  params["order_state"] = $("#labelTable #order_state").val();
										  $.ajax(
												{
													url: '{/literal}{$ps_base_uri|escape:'htmlall':'UTF-8'}{literal}seur/ajax/getLabelAjax.php',
													data: params,
													type: 'POST',
													async: true,
													dataType: 'json',
													success: function (jsonData)
													{
														$('#labelTable tbody').html(jsonData.response);
													},
											});
								});							
							});
							{/literal}
						</script>
					</form>
									
					</li>
			</ul>
		  </div>
	  </fieldset>	
			
	  </div>