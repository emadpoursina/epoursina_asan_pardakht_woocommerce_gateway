<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @package           Epoursina_asan_pardakht_woocommerce_gateway
 *
 * @wordpress-plugin
 * Plugin Name:       Epoursina Asan Pardakht Woocommerce Gateway
 * Description:       This is client wordpress plugin for Asan pardakht crypto gateway
 * Version:           1.0.0
 * Author:            Emad Poursina
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       asan-pardakht-gateway
 * Domain Path:       /languages
*/

// Check for woocommerce activation
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

include('class-wc-gateway-asan-pardakht.php');