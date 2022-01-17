<?php

// Prevent user from access directly
if (!defined('ABSPATH')) {
    exit;
}

// Initialize the plugin
function init_epoursina_asan_pardakht_gateway_class() {

    if (!function_exists('add_epoursina_asan_pardakht_gateway_class') && class_exists('WC_Payment_Gateway') && !class_exists('WC_Gateway_Epoursina_Asan_Pardakht_Gateway')) {

		// Show gateway setting
		add_filter( 'woocommerce_payment_gateways', 'add_epoursina_asan_pardakht_gateway_class' );
		function add_epoursina_asan_pardakht_gateway_class( $methods ) {
			$methods[] = 'WC_Gateway_Epoursina_Asan_Pardakht_Gateway'; 
			return $methods;
		}

		// Add a new currency
		add_filter('woocommerce_currencies', 'add_Tether_currency');
		function add_Tether_currency($currencies){
			$currencies['Tether'] = __('تتر', 'woocommerce');

			return $currencies;
		}

		add_filter('woocommerce_currency_symbol', 'add_Tether_currency_symbol', 10, 2);
		function add_Tether_currency_symbol($currency_symbol, $currency){
			if($currency === 'Tether') {
				$currency_symbol = "T";
			}
			return $currency_symbol;
		}

		// Variables
		define('plugin_dir_path', __FILE__);

		class WC_Gateway_epoursina_Asan_Pardakht_Gateway extends WC_Payment_Gateway {
			private $gatewayKey;
			private $failedMassage;
			private $successMassage;

			function __construct() {
				$this -> id = 'epoursina_asan_pardakht_gateway';
				$this -> icon = apply_filters('WC_Epoursina_AsanPar_logo', WP_PLUGIN_URL . '/' . plugin_basename(__DIR__) . '/assets/images/logo.png');
				$this -> has_fields = false; 
				$this -> method_title = __('درگاه رمزارز آسان پرداخت', 'woocommerce');
				$this -> method_description = __('تنظیمات افزونه درگاه پرداخت رمزارز تتر آسان پرداخت', 'woocommerce');
				$this -> supports = array(
					'products'
				);

				$this -> init_form_fields();
				$this -> init_settings();

				$this->supports = array(
					'products'
				);

				$this -> title = $this -> settings['title'];
				$this -> description = $this -> settings['description'];

				$this -> gatewayKey = $this -> settings['gatewayKey'];

				$this -> successMassage = $this -> settings['success_massage'];
				$this -> failedMassage = $this -> settings['failed_massage'];

				if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
					add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
				} else {
					add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
				}

				// Save the settings
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

				// Register callback handler
				add_action( 'woocommerce_api_epoursina_asan-pardakht-callback', array( $this, 'process_getway_final_response' ) );
			}

			/**
			 * Initialize gateway setting form fileds
			 */
			public function init_form_fields() {
				$this->form_fields = array(
					'base_config' => array(
						'title' => __('تنظیمات اصلی', 'woocommerce'),
						'type' => 'title',
						'description' => 'این تنظیمات اصلی درگاه است',
					),
					'enabled' => array(
						'title' => __( 'فعال کردن درگاه', 'woocommerce' ),
						'type' => 'checkbox',
						'label' => __( 'فعال کردن درگاه پرداخت', 'woocommerce' ),
						'default' => 'no',
						'description' => __('برای فعال سازی درگاه نیاز است که تیک را بزنید.', 'woocommerce'),
						'default' => 'yes',
						'desc_tip' => true,
					),
					'title' => array(
						'title' => __( 'عنوان درگاه', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'نام نمایشی درگاه به مشتری', 'woocommerce' ),
						'default' => __( 'درگاه آسان پرداخت', 'woocommerce' ),
						'desc_tip'      => true,
					),
					'description' => array(
						'title' => __( 'توضیحات مشتری', 'woocommerce' ),
						'type' => 'text',
						'description' => __('توضیحاتی که به مشتری در حین خرید نمایش داده میشود', 'woocommerce'),
						'desc_tip' => true,
						'default' => __('قیمت دور خود را به وسیله تتر پرداخت کنید.', 'woocommerce')
					),
					'account_config' => array(
						'title' => __('تنظیمات مربوط به سایت حساب شما در سایت آسان پرداخت', 'woocommerce'),
						'type' => 'title',
						'description' => '',
					),
					'gatewayKey' => array(
						'title' => __('کد درگاه', 'woocommerce'),
						'type' => 'text',
						'description' => __('کد درگاه شما که از سایت آسان پرداخت باید بگیرید', 'woocommerce'),
						'default' => '',
						'desc_tip' => true
					),
					'payment_config' => array(
						'title' => __('پیغام های نمایشی', 'woocommerce'),
						'type' => 'title',
						'description' => 'این پیغام ها به کاربر نمایش داده می شوند.',
					),
					'success_massage' => array(
						'title' => __('پیام پرداخت موفق', 'woocommerce'),
						'type' => 'textarea',
						'description' => __('پیغام یا شورت کدی که در صورت موفقیت آمیز بودن عملیات به کاربر نمایش داده می شود را این جا بنویسید. شما می توانید از عبارت {authority} برای نشان دادن کد رهگیری آسان پرداخت استفاده کنید.', 'woocommerce'),
						'default' => __('با تشکر از اعتماد شما', 'woocommerce'),
					),
					'failed_massage' => array(
						'title' => __('پیام پرداخت ناموفق', 'woocommerce'),
						'type' => 'textarea',
						'description' => __('پیغام یا شورت کدی که در صورت نبودن عملیات به کاربر نمایش داده می شود را این جا بنویسید. شما می توانید از عبارت {fault} برای نمایش دلیل خطا استفاده کیند.', 'woocommerce'),
						'default' => __('پرداخت شما موفقیت آمیز نبود. لطفا دوباره امتحان کنید و در صورت ادامه پیدا کرد مشکل با مدیریت سایت تماس بگیرید.', 'woocommerce'),
					),
				);
			}

			/**
			 * Process the order
			 */
			public function process_payment($order_id) {
				// Set variables
				global $woocommerce;
				$order = wc_get_order( $order_id );
				$currency = $order -> get_currency();
				$strToLowerCurrency = strtolower($currency);
				$amount = (int)$order -> order_total;
				$callbackUrl = add_query_arg('wc_order', $order_id, WC()->api_request_url('epoursina_asan-pardakht-callback'));

				// Process the payment
				if(strtolower($currency) === strtolower('Tether') ||
				strtolower($currency) === strtolower('USD') ){
					/**
					 * Get authority from gateway site
					 */
					$endpoint = 'http://localhost:5000/payment/paymentRequest';
					$data = array(
						'amount' => $amount,
						'callbackURL' => $callbackUrl,
						'gatewayKey' => $this -> gatewayKey,
					);
					$response_body = $this -> sendRequest($endpoint, json_encode($data));

					/**
					 * Handle first response,
					 * get autherity and send customer to gateway website
					 */

					if($response_body["status"] === 200) {
						$authority = $response_body['authority'];
						WC() -> session -> set('epoursina_asan-pardakht-gateway-authority', $authority);

						return array(
							'result' => 'success',
							'redirect' => "http://localhost:5000/payment/startPay/" . $authority 
						);
					} elseif($response_body === false) {
						$error_message = 'درگاه پرداخت آسان پرداخت پاسخ نمی دهد.';
					}else {
						$error_message .= "<p>خطا:‌ " . $response_body["message"] . "</p>";
					}
				}else {
					$error_message = 'ارز نا معتبر: لطفا از یکی از ارزهای تتر یا دلار استفاده کنید.';
				}

				// Error handling
				if(!empty($error_message) && $error_message) {
					$Note = sprintf(__('خطا در هنگام ارسال به درگاه: %s', 'woocommerce'), $error_message);
					$order -> add_order_note($Note);

					$Notice = sprintf(__('در هنگام اتصال به درگاه خطای زیر رخ داده است : <br/>%s', 'woocommerce'), $error_message);
					wc_add_notice($Notice, 'error');
				}
			}

			/**
			 * Process gateway response after callback
			 */
			public function process_getway_final_response() {
				$order_id = explode('?', $_GET["wc_order"])[0];

				global $woocommerce;

				if($order_id) {
					$order = wc_get_order( $order_id );

					if ($order -> status !== 'completed') {
						$authority = WC() -> session -> get('epoursina_asan-pardakht-gateway-authority');
						WC() -> session -> __unset('epoursina_asan-pardakht-gateway-authority');
						$response_authority = explode('=', explode('?', $_GET["wc_order"])[1])[1];

						// Compare session data to response body
						if($_GET["status"] === "OK" && $response_authority === $authority) {
							/**
							 * Final check for payment success
							 */
							$endpoint = 'http://localhost:5000/payment/checkPay';
							$data = array(
								'authority' => $authority,
								'gatewayKey' => $this -> gatewayKey,
							);
							$response_body = $this -> sendRequest($endpoint, json_encode($data));

							/**
							 * Handle second response,
							 * Final check for successfull payment
							 */
						
							if($response_body["status"] === 200) {
								// Remove cart
								$woocommerce->cart->empty_cart();
								$order->payment_complete();
								$order->add_order_note( __('خرید کامل شد.', 'woothemes') );
								$order->add_order_note( __("authority code: $authority", 'woothemes') );
								update_post_meta($order_id, 'epoursina_asan_authority', $authority);
								
								// Return thankyou redirect
								if(wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)))) {
									exit;
								}
							} elseif($response_body === false) {
								$error_message = 'درگاه پرداخت آسان پرداخت پاسخ نمی دهد.';
							}else {
								$error_message = "<p>پرداخت انجام نشد.</p>";
								$error_message .= "<p>کد درخواست:‌ " . $response_body["status"] . "</p>";
								$error_message .= "</p>پیغام خطا: " . $response_body["message"] . "</p>";

								$order->update_status('failed', __('', 'epoursina_asan_pardakht_gateway'));
								wc_add_notice( __('خطای پرداخت۲: ', 'epoursina_asan_pardakht_gateway') . $error_message, 'error' );
							}
						}else {
							$error_message = "پرداخت انجام نشد.";
							$order -> update_status('failed', __('', 'epoursina_asan_pardakht_gateway'));
							wc_add_notice( __('خطای پرداخت ۱: ', 'epoursina_asan_pardakht_gateway') . $error_message, 'error' );
						}

						$Notice = sprintf(__('در هنگام اتصال به درگاه خطای زیر رخ داده است : <br/>%s', 'woocommerce'), $error_message);
						wc_add_notice($Notice, 'error');
						return;
					}

					$authority = get_post_meta($order_id, 'epoursina_asan_authority', true);
					$Notice = wpautop(wptexturize($this -> successMassage));
					$Notice = str_replace('{authority}', $authority, $Notice);
					wc_add_notice($Notice, 'success');
					// Return thankyou redirect
					if(wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)))) {
						exit;
					}
				}

				$error_message = __('سفارشی با این شماره وجود ندارد.', 'woocommerce');
				$Notice = wpautop(wptexturize($this -> failedMassage));
				$Notice = str_replace('{fault}', $Notice, $Notice);

				// Error handling
				if(!empty($error_message) && $error_message) {
					$Notice = sprintf(__('<p>در هنگام اتصال به درگاه خطای زیر رخ داده است : <br/>%s</p>', 'woocommerce'), $error_message);
					wc_add_notice($Notice, 'error');

					if(wp_redirect($woocommerce->cart->get_checkout_url())) {
						exit;
					}
				}
			}

			/**
			 * Send request
			 * @param $url The url request should be sent to
			 * @param $args The body of the request
			 */
			public function sendRequest($url, $args) {
				try {
					$number_of_connection_tries = 5;
					$response = null;
					while ($number_of_connection_tries > 0) {
						$response = wp_remote_post($url, array(
							'body' => $args,
							'headers' => array(
								'Content-Type' => 'application/json',
								'Content-Length' =>   strlen($args) ,
							)
						));
						if (is_wp_error($response)) {
							$number_of_connection_tries--;
							continue;
						} else {
							break;
						}
					}
					if(is_wp_error($response)) {
						throw new Exception('درگاه پاسخ نداد', 1);
					}

					$body = wp_remote_retrieve_body($response);

					return json_decode($body, true);
				} catch (Exception $ex) {
					return false;
				}
			}
		}
	}
}

add_action( 'plugins_loaded', 'init_epoursina_asan_pardakht_gateway_class', 0);
