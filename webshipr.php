<?php

class WebshiprAPI{

	private $api_token;
	private $base_host;

	function __construct($base_host, $api_token){
		$this->api_token = $api_token;
		$this->base_host = $base_host;
	}


	/* Version 2 of PUP API */


	function getShopsByCarrierAndZip($zip, $carrier){
		return $this->post_datav2('PUP/pupShopsByZip', array("carrier" => $carrier, "zip" => $zip));
	}

	function getShopsByRateAndZip($zip,$country, $rate_id){
		return $this->post_datav2('PUP/pupShopsByZip', array("rate_id" => $rate_id, "zip" => $zip, "country" => $country));
	}

	function getShopsByRateAndAddress($address, $zip, $country, $rate_id){
		return $this->post_datav2("PUP/pupShopsByAddress",  array("rate_id" => $rate_id,
															      "address" => $address,
															      "zip" 	=> $zip,
															      "country" => $country));
	}

	function getShopsByCarrierAndAddress($address, $zip, $country, $carrier){
		return $this->post_datav2("PUP/pupShopsByAddress",  array("carrier" => $carrier,
								      "address" => $address,
								      "zip" 	=> $zip,
								      "country" => $country));
	}

	/* End V2 PUP API */

	function OrderExists($order_no){
		$result = $this->post_data("order_exists", array("ExtRef" => $order_no));

		if($result->exists){
			return true;
		}else{
			return false;
		}
	}


	private function post_datav2($path, $data){
        $ch = curl_init($this->base_host.'/APIV2/'.$path); #$this->base_host."/APIV2/".$resource);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
        'Authorization: Token token="'.$this->api_token.'"'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return json_decode(curl_exec($ch));
	}
	private function post_data($resource, $data){
		$ch = curl_init($this->base_host."/API/".$resource);
        curl_setopt($ch, CURLOPT_POST, true);
	    	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
 	   	'Authorization: Token token="'.$this->api_token.'"'));
 	   	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  	  	return json_decode(curl_exec($ch));
	}

	function GetOrder($order_no){
		return $this->post_data("get_order", array("ExtRef" => $order_no));
	}

	function SetAndProcessOrder($order_no, $rate_id){
        	return $this->post_data("set_rate", array("ExtRef" => $order_no, "new_rate" => $rate_id));
	}

	function CreateShipment($shipment){

		$datatopost = array (
			"billing_address" => (array)$shipment->BillingAddress,
			"delivery_address" => (array)$shipment->DeliveryAddress,
			"dynamic_address" => (array)$shipment->DynamicAddress,
			"items" => (array)$shipment->Items,
			"ExtRef" => $shipment->ExtRef,
			"VisibleRef" => $shipment->VisibleRef,
			"Weight" => $shipment->Weight,
			"ShippingRate" => $shipment->ShippingRate,
			"SubTotalPrice" => $shipment->SubTotalPrice,
			"TotalPrice" => $shipment->TotalPrice,
			"Currency"	=> $shipment->Currency,
                        "ShippingFinancial" => $shipment->ShippingFinancial,
                        "Discounts"     => $shipment->Discounts,
			"Comment" => $shipment->Comment,
			"custom_pickup_identifier" => $shipment->custom_pickup_identifier,
			"swipbox_size" => $shipment->swipbox_size
		);

		$ch = curl_init($this->base_host."/API/create_shipment");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
		'Authorization: Token token="'.$this->api_token.'"'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datatopost));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$returndata = curl_exec($ch);
		return $returndata;
	}

	function UpdateShipment($shipment){

		$datatopost = array (
			"billing_address" => (array)$shipment->BillingAddress,
			"delivery_address" => (array)$shipment->DeliveryAddress,
			"dynamic_address" => (array)$shipment->DynamicAddress,
			"items" => (array)$shipment->Items,
			"ExtRef" => $shipment->ExtRef,
			"Weight" => $shipment->Weight,
			"ShippingRate" => $shipment->ShippingRate,
			"SubTotalPrice" => $shipment->SubTotalPrice,
			"TotalPrice" => $shipment->TotalPrice,
			"Currency"	=> $shipment->Currency,
			"Comment" => $shipment->Comment,
			"ShippingFinancial" => $shipment->ShippingFinancial,
			"Discounts" 	=> $shipment->Discounts,
			"custom_pickup_identifier" => $shipment->custom_pickup_identifier,
			"swipbox_size" => $shipment->swipbox_size,
			"process" => true
		);

		$ch = curl_init($this->base_host."/API/update_shipment");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
		'Authorization: Token token="'.$this->api_token.'"'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datatopost));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$returndata = curl_exec($ch);
		return $returndata;
	}

	function CheckConnection(){
		$ch = curl_init($this->base_host."/API/check_connection");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
		'Authorization: Token token="'.$this->api_token.'"'));
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$returndata = curl_exec($ch);
		$res = json_decode($returndata);

		if(isset($res->status) && $res->status == 401){
			return false;
		}else{
			return $res;
		}
	}

    function GetShippingRates($total = 0){
    	    $ch = curl_init($this->base_host."/API/shipping_rates");
    	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
    	    'Authorization: Token token="'.$this->api_token.'"'));
    	    curl_setopt($ch, CURLOPT_POST, true);
       	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( array("order_total" => $total ) ) );
    	    $returndata = curl_exec($ch);
    	    $res = json_decode($returndata);

    	    if($res){
              return $res;
    	    }else{
              return false;
    	    }
    }

    public static function generateAjaxToken($length = 10) {

    	$token = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    	$_SESSION['WS_AJAX_TOKEN'] = $token;
    	return $token;
	}

}

/* Instead of structs */

class Shipment{
		public $BillingAddress;
		public $DeliveryAddress;
		public $DynamicAddress;
		public $Items;
		public $WebshopID;
		public $Weight;
		public $ExtRef;
		public $VisibleRef;
		public $ShippingRate;
		public $SubTotalPrice;
		public $TotalPrice;
		public $Currency;
		public $custom_pickup_identifier;
		public $Comment;
		public $ShippingFinancial;
		public $Discounts;
		public $swipbox_size;
}

class ShipmentAddress{
		public $Address1;
		public $Address2;
		public $City;
		public $ContactName;
		public $ContactName2;
		public $CountryCode;
		public $EMail;
		public $Phone;
		public $ZIP;
		public $province;
}

class ShipmentItem{
		public $Description;
		public $ProductName;
		public $ProductNo;
		public $Quantity;
		public $UOM;
		public $Weight;
		public $Location;
		public $Price;
		public $TaxPercent;
		public $TarifNumber;
		public $OriginCountryCode;

		public function __construct($Description, $ProductName, $ProductNo, $Quantity, $UOM,$Weight, $Location, $Price, $TaxPercent, $TarifNumber, $OriginCountryCode){
			$this->Description = $Description;
			$this->ProductName = $ProductName;
			$this->ProductNo = $ProductNo;
			$this->Quantity = $Quantity;
			$this->UOM = $UOM;
			$this->Weight = $Weight;
			$this->Location = $Location;
			$this->Price = $Price;
			$this->TaxPercent = $TaxPercent;
			$this->TarifNumber = $TarifNumber;
			$this->OriginCountryCode = $OriginCountryCode;
		}

}

