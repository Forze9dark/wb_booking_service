<?php
/**
 * Proporciona la vista de "Descuentos" para el área de administración.
 *
 * Esta vista permite gestionar los descuentos aplicables a los servicios.
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

// Obtener servicios para el selector
global $wpdb;
$services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}booking_services WHERE status = 1 ORDER BY title ASC");

// Filtrar por servicio si se especifica
$service_filter = isset($_GET['service']) ? intval($_GET['service']) : 0;
$where_clause = $service_filter > 0 ? $wpdb->prepare("WHERE d.service_id = %d", $service_filter) : "";

// Obtener descuentos
$discounts = $wpdb->get_results("
    SELECT d.*, s.title as service_title 
    FROM {$wpdb->prefix}booking_discounts d
    LEFT JOIN {$wpdb->prefix}booking_services s ON d.service_id = s.id
    $where_clause
    ORDER BY d.start_date DESC
");
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Descuentos', 'wp-booking-plugin'); ?></h1>
    <a href="#" class="page-title-action add-new-discount"><?php _e('Añadir nuevo', 'wp-booking-plugin'); ?></a>
    <hr class="wp-header-end">
    
    <div class="notice notice-info is-dismissible">
        <p><?php _e('Los descuentos te permiten ofrecer precios especiales para tus servicios durante períodos específicos.', 'wp-booking-plugin'); ?></p>
    </div>
    
    <!-- Filtro por servicio -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get">
                <input type="hidden" name="page" value="wp-booking-discounts">
                <select name="service">
                    <option value="0"><?php _e('Todos los servicios', 'wp-booking-plugin'); ?></option>
                    <?php foreach ($services as $service) : ?>
                        <option value="<?php echo esc_attr($service->id); ?>" <?php selected($service_filter, $service->id); ?>><?php echo esc_html($service->title); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" class="button" value="<?php _e('Filtrar', 'wp-booking-plugin'); ?>">
            </form>
        </div>
        <div class="clear"></div>
    </div>
    
    <!-- Formulario para añadir/editar descuento -->
    <div id="discount-form" class="postbox" style="display: none;">
        <div class="postbox-header">
            <h2 class="hndle"><?php _e('Detalles del descuento', 'wp-booking-plugin'); ?></h2>
        </div>
        <div class="inside">
            <form id="wp-booking-discount-form">
                <input type="hidden" id="discount-id" name="id" value="0">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="discount-name"><?php _e('Nombre', 'wp-booking-plugin'); ?></label></th>
                        <td><input type="text" id="discount-name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="discount-service"><?php _e('Servicio', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <select id="discount-service" name="service_id" required>
                                <option value=""><?php _e('Seleccionar servicio', 'wp-booking-plugin'); ?></option>
                                <?php foreach ($services as $service) : ?>
                                    <option value="<?php echo esc_attr($service->id); ?>"><?php echo esc_html($service->title); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="discount-description"><?php _e('Descripción', 'wp-booking-plugin'); ?></label></th>
                        <td><textarea id="discount-description" name="description" class="large-text" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="discount-type"><?php _e('Tipo de descuento', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <select id="discount-type" name="discount_type">
                                <option value="percentage"><?php _e('Porcentaje (%)', 'wp-booking-plugin'); ?></option>
                                <option value="fixed"><?php _e('Cantidad fija (€)', 'wp-booking-plugin'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="discount-value"><?php _e('Valor del descuento', 'wp-booking-plugin'); ?></label></th>
                        <td><input type="number" id="discount-value" name="discount_value" step="0.01" min="0" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="discount-start-date"><?php _e('Fecha de inicio', 'wp-booking-plugin'); ?></label></th>
                        <td><input type="datetime-local" id="discount-start-date" name="start_date"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="discount-end-date"><?php _e('Fecha de finalización', 'wp-booking-plugin'); ?></label></th>
                        <td><input type="datetime-local" id="discount-end-date" name="end_date"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="discount-status"><?php _e('Estado', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <select id="discount-status" name="status">
                                <option value="1"><?php _e('Activo', 'wp-booking-plugin'); ?></option>
                                <option value="0"><?php _e('Inactivo', 'wp-booking-plugin'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary"><?php _e('Guardar descuento', 'wp-booking-plugin'); ?></button>
                    <button type="button" class="button cancel-form"><?php _e('Cancelar', 'wp-booking-plugin'); ?></button>
                </p>
            </form>
        </div>
    </div>
    
    <!-- Tabla de descuentos -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name column-primary"><?php _e('Nombre', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-service"><?php _e('Servicio', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-discount"><?php _e('Descuento', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-dates"><?php _e('Período', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-status"><?php _e('Estado', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-actions"><?php _e('Acciones', 'wp-booking-plugin'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($discounts)) : ?>
                <tr>
                    <td colspan="6"><?php _e('No hay descuentos disponibles.', 'wp-booking-plugin'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($discounts as $discount) : ?>
                    <tr>
                        <td class="column-name column-primary">
                            <strong><?php echo esc_html($discount->name); ?></strong>
                            <button type="button" class="toggle-row"><span class="screen-reader-text"><?php _e('Mostrar más detalles', 'wp-booking-plugin'); ?></span></button>
                        </td>
                        <td class="column-service">
                            <?php echo esc_html($discount->service_title); ?>
                        </td>
                        <td class="column-discount">
                            <?php 
                            if ($discount->discount_type === 'percentage') {
                                echo esc_html($discount->discount_value) . '%';
                            } else {
                                echo number_format($discount->discount_value, 2) . ' €';
                            }
                            ?>
                        </td>
                        <td class="column-dates">
                            <?php 
                            $start_date = $discount->start_date ? date_i18n(get_option('date_format'), strtotime($discount->start_date)) : __('Sin fecha de inicio', 'wp-booking-plugin');
                            $end_date = $discount->end_date ? date_i18n(get_option('date_format'), strtotime($discount->end_date)) : __('Sin fecha de fin', 'wp-booking-plugin');
                            echo esc_html($start_date) . ' - ' . esc_html($end_date);
                            ?>
                        </td>
                        <td class="column-status">
                            <?php if ($discount->status) : ?>
                                <span class="status-active"><?php _e('Activo', 'wp-booking-plugin'); ?></span>
                            <?php else : ?>
                                <span class="status-inactive"><?php _e('Inactivo', 'wp-booking-plugin'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-actions">
                            <a href="#" class="edit-discount" data-id="<?php echo esc_attr($discount->id); ?>"><?php _e('Editar', 'wp-booking-plugin'); ?></a> | 
                            <a href="#" class="delete-discount" data-id="<?php echo esc_attr($discount->id); ?>"><?php _e('Eliminar', 'wp-booking-plugin'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Mostrar formulario para añadir nuevo descuento
    $('.add-new-discount').on('click', function(e) {
        e.preventDefault();
        $('#discount-id').val(0);
        $('#discount-name').val('');
        $('#discount-service').val('');
        $('#discount-description').val('');
        $('#discount-type').val('percentage');
        $('#discount-value').val('');
        $('#discount-start-date').val('');
        $('#discount-end-date').val('');
        $('#discount-status').val(1);
        $('#discount-form').show();
    });
    
    // Cancelar formulario
    $('.cancel-form').on('click', function(e) {
        e.preventDefault();
        $('#discount-form').hide();
    });
    
    // Editar descuento
    $('.edit-discount').on('click', function(e) {
        e.preventDefault();
        var discountId = $(this).data('id');
        
        // Aquí normalmente harías una petición AJAX para obtener los datos del descuento
        // Por simplicidad, vamos a simular que ya tenemos los datos
        var discountRow = $(this).closest('tr');
        var name = discountRow.find('.column-name strong').text();
        var status = discountRow.find('.status-active').length ? 1 : 0;
        
        $('#discount-id').val(discountId);
        $('#discount-name').val(name);
        $('#discount-status').val(status);
        $('#discount-form').show();
    });
    
    // Eliminar descuento
    $('.delete-discount').on('click', function(e) {
        e.preventDefault();
        if (confirm('¿Estás seguro de que quieres eliminar este descuento?')) {
            var discountId = $(this).data('id');
            
            // Aquí harías una petición AJAX para eliminar el descuento
            $.ajax({
                url: wp_booking_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_booking_delete_discount',
                    nonce: wp_booking_admin_ajax.nonce,
                    id: discountId
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        }
    });
    
    // Enviar formulario
    $('#wp-booking-discount-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&action=wp_booking_save_discount&nonce=' + wp_booking_admin_ajax.nonce;
        
        $.ajax({
            url: wp_booking_admin_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});
</script>

<style>
.status-active {
    color: green;
    font-weight: bold;
}
.status-inactive {
    color: red;
}
</style>
