<?php




// dokan_geo_address
// dokan_geo_latitude
// dokan_geo_longitude


$latitude  = get_user_meta( get_current_user_id(), 'wcv_address_latitude', true );
$longitude = get_user_meta( get_current_user_id(), 'wcv_address_longitude', true );

add_action( 'wcv_pro_store_settings_saved', 'rummel_set_lat_long_from_address' );
function rummel_set_lat_long_from_address( $vendor_id ) {
    $address1   = ( isset( $_POST['_wcv_store_address1'] ) ) ? sanitize_text_field( $_POST['_wcv_store_address1'] ) : '';
    $city       = ( isset( $_POST['_wcv_store_city'] ) ) ? sanitize_text_field( $_POST['_wcv_store_city'] ) : '';
    $state      = ( isset( $_POST['_wcv_store_state'] ) ) ? sanitize_text_field( $_POST['_wcv_store_state'] ) : '';
    $country    = ( isset( $_POST['_wcv_store_country'] ) ) ? sanitize_text_field( $_POST['_wcv_store_country'] ) : '';
    $postcode   = ( isset( $_POST['_wcv_store_postcode'] ) ) ? sanitize_text_field( $_POST['_wcv_store_postcode'] ) : '';

    $address = $address1 . '+' . $city . '+' . $state . '+' . $postcode . '+' . $country;
    $prepAddr = str_replace(' ','+',$address);
    // $apikey = get_option( 'wcvendors_pro_google_maps_api_key' );
    $apikey = '859535672206013500305x66187';

    // $geocode = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($prepAddr).'&sensor=false&key='.$apikey);
    $geocode = file_get_contents( 'https://geocode.xyz/' . urlencode($prepAddr) . '?json=1&auth=' . $apikey );

    $output = json_decode($geocode);
    $latitude = $output->latt;
    $longitude = $output->longt;

    // vell($latitude);
    // vell($longitude);
    // Store Address Latitude
    if ( isset( $latitude ) && '' != $latitude ) {
        update_user_meta( $vendor_id, 'wcv_address_latitude', $latitude );
    } else {
        delete_user_meta( $vendor_id, 'wcv_address_latitude' );
    }

    // Store Address Longitude
    if ( isset( $longitude ) && '' != $longitude ) {
        update_user_meta( $vendor_id, 'wcv_address_longitude', $longitude );
    } else {
        delete_user_meta( $vendor_id, 'wcv_address_longitude' );
    }
}






