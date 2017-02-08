<?php
/*
 * Plugin Name: Pay2go e-Invoice for WooCommerce
 * Description: 智付寶電子發票 by cloudwp
 * Author: JanaLEE
 * Author URI: https://makotostudio.tw/
 * Plugin URI: http://cloudwp.pro/market/plugins/free-plugins/pay2go-einvoice-woocommerce/
 * Version: 1.4.1
 */
/*<makotostudio />*/

if(strpos($_SERVER['SERVER_ADDR'], '192.168')===false){
	ini_set('log_errors', 'On');
	ini_set('display_errors', 'Off');
	ini_set('error_log', dirname(__FILE__).'/error_log.log');
}

if(!defined('CWP2GEI_DIR')){
	define('CWP2GEI_DIR', dirname(__FILE__));
}

define('CWP2GEI_URL', plugin_dir_url(__FILE__));

add_action('plugins_loaded', 'CWP2GEI');

function CWP2GEI(){
	if(class_exists('WC_Payment_Gateway')){
		include CWP2GEI_DIR.'/includes/class_cw-pay2go-ei.php';
	}
}