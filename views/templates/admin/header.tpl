{*
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
*  @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{* Cargas conjuntas *}
	<link type="text/css" href="{$module_dir|escape}css/seur.css" rel="stylesheet" media="all" />
	<!--[if lt IE 9]>
		<script src="{$module_dir|escape}js/headerIE.js"></script>
	<![endif]-->
{* Cargas individuales *}
{if isset($tab) AND $tab}
	{if $tab == 'AdminSeur' || $tab == 'adminseur'}
	<link type="text/css" href="{$module_dir|escape}css/jquery-ui.css" rel="stylesheet" media="all" />
	<link type="text/css" href="{$module_dir|escape}css/jquery.fancybox.css" rel="stylesheet" media="all" />
{*
	<script type="text/javascript" src="{$module_dir|escape}js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="{$module_dir|escape}js/jquery.fancybox.js"></script>
*}

		{if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
	<script type="text/javascript" src="{$module_dir|escape}js/jquery-ui-1.9.2.custom.min.js"></script>
	<script type="text/javascript" src="{$smarty.const._PS_JS_DIR_|escape}jquery/plugins/fancybox/jquery.fancybox.js"></script>
			
		{else}
	<script type="text/javascript" src="{$module_dir|escape}js/ui-1.8.7/jquery-ui.js"></script>
	<link type="text/css" href="{$smarty.const._PS_CSS_DIR_|escape}jquery.fancybox-1.3.4.css" rel="stylesheet" media="all" />
	<script type="text/javascript" src="{$smarty.const._PS_JS_DIR_|escape}jquery/jquery.fancybox-1.3.4.js"></script>
		{/if}
	<script type="text/javascript" src="{$module_dir|escape}js/seurToolsAdmin.js"></script>
	{/if}
	{if $tab == 'AdminOrders'|| $tab == 'adminorders'}
		{if isset($ps4) AND $ps4}
	<script type="text/javascript" src="{$module_dir|escape}js/jquery-1.9.1.min.js"></script>
		{/if}
	<script type="text/javascript" src="{$module_dir|escape}js/seurToolsOrder.js"></script>
	<script type="text/javascript" src="{$module_dir|escape}js/functionsZebra.js"></script>
	<script type="text/javascript" src="{$module_dir|escape}js/html2canvas.js"></script>
	<script type="text/javascript" src="{$module_dir|escape}js/jquery.plugin.html2canvas.js"></script>  
	{/if}
	{if $configure == 'seur' && $tab == 'adminmodules'}
{*
	<link type="text/css" href="{$module_dir|escape}css/jquery.fancybox.css" rel="stylesheet" media="all" />
	<script type="text/javascript" src="{$module_dir|escape}js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="{$module_dir|escape}js/jquery.fancybox.js"></script>
*}
		{if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
		{else}
	<link type="text/css" href="{$smarty.const._PS_CSS_DIR_|escape}jquery.fancybox-1.3.4.css" rel="stylesheet" media="all" />
	<script type="text/javascript" src="{$smarty.const._PS_JS_DIR_|escape}jquery/jquery.fancybox-1.3.4.js"></script>
		{/if}
	<script type="text/javascript" src="{$module_dir|escape}js/seurToolsConfig.js"></script>
	{/if}
{/if}