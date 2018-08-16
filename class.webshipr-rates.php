<?php
function shipping_method_init() {

    if ( ! class_exists( 'WebshiprRates' ) ) {

        class WebshiprRates extends WC_Shipping_Method {

            private $options;

            // Constructor
            public function __construct() {
                $this->options = get_option('webshipr_options');
                $this->init("WS", "Webshipr", "Webshipr calculates shipping rates autmatically and live directly from your Webshipr.com account.<br/>
                    If you experience any issues, please contact support@webshipr.com.<br/><br/>
                    If you want to disable the webshipr shipping, please disable the plugin, under the plugins menu.");
            }


            // Initialize
            function init($id, $title, $description) {

                $this->id = $id;
                $this->method_title = $title;
                $this->method_description = $description;
                $this->title = $title;
                $this->enabled = "yes";


                // Load the settings API
                //$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
                //$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

                // Save settings in admin if you have any defined
                add_action( 'woocommerce_update_options_shipping_' . $id, array( $this, 'process_admin_options' ) );
            }


            // Calculate shipping rates
            public function calculate_shipping( $package = array() ) {
                global $woocommerce;

	            $total = 0;
                $coupon_free_shipping = false;

	            // Calculate cart total incl. taxes
      		    if(count($package["contents"] > 0)){
          			foreach($package["contents"] as $content){
          		    		$total += $content["line_total"] + $content["line_tax"];
          			}
      		    }

                // Check if any coupon codes are applied
                if(count($package['applied_coupons']) > 0){
                    foreach($package['applied_coupons'] as $coupon){
                            $obj = new WC_Coupon($coupon);

                            // check if coupon grants free shipping
                            if($obj->get_free_shipping($coupon) == 'yes'){
                                $coupon_free_shipping = true;
                            }
                    }
                }


                $api = $this->ws_api($this->options['api_key']);
                $rates = $api->GetShippingRates($total);
                $destination  = $package["destination"]["country"];

                $weight_uom = get_option('woocommerce_weight_unit');

                // Webshipr wants UOM in grams
                if ($weight_uom == 'kg'){
                    $cart_weight = $woocommerce->cart->cart_contents_weight * 1000;
                }else{
                    $cart_weight = $woocommerce->cart->cart_contents_weight;
                }


                /*
                *  If WPML enabled we would like to remove filter that converts currency from base to cart currency,
                *  as only rates with the right currency will be enqueued, and should therefore not be converted.
                */
                if( class_exists( "WCML_Multi_Currency_Support" ) ){
                    global $woocommerce_wpml;
                    remove_filter( 'wcml_shipping_price_amount', array( $woocommerce_wpml->multi_currency, 'shipping_price_filter' ) );
                }

                // If any rates were found
                if($rates && !(isset($rates->status) && $rates->status == 401)){

                    foreach($rates as $rate){

                        if( $this->country_accepted($rate, $destination) &&
                            $rate->max_weight >= $cart_weight && $rate->min_weight <= $cart_weight ){

                            /*
                            *  If WPML with multicurrency is enabled - and currencies are the same - add the rate.
                            *  If WPML is not enabled - add the rate even if rate is misconfigured.
                            */
                            if ( !class_exists( "WCML_Multi_Currency_Support" ) || $rate->currency == get_woocommerce_currency() ){

                                // Make and add the rate
                                $new_rate = array(
                                    'id' => "WS".$rate->id,
                                    'label' => $rate->name,
                                    'cost' => ($coupon_free_shipping ? 0 : $rate->price ),
                                    'taxes' => ($rate->tax_percent > 0 ? '' : false),
                                    'calc_tax' => 'per_order',
                                    'meta_data' => array(
                                        'shipping_rate_id' => 'WS'.$rate->id
                                    )
                                );
                                $this->add_rate( $new_rate );
                            }

                        }
                    }
                }
            }

            // Method to validate rate from country id
            private function country_accepted($rate, $cur_country){
                $result = false;
                foreach($rate->accepted_countries as $country){
                        if($country->code == $cur_country || $country == 'ALL'){
                                $result = true;
                        }
                }
                return $result;
            }

            // Return Webshipr API object
            private function ws_api($key){
                return new WebshiprAPI(API_RESOURCE, $key);
            }
        }
    }
}

add_action( 'woocommerce_shipping_init', 'shipping_method_init' );

function add_shipping( $methods ) {
    $methods[] = 'WebshiprRates';
    return $methods;
}

add_filter( 'woocommerce_shipping_methods', 'add_shipping' );


