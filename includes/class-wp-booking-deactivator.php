<?php
/**
 * Fired during plugin deactivation.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * Esta clase define todo lo necesario para ejecutar durante la desactivación del plugin.
 *
 * @since      1.0.0
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/includes
 * @author     Manus
 */
class WP_Booking_Deactivator {

    /**
     * Método principal de desactivación.
     *
     * Elimina todas las tablas de la base de datos creadas por el plugin.
     * ¡ADVERTENCIA! Esta acción es destructiva y eliminará todos los datos.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        global $wpdb;
        
        // Prefijo de las tablas del plugin
        $table_prefix = $wpdb->prefix . 'booking_';
        
        // Lista de tablas a eliminar
        $tables_to_drop = array(
            $table_prefix . 'reservation_items',
            $table_prefix . 'reservations',
            $table_prefix . 'discounts',
            $table_prefix . 'service_item_groups',
            $table_prefix . 'items',
            $table_prefix . 'item_groups',
            $table_prefix . 'images',
            $table_prefix . 'services',
            $table_prefix . 'categories'
        );
        
        // Eliminar cada tabla
        foreach ($tables_to_drop as $table_name) {
            $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        }
        
        // Eliminar opciones del plugin (opcional, pero recomendado para limpieza completa)
        delete_option('wp_booking_plugin_version');
        delete_option('wp_booking_page_id');
        delete_option('wp_booking_general_settings');
        delete_option('wp_booking_plugin_deactivated'); // Eliminar el registro de desactivación anterior
        
        // Eliminar la página de reservas creada por el plugin (opcional)
        $page_id = get_option('wp_booking_page_id');
        if ($page_id) {
            wp_delete_post($page_id, true); // true para forzar el borrado
        }
    }
}

