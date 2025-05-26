<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/includes
 */

/**
 * Fired during plugin activation.
 *
 * Esta clase define todo lo necesario para ejecutar durante la activación del plugin.
 *
 * @since      1.0.0
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/includes
 * @author     Manus
 */
class WP_Booking_Activator {

    /**
     * Método principal de activación.
     *
     * Crea las tablas necesarias en la base de datos y la página personalizada.
     *
     * @since    1.0.0
     */
    public static function activate() {
        self::create_database_tables();
        self::create_custom_page();
        
        // Guardar la versión del plugin para futuras actualizaciones
        update_option('wp_booking_plugin_version', WP_BOOKING_VERSION);
    }

    /**
     * Crea las tablas necesarias en la base de datos.
     *
     * @since    1.0.0
     */
    private static function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Prefijo para las tablas del plugin
        $table_prefix = $wpdb->prefix . 'booking_';
        
        // Array con las consultas SQL para crear las tablas
        $sql = array();
        
        // 1. Tabla de Categorías
        $table_categories = $table_prefix . 'categories';
        $sql[] = "CREATE TABLE IF NOT EXISTS $table_categories (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // 2. Tabla de Servicios
        $table_services = $table_prefix . 'services';
        $sql[] = "CREATE TABLE IF NOT EXISTS $table_services (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            category_id INT UNSIGNED NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            service_date DATETIME,
            max_capacity INT UNSIGNED NOT NULL DEFAULT 0,
            current_bookings INT UNSIGNED NOT NULL DEFAULT 0,
            status TINYINT(1) NOT NULL DEFAULT 1,
            enable_qr TINYINT(1) NOT NULL DEFAULT 0,
            main_image_id INT UNSIGNED,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id),
            KEY status (status),
            KEY service_date (service_date)
        ) $charset_collate;";
        
        // 3. Tabla de Imágenes
        $table_images = $table_prefix . 'images';
        $sql[] = "CREATE TABLE IF NOT EXISTS $table_images (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            service_id INT UNSIGNED NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            is_main TINYINT(1) NOT NULL DEFAULT 0,
            `order` INT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY service_id (service_id)
        ) $charset_collate;";
        
        // 4. Tabla de Grupos de Artículos
        $table_item_groups = $table_prefix . 'item_groups';
        $sql[] = "CREATE TABLE IF NOT EXISTS $table_item_groups (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status)
        ) $charset_collate;";
        
        // 5. Tabla de Artículos
        $table_items = $table_prefix . 'items';
        $sql[] = "CREATE TABLE IF NOT EXISTS $table_items (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            group_id INT UNSIGNED NOT NULL,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY group_id (group_id),
            KEY status (status)
        ) $charset_collate;";
        
        // 6. Tabla de Relación Servicios-Grupos
        $table_service_item_groups = $table_prefix . 'service_item_groups';
        $sql[] = "CREATE TABLE IF NOT EXISTS $table_service_item_groups (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            service_id INT UNSIGNED NOT NULL,
            group_id INT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY service_group (service_id, group_id),
            KEY service_id (service_id),
            KEY group_id (group_id)
        ) $charset_collate;";
        
        // 7. Tabla de Descuentos
        $table_discounts = $table_prefix . 'discounts';
        $sql[] = "CREATE TABLE IF NOT EXISTS $table_discounts (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            service_id INT UNSIGNED NOT NULL,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            discount_type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
            discount_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            start_date DATETIME,
            end_date DATETIME,
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY service_id (service_id),
            KEY status (status),
            KEY date_range (start_date, end_date)
        ) $charset_collate;";
        
        // 8. Tabla de Reservas
        $table_reservations = $table_prefix . 'reservations';
        $sql[] = "CREATE TABLE IF NOT EXISTS $table_reservations (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            service_id INT UNSIGNED NOT NULL,
            customer_name VARCHAR(100) NOT NULL,
            customer_email VARCHAR(100) NOT NULL,
            customer_phone VARCHAR(20),
            num_people INT UNSIGNED NOT NULL DEFAULT 1,
            total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            reservation_date DATETIME NOT NULL,
            status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
            qr_codes TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY service_id (service_id),
            KEY status (status),
            KEY reservation_date (reservation_date)
        ) $charset_collate;";
        
        // 9. Tabla de Detalles de Reserva
        $table_reservation_items = $table_prefix . 'reservation_items';
        $sql[] = "CREATE TABLE IF NOT EXISTS $table_reservation_items (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            reservation_id INT UNSIGNED NOT NULL,
            item_id INT UNSIGNED NOT NULL,
            quantity INT UNSIGNED NOT NULL DEFAULT 1,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY reservation_id (reservation_id),
            KEY item_id (item_id)
        ) $charset_collate;";
        
        // Ejecutar las consultas SQL para crear las tablas
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($sql as $query) {
            dbDelta($query);
        }
    }

    /**
     * Crea la página personalizada para el plugin.
     *
     * @since    1.0.0
     */
    private static function create_custom_page() {
        // Comprobar si la página de catálogo ya existe
        $page_exists = get_page_by_path('catalogo-servicios');
        
        if (!$page_exists) {
            // Crear la página
            $page_data = array(
                'post_title'    => 'Catálogo de Servicios',
                'post_name'     => 'catalogo-servicios',
                'post_content'  => '[wp_booking_reservations]',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_author'   => 1,
                'comment_status' => 'closed'
            );
            
            // Insertar la página y guardar el ID
            $page_id = wp_insert_post($page_data);
            
            // Guardar el ID de la página en las opciones
            if ($page_id) {
                update_option('wp_booking_page_id', $page_id);
                
                // Asignar la plantilla personalizada a la página
                update_post_meta($page_id, '_wp_page_template', 'wp-booking-template.php');
            }
        }
    }
}
