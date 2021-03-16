<?php
/**
 * Enqueue child styles.
 */
add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 100 );
function child_enqueue_styles() {
	wp_enqueue_style( 'child-theme', get_stylesheet_directory_uri() . '/style.css', array() );
}

/**
 * Add custom functions here
 */
/*------------------------------------*
    $FUNCTIONS.PHP CONTENTS
*------------------------------------*/
/**
 * TEST CODE
 *
 * GENERAL SITE PHP
 * - ADD FAVICON TO HEADER
 * - BETTER SEARCH AND REPLACE FIXER
 *
 * WOOCOMMERCE
 * - PRODUCT ARCHIVE PAGE
 * - SINGLE PRODUCT PAGE
 * - CART & CHECKOUT
 *
 * WC VENDORS
 * - GENERAL
 * - RENAME, REORGANIZE SIDEBAR AND SETTINGS
 * - PRODUCTS
 * - SETTINGS
 *
 * FACETWP
 * - RESET BUTTON
 *
 * UNUSED CODE
 *
 */

 /*------------------------------------*
	$TEST CODE
*------------------------------------*/

function helpscout_beacon() {
	if (is_page ('11')) { 
		?>
			<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
			<script type="text/javascript">window.Beacon('init', '6ec654ae-7a21-494f-879d-8b5865fbd3ba')</script>
		<?php
	}
  }
  add_action('wp_footer', 'helpscout_beacon');


/*------------------------------------*
	$GENERAL
*------------------------------------*/

/* Add Favicons to header */
add_action('wp_head', 'add_favicon');
function add_favicon(){
?>
    <link rel="apple-touch-icon" sizes="180x180" href="/wp-content/themes/kadence-child/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/wp-content/themes/kadence-child/favicons/favicon-32x32.png">
    <link rel="manifest" href="/wp-content/themes/kadence-child/favicons/site.webmanifest">
    <link rel="mask-icon" href="/wp-content/themes/kadence-child/favicons/safari-pinned-tab.svg" color="#5050e6">
    <meta name="apple-mobile-web-app-title" content="Frosting">
    <meta name="application-name" content="Frosting">
    <meta name="msapplication-TileColor" content="#5050e6">
    <meta name="theme-color" content="#ffffff">
<?php
};

// Needed for Better Search and Replace to work on Pantheon
function better_search_replace_cap_override() {
    return 'manage_options';
}
add_filter( 'bsr_capability', 'better_search_replace_cap_override' );

// Upload any size image
add_filter( 'big_image_size_threshold', '__return_false' );

/*------------------------------------*
	$WOOCOMMERCE
*------------------------------------*/

/*----------  PRODUCT ARCHIVE   ---------- *

/*----------  SINGLE PRODUCT   ---------- */

// Show Bakery icon on Single Product Page
// TODO: This is not working.
function wcv_custom_show_icon() {
	if ( WCV_Vendors::is_vendor_page() ) { 
		$vendor_shop 		= urldecode( get_query_var( 'vendor_shop' ) );
		$vendor_id   		= WCV_Vendors::get_vendor_id( $vendor_shop ); 
			$store_icon_src         = wp_get_attachment_image_src( get_post_meta( $store_id, '_wcv_store_icon_id', true ), array( 150, 150  ) );
			$store_icon             = '';
	    // see if the array is valid
	    if ( is_array( $store_icon_src ) ) {
	        $store_icon     = '<img src="'. $store_icon_src[0].'" alt="" class="store-icon" style="max-width:100%;" />';
	    }
	    echo $store_icon; // You might wrap this in some HTML or something depending on your layout.
	} 
}
add_action( 'woocommerce_single_product_summary', 'wcv_custom_show_icon', 5 );

/*----------  CART & CHECKOUT   ---------- */

// Limit checkout to a single vendor store
add_action( 'woocommerce_add_to_cart_validation', 'limit_single_vendor_to_cart', 10, 3 );
function limit_single_vendor_to_cart( $valid, $product_id, $quantity ) {
	$vendor_id = get_post_field( 'post_author', $product_id );
	// loop through the cart to check each vendor id
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$cart_product_id 	= $cart_item[ 'product_id' ];
		$cart_vendor_id 	= get_post_field( 'post_author', $cart_product_id );
		if( $cart_vendor_id != $vendor_id ) {
			$valid = false;
			break;
		}
	}
	if ( ! $valid ){
		// Display a message in the cart.
		wc_clear_notices();
		wc_add_notice( __( "We can only process orders for one bakery at a time. Please proceed to checkout or clear your cart."), 'error' );
	}
	return $valid;
}

// Add a 9% Service Fee to cart & checkout
add_action( 'woocommerce_cart_calculate_fees','woocommerce_custom_surcharge' );
function woocommerce_custom_surcharge() {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ){
		return;
  	}
  	$percentage = 0.09;
  	$surcharge = ( WC()->cart->get_cart_contents_total() + WC()->cart->get_shipping_total()) * $percentage;	
  	WC()->cart->add_fee( __( 'Service Fee', 'text-domain' ), $surcharge, true, '' );
}

/*------------------------------------*
	$WC VENDORS
*------------------------------------*/

/*----------  WCV - GENERAL   ---------- */

