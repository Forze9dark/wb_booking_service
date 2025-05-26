<?php
/**
 * Proporciona la vista para la página pública de reservas.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/public/partials
 */

// Obtener categorías
global $wpdb;
$categories_table = $wpdb->prefix . 'booking_categories';
$categories = $wpdb->get_results("SELECT * FROM $categories_table WHERE status = 1 ORDER BY name ASC");

// Obtener servicios
$services_table = $wpdb->prefix . 'booking_services';
$services = $wpdb->get_results("
    SELECT s.*, c.name as category_name 
    FROM $services_table s
    LEFT JOIN $categories_table c ON s.category_id = c.id
    WHERE s.status = 1
    ORDER BY s.title ASC
");

// Obtener grupos de artículos y sus items
$item_groups_table = $wpdb->prefix . 'booking_item_groups';
$items_table = $wpdb->prefix . 'booking_items';
$service_item_groups_table = $wpdb->prefix . 'booking_service_item_groups';

// Preparar array para almacenar los grupos de artículos por servicio
$service_groups = array();

// Para cada servicio, obtener sus grupos de artículos asociados
foreach ($services as $service) {
    $service_groups[$service->id] = $wpdb->get_results($wpdb->prepare("
        SELECT g.*, sig.service_id
        FROM $item_groups_table g
        JOIN $service_item_groups_table sig ON g.id = sig.group_id
        WHERE sig.service_id = %d AND g.status = 1
        ORDER BY g.name ASC
    ", $service->id));
}

// Para cada grupo, obtener sus items
$group_items = array();
$groups = $wpdb->get_results("SELECT * FROM $item_groups_table WHERE status = 1");

foreach ($groups as $group) {
    $group_items[$group->id] = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM $items_table 
        WHERE group_id = %d AND status = 1
        ORDER BY name ASC
    ", $group->id));
}

// Generar nonce para AJAX
$ajax_nonce = wp_create_nonce('wp_booking_public_actions_nonce');
?>

<div class="wp-booking-container">
    <!-- Sección de encabezado con filtros de categoría -->
    <div class="wp-booking-header">
        <h2><?php echo esc_html__('Catálogo de Servicios', 'wp-booking-plugin'); ?></h2>
        <p><?php echo esc_html__('Explora nuestra selección de servicios disponibles', 'wp-booking-plugin'); ?></p>
        
        <div class="wp-booking-categories">
            <button class="wp-booking-category-btn active" data-category="all">
                <i class="fas fa-th-large"></i> <?php echo esc_html__('Todos', 'wp-booking-plugin'); ?>
            </button>
            <?php foreach ($categories as $category) : ?>
                <button class="wp-booking-category-btn" data-category="<?php echo esc_attr($category->id); ?>">
                    <i class="fas fa-tag"></i> <?php echo esc_html($category->name); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Indicador de carga -->
    <div class="wp-booking-loading">
        <p><?php echo esc_html__('Cargando servicios...', 'wp-booking-plugin'); ?></p>
        <div class="wp-booking-spinner"></div>
    </div>
    
    <!-- Contenedor de servicios -->
    <div class="wp-booking-services">
        <?php if (empty($services)) : ?>
            <div class="wp-booking-no-services">
                <p><?php echo esc_html__('No hay servicios disponibles en este momento.', 'wp-booking-plugin'); ?></p>
            </div>
        <?php else : ?>
            <?php foreach ($services as $service) : ?>
                <div class="wp-booking-service-card" data-category="<?php echo esc_attr($service->category_id); ?>" data-service-id="<?php echo esc_attr($service->id); ?>">
                    <div class="wp-booking-service-image">
                        <?php 
                        // Obtener la URL de la imagen usando el ID almacenado
                        $image_url = ''; // Inicializar vacío
                        if (!empty($service->main_image_id)) {
                            $image_url = wp_get_attachment_url($service->main_image_id);
                        }
                        
                        if ($image_url) : ?>
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($service->title); ?>">
                        <?php else : ?>
                            <div class="wp-booking-placeholder-image">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        <span class="wp-booking-service-category"><?php echo esc_html($service->category_name); ?></span>
                    </div>
                    
                    <div class="wp-booking-service-info">
                        <h3><?php echo esc_html($service->title); ?></h3>
                        
                        <div class="wp-booking-service-meta">
                            <div class="wp-booking-meta-item">
                                <i class="fas fa-calendar"></i>
                                <?php 
                                if (!empty($service->service_date)) {
                                    $date = new DateTime($service->service_date);
                                    echo esc_html($date->format('j F, Y'));
                                } else {
                                    echo esc_html__('Fecha no disponible', 'wp-booking-plugin');
                                }
                                ?>
                            </div>
                            <div class="wp-booking-meta-item">
                                <i class="fas fa-users"></i>
                                <?php 
                                echo esc_html__('Disp:', 'wp-booking-plugin'); 
                                echo ' ';
                                if ($service->max_capacity > 0) {
                                    $current_bookings = isset($service->current_bookings) ? intval($service->current_bookings) : 0;
                                    $available = $service->max_capacity - $current_bookings;
                                    echo esc_html($available);
                                } else {
                                    echo esc_html__('∞', 'wp-booking-plugin');
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="wp-booking-service-price">
                            <span><?php echo esc_html(number_format($service->price, 2)); ?> €</span>
                        </div>
                        
                        <button class="wp-booking-reserve-btn" data-service-id="<?php echo esc_attr($service->id); ?>">
                            <i class="fas fa-info-circle"></i> <?php echo esc_html__('Ver detalles', 'wp-booking-plugin'); ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Modal de detalles del servicio -->
    <div class="wp-booking-modal" id="wp-booking-service-modal">
        <div class="wp-booking-modal-overlay"></div>
        <div class="wp-booking-modal-container">
            <button class="wp-booking-modal-close">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="wp-booking-modal-content">
                <div class="wp-booking-service-details">
                    <a href="#" class="wp-booking-back-btn">
                        <i class="fas fa-arrow-left"></i> <?php echo esc_html__('Volver a servicios', 'wp-booking-plugin'); ?>
                    </a>
                    
                    <div class="wp-booking-service-header">
                        <h2 id="wp-booking-service-title"></h2>
                        <div class="wp-booking-service-category-badge" id="wp-booking-service-category"></div>
                    </div>
                    
                    <div class="wp-booking-service-image-large" id="wp-booking-service-image">
                        <div class="wp-booking-placeholder-image-large">
                            <i class="fas fa-image"></i>
                        </div>
                    </div>
                    
                    <div class="wp-booking-service-details-grid">
                        <div class="wp-booking-detail-item">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <h4><?php echo esc_html__('Fecha', 'wp-booking-plugin'); ?></h4>
                                <p id="wp-booking-service-date"></p>
                            </div>
                        </div>
                        
                        <div class="wp-booking-detail-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <h4><?php echo esc_html__('Capacidad', 'wp-booking-plugin'); ?></h4>
                                <p id="wp-booking-service-capacity"></p>
                            </div>
                        </div>
                        
                        <div class="wp-booking-detail-item">
                            <i class="fas fa-tag"></i>
                            <div>
                                <h4><?php echo esc_html__('Precio', 'wp-booking-plugin'); ?></h4>
                                <p id="wp-booking-service-price"></p>
                            </div>
                        </div>
                        
                        <div class="wp-booking-detail-item">
                            <i class="fas fa-qrcode"></i>
                            <div>
                                <h4><?php echo esc_html__('Código QR', 'wp-booking-plugin'); ?></h4>
                                <p id="wp-booking-service-qr"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="wp-booking-service-description">
                        <h3><?php echo esc_html__('Descripción', 'wp-booking-plugin'); ?></h3>
                        <div id="wp-booking-service-description"></div>
                    </div>
                </div>
                
                <div class="wp-booking-reservation-form">
                    <h3><?php echo esc_html__('Reservar ahora', 'wp-booking-plugin'); ?></h3>
                    
                    <form id="wp-booking-form">
                        <input type="hidden" id="wp-booking-service-id" name="service_id" value="">
                        
                        <div class="wp-booking-form-group">
                            <label for="wp-booking-customer-name"><?php echo esc_html__('Nombre completo', 'wp-booking-plugin'); ?></label>
                            <input type="text" id="wp-booking-customer-name" name="customer_name" required>
                        </div>
                        
                        <div class="wp-booking-form-group">
                            <label for="wp-booking-customer-email"><?php echo esc_html__('Email', 'wp-booking-plugin'); ?></label>
                            <input type="email" id="wp-booking-customer-email" name="customer_email" required>
                        </div>
                        
                        <div class="wp-booking-form-group">
                            <label for="wp-booking-customer-phone"><?php echo esc_html__('Teléfono', 'wp-booking-plugin'); ?></label>
                            <input type="tel" id="wp-booking-customer-phone" name="customer_phone" required>
                        </div>
                        
                        <div class="wp-booking-form-group">
                            <label for="wp-booking-num-people"><?php echo esc_html__('Número de personas', 'wp-booking-plugin'); ?></label>
                            <input type="number" id="wp-booking-num-people" name="num_people" min="1" value="1" required>
                        </div>
                        
                        <div class="wp-booking-form-group" id="wp-booking-items-container">
                            <h4><?php echo esc_html__('ARTÍCULOS ADICIONALES', 'wp-booking-plugin'); ?></h4>
                            <div id="wp-booking-items-list"></div>
                        </div>
                        
                        <div class="wp-booking-total">
                            <h4><?php echo esc_html__('Total:', 'wp-booking-plugin'); ?> <span id="wp-booking-total-price">0.00 €</span></h4>
                        </div>
                        
                        <button type="submit" class="wp-booking-submit-btn">
                            <i class="fas fa-check-circle"></i> <?php echo esc_html__('Confirmar reserva', 'wp-booking-plugin'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de procesamiento -->
    <div class="wp-booking-modal" id="wp-booking-processing-modal">
        <div class="wp-booking-modal-overlay"></div>
        <div class="wp-booking-modal-container wp-booking-modal-small">
            <div class="wp-booking-modal-content wp-booking-modal-center">
                <div class="wp-booking-processing">
                    <h3><?php echo esc_html__('Procesando', 'wp-booking-plugin'); ?></h3>
                    <div class="wp-booking-spinner"></div>
                    <p><?php echo esc_html__('Por favor espere mientras procesamos su reserva...', 'wp-booking-plugin'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de éxito -->
    <div class="wp-booking-modal" id="wp-booking-success-modal">
        <div class="wp-booking-modal-overlay"></div>
        <div class="wp-booking-modal-container wp-booking-modal-small">
            <button class="wp-booking-modal-close">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="wp-booking-modal-content wp-booking-modal-center">
                <div class="wp-booking-success">
                    <div class="wp-booking-success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3><?php echo esc_html__('¡Reserva Confirmada!', 'wp-booking-plugin'); ?></h3>
                    <p><?php echo esc_html__('Su reserva ha sido procesada correctamente.', 'wp-booking-plugin'); ?></p>
                    <div id="wp-booking-reservation-details"></div>
                    <div id="wp-booking-qr-code"></div>
                    <button class="wp-booking-close-btn">
                        <i class="fas fa-times"></i> <?php echo esc_html__('Cerrar', 'wp-booking-plugin'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de error -->
    <div class="wp-booking-modal" id="wp-booking-error-modal">
        <div class="wp-booking-modal-overlay"></div>
        <div class="wp-booking-modal-container wp-booking-modal-small">
            <button class="wp-booking-modal-close">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="wp-booking-modal-content wp-booking-modal-center">
                <div class="wp-booking-error">
                    <div class="wp-booking-error-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h3><?php echo esc_html__('Error', 'wp-booking-plugin'); ?></h3>
                    <p id="wp-booking-error-message"><?php echo esc_html__('Error de conexión. Por favor, inténtalo de nuevo más tarde.', 'wp-booking-plugin'); ?></p>
                    <button class="wp-booking-close-btn">
                        <i class="fas fa-times"></i> <?php echo esc_html__('Aceptar', 'wp-booking-plugin'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Datos de servicios y grupos de artículos para JavaScript
    var wpBookingServices = <?php echo json_encode($services); ?>;
    var wpBookingServiceGroups = <?php echo json_encode($service_groups); ?>;
    var wpBookingGroupItems = <?php echo json_encode($group_items); ?>;
    var wpBookingAjaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
