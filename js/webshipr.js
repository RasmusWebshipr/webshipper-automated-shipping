
var ws_ajax_url = ""; 

// Bool if dynamic rate is picked
function dynamic_picked(){

	var selected_method = jQuery(".shipping_method:checked").val();
	var result = false; 
	jQuery.ajax({
         type : "post",
         dataType : "json",
         async: false, 
         url : ws_ajax_url,
         data : {action: "check_rates", rate_id: selected_method},
         success: function(response) {
         	if(response.data.dyn){
         		result = true;   
         	}
         }
    });
    return result; 
}

// Is address filled? 
function is_address_filled(){
	if ( jQuery("#billing_address_1").val().length > 0 &&
		 jQuery("#billing_postcode").val().length > 0 &&
		 jQuery("#billing_city").val().length > 0){
		return true;
	} else{
		return false;
	}
}


// Is a shop selected?
function shop_selected(){
	if(jQuery("#dynamic_destination_select :selected") === undefined){
		var selected_pickup = false; 
	}else{
		if(jQuery("#dynamic_destination_select :selected").val() === undefined){
			var selected_pickup = false; 
		}else{
			var selected_pickup = true; 
		}
	}
	return selected_pickup; 
}


function update_shipping_methods(){
	if(is_address_filled()){jQuery("body").trigger('update_checkout');}
}


jQuery(function(){

	if (jQuery("#woocommerce-order-data").length > 0 ) {
		jQuery("#webshipr_backend").insertAfter("#woocommerce-order-data");
	} else {
		jQuery("#webshipr_backend").insertAfter("#woocommerce-subscription-data");
	}
	jQuery("#webshipr_backend").show();

	jQuery(document).ready(function(){

		// Listen on blur
		jQuery("#billing_city").blur(function(){
    			update_shipping_methods(); 
		});
		jQuery("#billing_postcode").blur(function(){
    			update_shipping_methods(); 
		});
		jQuery("#billing_address_1").blur(function(){
    			update_shipping_methods(); 
		});
		jQuery("#billing_phone").blur(function(){
    			update_shipping_methods(); 
		});


	
	});





	jQuery(document).on('click',jQuery('#dynamic_destination_select'), function(){
        jQuery("#dynamic_destination_select").change(function(){
            jQuery(".service_point").hide();
            jQuery("#servicepoint_"+jQuery("#dynamic_destination_select").val()).show();
        });
	}); 




});

function set_selection(){
    jQuery(".service_point").hide();
    jQuery("#servicepoint_"+jQuery("#dynamic_destination_select").val()).show();
}