// Add a view profile/store button on the WooCommerce my account page to link to the vendor store in WC Vendors Pro
// ! NOT WORKING 
add_action( 'woocommerce_before_my_account', 'show_my_view_store' );
	function show_my_view_store(){
		$user = get_user_by( 'id', get_current_user_id() );
		if ( ! WCV_Vendors::is_vendor( $user->ID ) ) {
				return;
		}
		$view_store_url = WCVendors_Pro_Vendor_Controller::get_vendor_store_url( $user->ID );
		echo '<a href="' . $view_store_url . '" class="button">View Profile</a>';
}

// TODO: CHECK TO MAKE SURE WORKS *IT MIGHT BE BETTER TO REDIRECT TO STRIPE CONNECT 
// Redirect vendors to settings after sign up
add_filter( 'wcv_register_vendor_url', 'settings_redirect' );
function settings_redirect( $url ){
    $settings_url = WCVendors_Pro_Dashboard::get_dashboard_page_url( 'settings' );
    return $settings_url;
}

// TODO: CHECK TO SEE IF WORKS
// Use vendor store name for brand if using RankMath SEO.
add_filter( 'rank_math/snippet/rich_snippet_product_entity', function( $entity ) {
	global $post; 
	$shop_name = get_user_meta( $post->post_author, 'pv_shop_name', true );
	$entity['brand'] = $shop_name;
	return $entity;
	});

/*----------  WCV SM - RENAME, REORGANIZE SIDEBAR AND SETTINGS   ---------- */

// Add links to WCV Dashboard 
function add_menu_item( $pages ){ 
	$pages['bakery_help'] = array(
		'slug'    => 'https://frosting.helpscoutdocs.com?utm_source=frosting&utm_medium=website&utm_campaign=bism&utm_term=frosting-shop-manager',
		'id'      => 'bakery_help', 
		'label'   => __( 'Learning & Tutorials', 'wcvendors-pro' ),
		'actions' => array()
	);
    $pages['contact_frosting'] = array( 
		'slug'    => 'https://bakerysupport.paperform.co?utm_source=frosting&utm_medium=website&utm_campaign=bism&utm_term=frosting-shop-manager',
		'id'      => 'contact_frosting',
        'label'   => __( 'Contact Frosting', 'wcvendors-pro' ), 
		'actions' => array()
    ); 
	$pages['invite_baker'] = array( 
		'slug'    => 'https://frosting.helpscoutdocs.com/article/128-how-do-i-refer-a-baker-or-bakery?utm_source=frosting&utm_medium=website&utm_campaign=bism&utm_term=frosting-shop-manager',
		'id'      => 'invite_baker',
        'label'   => __( 'Invite a Baker', 'wcvendors-pro' ), 
		'actions' => array()
    ); 	
    return $pages;
}
add_filter( 'wcv_pro_dashboard_urls', 'add_menu_item' ); 

// Reorder Shop Manager sidebar links
add_filter( 'wcv_dashboard_pages_nav', 'change_nav_order'); 
function change_nav_order( $pages ){ 
	$new_nav_order = array(); 
		$new_nav_order['dashboard_home'] = $pages['dashboard_home']; 
		$new_nav_order['product'] = $pages['product']; 
		$new_nav_order['order'] = $pages['order']; 
		$new_nav_order['bkap-booking'] = $pages['bkap-booking/?custom=bkap-booking']; 
		$new_nav_order['shop_coupon'] = $pages['shop_coupon']; 
		$new_nav_order['shop_subscription'] = $pages['shop_subscription']; 
		$new_nav_order['wcv_refund_request'] = $pages['wcv_refund_request']; 
		$new_nav_order['invite_baker'] = $pages['invite_baker'];  
		$new_nav_order['bakery_help'] = $pages['bakery_help']; 
		$new_nav_order['contact_frosting'] = $pages['contact_frosting']; 
		$new_nav_order['settings'] = $pages['settings']; 
		$new_nav_order['view_store'] = $pages['view_store']; 
		$new_nav_order['logout'] = $pages['logout']; 
	return $new_nav_order; 
}

// Rename Shop Manager sidebar links
add_filter( 'wcv_pro_dashboard_urls', 'change_nav_labels' );
function change_nav_labels( $urls ){
		$urls['shop_coupon']['label'] = 'Promotions';
		$urls['wcv_refund_request']['label'] = 'Customer Refunds';
		$urls['rating']['label'] = 'Store Ratings';
	return $urls;
}

// Reorder Shop Manager settings links
add_filter( 'wcv_store_tabs', 'reorder_settings_tabs' );
function reorder_settings_tabs( $tabs ){
	$new_tabs = array();
		$new_tabs['store'] = $tabs['store'];
		$new_tabs['payment'] = $tabs['payment'];
		$new_tabs['shipping'] = $tabs['shipping'];
		$new_tabs['policies'] = $tabs['policies'];
		$new_tabs['branding'] = $tabs['branding'];
		$new_tabs['social'] = $tabs['social'];
		$new_tabs['seo'] = $tabs['seo'];
	return $new_tabs;
}

/*----------  WCV SM - PRODUCTS   ---------- */

// Rename the tags field label to Product Options
add_action( 'wcv_product_tags', 'wcv_change_tag_label' );
function wcv_change_tag_label( $field ){
	$field['label'] = 'Customization field tags';
	return $field;
}

