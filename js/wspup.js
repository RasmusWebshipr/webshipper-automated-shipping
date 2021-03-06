var wspup = {
	
	// Variables 
	ajaxToken: '', 
	ajaxUrl: '/wp-admin/admin-ajax.php',
	currentRateId: 0,
	currentCountry: '',
	lastShopArray: [],
	currentDistance: -1, 
	// Get translation
	getTranslation: function(key){
		return jQuery("input[name=i18n_"+key+"]").val();;
	},

	// Get shops by carrier 
	getShopsByCarrier: function(carrier, zip){
		wspup.enableLoader();
		jQuery.ajaxSetup({
            async: false
        });
		jQuery.post( wspup.ajaxUrl, {
									action: "get_shops",
									ajaxToken: wspup.ajaxToken, 
									method: 'getByZipCarrier', 
									country: wspup.currentCountry,
									carrier: carrier, 
									zip: zip
									}
		,function( response ) {
			var data = response.data;
			wspup.lastShopArray = data.data; 
			wspup.disableLoader();
			if(data.data.length > 0){
				wspup.fillShopsInList(data.data);
				wspup.setFirstSelection();
			}
			

		}, "json").fail(function() {
				wspup.lastShopArray = [];
				wspup.disableLoader();
		}); ; 
	},

	// Get and set shops by zip
	getShopsInZipByRate: function(zip){
		// Start Ajax loader
		wspup.enableLoader();

		// Make the call
		jQuery.post( wspup.ajaxUrl, {	
									action: "get_shops",
									ajaxToken: wspup.ajaxToken, 
									method: 'getByZipRate', 
									country: wspup.currentCountry,
									rate_id: wspup.currentRateId, 
									zip: zip
									},
		function( response ) {	
			var data = response.data;
			wspup.lastShopArray = data.data; 		// Save latest data in obj
			wspup.disableLoader();
			if(data.data.length > 0){	// Fill the shops in layout
				wspup.fillShopsInList(data.data);
				wspup.setFirstSelection();
			}
			
			
						
		}, "json").fail(function() {
				wspup.lastShopArray = [];
				wspup.disableLoader();
		}); ; 
	},

	// Get and set shops by address
	getShopsNearAddressByRate: function(zip, adr){
		// Start Ajax loader
		wspup.enableLoader();

		// Make the call
		jQuery.post( wspup.ajaxUrl, {
									action: "get_shops",
									ajaxToken: wspup.ajaxToken, 
									method: 'getByAddressRate', 
									rate_id: wspup.currentRateId, 
									country: wspup.currentCountry,
									address: adr,
									zip: zip
									},
		function( response ) {
			var data = response.data; 
			wspup.lastShopArray = data.data;
			wspup.disableLoader();
			if(data.data.length > 0){
				wspup.fillShopsInList(data.data); // Fill data
				wspup.setFirstSelection();	// Set first item as selected
			} 
			 
						
			 			
		}, "json").fail(function() {
				wspup.lastShopArray = [];
				wspup.disableLoader();
		});  
	},

	// Put array of shops into the layour
	fillShopsInList: function(data){
		jQuery("#wspup_select_list ol").html('');
		var result = ''; 
		
		jQuery.each(data, function(index){
			var item = data[index];
			result += '<li class="wspup_item">'; 
			result += '<h6>'+item.name +'</h6>';
			result += '<p>'+item.street+ '</p>';
			result += '<p>'+item.zip+ ' ' + item.city + '</p>';
			if(item.opening_hours.length > 0){
				result += '<p><a href="#" class="wspupSeeOpeningHour">'+wspup.getTranslation('see_opening_hours')+'</a></p>';
			}
			result += '<input class="shop_address" value="'+item.street+'" type="hidden">'; 
			result += '<input class="shop_carrier" value="'+item.carrier+'" type="hidden">';
			result += '<input class="shop_zip" value="'+item.zip+'" type="hidden">';
			result += '<input class="shop_city" value="'+item.city+'" type="hidden">';  
			result += '<input class="shop_id" value="'+item.id+'" type="hidden">';
			result += '<input class="shop_longitude" value="'+item.longitude+'" type="hidden">';
			result += '<input class="shop_latitude" value="'+item.latitude+'" type="hidden">';  
			result += '<input class="shop_country" value="'+item.country+'" type="hidden">';
			result += '<input class="shop_name" value="'+item.name+'" type="hidden">';  
			result += '<div class="wspupOpeningHours">';
			result += '<a class="wspupOpnClose" onClick="jQuery(this).parent().hide();" href="#" style="color: black;">Luk [X]</a>';
			result += '<h5>'+wspup.getTranslation('opening_hours')+'</h5>';
			result += '<table style="border: 0">';

			result += '<tr><td width="90px;"><b>'+wspup.getTranslation('monday')+':</b></td><td>'	+wspup.formatOpeningHour(item.opening_hours, 'MO')+	'</td></tr>';
			result += '<tr><td width="90px;"><b>'+wspup.getTranslation('tuesday')+':</b></td><td>'	+wspup.formatOpeningHour(item.opening_hours, 'TU')+	'</td></tr>';
			result += '<tr><td width="90px;"><b>'+wspup.getTranslation('wednesday')+':</b></td><td>'+wspup.formatOpeningHour(item.opening_hours, 'WE')+	'</td></tr>';
			result += '<tr><td width="90px;"><b>'+wspup.getTranslation('thursday')+':</b></td><td>'	+wspup.formatOpeningHour(item.opening_hours, 'TH')+	'</td></tr>';
			result += '<tr><td width="90px;"><b>'+wspup.getTranslation('friday')+':</b></td><td>'	+wspup.formatOpeningHour(item.opening_hours, 'FR')+	'</td></tr>';
			result += '<tr><td width="90px;"><b>'+wspup.getTranslation('saturday')+':</b></td><td>'	+wspup.formatOpeningHour(item.opening_hours, 'SA')+	'</td></tr>';
			result += '<tr><td width="90px;"><b>'+wspup.getTranslation('sunday')+':</b></td><td>'	+wspup.formatOpeningHour(item.opening_hours, 'SU')+	'</td></tr>';

			result += '</table>';
			result += '</div>';
			result += '</li>';
		});
		jQuery("#wspup_select_list ol").html(result);
		wspup.listen();
		return true;

		
	},

	// Format openinghours from array
	formatOpeningHour: function(arrDays, day){
		
		// Loop through days to fint the correct one
		for(var i = 0; arrDays.length > i; i++){
			if(arrDays[i].day == day){
				return arrDays[i].openFrom + " - " + arrDays[i].openTo; 
			}
		}
		return 'Lukket';

	},

	// Get weekday from short
	getWeekDayFromshort: function(day){
		switch(day) {
		    case 'MO':
		        return wspup.getTranslation('monday');
		        break;
		    case 'TU':
		        return wspup.getTranslation('tuesday');
		        break;
		    case 'WE':
		    	return wspup.getTranslation('wednesday');
		    	break;
		    case 'TH':
		    	return wspup.getTranslation('thursday');
		    	break;
		    case 'FR':
		    	return wspup.getTranslation('friday');
		    	break;
		    case 'SA':
		    	return wspup.getTranslation('saturday');
		    	break;
		    case 'SU':
		    	return wspup.getTranslation('sunday');
		    	break;
		    default:
		        return ''
		}
	},

	// Set listeners that might dissappear
	listen: function(){
		jQuery(".wspup_item").click(function(){
			if(!jQuery(this).hasClass('wspup_selected')){
				jQuery(".wspup_item").removeClass('wspup_selected');
				jQuery(this).addClass('wspup_selected');
				wspup.setSelection(jQuery(this));
			}
		});
		jQuery(".wspupSeeOpeningHour").click(function(){
			jQuery(".wspupOpeningHours").hide();
			jQuery(this).parent().parent().children('.wspupOpeningHours').fadeIn("fast");
		}); 

		jQuery("#accept_button").unbind('click');
		jQuery("#accept_button").click(function(){
			wspup.transferSelection();
		});
		
		jQuery("#wspup_show_btn").unbind('click');
		jQuery("#wspup_show_btn").click(function(){
			jQuery("#wspup_wrapper").fadeIn("slow");
		});

		jQuery(".pup_close").unbind('click');
		jQuery(".pup_close").click(function(){
			jQuery("#wspup_wrapper").hide();
		});

		jQuery("#wspup_search_btn").unbind('click');
		jQuery("#wspup_search_btn").click(function(){
			wspup.search(); 
		}); 

		jQuery(".wspup_input").unbind('keydown');
		jQuery(".wspup_input").keydown(function(event){
			if(event.keyCode == 13) {
				wspup.search();
			}
		});
	}, 

	// Enable ajax loader end hide results
	enableLoader: function(){
		jQuery("#wspup_results").hide(); 
		jQuery("#wspup_search_now").hide();
		jQuery("#wspup_loader").show();
	}, 

	// Disable ajax loader and show results
	disableLoader: function(){
		if(wspup.lastShopArray.length > 0){
			jQuery("#wspup_loader").hide(); 
			jQuery("#wspup_search_now").hide();
			jQuery("#wspup_results").fadeIn("fast");
		}else{
			jQuery("#wspup_loader").hide(); 
			jQuery("#wspup_search_now").hide();
			jQuery("#wspup_noresults").fadeIn("fast");
		}

	}, 

	// Set the map by lng, lat
	setMap: function(li){
		var lng 		= li.children(".shop_longitude").val();
		var lat 		= li.children(".shop_latitude").val();
		var shopName 	= li.children(".shop_name").val(); 

		var placering = new google.maps.LatLng(lat, lng);

	    var kortValg = {
	        mapTypeId: google.maps.MapTypeId.ROADMAP,
	        zoom: 13,
	        center: placering
		};


		map = new google.maps.Map(document.getElementById("wspup_map"), kortValg);
		var placering = new google.maps.LatLng(lat, lng);

	   	google.maps.event.trigger(map,'resize');
		map.setZoom( map.getZoom() );

		if(jQuery("#wspup_address").val().length > 0){
			// If home address available put that one in. Else just plot the shop.

			var geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': jQuery("#wspup_address").val()+", "+jQuery("#wspup_zip").val()+", Denmark"}, function(results, status) {

			  console.log("Status: " + status);

			  if(status == google.maps.GeocoderStatus.REQUEST_DENIED){console.log("REQUEST DENIED")}

			  if(status == google.maps.GeocoderStatus.INVALID_REQUEST){console.log("INVALID REQUEST")}

			  if(status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT){console.log("OVER QUERY DENIED")}

			  if(status == google.maps.GeocoderStatus.ZERO_RESULTS){console.log("ADDRESS NOT FOUND")}

			  if (status == google.maps.GeocoderStatus.OK) {

			    // Insert directions
			    var directionsDisplay = new google.maps.DirectionsRenderer();
			    var directionsService = new google.maps.DirectionsService();
				directionsDisplay.setMap(map);

				// Define direction request
			    var request = {
			        origin : results[0].geometry.location,
			        destination : placering,
			        travelMode : google.maps.TravelMode.DRIVING
			    };

			    console.log("From" + results[0].geometry.location);
			    console.log("To" + placering);

			    // Draw route
			    directionsService.route(request, function(response, status) {
			    		
			        if (status == google.maps.DirectionsStatus.OK) {

			            directionsDisplay.setDirections(response);
			            wspup.currentDistance = response.routes[0].legs[0].distance.value;


			            // Set labels
			            var mapLabelShop = new MapLabel({
				           text: shopName + " ( " + (wspup.currentDistance/1000).toFixed(2) + " Km" +  " )",
				           position: placering,
				           map: map,
				           fontSize: 16,
				           align: 'center',
				           zIndex: 999999999
				        }); 
				         
				        // Set labels
			            var mapLabelShop = new MapLabel({
				           text: 'Dig',
				           position: results[0].geometry.location,
				           map: map,
				           fontSize: 16,
				           align: 'center',
				           zIndex: 999999999
				         });
			        }else{
				        var marker = new google.maps.Marker({
							position: placering,
							map: map,
						});
						
						// Set label
			            var mapLabel = new MapLabel({
				           text: shopName,
				           position: placering,
				           map: map,
				           fontSize: 16,
				           align: 'center', 
				           zIndex: 999999999
				         });
			        }

			    });

			  } else { // Could not locate address, just put shop - no route
			   	    var marker = new google.maps.Marker({
						position: placering,
						map: map,
					});
					
					// Set label
		            var mapLabel = new MapLabel({
			           text: shopName,
			           position: placering,
			           map: map,
			           fontSize: 16,
			           align: 'center', 
			           zIndex: 999999999
			         });
			  }
			});
		} else{ // Only zip - just put shop - no route
			var marker = new google.maps.Marker({
				position: placering,
				map: map
			});
		}
	},

	// Set a shop as selected by li element
	setSelection: function(li){
		wspup.setMap(li);
		jQuery(".wspup_item").removeClass('wspup_selected');
		li.addClass('wspup_selected');
		jQuery(".wspupOpeningHours").hide();

	}, 

	// Set the first shop result as selected
	setFirstSelection: function(){
		wspup.setSelection(jQuery("#wspup_select_list ol > li:first-child"));
	}, 

	// Method to invoke search for shops
	search: function(){
		jQuery("#wspup_noresults").hide();

		// If address assigned, call by address
		if(jQuery("#wspup_address").val().length > 0){
			wspup.getShopsNearAddressByRate(jQuery("#wspup_zip").val(), jQuery("#wspup_address").val());
		}else{
			wspup.getShopsInZipByRate(jQuery("#wspup_zip").val());
		}
	},

	// Transfer selection
	transferSelection: function(){
		var selected = jQuery('.wspup_selected'); 
		var name 	 = selected.children('.shop_name').val();
		var address  = selected.children('.shop_address').val();
		var zip  	 = selected.children('.shop_zip').val();
		var city  	 = selected.children('.shop_city').val();
		var id  	 = selected.children('.shop_id').val();
		var country  = selected.children('.shop_country').val();
		var carrier  = selected.children('.shop_carrier').val();

		var text = '<div class="wspup_confirmation">';
		text += '<h3>'+wspup.getTranslation('selected_pickup_point')+'</h3>'; 
		text += '<p>' + name + '</p>';
		text += '<p>' + address + '</p>'; 
		text += '<p>' + zip + ' ' + city + '</p>';
		text += '</div><br/>';

		jQuery("input[name=wspup_name]").val(name);
		jQuery("input[name=wspup_id]").val(id);
		jQuery("input[name=wspup_address]").val(address);
		jQuery("input[name=wspup_zip]").val(zip);
		jQuery("input[name=wspup_country]").val(country);
		jQuery("input[name=wspup_city]").val(city);
		jQuery("input[name=wspup_carrier]").val(carrier);
		jQuery("#wspup_selected_text").html(text);
		jQuery("#wspup_wrapper").hide();

		jQuery('html, body').animate({
    		scrollTop: jQuery("#wspup_show_btn").offset().top
   		}, 1000);

	},

	showPup: function(){
		jQuery("#wspup_wrapper").fadeIn("fast");
		wspup.listen();
		if(jQuery("#wspup_zip").val().length>0){
			wspup.search(); 
		}
	}
};


jQuery(document).ready(function(){
	wspup.listen();
});


