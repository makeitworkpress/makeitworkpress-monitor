<?php
/** 
 * Plugin Name:  Make it WorkPress
 * Plugin URI:   https://www.makeitwork.press/
 * Description:  This plugins is used for setting up WordPress websites that are taken care of by Make it WorkPress. Keep this activated all the time.
 * Version:      1.0
 * Author:       Make it WorkPress
 * Author URI:   https://www.makeitwork.press/
 * License:      GPL3
 * License URI:  https://www.gnu.org/licenses/gpl-3.0.html
 * 
 * 
 * - Prohibit the activation of certain plugins
 * - connect to repo for updates
 * - connect to global api to check server & usage
 */

/**
 * Registers the autoloading for classes and our vendors within the waterfall reviews plugin
 */
spl_autoload_register( function($classname) {

    $class      = str_replace( ['\\', 'makeitworkpress/'], [DIRECTORY_SEPARATOR, ''], str_replace( '_', '-', strtolower($classname) ) );
    $classes    = dirname(__FILE__) .  DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $class . '.php';

    if( file_exists($classes) ) {
        require_once( $classes );
    }
   
} );

/**
 * Boots our plugin
 */
add_action( 'plugins_loaded', function() {
    $plugin = new MakeitWorkPress\Plugin();
} );