// Add Custom fields
add_action( 'wcv_after_product_details', 'wcv_frosting_taxonomy' );
function wcv_frosting_taxonomy( $object_id ){
	WCVendors_Pro_Form_helper::select2( array(
		'post_id'			=> $object_id,
		'id'				=> 'wcv_custom_taxonomy_holiday[]',
		'class'				=> 'select2',
		'custom_tax'        => true,
		'label'				=> __('Holidays', 'wcvendors-pro'),
		'wrapper_start'     => '<div class="all-100">',
		'wrapper_end'       => '</div>',
		'taxonomy'			=>	'holiday',
		'taxonomy_args'		=> array(
			'hide_empty'		=> 0, ),
		'custom_attributes'	=> array( 
			'multiple' => 'multiple' ),
		)
	);
	echo '<p class="tip">Culturally or religiously significant dates like Easter, Halloween, Christmas.</p>';

	WCVendors_Pro_Form_helper::select2( array(
		'post_id'			=> $object_id,
		'id'				=> 'wcv_custom_taxonomy_occasion[]',
		'class'				=> 'select2',
		'custom_tax'        => true,
		'label'				=> __('Occasions', 'wcvendors-pro'),
		'wrapper_start'     => '<div class="all-100">',
		'wrapper_end'       => '</div>',
		'taxonomy'			=>	'occasion',
		'taxonomy_args'		=> array(
			'hide_empty'		=> 0, ),
		'custom_attributes'	=> array(
			'multiple' => 'multiple' ),
		)
	);
	echo '<p class="tip">Special events and social gatherings like Birthday, Prom, Wedding.</p>';

	WCVendors_Pro_Form_helper::select2( array(
		'post_id'			=> $object_id,
		'id'				=> 'wcv_custom_taxonomy_for_whom[]',
		'class'				=> 'select2',
		'custom_tax'        => true,
		'label'				=> __('For Whom', 'wcvendors-pro'),
		'wrapper_start'     => '<div class="all-100">',
		'wrapper_end'       => '</div>',
		'taxonomy'			=>	'for_whom',
		'taxonomy_args'		=> array(
			'hide_empty'		=> 0, ),
		'custom_attributes'	=> array(
			'multiple' => 'multiple' ),
		)
	);
	echo '<p class="tip">Is this specifically for a boy or girl?  Leave blank if the item is gender neutral.</p>';

	//Changed to post id to test
	WCVendors_Pro_Form_helper::select2( array(
		'post_id'			=> $object_id,
		'id'				=> 'wcv_custom_taxonomy_themes[]',
		'class'				=> 'select2',
		'custom_tax'        => true,
		'label'				=> __('Themes', 'wcvendors-pro'),
		'wrapper_start'     => '<div class="all-100">',
		'wrapper_end'       => '</div>',
		'taxonomy'			=>	'themes',
		'taxonomy_args'		=> array(
			'hide_empty'	=> 0, ),
		'custom_attributes'	=> array(
			'multiple' 		=> 'multiple' ,
			'data-tags'     => 'true' ),
		)
	);
	echo '<p class="tip">Keywords that describe your design like Easter Egg, Diamond, Shark.</br>Just start typing to add your own keyword.</p>';

	WCVendors_Pro_Form_helper::select2( array(
		'post_id'			=> $object_id,
		'id'				=> 'wcv_custom_taxonomy_character[]',
		'class'				=> 'select2',
		'custom_tax'        => true,
		'label'				=> __('Character', 'wcvendors-pro'),
		'wrapper_start'     => '<div class="all-100">',
		'wrapper_end'       => '</div>',
		'taxonomy'			=>	'character',
		'taxonomy_args'		=> array(
			'hide_empty'		=> 0, ),
		'custom_attributes'	=> array(
			'multiple' 		=> 'multiple' ,
			'data-tags'     => 'true' ),
		)
	);
	echo '<p class="tip">List any characters like Peter Pan, Aladdin, or Frankenstein?</br>Just start typing to add your own keyword.</p>';

	WCVendors_Pro_Form_helper::select2( array(
		'post_id'			=> $object_id,
		'id'				=> 'wcv_custom_taxonomy_sports_team[]',
		'class'				=> 'select2',
		'custom_tax'        => true,
		'label'				=> __('Sports Team', 'wcvendors-pro'),
		'wrapper_start'     => '<div class="all-100">',
		'wrapper_end'       => '</div>',
		'taxonomy'			=>	'sports_team',
		'taxonomy_args'		=> array(
			'hide_empty'		=> 0, ),
		'custom_attributes'	=> array(
			'multiple' 		=> 'multiple' ,
			'data-tags'     => 'true' ),
		)
	);
	echo '<p class="tip">Include the city and team like Bentonville Tigers.</br>Just start typing to add your own keyword.</br></p>';

	WCVendors_Pro_Form_helper::select2( array(
		'post_id'			=> $object_id,
		'id'				=> 'wcv_custom_taxonomy_specialty_diet[]',
		'class'				=> 'select2',
		'custom_tax'        => true,
		'label'				=> __('Specialty Diet', 'wcvendors-pro'),
		'wrapper_start'     => '<div class="all-100">',
		'wrapper_end'       => '</div>',
		'taxonomy'			=>	'specialty_diet',
		'taxonomy_args'		=> array(
			'hide_empty'		=> 0, ),
		'custom_attributes'	=> array(
			'multiple' => 'multiple' ),
		)
	);
	echo '<p class="tip">Product is made for a special diet like gluten-free or vegan.</p>';

	WCVendors_Pro_Form_helper::select2( array(
		'post_id'			=> $object_id,
		'id'				=> 'wcv_custom_taxonomy_age_restricted[]',
		'class'				=> 'select2',
		'custom_tax'        => true,
		'label'				=> __('Adult Content', 'wcvendors-pro'),
		'wrapper_start'     => '<div class="all-100">',
		'wrapper_end'       => '</div>',
		'taxonomy'			=>	'age_restricted',
		'taxonomy_args'		=> array(
			'hide_empty'		=> 0, ),
		'custom_attributes'	=> array(
			'multiple' => 'multiple' ),
		)
	);
	echo '<p class="tip">Does this item contain a sexually explicit, vulgar language, or drugs theme?</p>';
}

