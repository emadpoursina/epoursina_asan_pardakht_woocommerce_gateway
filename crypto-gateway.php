<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @package           Crypto_Gateway
 *
 * @wordpress-plugin
 * Plugin Name:       Crypto gateway
 * Description:       This is a wordpress plugin to work with Erfan Poursina's crypto gateway
 * Version:           1.0.0
 * Author:            Emad Poursina
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       crypto-gateway
 * Domain Path:       /languages
 */

 function init_asan_pardakht_gateway_class() {
    class WC_Gateway_Asan_Pardakht_Gateway extends WC_Payment_Gateway {}
}

add_action( 'plugins_loaded', 'init_asan_pardakht_gateway_class' );

// Show gateway setting
function add_your_gateway_class( $methods ) {
    $methods[] = 'WC_Gateway_Your_Gateway'; 
    return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'add_your_gateway_class' );