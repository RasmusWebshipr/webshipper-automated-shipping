<?php
require_once( "class.webshipr-order-html.php" );

if ( ! class_exists( 'WebshiprWC' ) ) {

	class WebshiprWC {

		protected $option_name = 'webshipr_options';
		protected $data = array(
			'api_key'        => '',
			'auto_process'   => array(),
			'google_api_key' => '',
			'sku_split'      => false
		);

		public $options;


		// Constructor to be initialized
		public function __construct() {

			global $woocommerce;

			// Backend order view
			add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'show_on_order' ) );

			// Hook backend admin stuff
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'add_page' ) );

			// Hook cart PUP content
			add_action( 'woocommerce_review_order_before_order_total', array( $this, 'append_dynamic' ) );

			// Register and load JS and CSS
			add_action( 'wp_loaded', array( $this, 'register_frontend' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_backend' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend' ) );

			// Hook autoprocess
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'order_placed' ) );

			// Hook on update order meta to ensure correct delivery address
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'override_delivery' ) );

			// Hook to do checkout validation
			add_action( 'woocommerce_checkout_process', array( $this, 'validate_on_process' ) );


			// Hook ajax methods
			add_action( 'wp_ajax_nopriv_check_rates', array( $this, "check_rates" ) );
			add_action( 'wp_ajax_check_rates', array( $this, 'check_rates' ) );
			add_action( 'woocommerce_after_checkout_shipping_form', array( $this, 'set_ajaxurl' ) );
			add_action( 'wp_ajax_nopriv_get_shops', array( $this, "ajax_get_shops" ) );
			add_action( 'wp_ajax_get_shops', array( $this, 'ajax_get_shops' ) );


			// Localization
			load_plugin_textdomain( 'WebshiprWC', false, basename( dirname( __FILE__ ) ) . '/languages' );

			// Initialize settings
			$this->options = get_option( 'webshipr_options' );



			/*
			  Module can autoprocess orders in different states. It used to only autoprocess on payment_complete, but
			  since not all payment modules fires this action, it has been decided to autoprocess on different statuses ( by setting ).
			*/
			$auto_process = isset( $this->options['auto_process'] ) ? $this->options['auto_process'] : false;

			if ( isset( $auto_process ) && is_array( $auto_process ) ) {

				// Hook the different statuses
				if ( in_array( "status_processing", $auto_process ) ) {
					add_action( 'woocommerce_order_status_processing', array( $this, 'auto_process' ) );
				}

				if ( in_array( "status_pending", $auto_process ) ) {
					add_action( 'woocommerce_order_status_pending', array( $this, 'auto_process' ) );
				}

				if ( in_array( "status_completed", $auto_process ) ) {
					add_action( 'woocommerce_order_status_completed', array( $this, 'auto_process' ) );
				}

				if ( in_array( "status_pre-ordered", $auto_process ) ) {
					add_action( 'woocommerce_order_status_pre-ordered', array( $this, 'auto_process' ) );
				}

				if ( in_array( "payment_complete", $auto_process ) ) {
					add_action( 'woocommerce_payment_complete', array( $this, 'auto_process' ) );
				}


			} else if ( isset( $auto_process ) && $auto_process && (int) $auto_process == 1 ) { // Support the old setting type
				add_action( 'woocommerce_payment_complete', array( $this, 'auto_process' ) );
			}
		}


		// Register JS scripts
		function register_frontend() {

			// Register CSS
			wp_register_style( "ws_css", plugins_url( "css/wspup.min.css", __FILE__ ) );

			// Register JS
			if ( isset( $this->options['google_api_key'] ) && ( strlen( $this->options['google_api_key'] ) > 0 ) ) {
				wp_register_script( "ws_maps", "https://maps.googleapis.com/maps/api/js?key=" . $this->options['google_api_key'] . "&sensor=false", array(), WEBSHIPR_VER, true );
			} else {
				wp_register_script( "ws_maps", "https://maps.googleapis.com/maps/api/js?sensor=false" );
			}

			wp_register_script( "ws_maplabel", plugins_url( "js/maplabel.min.js", __FILE__ ), array(), WEBSHIPR_VER, true );
			wp_register_script( "ws_pup", plugins_url( "js/wspup.min.js", __FILE__ ), array( 'jquery' ), WEBSHIPR_VER, true );

			wp_register_script( "ws_js", plugins_url( "js/webshipr.min.js", __FILE__ ), array( 'jquery' ), WEBSHIPR_VER, true );

		}

		function register_backend() {
			// Register backend JS
			wp_register_script( "ws_backend_js", plugins_url( "/js/ws_backend.min.js", __FILE__ ), array( 'jquery' ), WEBSHIPR_VER, true );

		}

		// Enqueue scripts
		function enqueue_frontend() {

			// CSS
			wp_enqueue_style( 'ws_css' );

			// JS
			wp_enqueue_script( 'ws_maps' );
			wp_enqueue_script( 'ws_maplabel' );
			wp_enqueue_script( 'ws_pup' );
			wp_enqueue_script( 'ws_js' );

			// Hook ajax stuff
			wp_localize_script( 'check_rates', 'wsAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
			wp_localize_script( 'get_shops', 'wsAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		}

		// Enqueue scripts backend
		function enqueue_backend() {

			// CSS
			wp_enqueue_style( 'ws_css' );

			// JS
			wp_enqueue_script( "jquery" );
			wp_enqueue_script( 'ws_js' );
			wp_enqueue_script( 'ws_backend_js' );

			// Hook ajax stuff
			wp_localize_script( 'check_rates', 'wsAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
			wp_localize_script( 'get_shops', 'wsAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		}

		// Autoprocess
		public function auto_process( $order_id ) {
			// Autoprocess logic
			if ( (int) $this->options['auto_process'] == 1 && (int) $order_id > 0 ) {
				$woo_order = new WC_Order( $order_id );

				// If no products on order requires shipping, we wont autoprocess.
				if ( $this->requires_shipping( $woo_order ) == false ) {
					return false;
				}

				// Depending on woocommerce version, get the shipping method / rate id
				if(method_exists($woo_order, 'get_shipping_methods')) {
					$arr = $woo_order->get_shipping_methods();
					if ($arr) {
						$woo_method_array = reset($arr);
						$woo_method_id = $woo_method_array->get_meta('shipping_rate_id', true);
						if (!$woo_method_id) {
							$woo_method_id = $woo_method_array["method_id"];
						}
					} else {
						$woo_method_id = "";
					}
				} else {
					$woo_method_id = $woo_order->shipping_method->get_meta('shipping_rate_id');
				}

				$ws_rate_id = (preg_match("/WS/", $woo_method_id) ? str_replace("WS", "", $woo_method_id) : -1);


				// Place order
				$this->WooOrderToWebshipr( $woo_order, $ws_rate_id );
			}

		}

		// Check if order requires shipping
		private function requires_shipping( $wc_order ) {
			$items             = $wc_order->get_items();
			$requires_shipping = false;
			foreach ( $items as $item ) {
				$product = new WC_Product( $item["product_id"] );
				if ( $product->needs_shipping() ) {
					$requires_shipping = true;
				}
			}

			return $requires_shipping;
		}


		// Get shops AJAX
		public function ajax_get_shops() {

			// Webshipr API Instance
			$api     = $this->ws_api();
			$country = ( isset( $_POST["country"] ) && strlen( $_POST["country"] ) > 0 ? $_POST["country"] : 'DK' );

			// Check the method
			switch ( $_POST["method"] ) {
				case 'getByZipCarrier':
					wp_send_json_success( $api->getShopsByCarrierAndZip( $_POST["zip"], $_POST['carrier'] ) );
					break;
				case 'getByZipRate':
					wp_send_json_success( $api->getShopsByRateAndZip( $_POST["zip"], $country, $_POST['rate_id'] ) );
					break;
				case 'getByAddressRate':
					wp_send_json_success( $api->getShopsByRateAndAddress( $_POST['address'], $_POST['zip'], $country, $_POST['rate_id'] ) );
					break;
				default:
					wp_send_json_success( array( "error" => 'Method not defined' ) );
					break;
			}
		}

		// Validate if pakkeshop is required
		public function validate_on_process() {

			global $woocommerce;

			$rate_id = "";
			if ( is_array( $_REQUEST["shipping_method"] ) ) {
				$rate_id = $_REQUEST["shipping_method"][0];
			} else {
				$rate_id = $_REQUEST["shipping_method"];
			}

			$api                 = $this->ws_api();
			$rates               = $api->GetShippingRates();
			$is_dyn_required     = false;
			$is_comment_required = false;

			// Loop through rates, and check if the rate is dyn
			if ( is_array( $rates ) ) {
				foreach ( $rates as $rate ) {
					if ( $rate->dynamic_pickup && ( ( "WS" . $rate->id ) == $rate_id ) ) {
						$is_dyn_required = true;
					}
					if ( $rate->flex_delivery && ( ( "WS" . $rate->id ) == $rate_id ) ) {
						$is_comment_required = true;
					}
				}
			}

			// Add error
			if ( $is_dyn_required && ( ! isset( $_REQUEST["wspup_id"] ) || strlen( $_REQUEST["wspup_id"] ) == 0 ) ) {
				wc_add_notice( __( 'Select a pickup point to proceed', 'WebshiprWC' ), "error" );
			}


			if ( $is_comment_required && ( ! isset( $_REQUEST["order_comments"] ) || strlen( $_REQUEST["order_comments"] ) == 0 ) ) {
				wc_add_notice( __( 'Please add a comment describing where the package can be placed', 'WebshiprWC' ), "error" );
			}

		}

		// Set ajax url in checkout
		public function set_ajaxurl() {
			global $woocommerce;
			?>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    wspup.ajaxUrl = '<?php echo $woocommerce->ajax_url(); ?>';
                    ws_ajax_url = '<?php echo $woocommerce->ajax_url(); ?>';
                });
            </script>
			<?php
		}

		// Override delivery info
		public function override_delivery( $order_id ) {

			// Some themes started to not register any delivery address.
			$order = new WC_Order( $order_id );

			// If PUP Shipment
			if ( isset( $_POST["wspup_id"] ) && strlen( $_POST["wspup_id"] ) > 0 ) {
				update_post_meta( $order_id, '_shipping_first_name', $order->billing_first_name );
				update_post_meta( $order_id, '_shipping_last_name', $order->billing_last_name );
				update_post_meta( $order_id, '_shipping_address_1', $_POST["wspup_address"] );
				update_post_meta( $order_id, '_shipping_address_2', '' );
				update_post_meta( $order_id, '_shipping_company', $_POST["wspup_name"] );
				update_post_meta( $order_id, '_shipping_city', $_POST["wspup_city"] );
				update_post_meta( $order_id, '_shipping_postcode', $_POST["wspup_zip"] );
			}

		}

		// Is rate dynamic, Ajax
		public function check_rates() {
			// Get Rate
			$rate_id         = esc_sql( $_REQUEST['rate_id'] );
			$api             = $this->ws_api();
			$rates           = $api->GetShippingRates();
			$is_dyn_required = false;

			// Loop through rates, and check if the rate is dyn
			if ( is_array( $rates ) ) {
				foreach ( $rates as $rate ) {
					if ( $rate->dynamic_pickup && ( ( "WS" . $rate->id ) == $rate_id ) ) {
						$is_dyn_required = true;
					}
				}
			}

			// Send response
			wp_send_json_success( array( "rate" => $_REQUEST['rate_id'], "dyn" => $is_dyn_required ) );
		}


		// Order placed
		public function order_placed( $order_id ) {

			if ( isset( $_POST["wspup_id"] ) && strlen( $_POST["wspup_id"] ) > 0 ) {
				// Save pickup point id
				add_post_meta( $order_id, 'wspup_pickup_point_id', $_POST["wspup_id"] );
			}

		}


		// Method to handle dynamic pickup places
		public function append_dynamic() {
			global $woocommerce;

			// Get selected rate id
			if ( is_array( $woocommerce->session->chosen_shipping_methods ) ) {
				$rate_id = $woocommerce->session->chosen_shipping_methods[0];
			} elseif ( $_POST && is_array( $_POST["shipping_method"] ) ) {
				$rate_id = $_POST["shipping_method"][0];
			} elseif ( $_POST && is_string( $_POST["shipping_method"] ) ) {
				$rate_id = $_POST["shipping_method"];
			} else {
				$rate_id = "not_known";
			}

			// Is it a webshipr rate at all?
			if ( preg_match( "/WS/", $rate_id ) ) {
				$this_rate = $this->get_rate_details( $rate_id );

				// If dynamic, load puptpl
				if ( $this_rate->dynamic_pickup ) {
					$rate = str_replace( "WS", "", $rate_id );
					global $this_rate;
					include 'puptpl.php';
				}

			}
		}

		// Get WS rate from WS shipping id
		private function get_rate_details( $rate_id ) {
			$api = $this->ws_api();
			if ( $api->CheckConnection() ) {
				foreach ( $api->GetShippingRates() as $rate ) {
					if ( (int) $rate->id == (int) str_replace( "WS", "", $rate_id ) ) {
						return $rate;
					}
				}
			} else {
				return false;
			}
		}


		// White list our options using the Settings API
		public function admin_init() {
			register_setting( 'webshipr_options', $this->option_name, array( $this, 'validate' ) );
		}

		// Add entry in the settings menu
		public function add_page() {
			add_options_page( 'webshipr options', 'Webshipr options', 'manage_options', 'webshipr_options', array(
				$this,
				'options'
			) );
		}


		// Print the settings menupage itself
		public function options() {
			$options = get_option( $this->option_name );
			?>
            <div class="wrap">
                <h2>Webshipr account options</h2>
                <form method="post" action="options.php">
					<?php settings_fields( 'webshipr_options' ); ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Webshipr API key:</th>
                            <td><input type="text" name="<?php echo $this->option_name ?>[api_key]"
                                       value="<?php echo $options['api_key']; ?>"/></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Google Maps API key:</th>
                            <td>
                                <input type="text" name="<?php echo $this->option_name ?>[google_api_key]"
                                       value="<?php echo $options['google_api_key']; ?>"/>
                                <p><i>In order to use maps for Drop Points you will need to create a free Google Maps
                                        API Token.</i></p>
                                <p><a href="http://support.webshipr.com/article/get-a-google-maps-api-key/">Click here
                                        to see how to create an API token for Google Maps</a></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Autoprocess shipments</th>
                            <td>
								<?php $autoprocess = $options["auto_process"]; ?>
                                <select name="<?php echo $this->option_name ?>[auto_process][]?>" multiple='multiple'>
                                    <option value="payment_received" <?php echo ( is_array( $autoprocess ) && in_array( "payment_received", $autoprocess ) ) ? "selected" : "" ?>>
                                        Payment complete hook
                                    </option>
                                    <option value="status_pending" <?php echo ( is_array( $autoprocess ) && in_array( "status_pending", $autoprocess ) ) ? "selected" : "" ?>>
                                        Status pending
                                    </option>
                                    <option value="status_completed" <?php echo ( is_array( $autoprocess ) && in_array( "status_completed", $autoprocess ) ) ? "selected" : "" ?>>
                                        Status completed
                                    </option>
                                    <option value="status_processing" <?php echo ( is_array( $autoprocess ) && in_array( "status_processing", $autoprocess ) ) ? "selected" : "" ?>>
                                        Status processing
                                    </option>
									<option value="status_pre-ordered" <?php echo ( is_array( $autoprocess ) && in_array( "status_pre-ordered", $autoprocess ) ) ? "selected" : "" ?>>
                                        Status pre-ordered
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2">
                                <i style="font-weight: normal;">Autoprocess shipments means that the order will
                                    automatically be sent to webshipr in a given state.</i>
                            </th>
                        </tr>
						<tr valign="top">
							<th scope="row">Get location from SKU </br>(sku - location)</th>
							<td>
								<?php if($options['sku_split']) { $sku_checked = ' checked="checked" '; } ?>
								<input <?php echo $sku_checked ?> type="checkbox" name="webshipr_options[sku_split]"/>
							</td>
						</tr>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>"/>
                    </p>
                </form>

            </div>
			<?php
		}

		// Validate settings
		public function validate( $input ) {



			$valid                   = array();
			$valid['api_key']        = sanitize_text_field( $input['api_key'] );
			$valid['google_api_key'] = sanitize_text_field( $input['google_api_key'] );
			$valid['auto_process']   = isset($input['auto_process'])?$input['auto_process']: '';
			$valid['sku_split']      = isset($input['sku_split']) ? true : false;

			$api   = $this->ws_api( $valid['api_key'] );
			$check = $api->CheckConnection();

			if ( ! $check ) {
				add_settings_error(
					'api_key',
					'not_connected',
					'Please enter a valid key from your webshipr account. It can be found in your profile under the webshop.',
					'error'
				);

				$valid['api_key']        = $this->data['api_key'];
				$valid['google_api_key'] = $this->data['google_api_key'];
			} else {
				add_settings_error(
					'api_key',
					'connected',
					'Congratulations! Shop is now connected as ' . $check->Shop_name,
					'updated'
				);
			}

			return $valid;
		}

		public static function activate( $network_wide ) {

			// If multisite - loop through the sites and activate webshipr module
			if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {

				global $wpdb;

				// Get this so we can switch back to it later
				$current_blog = $wpdb->blogid;
				// For storing the list of activated blogs
				$activated = array();

				// Get all blogs in the network and activate plugin on each one
				$sql      = "SELECT blog_id FROM $wpdb->blogs";
				$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					update_option( 'webshipr_options', array(
						'api_key'        => '',
						'auto_process'   => array(),
						'google_api_key' => '',
						'sku_split'		 => false
					)); // The normal activation function
					$activated[] = $blog_id;
				}

				// Switch back to the current blog
				switch_to_blog( $current_blog );

				// Store the array for a later function
				update_site_option( 'webshipr_activated', $activated );

			} else {
				update_option( 'webshipr_options', array(
					'api_key'        => '',
					'auto_process'   => array(),
					'google_api_key' => '',
					'sku_split'		 => false
				) );
			}

		}


		// Method to display webshipr on orders
		public function show_on_order() {

			$wooOrder = new WC_Order( $_GET["post"] );

			if(!current_user_can('manage_woocommerce')) return;
			// If user tried to process or reprocess - handle this.
			if ( isset( $_GET["webshipr_process"] ) ) {
				if ( $_GET["webshipr_process"] == 'true' ) {
					$this->WooOrderToWebshipr( $wooOrder, $_GET["ws_rate"], ( isset( $_GET["swipbox"] ) ? $_GET["swipbox"] : '' ) );
				}
			}
			if ( isset( $_GET["webshipr_reprocess"] ) ) {
				if ( $_GET["webshipr_reprocess"] == 'true' ) {
					$api = $this->ws_api();
					$this->UpdateWebshiprOrder( $wooOrder, $_GET["ws_rate"], ( isset( $_GET["swipbox"] ) ? $_GET["swipbox"] : '' ) );
				}
			}
			if ( isset( $_GET["webshipr_change"] ) ) {
				if ( $_GET["webshipr_change"] == 'true' ) {
					$this->ChangeShippingMethod( $wooOrder, $_GET["ws_rate"], $_GET["name"]);
				}
			}
			if ( isset( $_GET["webshipr_droppoint"] ) ) {
				if ( $_GET["webshipr_droppoint"] == 'true' ) {
					$this->SetDroppoint( $wooOrder, $_GET["dp_id"], $_GET["dp_street"], $_GET["dp_zip"], $_GET["dp_city"], $_GET["dp_name"], $_GET["dp_country"]);
				}
			}

			$orderHtml = new WebshiprOrderHtml( $wooOrder );
			$orderHtml->RenderHTML();

		}

		// Get API instance
		public function ws_api( $key = false ) {
			if ( ! $key ) {
				$key = $this->options['api_key'];
			}

			return new WebshiprAPI( API_RESOURCE, $key );
		}

		// Method to create the order in Webshipr
		private function WooOrderToWebshipr( $woo_order, $rate_id, $swipbox = null ) {

			// Generate shipment
			$shipment = $this->prepareShipment( $woo_order, $rate_id, $swipbox );

			// Send the order
			$api = $this->ws_api();
			$api->CreateShipment( $shipment );

		}

		// Mehtod to update order in webshipr
		private function UpdateWebshiprOrder( $woo_order, $rate_id, $swipbox ) {
			// Generate shipment
			$shipment = $this->prepareShipment( $woo_order, $rate_id, $swipbox );

			// Update the order
			$api = $this->ws_api();
			$api->UpdateShipment( $shipment );

		}

		// Method to update shipping method on subscription
		private function ChangeShippingMethod( $woo_order, $rate_id, $rate_name ) {
			if (method_exists($woo_order, 'get_shipping_method') && $woo_order->get_shipping_method()) {
				foreach ( $woo_order->get_shipping_methods() as $shipping_method ) {
					$shipping_method->set_method_id( 'WS' . $rate_id );
					$shipping_method->set_method_title( $rate_name );
					$woo_order->save();
				}
			} else {
				$shipping_method = new WC_Shipping_Rate("WS".$rate_id, $rate_name, 0, array());
				$woo_order->add_shipping($shipping_method);
			}
		}

		// Method to update shipping method on subscription
		private function SetDroppoint( $woo_order, $dp_id, $dp_street, $dp_zip, $dp_city, $dp_name, $dp_country) {
			$woo_order->set_shipping_company( $dp_name );
			$woo_order->set_shipping_address_1( $dp_street );
			$woo_order->set_shipping_address_2( "" );
			$woo_order->set_shipping_city( $dp_city );
			$woo_order->set_shipping_postcode( $dp_zip );
			$woo_order->set_shipping_country( $dp_country );
			$woo_order->save();

			update_post_meta($woo_order->id, 'wspup_pickup_point_id', $dp_id);
		}

		// Build shipment object prepared for the API
		private function prepareShipment( $woo_order, $rate_id, $swipbox ) {
			$items = $woo_order->get_items();

			// Create Items and collect info
			$ws_items = array();

			$options = get_option( 'webshipr_options' );


			// Append items
			foreach ( $items as $item ) {
				try {
					// Get apropriate product info
					if ( (int) $item["variation_id"] > 0 ) {
						try {
							$product = new WC_Product_Variation( $item["variation_id"] ); // Variation inherits Product
						} catch ( Exception $e ) { // They might have removed the variation.
							$product = new WC_Product( $item["product_id"] );
						}
					} else {
						$product = new WC_Product( $item["product_id"] );
					}

					// Get apropriate weight
					$weight_uom        = get_option( 'woocommerce_weight_unit' );
					$weight_multiplier = $weight_uom == 'kg' ? 1000 : 1;
					$weight            = (double) $product->get_weight() * (double) $item["qty"] * $weight_multiplier;

					// Get financial data for item.
					// In webshipr price = unit price
					$price = (double) $item["line_subtotal"] / (double) $item["qty"];

					// Ensure to not divide by zero
					if ( (double) $item["line_tax"] == 0 || (double) $item["line_total"] == 0 ) {
						$tax_percent = 0;
					} else {
						$tax_percent = (double) $item["line_tax"] / (double) $item["line_total"] * 100;
					}

					$arr_vars = array();
					foreach ( $item["item_meta_array"] as $meta ) {
						if ( substr( $meta->key, 0, 1 ) != '_' && $item["name"] != $meta->value ) {
							$arr_vars[] = $meta->key . ": " . $meta->value;
						}
					}
					$description = $item["name"] . ". " . join( ", ", $arr_vars );

					if (isset($options['sku_split'])) {
						$sku = $this->getSku($product->get_sku());
						$location = $this->getLocation($product->get_sku());
					} else {
						$sku = $product->get_sku();
						$location = '';
					}

					// Add items
					$ws_items[] = new ShipmentItem( $description,
						$sku,
						$item["product_id"],
						$item["qty"],
						"pcs",
						$weight,
						$location,
						$price,
						$tax_percent,
						$product->get_attribute( 'tarif_number' ),
						$product->get_attribute( 'origin_country' )
					);
				} catch ( Exception $e ) {
					// For some reason we could not fetch the product info. The product might be removed.
					// Send the message to webshipr anyways, so the customer or support can see the error.
					error_log( "Error sending item to webshipr.: $e->getMessage()" );
					$ws_items[] = new ShipmentItem( $e->getMessage(),
						'SKU',
						'9999999',
						1,
						"pcs",
						1000,
						'LOC',
						0,
						0
					);
				}
			}


			// Billing Address
			$bill_adr               = new ShipmentAddress();
			$bill_adr->Address1     = $woo_order->billing_address_1;
			$bill_adr->Address2     = $woo_order->billing_address_2;
			$bill_adr->City         = $woo_order->billing_city;
			$bill_adr->ContactName  = $woo_order->billing_company;
			$bill_adr->ContactName2 = $woo_order->billing_first_name . " " . $woo_order->billing_last_name;
			$bill_adr->CountryCode  = $woo_order->billing_country;
			$bill_adr->EMail        = $woo_order->billing_email;
			$bill_adr->Phone        = $woo_order->billing_phone;
			$bill_adr->ZIP          = $woo_order->billing_postcode;
			$bill_adr->Province     = $woo_order->billing_state;


			// Delivery Address
			$deliv_adr               = new ShipmentAddress();
			$deliv_adr->Address1     = $woo_order->shipping_address_1;
			$deliv_adr->Address2     = $woo_order->shipping_address_2;
			$deliv_adr->City         = $woo_order->shipping_city;
			$deliv_adr->ContactName  = $woo_order->shipping_company;
			$deliv_adr->ContactName2 = $woo_order->shipping_first_name . " " . $woo_order->shipping_last_name;
			$deliv_adr->CountryCode  = $woo_order->shipping_country;
			$deliv_adr->EMail        = $woo_order->billing_email;
			$deliv_adr->Phone        = $woo_order->billing_phone;
			$deliv_adr->ZIP          = $woo_order->shipping_postcode;
			$deliv_adr->Province     = $woo_order->shipping_state;

			// Woo has started to only offer billing adr some times # For some weird reason strlen has been removed at this time?!
			if ( ! $deliv_adr->Address1 || count( str_split( (string) $deliv_adr->Address1 ) ) == 0 ) {
				$deliv_adr = $bill_adr;
			}


			// Get shipping methods
			$shipping_financial = array();
			foreach ( $woo_order->get_shipping_methods() as $method ) {
				$shipping_financial[] = array(
					"Name"       => $method["method_id"],
					"Price"      => $method["cost"],
					"TaxPercent" => 0
				);
			}


			// Get applied coupons / discounts
			if ( $woo_order->cart_discount > 0 ) {
				$discount_tax = (double) $woo_order->cart_discount_tax / (double) $woo_order->cart_discount * 100;
				$discounts    = array(
					array(
						"Price"       => $woo_order->get_total_discount(),
						"TaxIncluded" => false,
						"TaxPercent"  => $discount_tax
					)
				);
			} else {
				$discounts = array();
			}

			// Create the shipment
			$shipment                    = new Shipment();
			$shipment->BillingAddress    = $bill_adr;
			$shipment->DeliveryAddress   = $deliv_adr;
			$shipment->Items             = $ws_items;
			$shipment->ExtRef            = $woo_order->id;
			$shipment->VisibleRef        = $woo_order->get_order_number();
			$shipment->ShippingRate      = $rate_id;
			$shipment->SubTotalPrice     = $woo_order->order_total - $woo_order->order_shipping - $woo_order->order_shipping_tax - $woo_order->order_tax;
			$shipment->TotalPrice        = $woo_order->order_total - $woo_order->order_shipping - $woo_order->order_shipping_tax;
			$shipment->Currency          = $woo_order->get_order_currency();
			$shipment->swipbox_size      = $swipbox;
			$shipment->Comment           = $woo_order->customer_message;
			$shipment->ShippingFinancial = $shipping_financial;
			$shipment->Discounts         = $discounts;

			// Check if the order has a dynamic address
			$pickup_point_id = get_post_meta( $woo_order->id, 'wspup_pickup_point_id', true );

			if ( isset( $pickup_point_id ) && strlen( $pickup_point_id ) > 0 ) {

				// Reset email and phone for delivery
				$deliv_adr->EMail = '';
				$deliv_adr->Phone = '';

				// Define dyn adr
				$dynamic_adr              = new ShipmentAddress();
				$dynamic_adr->Address1    = $woo_order->shipping_address_1;
				$dynamic_adr->City        = $woo_order->shipping_city;
				$dynamic_adr->ContactName = $woo_order->shipping_company;
				$dynamic_adr->ZIP         = $woo_order->shipping_postcode;
				$dynamic_adr->CountryCode = $woo_order->shipping_country;

				$shipment->custom_pickup_identifier = $pickup_point_id;
				$shipment->DynamicAddress           = $dynamic_adr;
			}

			return $shipment;
		}

		/*
		 * In order to satisfy requirements of seperating SKU from Stock locations we have decided to
		 * put both in SKU. This because there is no nice way to add additional attributes to Product Variations.
		 * Therefore the SKU can be seperated from Locations by using "SKU - LOC"
		 */

		// Get SKU by SKU Field
		private function getSku( $sku ) {
			if ( isset( $sku ) ) {
				$spl = explode( '-', $sku );

				return $spl[0];
			} else {
				return "";
			}
		}

		// Get Location by SKU Field
		private function getLocation( $sku ) {
			if ( isset( $sku ) ) {
				$spl = explode( '-', $sku );

				return end( $spl );
			} else {
				return "";
			}
		}


	}
}
// Add to globals
$GLOBALS['WebshiprWC'] = new WebshiprWC();