/** Form Helpers and Placeholders */
// Product Title
add_filter( 'wcv_product_title', 'customize_wcv_product_title' );
function customize_wcv_product_title( $args ) {
    $more_args = array(
        'placeholder' => __( 'Ex: Princess Birthday Cookies - 1 dozen', 'wcvendors-pro' ),
        'desc_tip'    => 'true',
        'description' => __( 'A concise and relevant title will help customers find your product. Try to keep a consistent format like "Theme, Holiday or Occasion, Product Category - Size" for all your product titles.</br><i class="fak fa-frosting"></i> Learn more: <a href="https://frosting.helpscoutdocs.com/article/149-product-titles">Product Titles</a>', 'wcvendors-pro' ),
    );
    return array_merge( $args, $more_args);
}
// Product Categories
add_filter( 'wcv_product_categories', 'customize_wcv_product_categories' );
function customize_wcv_product_categories( $args ) {
    $more_args = array(
        'placeholder' => __( 'Ex: Cookies', 'wcvendors-pro' ),
        'desc_tip'    => 'true',
        'description' => __( 'Categories are the main way we organize items to help customers find what they are looking for.', 'wcvendors-pro' ),
    );
    return array_merge( $args, $more_args);
}
/** Short Description
add_filter( 'wcv_product_short_description', 'customize_wcv_product_short_description' );
function customize_wcv_product_short_description( $args ) {
    $more_args = array(
        'placeholder' => __( '
		This listing is for a dozen (12) decorated Princess Cookies. These are sugar cookies with royal icing and each cookie is approximately 4 inches.
		Each order will include:
		(3) - Dresses
		(3) - Crowns
		(3) - Castles
		(3) - Wands
		', 'wcvendors-pro' ),
        'desc_tip'    => 'true',
        'description' => __( 'The text shown between the Product Name and Buy Box. Try for 2-4 sentences that include keywords and specific information about this product. Get your customers excited about buying from your bakery!!', 'wcvendors-pro' ),
    );
    return array_merge( $args, $more_args);
}
*/
// Product Options
add_filter( 'wcv_product_tags', 'customize_wcv_product_tags' );
function customize_wcv_product_tags( $args ) {
    $more_args = array(
        'desc_tip'    => 'true',
        'description' => __( 'You can allow customers to personalize items with the following tags:</br>Name, Age, Gender, Monogram, School, Colors, Background Color, Accent Color, Written Message, Design Request', 'wcvendors-pro' ),
    );
    return array_merge( $args, $more_args);
}
// Tax Status
add_filter( 'wcv_product_tax_status', 'customize_wcv_product_tax_status' );
function customize_wcv_product_tax_status( $args ) {
    $more_args = array(
        'desc_tip'    => 'true',
        'description' => __( 'If your state charges Sales Tax, then leave this as "Taxable" and select your bakery under Tax Class.', 'wcvendors-pro' ),
    );
    return array_merge( $args, $more_args);
}
// Tax Class
add_filter( 'wcv_product_tax_class', 'customize_wcv_product_tax_class' );
function customize_wcv_product_tax_class( $args ) {
    $more_args = array(
        'desc_tip'    => 'true',
        'description' => __( 'Select your bakery to charge the appropriate sales tax.', 'wcvendors-pro' ),
    );
    return array_merge( $args, $more_args);
}
// Up-Sells
add_filter( 'wcv_product_upsells', 'customize_wcv_product_upsells' );
function customize_wcv_product_upsells( $args ) {
    $more_args = array(
        'desc_tip'    => 'true',
        'description' => __( 'Upsells are shown near the bottom of the page. Customers will see an image, title, and price for the products you choose.', 'wcvendors-pro' ),
    );
    return array_merge( $args, $more_args);
}

// Disable booking types on Tyches Booking plugin 
function bkap_get_booking_types_callback( $booking_type ) {
	unset( $booking_type['multiple_days']);
    unset( $booking_type['duration_time'] );
    unset( $booking_type['multidates'] );
    unset( $booking_type['multidates_fixedtime'] );    return $booking_type;
}
add_filter( 'bkap_get_booking_types', 'bkap_get_booking_types_callback', 10, 1 );

