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
function getPath()
{
    var path = window.location.href;
    return path.substring(0, path.lastIndexOf("/")) + "/";
}

function monitorPrinting()
{
    var applet = document.jzebra;
    if (applet != null) {
        if (!applet.isDonePrinting()) {
            window.setTimeout('monitorPrinting()', 100);
        } else {
            var e = applet.getException();
            if (e == null)
                alert("Exception occured: " + e.getLocalizedMessage());
        }
    } else {
        alert("Applet not loaded!");
    }
}

function monitorFinding()
{
    var applet = document.jzebra;
    if (applet != null) {
        if (!applet.isDoneFinding()) {
            window.setTimeout('monitorFinding()', 100);
        } else {
            var printer = applet.getPrinter();
            if (printer == null) {
                alert("Printer not found: " + document.getElementById('name_type').value);
            }
        }
    } else {
        alert("Applet not loaded!");
    }
}

function jzebraReady()
{


}
function printFile(fichero)
{

    var applet = document.jzebra;
    if (applet != null) {
        applet.findPrinter(document.getElementById('name_type').value);
    }
    monitorFinding();
    var applet = document.jzebra;
    if (applet != null)
    {
        applet.appendFile(fichero);
        applet.print();
    }
    monitorPrinting();
}