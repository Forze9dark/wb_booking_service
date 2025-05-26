<?php
/**
 * La clase responsable de definir la funcionalidad de internacionalización.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/includes
 */

/**
 * Define la funcionalidad de internacionalización.
 *
 * Define el dominio y registra los hooks para cargar el dominio de texto del plugin.
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/includes
 * @author     Manus
 */
class WP_Booking_i18n {

    /**
     * Carga el dominio de texto del plugin para la traducción.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'wp-booking-plugin',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