/*----------  WCV SM - SETTINGS   ---------- */

// TODO: NEEDS A LINK TO A HELP DOC EXPLAINING GA AND HOW TO SET IT UP
// Add the Google Analytics Tracking ID field to the Settings -SEO page for vendors */
add_action( 'wcvendors_settings_before_seo', 'wcv_add_ga_code' ); 
function wcv_add_ga_code(){ 
    $value = get_user_meta( get_current_user_id(), '_wcv_custom_settings_ga_tracking_id', true ); 
  
      // Output GA property field data 
      WCVendors_Pro_Form_Helper::input(
          apply_filters(
              'wcv_vendor_ga_code',
              array(
				'id'           	=> '_wcv_custom_settings_ga_tracking_id',
				'label'         => __( 'Google Analytics Tracking ID', 'wcvendors-pro' ),
				'placeholder' 	=> __( 'UA-XXXXXXX-X', 'wcvendors-pro' ), 
				'desc_tip'      => 'true',
				'description'   => __( 'Google Analytics monitors customer activity and generates reports that help you grow your business — for free. </br> <a href="https://frosting.helpscoutdocs.com/article/151-setting-up-google-analytics">How to set up Google Analytics.</a> ', 'wcvendors-pro' ),
				'wrapper_start' => '<div class="all-100">',
				'wrapper_end'   => '</div>',
				'value'			=> $value
              )
          )
      );
  }
  
// Output the vendor google analytics code if they have added their tracking ID to their settings page 
add_action( 'wp_head', 'wcv_add_vendor_ga_code' );
function wcv_add_vendor_ga_code() { 
	global $post; 
	$vendor_id = 0;
	// Not on vendor store page or vendor single product bail out 
	if ( WCV_Vendors::is_vendor_page() ){
		$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
		$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );

	} elseif ( is_singular( 'product' ) && WCV_Vendors::is_vendor_product_page( $post->post_author ) ) {
		$vendor_id = $post->post_author;
	}

	$vendor_ga_code = wcv_output_vendor_ga_code( $vendor_id ); 
	echo $vendor_ga_code;
}
  
/**
 * Output the vendor tracking code 
 *
 * @param int $vendor_id - the vendor user ID
 * @return string $ga_code - the google analytics code
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
	<!-- Global site tag (gtag.js) - Google Analytics added by WC Vendors Pro -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=' . $vendor_tracking_id . '"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag(\'js\', new Date());
	gtag(\'config\', \' ' .$vendor_tracking_id . ' \');
	</script> '
	);
	return $ga_code;
}

/** RANK MATH SHOP PAGE FIX */
// Change OG title for Rank Math on Vendor Pages
function wcv_rankmath_change_og_title( $title ) {
	WC_Vendors::log( $title ); 
	if ( WCV_Vendors::is_vendor_page() ) {
		$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
		$vendor_id = WCV_Vendors::get_vendor_id( $vendor_shop );
		$shop_title = get_user_meta( $vendor_id, 'pv_shop_name', true );
		$og_title = get_user_meta( $vendor_id, 'wcv_seo_fb_title', true );

		if ( ! empty( $og_title ) ) {
			$title = $og_title;
		} else {
			$title = $shop_title;
		}
	}
	return $title;
}
add_filter( 'rank_math/frontend/title', 'wcv_rankmath_change_og_title' );

// Change Meta description for Rank Math on Vendor Pages
function wcv_rankmath_change_meta_description( $desc ) {
	if ( WCV_Vendors::is_vendor_page() ) {
		$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
		$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );
		$shopdesc    = get_user_meta( $vendor_id, 'pv_shop_description', true );
		$meta_desc   = get_user_meta( $vendor_id, 'wcv_seo_meta_description', true );

		if ( ! empty( $meta_desc ) ) {
			$desc = $meta_desc;
		} else {
			$desc = $shopdesc;
		}
	}
	return $desc;
}
add_filter( 'rank_math/frontend/description', 'wcv_rankmath_change_meta_description' );

// Change OG description for Rank Math on Vendor Pages
function wcv_rankmath_change_og_description( $desc ) {
	if ( WCV_Vendors::is_vendor_page() ) {
		$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
		$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );
		$shopdesc    = get_user_meta( $vendor_id, 'pv_shop_description', true );
		$meta_desc   = get_user_meta( $vendor_id, 'wcv_seo_meta_description', true );
		$og_desc     = get_user_meta( $vendor_id, 'wcv_seo_fb_description', true );

		if ( ! empty( $og_desc ) ) {
			$desc = $og_desc;
		} elseif (! empty( $meta_desc)) {
			$desc = $meta_desc;
		} else {
			$desc = $shopdesc;
		}
	}

	return $desc;
}
add_filter( 'rank_math/frontend/description', 'wcv_rankmath_change_og_description' );

// Change OG Facebook image for Rank Math on Vendor Pages
function wcv_rankmath_change_facebook_og_image ( $image ) {
	if ( WCV_Vendors::is_vendor_page() ) {
		$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
		$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );
		$og_image    = get_user_meta( $vendor_id, 'wcv_seo_fb_image_id', true );

		if (!empty($og_image)) {
			$image = wp_get_attachment_url( $og_image );
		}
	}
	return $image;
}
add_filter( 'rank_math/opengraph/facebook/image', 'wcv_rankmath_change_facebook_og_image' );

