<tr class="wspup_row">
	<th class="wspup_th">
		 <?php echo __('Pickup location', 'WebshiprWC'); ?>
	</th>
	<td class="wspup_td">
		<div id="wspup_wrapper">
			<?php echo '<h1>' . __('Select pickup point', 'WebshiprWC') . '</h1>'; ?>
			<div id="wspup_container">
				<div id="wspup_zipbox">
					<input  class="wspup_input" id="wspup_address" placeholder="<?php echo  __('Address', 'WebshiprWC'); ?>">  
					<input  class="wspup_input" id="wspup_zip" placeholder="<?php echo  __('ZIP', 'WebshiprWC'); ?>"> 
					<input type="button" class="button" value="<?php echo  __('Search', 'WebshiprWC'); ?>" id="wspup_search_btn" style="float:none;display: inline-block;">
				</div>
				<div id="wspup_noresults">
					<br>
						<h3><?php echo  __('Sorry! We couldnt find any pickup points. Try again!', 'WebshiprWC'); ?></h3>
					<br>
				</div>
				<div id="wspup_loader">
					<img src="<?php echo plugins_url("img/ajax-loader.gif", __FILE__) ?>">

					<h3><?php echo  __('We are looking for the nearest pickup points...', 'WebshiprWC'); ?></h3>
					<br>
				</div>
				<div id="wspup_search_now">
					<br>
					<h3><?php echo  __('Use the search field above to find a pickup point near you.', 'WebshiprWC'); ?></h3>
				</div>
				<div id="wspup_results">
					<div id="wspup_select_list">
						<ol>
			
						</ol>
					</div>
					<div id="wspup_map" style="box-sizing: content-box;">

					</div>
					<div id="accept_button" style="box-sizing: content-box;">
							<p><?php echo  __('Confirm pickup point', 'WebshiprWC'); ?></p>
							<p><?php echo  __('Click here', 'WebshiprWC'); ?></p>
					</div>

				</div>
			</div>
			<span class="pup_close">
			<?php echo  __('Close window', 'WebshiprWC'); ?> [X]
			</span>

			<span class="wspup_watermark">
				Module by <a href="http://www.webshipr.com" target="_blank">www.webshipr.com</a>
			</span>
		</div>

        <div class="wspup_cart">
                <input type="button" class="button" style="float: none;" value="<?php echo  __('Choose pickup point', 'WebshiprWC'); ?>" id="wspup_show_btn">
                <?php
                // If ie. validation failed - catch parameters to ensure the customer doesnt need to pick droppoint again
                $parameters = array();
                if( isset( $_POST["post_data"] ) )
                        parse_str( $_POST["post_data"], $parameters );
                ?>

		<input type="hidden" name="wspup_this_rate" value="<?php echo isset($this_rate->id)?$this_rate->id:''; ?>">
                <input type="hidden" class="wspup_info" name="wspup_name" value="<?php echo (isset($parameters["wspup_name"]) ? $parameters["wspup_name"] : ""); ?>">
                <input type="hidden" class="wspup_info" name="wspup_address" value="<?php echo (isset($parameters["wspup_address"]) ? $parameters["wspup_address"] : ""); ?>">
                <input type="hidden" class="wspup_info" name="wspup_zip" value="<?php echo (isset($parameters["wspup_zip"]) ? $parameters["wspup_zip"] : ""); ?>">
                <input type="hidden" class="wspup_info" name="wspup_city" value="<?php echo (isset($parameters["wspup_city"] )? $parameters["wspup_city"] : ""); ?>">
                <input type="hidden" class="wspup_info" name="wspup_country" value="<?php echo (isset($parameters["wspup_country"]) ? $parameters["wspup_country"] : ""); ?>">
                <input type="hidden" class="wspup_info" name="wspup_id" value="<?php echo (isset($parameters["wspup_id"]) ? $parameters["wspup_id"] : ""); ?>">
                <input type="hidden" class="wspup_info" name="wspup_carrier" value="<?php echo (isset($parameters["wspup_carrier"]) ? $parameters["wspup_carrier"] : ""); ?>">

                <input type="hidden" name="i18n_selected_pickup_point" value="<?php echo  __('Selected pickup point', 'WebshiprWC'); ?>">
                <input type="hidden" name="i18n_opening_hours" value="<?php echo  __('Opening hours', 'WebshiprWC'); ?>">
                <input type="hidden" name="i18n_see_opening_hours" value="<?php echo  __('See opening hours', 'WebshiprWC'); ?>">
                <input type="hidden" name="i18n_monday" value="<?php echo  __('Monday', 'WebshiprWC'); ?>">
                <input type="hidden" name="i18n_tuesday" value="<?php echo  __('Tuesday', 'WebshiprWC'); ?>">
                <input type="hidden" name="i18n_wednesday" value="<?php echo  __('Wednesday', 'WebshiprWC'); ?>">
                <input type="hidden" name="i18n_thursday" value="<?php echo  __('Thursday', 'WebshiprWC'); ?>">
                <input type="hidden" name="i18n_friday" value="<?php echo  __('Friday', 'WebshiprWC'); ?>">
                <input type="hidden" name="i18n_saturday" value="<?php echo  __('Saturday', 'WebshiprWC'); ?>">
                <input type="hidden" name="i18n_sunday" value="<?php echo  __('Sunday', 'WebshiprWC'); ?>">

                <div id="wspup_selected_text">
                        <?php if(isset( $parameters["wspup_id"] ) && strlen( $parameters["wspup_id"] ) > 0 ){ ?>
                        <div class="wspup_confirmation">
                                <h3><?php echo  __('Selected pickup point', 'WebshiprWC'); ?></h3>
                                <p><?php echo $parameters["wspup_name"]; ?></p>
                                <p><?php echo $parameters["wspup_address"]; ?></p>
                                <p><?php echo $parameters["wspup_zip"] . " " . $parameters["wspup_city"]; ?></p>
                        </div>
                        <?php } ?>
                </div>
		</div>

		
		<script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery("#wspup_wrapper").appendTo("body");
                wspup.currentRateId = <?php echo $rate; ?>;

                jQuery('#wspup_show_btn').on('click', function () {
                    wspup.showPup();
                    wspupTransferAddress();
                });


                <?php if(isset($_POST["country"])){ ?>
                    currentCountry = '<?php echo $_POST["country"]; ?>';
                    if (currentCountry.length > 2) {
                        switch(currentCountry) {
                            case 'dnk':
                                wspup.currentCountry = 'DK'
                                break;
                            case 'swe':
                                wspup.currentCountry = 'SE'
                                break;
                            case 'nor':
                                wspup.currentCountry = 'NO'
                                break;
                            default:
                                wspup.currentCountry = 'DK'
                        }
                    } else {
                        wspup.currentCountry = currentCountry;
                    }
	            <?php }else{ ?>
                wspup.currentCountry = 'DK';
	            <?php } ?>

                function wspupTransferAddress(){

                    if(jQuery("#wspup_address").val().length === 0 && jQuery("#wspup_zip").val().length === 0){
                        if(jQuery("#shipping_address_1").length){
                            if(jQuery("#shipping_address_1").val().length>0){
                                jQuery("#wspup_address").val(jQuery("#shipping_address_1").val());
                                jQuery("#wspup_zip").val(jQuery("#shipping_postcode").val());
                            }else{
                                jQuery("#wspup_address").val(jQuery("#billing_address_1").val());
                                jQuery("#wspup_zip").val(jQuery("#billing_postcode").val());
                            }
                        }else{
                            jQuery("#wspup_address").val(jQuery("#billing_address_1").val());
                            jQuery("#wspup_zip").val(jQuery("#billing_postcode").val());
                        }


                        if(jQuery("#wspup_zip").val().length>0){
                            wspup.search();
                        }
                    }
                }
	            <?php if( isset($parameters['wspup_this_rate'])&& isset($this_rate->id) && ($parameters['wspup_this_rate'] != $this_rate->id) ){ ?>
                jQuery(".wspup_info").val('');
                jQuery("#wspup_selected_text").html('');
	            <?php } ?>
            });
		</script>

	</td>
</tr>
