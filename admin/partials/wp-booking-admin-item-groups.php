<?php
/**
 * Proporciona la vista de "Grupos de Artículos" para el área de administración.
 *
 * Esta vista permite gestionar los grupos de artículos adicionales.
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

// Obtener grupos de artículos
global $wpdb;
$item_groups = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}booking_item_groups ORDER BY name ASC");
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Grupos de Artículos', 'wp-booking-plugin'); ?></h1>
    <a href="#" class="page-title-action add-new-item-group"><?php _e('Añadir nuevo', 'wp-booking-plugin'); ?></a>
    <hr class="wp-header-end">
    
    <div class="notice notice-info is-dismissible">
        <p><?php _e('Los grupos de artículos te permiten organizar elementos adicionales que se pueden vender con los servicios (bebidas, snacks, etc.).', 'wp-booking-plugin'); ?></p>
    </div>
    
    <!-- Formulario para añadir/editar grupo de artículos -->
    <div id="item-group-form" class="postbox" style="display: none;">
        <div class="postbox-header">
            <h2 class="hndle"><?php _e('Detalles del grupo de artículos', 'wp-booking-plugin'); ?></h2>
        </div>
        <div class="inside">
            <form id="wp-booking-item-group-form">
                <input type="hidden" id="item-group-id" name="id" value="0">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="item-group-name"><?php _e('Nombre', 'wp-booking-plugin'); ?></label></th>
                        <td><input type="text" id="item-group-name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="item-group-description"><?php _e('Descripción', 'wp-booking-plugin'); ?></label></th>
                        <td><textarea id="item-group-description" name="description" class="large-text" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="item-group-status"><?php _e('Estado', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <select id="item-group-status" name="status">
                                <option value="1"><?php _e('Activo', 'wp-booking-plugin'); ?></option>
                                <option value="0"><?php _e('Inactivo', 'wp-booking-plugin'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary"><?php _e('Guardar grupo', 'wp-booking-plugin'); ?></button>
                    <button type="button" class="button cancel-form"><?php _e('Cancelar', 'wp-booking-plugin'); ?></button>
                </p>
            </form>
        </div>
    </div>
    
    <!-- Tabla de grupos de artículos -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name column-primary"><?php _e('Nombre', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-description"><?php _e('Descripción', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-status"><?php _e('Estado', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-items"><?php _e('Artículos', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-actions"><?php _e('Acciones', 'wp-booking-plugin'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($item_groups)) : ?>
                <tr>
                    <td colspan="5"><?php _e('No hay grupos de artículos disponibles.', 'wp-booking-plugin'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($item_groups as $group) : ?>
                    <?php 
                    // Contar artículos en este grupo
                    $items_count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}booking_items WHERE group_id = %d",
                        $group->id
                    ));
                    ?>
                    <tr>
                        <td class="column-name column-primary">
                            <strong><?php echo esc_html($group->name); ?></strong>
                            <button type="button" class="toggle-row"><span class="screen-reader-text"><?php _e('Mostrar más detalles', 'wp-booking-plugin'); ?></span></button>
                        </td>
                        <td class="column-description">
                            <?php echo esc_html($group->description); ?>
                        </td>
                        <td class="column-status">
                            <?php if ($group->status) : ?>
                                <span class="status-active"><?php _e('Activo', 'wp-booking-plugin'); ?></span>
                            <?php else : ?>
                                <span class="status-inactive"><?php _e('Inactivo', 'wp-booking-plugin'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-items">
                            <?php echo esc_html($items_count); ?>
                            <?php if ($items_count > 0) : ?>
                                <a href="<?php echo admin_url('admin.php?page=wp-booking-items&group=' . $group->id); ?>"><?php _e('Ver', 'wp-booking-plugin'); ?></a>
                            <?php endif; ?>
                        </td>
                        <td class="column-actions">
                            <a href="#" class="edit-item-group" data-id="<?php echo esc_attr($group->id); ?>"><?php _e('Editar', 'wp-booking-plugin'); ?></a> | 
                            <?php if ($items_count == 0) : ?>
                                <a href="#" class="delete-item-group" data-id="<?php echo esc_attr($group->id); ?>"><?php _e('Eliminar', 'wp-booking-plugin'); ?></a>
                            <?php else : ?>
                                <span class="delete-disabled" title="<?php _e('No se puede eliminar porque tiene artículos asociados', 'wp-booking-plugin'); ?>"><?php _e('Eliminar', 'wp-booking-plugin'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Mostrar formulario para añadir nuevo grupo
    $('.add-new-item-group').on('click', function(e) {
        e.preventDefault();
        $('#item-group-id').val(0);
        $('#item-group-name').val('');
        $('#item-group-description').val('');
        $('#item-group-status').val(1);
        $('#item-group-form').show();
    });
    
    // Cancelar formulario
    $('.cancel-form').on('click', function(e) {
        e.preventDefault();
        $('#item-group-form').hide();
    });
    
    // Editar grupo
    $('.edit-item-group').on('click', function(e) {
        e.preventDefault();
        var groupId = $(this).data('id');
        
        // Aquí normalmente harías una petición AJAX para obtener los datos del grupo
        // Por simplicidad, vamos a simular que ya tenemos los datos
        var groupRow = $(this).closest('tr');
        var name = groupRow.find('.column-name strong').text();
        var description = groupRow.find('.column-description').text().trim();
        var status = groupRow.find('.status-active').length ? 1 : 0;
        
        $('#item-group-id').val(groupId);
        $('#item-group-name').val(name);
        $('#item-group-description').val(description);
        $('#item-group-status').val(status);
        $('#item-group-form').show();
    });
    
    // Eliminar grupo
    $('.delete-item-group').on('click', function(e) {
        e.preventDefault();
        if (confirm('¿Estás seguro de que quieres eliminar este grupo de artículos?')) {
            var groupId = $(this).data('id');
            
            // Aquí harías una petición AJAX para eliminar el grupo
            $.ajax({
                url: wp_booking_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_booking_delete_item_group',
                    nonce: wp_booking_admin_ajax.nonce,
                    id: groupId
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
    $('#wp-booking-item-group-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&action=wp_booking_save_item_group&nonce=' + wp_booking_admin_ajax.nonce;
        
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
.delete-disabled {
    color: #999;
    cursor: not-allowed;
}
</style>
