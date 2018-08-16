<?php 

if ( ! class_exists( 'WebshiprOrderHtml' ) ) {

	class WebshiprOrderHtml{

		private $orderExists; 
		private $connected; 
		private $webshiprOrder; 
		private $wooOrder; 
		private $rates; 
		private $api; 
		private $wsRateId; 
		private $rateName; 
		private $pickupPoint;
		private $postType; 


		public function __construct(WC_Order $wooOrder){
			
			global $WebshiprWC;
			global $wpdb; 

			$this->wooOrder 		= $wooOrder; 
			$this->WebshiprWC		= $WebshiprWC;
			$this->api 				= $this->WebshiprWC->ws_api();
            $this->rates 			= $this->api->GetShippingRates();

            // Depending on woocommerce version, get the shipping method / rate id
            if(method_exists($wooOrder, 'get_shipping_methods')) {
                $arr = $wooOrder->get_shipping_methods();
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
                $woo_method_id = $wooOrder->shipping_method->get_meta('shipping_rate_id');
            }
            $this->wsRateId = (preg_match("/WS/", $woo_method_id) ? str_replace("WS", "", $woo_method_id) : -1);
            $this->rateName = $this->get_rate_name($this->wsRateId, $this->rates);

            // Check if edit shipping should be renderede on subscriptions
			$this->postType = get_post_type($wooOrder->id);


            // Check if connected to webshipr
            if($this->api->CheckConnection()){

            	// Is connected
            	$this->connected = true; 

                // Need this later again.
                $this->orderExists = $this->api->OrderExists($wooOrder->id);

                if($this->orderExists){
                    $this->webshiprOrder = $this->api->GetOrder($wooOrder->id);
                }else{
                    $this->webshiprOrder = false; 
                }


                $pickup_id = get_post_meta($this->wooOrder->id, 'wspup_pickup_point_id', true); 
              	$this->pickupPoint = false; 

              	if(isset($pickup_id) && strlen($pickup_id) > 0 )
                	$this->pickupPoint  = true;  
            
            }else{
            	
            	$this->connected 	 = false; 
            	$this->orderExists 	 = false; 
            	$this->webshiprOrder = false;
            	$this->renderNotConnected(); 
            	
            }

		}

		public function RenderHTML(){
			// Render header
			$this->renderHeader(); 

			// Check if connected 
			if($this->connected){
                // Render subscription block
            	if($this->postType == "shop_subscription"){
                    $this->renderShippingChange();

                } else {
                    // Render content
                    if($this->orderExists){
                        
                        $this->renderStatus(); 
                        $this->renderShipmentErrors();

                        // Render droppoint if chosen
                        if($this->pickupPoint)
                            $this->renderPickupAddress(); 

                        if($this->webshiprOrder->status == "dispatched" ||  $this->webshiprOrder->status == "partly_dispatched" ){
                            $this->renderSent(); 
                        }else{
                            $this->renderError(); 
                        }

                    }else{

                        // Render droppoint if chosen
                        if($this->pickupPoint)
                            $this->renderPickupAddress(); 

                        $this->renderNotSent(); 
                    
                    }
                }

			}else{
				$this->renderNotConnected(); 
            }
            


			// Render "footer"
			$this->renderBottom(); 
        }
        
        private function renderShippingChange(){
            
            echo '<tr><td colspan=2><hr></td></tr>';
            echo "<tr><td colspan=2>";
            echo "Here you can change the shipping method and pickup point for the subscription";
            echo "</br></br>";
            echo "</td></tr>";
            echo "<tr><td colspan=2>";

            echo "<select name=\"ws_rate\" id =\"ws_rate\">";      
            foreach($this->rates as $rate){
                echo "<option value = ". $rate->id . (($rate->id == $this->wsRateId) ? " selected" : "").">".$rate->name."</option>";
                if ($this->wsRateId == $rate->id && $rate->dynamic_pickup) {
                    $this->get_droppoints = true;
                }
            }
            
            echo "</select>&nbsp;";
            echo "<a class=\"button button-primary\" onClick=\"change_order()\" href=\"#\">Set shipping</a>";
            echo "</td></tr>";
            
            if ($this->get_droppoints){
                echo "</br>";
                echo "<tr><td colspan=2>";
                echo "<select name=\"ws_droppoint\" id =\"ws_droppoint\">";      
                $droppoints = $this->api->getShopsByRateAndAddress($this->wooOrder->shipping_address_1, $this->wooOrder->shipping_postcode, $this->wooOrder->shipping_country, $this->wsRateId);
                foreach($droppoints->data as $droppoint){
                    echo "<option value = \"".$droppoint->id.".".$droppoint->street.".".$droppoint->zip.".".$droppoint->city.".".$droppoint->name.".".$droppoint->country."\">
                    ".$droppoint->name." - ".$droppoint->street." - ".$droppoint->zip." - ".$droppoint->city."</option>";
                }
                echo "</select>&nbsp;";
                echo "<a class=\"button button-primary\" onClick=\"set_droppoint()\" href=\"#\">Set pickup point</a>";

                echo "</td></tr>";
            }
        }

		// Render the order status
		private function renderStatus(){
			echo "<tr><td style=\"width: 25%; font-weight: bold;\">Current status</td><td> <div>".$this->showStatus($this->webshiprOrder->status)."</div></td></tr>";
		}

		// Render html for not connected state
		private function renderNotConnected(){
			echo "<tr><td colspan=2>";
            echo "Currently not connected to Webshipr. Please check your API Key under options.";
            echo "</td></tr>";
		}

		// HTML for order if its not yet in webshipr
		private function renderNotSent(){
			
			// Order does not exist - allow to process
            echo "<tr><td colspan=2>";
            echo "<h4>The order has not yet been sent to webshipr.</h4>";


            
            // Check if the rate was from webshipr
            if($this->rateName == 'not_known'){
                echo "It was not bought with a rate from webshipr. But you can process it anyways.<br/>";
            }else{
                echo "It was bought in the store with '$this->rateName' <br/>";
            }

            echo "</td></tr>";
            echo '<tr><td colspan=2><hr></td></tr>';
            echo "<tr><td colspan=2>";

            echo "<select name=\"ws_rate\" id =\"ws_rate\">";      
            foreach($this->rates as $rate){
                echo "<option value = ". $rate->id . (($rate->id == $this->wsRateId) ? " selected" : "").">".$rate->name."</option>";
            }

            echo "</select>&nbsp;";

            // Select swipbox size

            if(isset($this->WebshiprWC->options['swipbox']) && ((int)$this->WebshiprWC->options['swipbox'] == 1)){
                echo '<select name="swipbox" id="swipbox">';
                echo '<option value="1">Small</option>';
                echo '<option value="2">Medium</option>';
                echo '<option value="3">Large</option>';
                echo '<option value="101">Oversize 1</option>';
                echo '<option value="102">Oversize 2</option>';
                echo '<option value="103">Oversize 3</option>';
                echo '</select>';
            }

            echo "<a class=\"button button-primary\" onClick=\"process_order()\" href=\"#\">Process order</a>";

            // This is already within a form, so we need a workaround to submit data for processing
            echo "</td></tr>";
		}

		// Render shipment_errors
		private function renderShipmentErrors(){
			// Order is in webshipr, but not processed correctly
			if(count($this->webshiprOrder->shipment_errors)>0){
				echo "<tr><td><b>Error message</b></td><td>"; 

				foreach($this->webshiprOrder->shipment_errors as $error)
					echo "<label style='color: red;'>$error->error_message</label>"; 
				
				echo "</td></tr>"; 
			}
		}
		// HTML for order if its processed with an error
		private function renderError(){

			echo "<tr><td colspan=2><hr></td></tr>";
            echo "<tr><td colspan=2>";
            echo "<select name=\"ws_rate\" id =\"ws_rate\">";
                    
            foreach($this->rates as $rate){
                echo "<option value = ". $rate->id . (($rate->id == $this->wsRateId) ? " selected" : "").">".$rate->name."</option>";
            }

            echo "</select>&nbsp;";
            echo "<a class=\"button button-primary\" onClick=\"reprocess_order()\"  href=\"#\">Re-process order</a>";

            echo "</td></tr>";
		}




		// HTML for order when is has been dispatched
		private function renderSent(){
			echo "<tr><td colspan=2><hr></td></tr>"; 
            if(strlen($this->webshiprOrder->print_link)>0)
                echo "<tr><td>Print label </td><td><a href =\"". $this->webshiprOrder->print_link . "\" target=\"_blank\">Click here</a></td></tr><br/>";
            if($this->webshiprOrder->print_return_link) 
                echo "<tr><td>Print return-label </td><td><a href =\"". $this->webshiprOrder->print_return_link . "\" target=\"_blank\">Click here</a></td></tr><br/>";
            if(strlen($this->webshiprOrder->tracking_url)>0)
                echo "<tr><td>Tracking</td><td><a href =\"". $this->webshiprOrder->tracking_url . "\" target=\"_blank\">Click here</a></td></tr>";
            echo "<tr><td>Processed with: </td><td>".$this->get_rate_name($this->webshiprOrder->shipping_rate_id,$this->rates)."</td></tr>";

		}

		// Status header
		private function renderHeader(){
			echo '<div class="postbox" id="webshipr_backend" style="display:none;">';
            echo '<h3>Webshipr status</h3>';
            echo '<div class="inside">';
            echo '<table style="margin-left: 10px; width: 100%; ">';
		}

		// Static "footer"
		private function renderBottom(){
			echo "</table>";
            echo "</div>";
            echo "</div>";
		}


		// Get rate name from id, if webshipr rate
        private function get_rate_name($rate_id,$rates){
            if(is_array($rates)){
                foreach($rates as $rate){
                    if((int)$rate->id == (int)$rate_id)
                        return $rate->name;
                }
            }
            return "not_known";
        }


        // Render droppoint if present
        private function renderPickupAddress(){

            echo "<tr><td colspan=2><br/></td></tr>";
            echo "<tr>";
            echo "<td colspan=2>";

            $rate_name = $this->get_rate_name($this->wsRateId, $this->rates); 

            // Check which text is appropriate to the situation
            if($this->orderExists){
                if($this->webshiprOrder->status == "dispatched" && (int)$this->webshiprOrder->shipping_rate_id == (int)$this->wsRateId){
                    echo "<b>Shipment processed with ' $rate_name ' and following pickup address<b>";
                }else if($this->webshiprOrder->status == "dispatched" && (int)$this->webshiprOrder->shipping_rate_id != (int)$this->wsRateId){
                    echo "<b>Shipment was processed with another rate. Following pickup address is therefore not used.<b>";
                }else{
                    echo "<b>Pickup place selected for  '$rate_name' <b>";
                }
            }else{
                echo "<b>Pickup place selected for '$rate_name' <b>";
            }

            echo "</td>";
            echo "</tr><tr>";
            echo "<td>Name</td>";
            echo "<td>".$this->wooOrder->shipping_company."</td>";
            echo "</tr><tr>";
            echo "<td>Address</td>";
            echo "<td>".$this->wooOrder->shipping_address_1."</td>";
            echo "</tr><tr>";
            echo "<td>City</td>";
            echo "<td>".$this->wooOrder->shipping_city. "</td>";
            echo "</tr><tr>";
            echo "<td>Country</td>";
            echo "<td>".$this->wooOrder->shipping_country."</td>";
            echo "</tr>";
                
        }

		// Render webshipr status 
        private function showStatus($status){
             switch ($status)
             {
                case "not_recognized":
                    return "<span style='color: red;'>Not recognized</span><br/> <i>(usually means the shipment wasnt processed with a rate from webshipr)</i>";
                    break;
                case "choose_size":
                    return "<span style='color: red;'>Choose size</span><br/> <i>(SwipBox needs to know the size of your parcel. Select size in webshipr.)</i>";
                    break;
                case "country_rejected":
                    return "<span style='color: red;'>Country rejected</span><br/> <i>( Means the shipping rate is configured to deny this country in webshipr )</i>";
                    break;
                case "dispatched": 
                    return "<span style='color: green; font-weight: bold;'>Sent to shipper</span>";
                    break;
                case "carrier_error":
                    return "<span style='color: red; font-weight: bold;'>Carrier error</span><br/> <i>Please check your credentials, and the data for the shipment. Correct and reprocess.</i>";
                    break;
                case "error_processing":
                    return "<span style='color: red; font-weight: bold;'>Carrier error</span><br/> <i>Please check your credentials, and the data for the shipment. Correct and reprocess.</i>";
                    break;
                case "disabled":
                    return "<span style='color: red; font-weight: bold;'>Subscription disabled</span> <i> Please check if all your invoices are paid on your webshipr subscription </i>";
                    break;
                case "not_processed":
                    return "<span style='color: red; font-weight: bold;'>Not processed</span> <i> System error - please contact webshipr! </i>";
                    break;
                case "limit_exceeded":
    	              return "<span style='color: red; font-weight: bold;'>Limit exceeded</span> <i> please upgrade your subscription! </i>";
                    break;
                case "partner_processing":
                    return "<span style='color: blue; font-weight: bold;'>Partner processing</span> <i> Partner is currently processing. </i>";
    	              break;
                case "pending_partner":
                    return "<span style='color: blue; font-weight: bold;'>Pending partner</span> <i> Waiting for partner to process. </i>";
                    break;
                case "partner_waiting_for_stock": 
                    return "<span style='color: red; font-weight: bold;'>Partner waiting for stock</span> <i> Partner is waiting for stock. </i>";
                    break;
                case "partly_dispatched":
                    return "<span style='color: blue; font-weight: bold;'>Partly dispatched</span> <i> Shipment is partly dispatched. </i>";
                    break;
                default: 
                    return $status;
                    break;
             }
        }
	}

}

