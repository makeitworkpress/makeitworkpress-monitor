<?php
/** 
 * Plugin Name:  Make it WorkPress Monitor
 * Plugin URI:   https://makeitwork.press/
 * Description:  This plugin is used for maintaining WordPress websites that are hosted or managed by Make it WorkPress. Keep it activated.
 * Version:      1.0
 * Author:       Make it WorkPress
 * Author URI:   https://www.makeitwork.press/
 * License:      GPL3
 * License URI:  https://www.gnu.org/licenses/gpl-3.0.html
 * 
 * - Prohibit the activation of certain plugins
 * - Connect to global api to check server & usage
 */

/**
 * Registers the autoloading for classes and our vendors within the waterfall reviews plugin
 */
spl_autoload_register( function($className) {

    $calledClass    = str_replace( '\\', DIRECTORY_SEPARATOR, str_replace( '_', '-', strtolower($className) ) );
    
    // Plugin Classes
    $pluginClass    = str_replace( 'makeitworkpress-maintenance' . DIRECTORY_SEPARATOR, '', $calledClass);
    $pluginClass    = dirname(__FILE__) .  DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $pluginClass . '.php';

    if( file_exists($pluginClass) ) {
        require_once( $pluginClass );
        return;
    }

    $classNames     = explode(DIRECTORY_SEPARATOR, $calledClass);
    array_splice($classNames, 2, 0, 'src');

    $vendorClass    = dirname(__FILE__) .  DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $classNames) . '.php';

    if( file_exists($vendorClass) ) {
        require_once( $vendorClass );    
    }    
   
} );

/**
 * Boots our plugin
 */
add_action( 'plugins_loaded', function() {
    $plugin = new MakeitWorkPress_Maintenance\Plugin();
} );

// Setup deactivation hook
register_deactivation_hook( __FILE__, function() {
    wp_mail( 
        'hosting@makeitwork.press', 
        sprintf( __('Make it WorkPress Monitor deactivated on %s', 'makeitworkpress'), esc_url(home_url('/')) ), 
        sprintf( __('The monitor was deactivated for %s', 'makeitworkpress'), esc_url(home_url('/')) )
    );
});