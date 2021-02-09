<?php

// TODO: NEEDS A LINK TO A HELP DOC EXPLAINING GA AND HOW TO SET IT UP
// Add the Google Analytics Tracking ID field to the settings page for vendors */
add_action( 'wcvendors_settings_before_seo', 'wcv_add_ga_code' ); 
function wcv_add_ga_code(){ 
	$value = get_user_meta( get_current_user_id(), '_wcv_custom_settings_ga_tracking_id', true ); 
		// Output GA property field data 
		WCVendors_Pro_Form_Helper::input(
			apply_filters(
				'wcv_vendor_ga_code',
				array(
				  'id'            => '_wcv_custom_settings_ga_tracking_id',
				  'label'         => __( 'Google Analytics Tracking ID', 'wcvendors-pro' ),
				  'placeholder' 		=> __( 'UA-XXXXXXX-X', 'wcvendors-pro' ), 
				  'desc_tip'          => 'true',
				  'description'       => __( 'Google Analytics monitors customer activity and generates reports that help you grow your business — for free. </br> <a href="https://frosting.helpscoutdocs.com/article/151-setting-up-google-analytics">Learn more and how to set up Google Analytics.</a> ', 'wcvendors-pro' ),
				  'wrapper_start' => '<div class="all-100">',
				  'wrapper_end'   => '</div>',
				  'value'			=> $value
			  	)
		  	)
	  	);
}
/** Output the vendor google analytics code if they have added their tracking ID to their settings page */
add_action( 'wp_head', 'wcv_add_vendor_ga_code' );
function wcv_add_vendor_ga_code() { 
	global $post; 
	$vendor_id = 0;
	// Not on vendor store page or vendor single product bail out 
	if ( WCV_Vendors::is_vendor_page() ){
		$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
		$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );
	} 
	elseif ( is_singular( 'product' ) && WCV_Vendors::is_vendor_product_page( $post->post_author ) ) {
		$vendor_id = $post->post_author;
	}
	$vendor_ga_code = wcv_output_vendor_ga_code( $vendor_id ); 
	echo $vendor_ga_code;
}
/** Output the vendor tracking code 
* 	@param int $vendor_id - the vendor user ID
* 	@return string $ga_code - the google analytics code
*/
function wcv_output_vendor_ga_code( $vendor_id ){
	// Not a vendor? return nothing
	if ( ! WCV_Vendors::is_vendor( $vendor_id ) ) {
		return '';
	}
	$vendor_tracking_id = get_user_meta( $vendor_id, '_wcv_custom_settings_ga_tracking_id', true ); 
	// No tracking code added, return nothing 
	if ( empty( $vendor_tracking_id ) ){ 
		return '';
	}
	$ga_code = sprintf('
		'
	);
	return $ga_code;
}

?>