// Change OG Twitter image for Rank Math on Vendor Pages
function wcv_rankmath_change_twitter_og_image ( $image ) {
	if ( WCV_Vendors::is_vendor_page() ) {
		$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
		$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );
		$og_image    = get_user_meta( $vendor_id, 'wcv_seo_twitter_image_id', true );

		if (!empty($og_image)) {
			$image = wp_get_attachment_url( $og_image );
		}
	}
	return $image;
}
add_filter( 'rank_math/opengraph/twitter/image', 'wcv_rankmath_change_twitter_og_image' );


// Change OG URL for Rank Math on Vendor Pages.
function wcv_rankmath_change_og_url ( $url ) {
	if ( WCV_Vendors::is_vendor_page() ) {
		$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
		$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );
		$url         = WCV_Vendors::get_vendor_shop_page( $vendor_id );
	}

	return $url;
}
add_filter( 'rank_math/opengraph/url', 'wcv_rankmath_change_og_url' );

/** Form Helpers and Placeholders */
// Store - Store Description 
add_filter( 'wcv_vendor_store_description', 'customize_wcv_vendor_store_description' );
function customize_wcv_vendor_store_description( $args ) {
    $more_args = array(
        'desc_tip'    => 'true',
        'description' => __( 'The store description is shown below your bakery name on your shop page banner. Try for 1-3 sentence summary about your bakery.', 'wcvendors-pro' ),
    );
    return array_merge( $args, $more_args);
}
// Store - Bakery Info 
add_filter( 'wcv_vendor_seller_info', 'customize_wcv_vendor_seller_info' );
function customize_wcv_vendor_seller_info( $args ) {
    $more_args = array(
        'desc_tip'    => 'true',
        'description' => __( 'Bakery info is shown as a tab on all of your product pages.</br>You can add a bio, info about your store location, hours, services you offer, customer reviews, or FAQs., ', 'wcvendors-pro' ),
    );
    return array_merge( $args, $more_args);
}
// Store - Google Maps Location 
add_filter( 'wcv_vendor_store_address1', 'customize_wcv_vendor_store_address1' );
function customize_wcv_vendor_store_address1( $args ) {
    $more_args = array(
        'desc_tip'    => 'true',
        'description' => __( 'Your products will be tagged with this location.', 'wcvendors-pro' ),
    );
    return array_merge( $args, $more_args);
}
// Store - Shop Notice
add_filter( '_wcv_vendor_store_notice', 'customize_wcv_vendor_store_notice' );
function customize_wcv_vendor_store_notice( $args ) {
    $more_args = array(
        'desc_tip'    => 'true',
        'description' => __( 'The store notice will be shown at the top of your shop page and on all your products.', 'wcvendors-pro' ),
    );
    return array_merge( $args, $more_args);
}
// Settings - Payment page description
add_action( 'wcvendors_settings_before_paypal', 'customize_wcv_settings_payments_description' ); 
function customize_wcv_settings_payments_description( $field ){
	echo '<p class="wcv_sm_setting_tab_description">You must create and connect your Frosting account to a free Stripe account in order to have payments processed correctly.</p>';
}
// Settings - Policy page description
add_action( 'wcvendors_settings_before_policies', 'customize_wcv_settings_policies_description' ); 
function customize_wcv_settings_policies_description( $field ){
	echo '<p class="wcv_sm_setting_tab_description">It is important that customers know your business policies up front.</br>The info you add below will be shown as a tab on all of your product pages. We recommend that you seek professional advice on your specific policies.</p>';
}
// Policies - Terms & Conditions
add_filter( 'wcv_policy_terms', 'customize_wcv_policy_terms' );
function customize_wcv_policy_terms( $args ) {
	$more_args = array(
		'desc_tip'    => 'true',
		'description' => __( 'You can add any policies that you want customer to know up front. These can include: Order Changes, Allergy Warning, Marketing, or any other policy you have.', 'wcvendors-pro' ),
	);
	return array_merge( $args, $more_args);
}
// Policies - Pickup, Delivery, and Shipping Policy
add_filter( 'wcv_vendor_shipping_policy', 'customize_wcv_vendor_shipping_policy' );
function customize_wcv_vendor_shipping_policy( $args ) {
	$more_args = array(
		'desc_tip'    => 'true',
		'description' => __( 'You can include an overview of the pickup process, delivery guidelines, or shipping info.', 'wcvendors-pro' ),
	);
	return array_merge( $args, $more_args);
}
// Policies - Refund & Cancellation Policy
add_filter( 'wcv_shipping_return_policy', 'customize_wcv_shipping_return_policy' );
function customize_wcv_shipping_return_policy( $args ) {
	$more_args = array(
		'desc_tip'    => 'true',
		'description' => __( 'If you choose to honor refund or cancellation requests, add the policy guidelines here. It helps to be as specific as possible with these policies.', 'wcvendors-pro' ),
	);
	return array_merge( $args, $more_args);
}
/**
 * I think this adds too much clutter
//Terms
'placeholder' => __( 'Example:
Custom orders are defined as items created with specific colors, themes, designs & details that were specified by the customer. 

Order Changes - No changes within 7 days of pickup or delivery.  Order changes more than 7 days before the pickup/delivery date will be charged a $20 change fee.

We reserve the right to use pictures and reviews from your order for marketing and other business-related purposes.  All orders are the responsibility of the customer once it is picked up or delivered.', 'wcvendors-pro' ),
//Shipping
'placeholder' => __( 'Example:
Pickup orders - Our store is located at 110 N Main St. Bentonville, AR. Public parking is located at the back of the building. Once you arrive, let one of our associates know that you have a pickup order.

Deliveries - We have a $10 delivery fee for all orders under $100. We provide delivery to locations within 15 miles of our store. Your order will be delivered to the address listed on checkout. Please be sure to include any special instructions that we may need in the Order Notes on checkout. The delivery fee is non refundable.', 'wcvendors-pro' ),	
//Refund
'placeholder' => __( 'Example:
Refund Policy:
We offer full refunds if your order was incorrect, or a mistake was made on our part. We reserve the right to issue store credit for any and all refunds.  Refunds are only given up to 7 days after your pickup/delivery date.  Store Credit will be issued for any weather, labor, “Act of God”, or any other events out of our control.

Cancellation Policy:
Wedding Cakes - 30 days prior notice from your pick up or delivery date.
Custom orders - We require notice 7 days prior to your pick up or delivery date.
All other items - We require 24-hour notice after your order was placed.', 'wcvendors-pro' ),
*/
// Settings - Branding page description
add_action( 'wcvendors_settings_before_branding', 'customize_wcv_settings_branding_description' ); 
function customize_wcv_settings_branding_description( $field ){
	echo '<p class="wcv_sm_setting_tab_description">Personalize your Frosting Shop Page by adding a banner and profile picture.</br><p class="tip"><i class="fak fa-frosting"></i> Learn more: <a href="https://frosting.helpscoutdocs.com/article/180-shop-banners-profile-pictures">Shop Banner, Profile Picture, and Free Templates.</a></br><b>Shop Banner:</b></br>4:1 image ratio in .jpg or .png format.</br>Recommended size is 3360 x 840px.<b></br>Profile Picture:</b></br>1:1 image ratio in .jpg or .png format.</br>Recommended size is 400 x 400px.</br></p>';
}
// Settings - Social page description
add_action( 'wcvendors_settings_before_social', 'customize_wcv_settings_social_description' ); 
function customize_wcv_settings_social_description( $field ){
	echo '<p class="wcv_sm_setting_tab_description">Add your Social Media info and we will create easy links below your banner for your customers to follow you.</p>';
}
// Settings - SEO page description
add_action( 'wcvendors_settings_before_seo', 'customize_wcv_settings_seo_description' ); 
function customize_wcv_settings_seo_description( $field ){
	echo '<p class="wcv_sm_setting_tab_description">You can customize the default SEO and Social Media info for your Shop page. When you or anyone else shares your shop page URL, this is the information that will be shown.</br><p class="tip"><i class="fak fa-frosting"></i> Learn more: <a href="https://frosting.helpscoutdocs.com/article/195-customize-your-shop-pages-seo-and-social-share-appearance">Customize your Shop Pages SEO and Social share appearance.</a></p>
		</p>';
}

