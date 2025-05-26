<?php
/**
 * Proporciona la vista de "Artículos" para el área de administración.
 *
 * Esta vista permite gestionar los artículos individuales dentro de los grupos.
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

// Obtener grupos para el selector
global $wpdb;
$groups = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}booking_item_groups WHERE status = 1 ORDER BY name ASC");

// Filtrar por grupo si se especifica
$group_filter = isset($_GET['group']) ? intval($_GET['group']) : 0;
$where_clause = $group_filter > 0 ? $wpdb->prepare("WHERE i.group_id = %d", $group_filter) : "";

// Obtener artículos
$items = $wpdb->get_results("
    SELECT i.*, g.name as group_name 
    FROM {$wpdb->prefix}booking_items i
    LEFT JOIN {$wpdb->prefix}booking_item_groups g ON i.group_id = g.id
    $where_clause
    ORDER BY g.name ASC, i.name ASC
");
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Artículos', 'wp-booking-plugin'); ?></h1>
    <a href="#" class="page-title-action add-new-item"><?php _e('Añadir nuevo', 'wp-booking-plugin'); ?></a>
    <hr class="wp-header-end">
    
    <div class="notice notice-info is-dismissible">
        <p><?php _e('Los artículos son elementos individuales que se pueden vender como adicionales a los servicios (agua, refrescos, etc.).', 'wp-booking-plugin'); ?></p>
    </div>
    
    <!-- Filtro por grupo -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get">
                <input type="hidden" name="page" value="wp-booking-items">
                <select name="group">
                    <option value="0"><?php _e('Todos los grupos', 'wp-booking-plugin'); ?></option>
                    <?php foreach ($groups as $group) : ?>
                        <option value="<?php echo esc_attr($group->id); ?>" <?php selected($group_filter, $group->id); ?>><?php echo esc_html($group->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" class="button" value="<?php _e('Filtrar', 'wp-booking-plugin'); ?>">
            </form>
        </div>
        <div class="clear"></div>
    </div>
    
    <!-- Formulario para añadir/editar artículo -->
    <div id="item-form" class="postbox" style="display: none;">
        <div class="postbox-header">
            <h2 class="hndle"><?php _e('Detalles del artículo', 'wp-booking-plugin'); ?></h2>
        </div>
        <div class="inside">
            <form id="wp-booking-item-form">
                <input type="hidden" id="item-id" name="id" value="0">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="item-name"><?php _e('Nombre', 'wp-booking-plugin'); ?></label></th>
                        <td><input type="text" id="item-name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="item-group"><?php _e('Grupo', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <select id="item-group" name="group_id" required>
                                <option value=""><?php _e('Seleccionar grupo', 'wp-booking-plugin'); ?></option>
                                <?php foreach ($groups as $group) : ?>
                                    <option value="<?php echo esc_attr($group->id); ?>"><?php echo esc_html($group->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="item-description"><?php _e('Descripción', 'wp-booking-plugin'); ?></label></th>
                        <td><textarea id="item-description" name="description" class="large-text" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="item-price"><?php _e('Precio', 'wp-booking-plugin'); ?></label></th>
                        <td><input type="number" id="item-price" name="price" step="0.01" min="0" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="item-status"><?php _e('Estado', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <select id="item-status" name="status">
                                <option value="1"><?php _e('Activo', 'wp-booking-plugin'); ?></option>
                                <option value="0"><?php _e('Inactivo', 'wp-booking-plugin'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary"><?php _e('Guardar artículo', 'wp-booking-plugin'); ?></button>
                    <button type="button" class="button cancel-form"><?php _e('Cancelar', 'wp-booking-plugin'); ?></button>
                </p>
            </form>
        </div>
    </div>
    
    <!-- Tabla de artículos -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name column-primary"><?php _e('Nombre', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-group"><?php _e('Grupo', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-description"><?php _e('Descripción', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-price"><?php _e('Precio', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-status"><?php _e('Estado', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-actions"><?php _e('Acciones', 'wp-booking-plugin'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)) : ?>
                <tr>
                    <td colspan="6"><?php _e('No hay artículos disponibles.', 'wp-booking-plugin'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($items as $item) : ?>
                    <tr>
                        <td class="column-name column-primary">
                            <strong><?php echo esc_html($item->name); ?></strong>
                            <button type="button" class="toggle-row"><span class="screen-reader-text"><?php _e('Mostrar más detalles', 'wp-booking-plugin'); ?></span></button>
                        </td>
                        <td class="column-group">
                            <?php echo esc_html($item->group_name); ?>
                        </td>
                        <td class="column-description">
                            <?php echo esc_html($item->description); ?>
                        </td>
                        <td class="column-price">
                            <?php echo number_format($item->price, 2); ?> €
                        </td>
                        <td class="column-status">
                            <?php if ($item->status) : ?>
                                <span class="status-active"><?php _e('Activo', 'wp-booking-plugin'); ?></span>
                            <?php else : ?>
                                <span class="status-inactive"><?php _e('Inactivo', 'wp-booking-plugin'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-actions">
                            <a href="#" class="edit-item" data-id="<?php echo esc_attr($item->id); ?>"><?php _e('Editar', 'wp-booking-plugin'); ?></a> | 
                            <a href="#" class="delete-item" data-id="<?php echo esc_attr($item->id); ?>"><?php _e('Eliminar', 'wp-booking-plugin'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Mostrar formulario para añadir nuevo artículo
    $('.add-new-item').on('click', function(e) {
        e.preventDefault();
        $('#item-id').val(0);
        $('#item-name').val('');
        $('#item-group').val('');
        $('#item-description').val('');
        $('#item-price').val('');
        $('#item-status').val(1);
        $('#item-form').show();
    });
    
    // Cancelar formulario
    $('.cancel-form').on('click', function(e) {
        e.preventDefault();
        $('#item-form').hide();
    });
    
    // Editar artículo
    $('.edit-item').on('click', function(e) {
        e.preventDefault();
        var itemId = $(this).data('id');
        
        // Aquí normalmente harías una petición AJAX para obtener los datos del artículo
        // Por simplicidad, vamos a simular que ya tenemos los datos
        var itemRow = $(this).closest('tr');
        var name = itemRow.find('.column-name strong').text();
        var description = itemRow.find('.column-description').text().trim();
        var price = itemRow.find('.column-price').text().trim().replace(' €', '');
        var status = itemRow.find('.status-active').length ? 1 : 0;
        
        $('#item-id').val(itemId);
        $('#item-name').val(name);
        $('#item-description').val(description);
        $('#item-price').val(price);
        $('#item-status').val(status);
        $('#item-form').show();
    });
    
    // Eliminar artículo
    $('.delete-item').on('click', function(e) {
        e.preventDefault();
        if (confirm('¿Estás seguro de que quieres eliminar este artículo?')) {
            var itemId = $(this).data('id');
            
            // Aquí harías una petición AJAX para eliminar el artículo
            $.ajax({
                url: wp_booking_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_booking_delete_item',
                    nonce: wp_booking_admin_ajax.nonce,
                    id: itemId
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
    $('#wp-booking-item-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&action=wp_booking_save_item&nonce=' + wp_booking_admin_ajax.nonce;
        
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
