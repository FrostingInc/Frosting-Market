<?php
/**
 * @link              https://petersmark.com
 * @since             1.0.0
 * @package           Frosting_Custom
 * @wordpress-plugin
 * Plugin Name:       Frosting Market Custom Plugin
 * Plugin URI:        https://petersmark.com
 * Description:       This is a custom plugin containing code specific to FrostingMarket.com.
 * Version:           1.0.0
 * Author:            David Rummelhoff
 * Author URI:        https://petersmark.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       frosting-custom
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { die; }

define( 'FROSTING_WCV_ABSPATH_TEMPLATES', dirname( __FILE__ ) . '/templates/' );

/**
 * Current plugin version.
 */
define( 'FROSTING_CUSTOM_FILE', __FILE__ );
define( 'FROSTING_CUSTOM_BASE', plugin_basename( FROSTING_CUSTOM_FILE ) );
define( 'FROSTING_CUSTOM_DIR', plugin_dir_path( FROSTING_CUSTOM_FILE ) );
define( 'FROSTING_CUSTOM_URI', plugins_url( '/', FROSTING_CUSTOM_FILE ) );

require_once FROSTING_CUSTOM_DIR . 'wcv_modifications.php';



if (!function_exists('write_log')) {
    function write_log ( $log )  {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        } else {
            $upload_dir = wp_upload_dir();
            $logpath = FROSTING_CUSTOM_DIR . 'rummel_error.log';
            if ( is_array( $log ) || is_object( $log ) ) {
                file_put_contents( $logpath, print_r( $log, true ), FILE_APPEND );
                error_log( print_r( $log, true ) );
            } else {
                file_put_contents( $logpath, $log, FILE_APPEND );
            }
        }
    }
}

function vell( $object=null ){
    ob_start();                    // start buffer capture
    var_dump( $object );           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    write_log( $contents );        // log contents of the result of var_dump( $object )
}


