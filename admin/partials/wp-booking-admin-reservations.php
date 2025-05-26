<?php
/**
 * Proporciona la vista de "Reservas" para el área de administración.
 *
 * Esta vista permite gestionar las reservas realizadas por los clientes.
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

// Filtrar por estado si se especifica
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$where_clause = '';

if ($status_filter && in_array($status_filter, array('pending', 'confirmed', 'cancelled'))) {
    global $wpdb;
    $where_clause = $wpdb->prepare("WHERE r.status = %s", $status_filter);
}

// Obtener reservas
global $wpdb;
$reservations = $wpdb->get_results("
    SELECT r.*, s.title as service_title 
    FROM {$wpdb->prefix}booking_reservations r
    LEFT JOIN {$wpdb->prefix}booking_services s ON r.service_id = s.id
    $where_clause
    ORDER BY r.reservation_date DESC
");
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Reservas', 'wp-booking-plugin'); ?></h1>
    <hr class="wp-header-end">
    
    <div class="notice notice-info is-dismissible">
        <p><?php _e('Gestiona las reservas realizadas por los clientes para tus servicios.', 'wp-booking-plugin'); ?></p>
    </div>
    
    <!-- Filtro por estado -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get">
                <input type="hidden" name="page" value="wp-booking-reservations">
                <select name="status">
                    <option value=""><?php _e('Todas las reservas', 'wp-booking-plugin'); ?></option>
                    <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php _e('Pendientes', 'wp-booking-plugin'); ?></option>
                    <option value="confirmed" <?php selected($status_filter, 'confirmed'); ?>><?php _e('Confirmadas', 'wp-booking-plugin'); ?></option>
                    <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>><?php _e('Canceladas', 'wp-booking-plugin'); ?></option>
                </select>
                <input type="submit" class="button" value="<?php _e('Filtrar', 'wp-booking-plugin'); ?>">
            </form>
        </div>
        <div class="clear"></div>
    </div>
    
    <!-- Tabla de reservas -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-id"><?php _e('ID', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-service"><?php _e('Servicio', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-customer column-primary"><?php _e('Cliente', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-people"><?php _e('Personas', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-price"><?php _e('Precio', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-date"><?php _e('Fecha', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-status"><?php _e('Estado', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-actions"><?php _e('Acciones', 'wp-booking-plugin'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reservations)) : ?>
                <tr>
                    <td colspan="8"><?php _e('No hay reservas disponibles.', 'wp-booking-plugin'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($reservations as $reservation) : ?>
                    <tr>
                        <td class="column-id">
                            <?php echo esc_html($reservation->id); ?>
                        </td>
                        <td class="column-service">
                            <?php echo esc_html($reservation->service_title); ?>
                        </td>
                        <td class="column-customer column-primary">
                            <strong><?php echo esc_html($reservation->customer_name); ?></strong><br>
                            <?php echo esc_html($reservation->customer_email); ?><br>
                            <?php echo esc_html($reservation->customer_phone); ?>
                            <button type="button" class="toggle-row"><span class="screen-reader-text"><?php _e('Mostrar más detalles', 'wp-booking-plugin'); ?></span></button>
                        </td>
                        <td class="column-people">
                            <?php echo esc_html($reservation->num_people); ?>
                        </td>
                        <td class="column-price">
                            <?php echo number_format($reservation->total_price, 2); ?> €
                        </td>
                        <td class="column-date">
                            <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($reservation->reservation_date)); ?>
                        </td>
                        <td class="column-status">
                            <?php 
                            $status_class = '';
                            $status_text = '';
                            
                            switch ($reservation->status) {
                                case 'pending':
                                    $status_class = 'status-pending';
                                    $status_text = __('Pendiente', 'wp-booking-plugin');
                                    break;
                                case 'confirmed':
                                    $status_class = 'status-confirmed';
                                    $status_text = __('Confirmada', 'wp-booking-plugin');
                                    break;
                                case 'cancelled':
                                    $status_class = 'status-cancelled';
                                    $status_text = __('Cancelada', 'wp-booking-plugin');
                                    break;
                            }
                            ?>
                            <span class="<?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_text); ?></span>
                        </td>
                        <td class="column-actions">
                            <a href="#" class="view-reservation" data-id="<?php echo esc_attr($reservation->id); ?>"><?php _e('Ver detalles', 'wp-booking-plugin'); ?></a><br>
                            <div class="row-actions">
                                <?php if ($reservation->status !== 'confirmed') : ?>
                                    <span class="confirm">
                                        <a href="#" class="update-status" data-id="<?php echo esc_attr($reservation->id); ?>" data-status="confirmed"><?php _e('Confirmar', 'wp-booking-plugin'); ?></a> |
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($reservation->status !== 'cancelled') : ?>
                                    <span class="cancel">
                                        <a href="#" class="update-status" data-id="<?php echo esc_attr($reservation->id); ?>" data-status="cancelled"><?php _e('Cancelar', 'wp-booking-plugin'); ?></a>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Modal para ver detalles de la reserva -->
    <div id="reservation-details-modal" class="wp-booking-modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2><?php _e('Detalles de la Reserva', 'wp-booking-plugin'); ?></h2>
            <div id="reservation-details-container"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Ver detalles de la reserva
    $('.view-reservation').on('click', function(e) {
        e.preventDefault();
        var reservationId = $(this).data('id');
        
        // Aquí normalmente harías una petición AJAX para obtener los detalles completos
        // Por simplicidad, vamos a simular que ya tenemos los datos
        var reservationRow = $(this).closest('tr');
        var id = reservationRow.find('.column-id').text().trim();
        var service = reservationRow.find('.column-service').text().trim();
        var customer = reservationRow.find('.column-customer strong').text().trim();
        var email = reservationRow.find('.column-customer').contents().filter(function() {
            return this.nodeType === 3;
        }).first().text().trim();
        var phone = reservationRow.find('.column-customer').contents().filter(function() {
            return this.nodeType === 3;
        }).last().text().trim();
        var people = reservationRow.find('.column-people').text().trim();
        var price = reservationRow.find('.column-price').text().trim();
        var date = reservationRow.find('.column-date').text().trim();
        var status = reservationRow.find('.column-status span').text().trim();
        
        var html = '<div class="reservation-details">';
        html += '<p><strong>' + <?php echo json_encode(__('ID de Reserva:', 'wp-booking-plugin')); ?> + '</strong> ' + id + '</p>';
        html += '<p><strong>' + <?php echo json_encode(__('Servicio:', 'wp-booking-plugin')); ?> + '</strong> ' + service + '</p>';
        html += '<p><strong>' + <?php echo json_encode(__('Cliente:', 'wp-booking-plugin')); ?> + '</strong> ' + customer + '</p>';
        html += '<p><strong>' + <?php echo json_encode(__('Email:', 'wp-booking-plugin')); ?> + '</strong> ' + email + '</p>';
        html += '<p><strong>' + <?php echo json_encode(__('Teléfono:', 'wp-booking-plugin')); ?> + '</strong> ' + phone + '</p>';
        html += '<p><strong>' + <?php echo json_encode(__('Número de personas:', 'wp-booking-plugin')); ?> + '</strong> ' + people + '</p>';
        html += '<p><strong>' + <?php echo json_encode(__('Precio total:', 'wp-booking-plugin')); ?> + '</strong> ' + price + '</p>';
        html += '<p><strong>' + <?php echo json_encode(__('Fecha de reserva:', 'wp-booking-plugin')); ?> + '</strong> ' + date + '</p>';
        html += '<p><strong>' + <?php echo json_encode(__('Estado:', 'wp-booking-plugin')); ?> + '</strong> ' + status + '</p>';
        
        // Aquí podrías añadir más detalles como artículos adicionales, códigos QR, etc.
        
        html += '</div>';
        
        $('#reservation-details-container').html(html);
        $('#reservation-details-modal').show();
    });
    
    // Cerrar modal
    $('.close-modal').on('click', function() {
        $('#reservation-details-modal').hide();
    });
    
    // Cerrar modal al hacer clic fuera
    $(window).on('click', function(event) {
        if ($(event.target).hasClass('wp-booking-modal')) {
            $('.wp-booking-modal').hide();
        }
    });
    
    // Actualizar estado de la reserva
    $('.update-status').on('click', function(e) {
        e.preventDefault();
        var reservationId = $(this).data('id');
        var newStatus = $(this).data('status');
        var statusText = newStatus === 'confirmed' ? <?php echo json_encode(__('confirmar', 'wp-booking-plugin')); ?> : <?php echo json_encode(__('cancelar', 'wp-booking-plugin')); ?>;
        
        if (confirm(<?php echo json_encode(__('¿Estás seguro de que quieres', 'wp-booking-plugin')); ?> + ' ' + statusText + ' ' + <?php echo json_encode(__('esta reserva?', 'wp-booking-plugin')); ?>)) {
            // Aquí harías una petición AJAX para actualizar el estado
            $.ajax({
                url: wp_booking_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_booking_update_reservation_status',
                    nonce: wp_booking_admin_ajax.nonce,
                    id: reservationId,
                    status: newStatus
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
});
</script>

<style>
.status-pending {
    background-color: #fcf8e3;
    color: #8a6d3b;
    padding: 3px 8px;
    border-radius: 3px;
}
.status-confirmed {
    background-color: #dff0d8;
    color: #3c763d;
    padding: 3px 8px;
    border-radius: 3px;
}
.status-cancelled {
    background-color: #f2dede;
    color: #a94442;
    padding: 3px 8px;
    border-radius: 3px;
}

/* Modal */
.wp-booking-modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
    overflow: auto;
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
}

.close-modal {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover,
.close-modal:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.reservation-details p {
    margin: 10px 0;
}
</style>
