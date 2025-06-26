<?php
/**
 * Plugin Name: Social Chat Floating Icons
 * Description: Display Social Chat Floating Icons or widget (WhatsApp, Messenger, Telegram) on your WordPress site.
 * Plugin URI: https://wpmethods.com/product/social-chat-floating-icons-wordpress-plugin/
 * Author: WP Methods
 * Author URI: https://wpmethods.com/about
 * Version: 1.0.0
 * Text Domain: social-chat-floating-icons
 * Contributors: wpmethods,ajharrashed
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.9
 * Requires PHP: 7.4
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
require_once LC_WPMETHODS_PATH . 'file_autoloader/autoload.php';

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
