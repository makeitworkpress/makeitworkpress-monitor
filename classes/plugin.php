<?php
/**
 * Boots our plugin and makes its internal data accessible
 */
namespace MakeitWorkPress;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Plugin {

    /**
     * Required plugins
     * @access private
     */
    private $required;

    /**
     * Constructor
     * @return void
     */
    public function __construct() {

        /**
         * Strictly Required plugins
         */
        $this->required = [
            // 'wordfence/wordfence.php',
            // 'wp-security-audit-log/wp-security-audit-log.php',
            // 'shortpixel-image-optimiser/wp-shortpixel.php',
            'makeitworkpress-maintenance/makeitworkpress-maintenance.php',
            'worker/init.php'
        ]; 
        
        /**
         * Boots our updater
         */

        /**
         * Some configs
         */
        define('SHORTPIXEL_HIDE_API_KEY', true);

        /**
         * Add our necessary hooks
         */
        $this->hook();

        /**
         * Checks if required plugins are active
         */
        $this->check();        

    }

    /**
     * Executes some hooked functions
     * @return void
     */
    private function hook() {

        if( class_exists('WpSecurityAuditLog') && current_user_can( 'manage_options' ) ) {
            
            // Remove the auditing log adverts
            add_action('wsal_init', function($wsal) {
                $set_transient_fn = $wsal->IsMultisite() ? 'set_site_transient' : 'set_transient'; // Check for multisite.
                $set_transient_fn( 'wsal-is-advert-dismissed', true ); // Set advert transient.            
            }, 40); 
        } 

        // Adjusts various menu's from plugins
        add_action( 'admin_menu', [$this, 'menu'], 20 );
        add_action( 'network_admin_menu', [$this, 'menu'], 20 );

        // Removes the W3 TC Icon and the ManageWP Instruction Bar (in a dirty way)
        add_action( 'admin_head', function() {
            echo '<style type="text/css">#toplevel_page_w3tc_dashboard .wp-menu-image { background: none !important; } .mwp-notice-container { display: none; } </style>';
        } );

        // Alters some plugin descriptions
        add_filter( 'all_plugins', function($plugins) {

            foreach( $plugins as $file => $plugin ) {
                if( $file == 'worker/init.php' ) {
                    // $plugins[$file]['Name']         = __('Make it WorkPress Worker', 'makeitworkpress');
                    $plugins[$file]['Description']  = __('The ManageWP Worker helps Make it WorkPress to manage and update your website.', 'makeitworkpress');
                }
            }
            return $plugins;

        }, 20 );

        // Adjusts some of the plugin meta information
        add_filter( 'plugin_row_meta', function($plugin_meta, $plugin_file, $plugin_data, $status) {

            if( $plugin_file == 'worker/init.php' ) {
                $plugin_meta[1] = __('Set up by Make it WorkPress', 'makeitworkpress');     
            }
            return $plugin_meta;

        }, 20, 4 );

    }

    /**
     * Adjusts some of the menu settings as a hooked function onder $this->hook();
     * @return void
     */
    public function menu() {

        // Adjusts the position and title of the WP Auditing Plugin menu
        if( current_user_can('read') ) {
            global $menu, $admin_page_hooks;

            foreach( $menu as $priority => $details ) {

                // Adjusts the position and icon of the Audit Log
                if( $details[2] == 'wsal-auditlog' ) {
                    unset($menu[$priority]);
                    $details[0]         = __('Activity', 'makeitworkpress');
                    $details[6]         = 'dashicons-visibility';

                    $menu[]             = $details;
                }

                // Adjusts the icon of the W3 Total Cache
                if( $details[2] == 'w3tc_dashboard' ) {
                    unset($menu[$priority]);
                    $details[6]         = 'dashicons-chart-line';  
                    $menu[$priority]    = $details; 
                }                

                // Adjusts the name and icon for WordFence
                if( $details[2] == 'Wordfence' ) {
                    unset($menu[$priority]);
                    $details[0]         = __('Security', 'makeitworkpress');
                    $details[6]         = 'dashicons-shield';   
                    $menu[$priority]    = $details;               
                }

            }

        }

    }

    /**
     * Checks if required plugins are active and prevent some actions
     * @return void
     */
    private function check() {

        // The plugins that notify for Worryless WordPress
        add_action( 'admin_init', function() { 

            $notified = [
                ['wordfence/wordfence.php', __('WordFence', 'makeitworkpress'), __('This plugin is required to enhance the security of the site. Please keep it activated.', 'makeitworkpress')],
                ['w3-total-cache/w3-total-cache.php', __('W3 Total Cache', 'makeitworkpress'), __('This plugin is advised to ensure a good performance of your site.', 'makeitworkpress')],
                ['wp-security-audit-log/wp-security-audit-log.php', __('WP Security Auditing Log', 'makeitworkpress'), __('This plugin will log any user actions. ', 'makeitworkpress')],
                ['worker/init.php', __('ManageWP Worker', 'makeitworkpress'), __('This plugin is required to have your site managed by Make it WorkPress.', 'makeitworkpress')],
                ['makeitworkpress-maintenance/makeitworkpress-maintenance.php', __('Make it WorkPress Maintenance', 'makeitworkpress'), __('This plugin is required to have your site managed by Make it WorkPress.', 'makeitworkpress')],
            ];

            foreach( $notified as $key => $plugin ) {

                if( ! is_plugin_active($plugin[0]) && ! is_plugin_active_for_network($plugin[0]) ) {
                    add_action( 'admin_notices', function() use($plugin) {
                        echo '<div class="error"><p>' . sprintf( __('%1$s is not activated. %2$s', 'makeitworkpress'), $plugin[1], $plugin[2]) . '</p></div>';
                    });   
                }

            }

        } );      

        /**
         * Disable activating of certain required plugins
         * We allow the deactiviation of W3 Total Cache as some sites may require a different performance set-up.
         */ 

        // Remove bulk deactivation 
        add_filter( 'bulk_actions-plugins', function($actions) {
            if( isset($actions['deactivate-selected']) ) {
                unset($actions['deactivate-selected']);
            }
            return $actions;
        } );

        // Remove the deactivation links at each plugin
        add_filter( 'plugin_action_links', [$this, 'actionLinks'], 10, 4 );
        add_filter( 'network_admin_plugin_action_links', [$this, 'actionLinks'], 10, 4 );
            

    }
        
    /**
     * Removes certain action links from plugins
     * 
     * @param array     $actions        An array of plugin action links. By default this can include 'activate','deactivate', and 'delete'.
     * @param string    $plugin_file    Path to the plugin file relative to the plugins directory.
     * @param array     $plugin_data    An array of plugin data. See `get_plugin_data()`.
     * @param string    $context        The plugin context. By default this can include 'all', 'active', 'inactive',
     * 
     * @return array    $actions        The modified plugin actions
     */
    public function actionLinks( $actions, $plugin_file, $plugin_data, $context ) {
 
        // Editing is prohibited by default
        if( array_key_exists('edit', $actions) ) {
            unset($actions['edit']);
        }

        if( array_key_exists('deactivate', $actions) && in_array($plugin_file, $this->required) ) {
            unset( $actions['deactivate'] );
        }        

        return $actions;

    }

}