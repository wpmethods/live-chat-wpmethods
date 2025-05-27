<?php
/**
 * Plugin Name: Social Chat Floating Icons
 * Description: Display Social Chat Floating Icons or widget (WhatsApp, Messenger, Telegram) on your WordPress site.
 * Plugin URI: https://wpmethods.com
 * Author: WP Methods
 * Author URI: https://wpmethods.com
 * Version: 1.0.0
 * Text Domain: lc-wpmethods
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin path and url
define('LC_WPMETHODS_PATH', plugin_dir_path(__FILE__));
define('LC_WPMETHODS_URL', plugin_dir_url(__FILE__));

define('VERSION_SFIW', '1.0.0');

// Autoload Classes
require_once LC_WPMETHODS_PATH . 'vendor/autoload.php';

// Initialize
add_action('plugins_loaded', function() {
    if (is_admin()) {
        new LC_WPMethods\Admin_Settings_Lcw();
        new LC_WPMethods\License_Lcw();

    } else {
        new LC_WPMethods\Front_End();
    }
});


//Allowed Custom Protocols
add_filter('kses_allowed_protocols', function ($protocols) {
    $protocols[] = 'skype';
    $protocols[] = 'viber';
    $protocols[] = 'weixin';
    return $protocols;
});