/*------------------------------------*
	$FACETWP
*------------------------------------*/

// RESET ALL BUTTON
// This assumes that your reset button looks like this:
// <a class="my-reset-btn" onclick="FWP.reset()">RESET</a>
add_action( 'wp_head', function() {
	?>
<script>

(function($) {
    $(document).on('facetwp-loaded', function() {
        var qs = FWP.build_query_string();
        if ( '' === qs ) { // no facets are selected
            $('.your-reset-btn').hide();
        }
        else {
            $('.your-reset-btn').show();
        }
    });
})(jQuery);

</script>
<?php
}, 100 );


/*------------------------------------*
	$UNUSED CODE
*------------------------------------*

/*------------------------------------*
	$GENERAL
*------------------------------------*
//! HELP SCOUT BEACON IN FOOTER
add_action('wp_footer', 'helpscout_beacon');
function helpscout_beacon(){
?>
<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
<script type="text/javascript">window.Beacon('init', '6ec654ae-7a21-494f-879d-8b5865fbd3ba')</script>
<?php
};

/*------------------------------------*
	$RANK MATH
*------------------------------------*

// MANUALLY ADD PAGE TO SITEMAP
add_action( 'rank_math/sitemap/page_content', function() {
    return '<url>
		    <loc>https://vooglue.com/store/brian-carew-hopkins/</loc>
		    <lastmod>2020-05-22T18:02:24+00:00</lastmod>
	    </url>';
});


/*------------------------------------*
	$WC VENDORS
*------------------------------------*
//! Add link before Product Details
add_action( 'wcv_before_product_details', 'product_how_to' );
function product_how_to( $field ){
echo'<a href="http://linktoyour.com/manul/goeshere/>Read the startup guide here</a>"';
}

//! Only show Related Product by the same Bakery
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $product, $woocommerce_loop;
$artist = get_the_author_meta('ID');
//Get url to put in related products title
$sold_by = WCV_Vendors::is_vendor( $artist )
                        ? sprintf( '<a href="%s" class="wcvendors_cart_more_work_by">%s</a>', WCV_Vendors::get_vendor_shop_page( $artist ), WCV_Vendors::get_vendor_sold_by( $artist ) )
                        : get_bloginfo( 'name' );

if ( class_exists( 'WCVendors_Pro' ) ) { 
        $store_url = WCVendors_Pro_Vendor_Controller::get_vendor_store_url( get_the_author_id() );
        $sold_by  = '<a href="'.$store_url.'" class="wcvendors_cart_more_work_by">'.WCV_Vendors::get_vendor_sold_by( $artist ).'</a>';
}

$args = apply_filters('woocommerce_related_products_args', array(
        'post_type'                             => 'product',
        'ignore_sticky_posts'   => 1,
        'no_found_rows'                 => 1,
        'posts_per_page'                => $posts_per_page,
        'orderby'                               => $orderby,
        'author'                                => $artist,
        'post__not_in'                  => array($product->id)
) );
$products = new WP_Query( $args );
$woocommerce_loop['columns']    = $columns;
if ( $products->have_posts() ) : ?>

        <div class="related products">

<?php echo apply_filters('wcvendors_cart_more_work_by', __( 'More work by: ', 'wcvendors' )) . $sold_by . '<br/>'; ?>

                <?php woocommerce_product_loop_start(); ?>

                        <?php while ( $products->have_posts() ) : $products->the_post(); ?>

                                <?php woocommerce_get_template_part( 'content', 'product' ); ?>

                        <?php endwhile; // end of the loop. ?>

                <?php woocommerce_product_loop_end(); ?>

        </div>

<?php endif;
wp_reset_postdata();

/*------------------------------------*
	$WOOCOMMERCE
*------------------------------------*
//! Add First Name and Last Name to Registration Page
// 1. ADD FIELDS
add_action( 'woocommerce_register_form_start', 'bbloomer_add_name_woo_account_registration' );
function bbloomer_add_name_woo_account_registration() {
    ?>
    <p class="form-row form-row-first">
    <label for="reg_billing_first_name"><?php _e( 'First name', 'woocommerce' ); ?> <span class="required">*</span></label>
    <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
    </p>
    <p class="form-row form-row-last">
    <label for="reg_billing_last_name"><?php _e( 'Last name', 'woocommerce' ); ?> <span class="required">*</span></label>
    <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" />
    </p>
    <div class="clear"></div>
    <?php
}

// 2. VALIDATE FIELDS
add_filter( 'woocommerce_registration_errors', 'bbloomer_validate_name_fields', 10, 3 );
function bbloomer_validate_name_fields( $errors, $username, $email ) {
    if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
        $errors->add( 'billing_first_name_error', __( '<strong>Error</strong>: First name is required!', 'woocommerce' ) );
    }
    if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
        $errors->add( 'billing_last_name_error', __( '<strong>Error</strong>: Last name is required!.', 'woocommerce' ) );
    }
    return $errors;
}

// 3. SAVE FIELDS
add_action( 'woocommerce_created_customer', 'bbloomer_save_name_fields' );
function bbloomer_save_name_fields( $customer_id ) {
    if ( isset( $_POST['billing_first_name'] ) ) {
        update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
        update_user_meta( $customer_id, 'first_name', sanitize_text_field($_POST['billing_first_name']) );
    }
    if ( isset( $_POST['billing_last_name'] ) ) {
        update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
        update_user_meta( $customer_id, 'last_name', sanitize_text_field($_POST['billing_last_name']) );
    }
}

//! Fees by Product Category - Needs work to make it a percentage instead of fixed $
// TODO: This is a little complicated and may need some work
add_action( 'woocommerce_cart_calculate_fees', 'cart_fees_by_product_category' );
if ( ! function_exists( 'cart_fees_by_product_category' ) ) {
    function cart_fees_by_product_category( $cart ) {
        // Enter fees here, in `category slug` => `fee amount` format
        $fees = array(
            'hats'    => 5,
            'hoodies' => 10,
            'tshirts' => 15,
        );
        foreach ( $cart->get_cart() as $cart_item_key => $values ) {
            $product_categories = get_the_terms( $values['product_id'], 'product_cat' );
            if ( $product_categories && ! is_wp_error( $product_categories ) ) {
                $product_categories = wp_list_pluck( $product_categories, 'slug' );
                foreach ( $product_categories as $product_category ) {
                    if ( ! empty( $fees[ $product_category ] ) ) {
                        $name      = 'Service fee: ' . get_the_title( $values['product_id'] );
                        $amount    = $fees[ $product_category ];
                        $taxable   = true;
                        $tax_class = '';
                        $cart->add_fee( $name, $amount, $taxable, $tax_class );
                    }
                }
            }
        }
    }
}

/*----------  CHECKOUT  ----------*
//! CheckoutWC: Don't automatically generate password for accounts
add_filter( 'cfw_registration_generate_password', '__return_false' );

/*----------  FACETWP  ----------*
//! Limit proximity results to city, state
add_filter( 'facetwp_proximity_autocomplete_options', function( $options ) {
	$options['types'] = ['(cities)'];
	return $options;
});

?>
