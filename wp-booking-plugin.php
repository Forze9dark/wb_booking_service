<?php
/**
 * Plugin Name: WP Booking Plugin
 * Plugin URI: https://example.com/wp-booking-plugin
 * Description: Un plugin para gestionar reservas de servicios turísticos, tours, pasadías y más.
 * Version: 1.0.0
 * Author: Manus
 * Author URI: https://example.com
 * Text Domain: wp-booking-plugin
 * Domain Path: /languages
 */

// Si este archivo es llamado directamente, abortar.
if (!defined('WPINC')) {
    die;
}

// Definir constantes del plugin
define('WP_BOOKING_VERSION', '1.0.0');
define('WP_BOOKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_BOOKING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_BOOKING_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Código que se ejecuta durante la activación del plugin.
 */
function activate_wp_booking_plugin() {
    require_once WP_BOOKING_PLUGIN_DIR . 'includes/class-wp-booking-activator.php';
    WP_Booking_Activator::activate();
}

/**
 * Código que se ejecuta durante la desactivación del plugin.
 */
function deactivate_wp_booking_plugin() {
    require_once WP_BOOKING_PLUGIN_DIR . 'includes/class-wp-booking-deactivator.php';
    WP_Booking_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wp_booking_plugin');
register_deactivation_hook(__FILE__, 'deactivate_wp_booking_plugin');

/**
 * El núcleo de la clase del plugin.
 */
require_once WP_BOOKING_PLUGIN_DIR . 'includes/class-wp-booking-plugin.php';

/**
 * Comienza la ejecución del plugin.
 */
function run_wp_booking_plugin() {
    $plugin = new WP_Booking_Plugin();
    $plugin->run();
}

run_wp_booking_plugin();

