<?php
/**
 * Proporciona la vista de "Categorías" para el área de administración.
 *
 * Esta vista permite gestionar las categorías de servicios.
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

// Obtener categorías
global $wpdb;
$categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}booking_categories ORDER BY name ASC");
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Categorías', 'wp-booking-plugin'); ?></h1>
    <a href="#" class="page-title-action add-new-category"><?php _e('Añadir nueva', 'wp-booking-plugin'); ?></a>
    <hr class="wp-header-end">
    
    <div class="notice notice-info is-dismissible">
        <p><?php _e('Las categorías te permiten organizar tus servicios en grupos lógicos (Tours, Pasadías, Resort, etc.).', 'wp-booking-plugin'); ?></p>
    </div>
    
    <!-- Formulario para añadir/editar categoría -->
    <div id="category-form" class="postbox" style="display: none;">
        <div class="postbox-header">
            <h2 class="hndle"><?php _e('Detalles de la categoría', 'wp-booking-plugin'); ?></h2>
        </div>
        <div class="inside">
            <form id="wp-booking-category-form">
                <input type="hidden" id="category-id" name="id" value="0">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="category-name"><?php _e('Nombre', 'wp-booking-plugin'); ?></label></th>
                        <td><input type="text" id="category-name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="category-description"><?php _e('Descripción', 'wp-booking-plugin'); ?></label></th>
                        <td><textarea id="category-description" name="description" class="large-text" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="category-status"><?php _e('Estado', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <select id="category-status" name="status">
                                <option value="1"><?php _e('Activo', 'wp-booking-plugin'); ?></option>
                                <option value="0"><?php _e('Inactivo', 'wp-booking-plugin'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary"><?php _e('Guardar categoría', 'wp-booking-plugin'); ?></button>
                    <button type="button" class="button cancel-form"><?php _e('Cancelar', 'wp-booking-plugin'); ?></button>
                </p>
            </form>
        </div>
    </div>
    
    <!-- Tabla de categorías -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name column-primary"><?php _e('Nombre', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-description"><?php _e('Descripción', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-status"><?php _e('Estado', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-services"><?php _e('Servicios', 'wp-booking-plugin'); ?></th>
                <th scope="col" class="manage-column column-actions"><?php _e('Acciones', 'wp-booking-plugin'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)) : ?>
                <tr>
                    <td colspan="5"><?php _e('No hay categorías disponibles.', 'wp-booking-plugin'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($categories as $category) : ?>
                    <?php 
                    // Contar servicios en esta categoría
                    $services_count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}booking_services WHERE category_id = %d",
                        $category->id
                    ));
                    ?>
                    <tr>
                        <td class="column-name column-primary">
                            <strong><?php echo esc_html($category->name); ?></strong>
                            <button type="button" class="toggle-row"><span class="screen-reader-text"><?php _e('Mostrar más detalles', 'wp-booking-plugin'); ?></span></button>
                        </td>
                        <td class="column-description">
                            <?php echo esc_html($category->description); ?>
                        </td>
                        <td class="column-status">
                            <?php if ($category->status) : ?>
                                <span class="status-active"><?php _e('Activo', 'wp-booking-plugin'); ?></span>
                            <?php else : ?>
                                <span class="status-inactive"><?php _e('Inactivo', 'wp-booking-plugin'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-services">
                            <?php echo esc_html($services_count); ?>
                            <?php if ($services_count > 0) : ?>
                                <a href="<?php echo admin_url('admin.php?page=wp-booking-services&category=' . $category->id); ?>"><?php _e('Ver', 'wp-booking-plugin'); ?></a>
                            <?php endif; ?>
                        </td>
                        <td class="column-actions">
                            <a href="#" class="edit-category" data-id="<?php echo esc_attr($category->id); ?>"><?php _e('Editar', 'wp-booking-plugin'); ?></a> | 
                            <?php if ($services_count == 0) : ?>
                                <a href="#" class="delete-category" data-id="<?php echo esc_attr($category->id); ?>"><?php _e('Eliminar', 'wp-booking-plugin'); ?></a>
                            <?php else : ?>
                                <span class="delete-disabled" title="<?php _e('No se puede eliminar porque tiene servicios asociados', 'wp-booking-plugin'); ?>"><?php _e('Eliminar', 'wp-booking-plugin'); ?></span>
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
    // Mostrar formulario para añadir nueva categoría
    $('.add-new-category').on('click', function(e) {
        e.preventDefault();
        $('#category-id').val(0);
        $('#category-name').val('');
        $('#category-description').val('');
        $('#category-status').val(1);
        $('#category-form').show();
    });
    
    // Cancelar formulario
    $('.cancel-form').on('click', function(e) {
        e.preventDefault();
        $('#category-form').hide();
    });
    
    // Editar categoría
    $('.edit-category').on('click', function(e) {
        e.preventDefault();
        var categoryId = $(this).data('id');
        
        // Aquí normalmente harías una petición AJAX para obtener los datos de la categoría
        // Por simplicidad, vamos a simular que ya tenemos los datos
        var categoryRow = $(this).closest('tr');
        var name = categoryRow.find('.column-name strong').text();
        var description = categoryRow.find('.column-description').text().trim();
        var status = categoryRow.find('.status-active').length ? 1 : 0;
        
        $('#category-id').val(categoryId);
        $('#category-name').val(name);
        $('#category-description').val(description);
        $('#category-status').val(status);
        $('#category-form').show();
    });
    
    // Eliminar categoría
    $('.delete-category').on('click', function(e) {
        e.preventDefault();
        if (confirm('¿Estás seguro de que quieres eliminar esta categoría?')) {
            var categoryId = $(this).data('id');
            
            // Aquí harías una petición AJAX para eliminar la categoría
            $.ajax({
                url: wp_booking_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_booking_delete_category',
                    nonce: wp_booking_admin_ajax.nonce,
                    id: categoryId
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
    $('#wp-booking-category-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&action=wp_booking_save_category&nonce=' + wp_booking_admin_ajax.nonce;
        
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
