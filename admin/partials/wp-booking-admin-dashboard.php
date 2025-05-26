<?php
/**
 * Proporciona la vista de "Dashboard" para el área de administración.
 *
 * Esta vista proporciona un resumen general del plugin y sus estadísticas.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/admin/partials
 */

// Si este archivo es llamado directamente, abortar.
if (!defined('WPINC')) {
    die;
}

// Obtener estadísticas
global $wpdb;
$categories_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}booking_categories");
$services_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}booking_services");
$item_groups_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}booking_item_groups");
$items_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}booking_items");
$reservations_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}booking_reservations");
$pending_reservations = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}booking_reservations WHERE status = 'pending'");
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="welcome-panel">
        <div class="welcome-panel-content">
            <h2><?php _e('¡Bienvenido al Plugin de Reservas!', 'wp-booking-plugin'); ?></h2>
            <p class="about-description"><?php _e('Gestiona tus servicios, categorías y reservas desde este panel.', 'wp-booking-plugin'); ?></p>
            <div class="welcome-panel-column-container">
                <div class="welcome-panel-column">
                    <h3><?php _e('Primeros pasos', 'wp-booking-plugin'); ?></h3>
                    <ul>
                        <li><a href="<?php echo admin_url('admin.php?page=wp-booking-categories'); ?>" class="welcome-icon welcome-add-page"><?php _e('Crear categorías', 'wp-booking-plugin'); ?></a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=wp-booking-services'); ?>" class="welcome-icon welcome-add-page"><?php _e('Añadir servicios', 'wp-booking-plugin'); ?></a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=wp-booking-item-groups'); ?>" class="welcome-icon welcome-add-page"><?php _e('Configurar grupos de artículos', 'wp-booking-plugin'); ?></a></li>
                    </ul>
                </div>
                <div class="welcome-panel-column">
                    <h3><?php _e('Más acciones', 'wp-booking-plugin'); ?></h3>
                    <ul>
                        <li><a href="<?php echo admin_url('admin.php?page=wp-booking-reservations'); ?>" class="welcome-icon welcome-view-site"><?php _e('Ver reservas', 'wp-booking-plugin'); ?></a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=wp-booking-settings'); ?>" class="welcome-icon welcome-widgets-menus"><?php _e('Configurar ajustes', 'wp-booking-plugin'); ?></a></li>
                        <li><a href="<?php echo get_permalink(get_option('wp_booking_page_id')); ?>" class="welcome-icon welcome-view-site" target="_blank"><?php _e('Ver página de reservas', 'wp-booking-plugin'); ?></a></li>
                    </ul>
                </div>
                <div class="welcome-panel-column welcome-panel-last">
                    <h3><?php _e('Estadísticas', 'wp-booking-plugin'); ?></h3>
                    <ul>
                        <li><?php printf(__('Categorías: %d', 'wp-booking-plugin'), $categories_count); ?></li>
                        <li><?php printf(__('Servicios: %d', 'wp-booking-plugin'), $services_count); ?></li>
                        <li><?php printf(__('Grupos de artículos: %d', 'wp-booking-plugin'), $item_groups_count); ?></li>
                        <li><?php printf(__('Artículos: %d', 'wp-booking-plugin'), $items_count); ?></li>
                        <li><?php printf(__('Reservas totales: %d', 'wp-booking-plugin'), $reservations_count); ?></li>
                        <li><?php printf(__('Reservas pendientes: %d', 'wp-booking-plugin'), $pending_reservations); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($pending_reservations > 0) : ?>
    <div class="card">
        <h2 class="title"><?php _e('Reservas pendientes', 'wp-booking-plugin'); ?></h2>
        <p><?php printf(__('Tienes %d reservas pendientes que requieren tu atención.', 'wp-booking-plugin'), $pending_reservations); ?></p>
        <p><a href="<?php echo admin_url('admin.php?page=wp-booking-reservations&status=pending'); ?>" class="button button-primary"><?php _e('Ver reservas pendientes', 'wp-booking-plugin'); ?></a></p>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <h2 class="title"><?php _e('Ayuda y soporte', 'wp-booking-plugin'); ?></h2>
        <p><?php _e('Si necesitas ayuda con el plugin, consulta la documentación o contacta con soporte.', 'wp-booking-plugin'); ?></p>
        <p>
            <a href="#" class="button"><?php _e('Documentación', 'wp-booking-plugin'); ?></a>
            <a href="#" class="button"><?php _e('Soporte', 'wp-booking-plugin'); ?></a>
        </p>
    </div>
</div>
