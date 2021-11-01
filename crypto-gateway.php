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

 // Check for woocommerce activation
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

 function init_asan_pardakht_gateway_class() {
    class WC_Gateway_Asan_Pardakht_Gateway extends WC_Payment_Gateway {
		function __construct() {
			$this -> id = "asan_pardakht_gateway";
			$this -> icon = "";
			$this -> method_title = "درگاه رمزارز آسان پرداخت";
			$this -> method_description = "این یک درگاه رمزارز است.";
			$this -> init_form_fields();
			$this -> init_settings();

			$this -> title = $this -> get_option('title');

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * Initialize Gateway from files setting section
		 */
		public function init_form_fields() {
			// Setting form fields
			$this->form_fields = array(
				'enabled' => array(
					'title' => __( 'فعال/غیرفعال', 'woocommerce' ),
					'type' => 'checkbox',
					'label' => __( 'فعال کردن درگاه پرداخت', 'woocommerce' ),
					'default' => 'yes'
				),
				'title' => array(
					'title' => __( 'عنوان', 'woocommerce' ),
					'type' => 'text',
					'description' => __( 'درگاه پرداخت رمزارز آسان پرداخت', 'woocommerce' ),
					'default' => __( 'درگاه آسان پرداخت', 'woocommerce' ),
					'desc_tip'      => true,
				),
				'description' => array(
					'title' => __( 'عنوان مشتری', 'woocommerce' ),
					'type' => 'textarea',
					'default' => ''
				)
			);
		}
	}
}

add_action( 'plugins_loaded', 'init_asan_pardakht_gateway_class' );

// Show gateway setting
function add_asan_pardakht_gateway_class( $methods ) {
    $methods[] = 'WC_Gateway_Asan_Pardakht_Gateway'; 
    return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'add_asan_pardakht_gateway_class' );