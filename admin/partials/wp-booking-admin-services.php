<?php
/**
 * Proporciona la vista para la página de servicios en el área de administración.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/admin/partials
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
    ORDER BY s.title ASC
");

// Obtener grupos de artículos
$item_groups_table = $wpdb->prefix . 'booking_item_groups';
$item_groups = $wpdb->get_results("SELECT * FROM $item_groups_table WHERE status = 1 ORDER BY name ASC");
?>

<div class="wrap wp-booking-admin-wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Servicios', 'wp-booking-plugin'); ?></h1>
    <a href="#" class="page-title-action add-new-service"><?php echo esc_html__('Añadir nuevo', 'wp-booking-plugin'); ?></a>
    
    <hr class="wp-header-end">

    <div id="col-container" class="wp-clearfix">
        <div id="col-left">
            <div class="col-wrap">
                <div class="form-wrap">
                    <h2 id="service-form-title"><?php echo esc_html__('Añadir nuevo servicio', 'wp-booking-plugin'); ?></h2>
                    <form id="wp-booking-service-form" method="post" class="validate">
                        <input type="hidden" id="service-id" name="id" value="0">
                        <?php wp_nonce_field('wp_booking_save_service_action', 'wp_booking_save_service_nonce'); ?>

                        <div class="form-field form-required term-name-wrap">
                            <label for="service-title"><?php echo esc_html__('Título', 'wp-booking-plugin'); ?></label>
                            <input name="title" id="service-title" type="text" value="" size="40" aria-required="true" required>
                            <p><?php echo esc_html__('El nombre es cómo aparece en tu sitio.', 'wp-booking-plugin'); ?></p>
                        </div>

                        <div class="form-field term-parent-wrap">
                            <label for="service-category"><?php echo esc_html__('Categoría', 'wp-booking-plugin'); ?></label>
                            <select name="category_id" id="service-category" class="postform" required>
                                <option value=""><?php echo esc_html__('Seleccionar categoría', 'wp-booking-plugin'); ?></option>
                                <?php foreach ($categories as $category) : ?>
                                    <option value="<?php echo esc_attr($category->id); ?>"><?php echo esc_html($category->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p><?php echo esc_html__('Asigna una categoría a este servicio.', 'wp-booking-plugin'); ?></p>
                        </div>

                        <div class="form-field term-description-wrap">
                            <label for="service-description"><?php echo esc_html__('Descripción', 'wp-booking-plugin'); ?></label>
                            <textarea name="description" id="service-description" rows="5" cols="40"></textarea>
                            <p><?php echo esc_html__('La descripción no es prominente por defecto; sin embargo, algunos temas pueden mostrarla.', 'wp-booking-plugin'); ?></p>
                        </div>

                        <div class="form-field form-required term-price-wrap">
                            <label for="service-price"><?php echo esc_html__('Precio (€)', 'wp-booking-plugin'); ?></label>
                            <input name="price" id="service-price" type="number" step="0.01" min="0" value="" size="40" aria-required="true" required>
                            <p><?php echo esc_html__('Precio base del servicio por persona.', 'wp-booking-plugin'); ?></p>
                        </div>

                        <div class="form-field term-date-wrap">
                            <label for="service-date"><?php echo esc_html__('Fecha del servicio', 'wp-booking-plugin'); ?></label>
                            <input name="service_date" id="service-date" type="datetime-local" value="">
                            <p><?php echo esc_html__('Fecha y hora de inicio del servicio (opcional).', 'wp-booking-plugin'); ?></p>
                        </div>

                        <div class="form-field term-capacity-wrap">
                            <label for="service-max-capacity"><?php echo esc_html__('Capacidad máxima', 'wp-booking-plugin'); ?></label>
                            <input name="max_capacity" id="service-max-capacity" type="number" min="0" value="0" size="40">
                            <p><?php echo esc_html__('Número máximo de personas. Deja 0 para capacidad ilimitada.', 'wp-booking-plugin'); ?></p>
                        </div>

                        <div class="form-field term-status-wrap">
                            <label for="service-status"><?php echo esc_html__('Estado', 'wp-booking-plugin'); ?></label>
                            <select name="status" id="service-status" class="postform">
                                <option value="1" selected><?php echo esc_html__('Activo', 'wp-booking-plugin'); ?></option>
                                <option value="0"><?php echo esc_html__('Inactivo', 'wp-booking-plugin'); ?></option>
                            </select>
                            <p><?php echo esc_html__('Estado del servicio (activo o inactivo).', 'wp-booking-plugin'); ?></p>
                        </div>

                        <div class="form-field term-qr-wrap">
                            <label for="service-enable-qr">
                                <input name="enable_qr" id="service-enable-qr" type="checkbox" value="1">
                                <?php echo esc_html__('Habilitar códigos QR', 'wp-booking-plugin'); ?>
                            </label>
                            <p><?php echo esc_html__('Generar códigos QR únicos para cada reserva de este servicio.', 'wp-booking-plugin'); ?></p>
                        </div>

                        <div class="form-field term-image-wrap">
                            <label><?php echo esc_html__('Imagen principal', 'wp-booking-plugin'); ?></label>
                            <div id="main-image-preview" class="wp-booking-image-preview-wrapper"></div>
                            <input type="hidden" id="service-main-image-id" name="main_image_id" value="0">
                            <button type="button" id="upload-main-image" class="button"><?php echo esc_html__('Seleccionar / Subir Imagen', 'wp-booking-plugin'); ?></button>
                            <button type="button" id="remove-main-image" class="button button-link-delete" style="display: none;"><?php echo esc_html__('Eliminar imagen', 'wp-booking-plugin'); ?></button>
                            <p><?php echo esc_html__('Imagen representativa del servicio.', 'wp-booking-plugin'); ?></p>
                        </div>

                        <div class="form-field term-groups-wrap">
                            <label><?php echo esc_html__('Grupos de artículos asociados', 'wp-booking-plugin'); ?></label>
                            <?php if (empty($item_groups)) : ?>
                                <p><?php echo esc_html__('No hay grupos de artículos disponibles. Créalos primero.', 'wp-booking-plugin'); ?></p>
                            <?php else : ?>
                                <div class="wp-booking-checkbox-group">
                                    <?php foreach ($item_groups as $group) : ?>
                                        <label>
                                            <input type="checkbox" name="item_groups[]" value="<?php echo esc_attr($group->id); ?>">
                                            <?php echo esc_html($group->name); ?>
                                        </label><br>
                                    <?php endforeach; ?>
                                </div>
                                <p><?php echo esc_html__('Selecciona los grupos de artículos adicionales que se ofrecerán con este servicio.', 'wp-booking-plugin'); ?></p>
                            <?php endif; ?>
                        </div>

                        <div id="publishing-action">
                            <span class="spinner"></span>
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_html__('Guardar servicio', 'wp-booking-plugin'); ?>">
                            <button type="button" class="button cancel-form button-secondary"><?php echo esc_html__('Cancelar', 'wp-booking-plugin'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- /col-left -->

        <div id="col-right">
            <div class="col-wrap">
                <table class="wp-list-table widefat fixed striped table-view-list">
                    <thead>
                        <tr>
                            <th scope="col" id="title" class="manage-column column-title column-primary"><?php echo esc_html__('Título', 'wp-booking-plugin'); ?></th>
                            <th scope="col" id="category" class="manage-column column-category"><?php echo esc_html__('Categoría', 'wp-booking-plugin'); ?></th>
                            <th scope="col" id="price" class="manage-column column-price"><?php echo esc_html__('Precio', 'wp-booking-plugin'); ?></th>
                            <th scope="col" id="date" class="manage-column column-date"><?php echo esc_html__('Fecha', 'wp-booking-plugin'); ?></th>
                            <th scope="col" id="capacity" class="manage-column column-capacity"><?php echo esc_html__('Capacidad', 'wp-booking-plugin'); ?></th>
                            <th scope="col" id="status" class="manage-column column-status"><?php echo esc_html__('Estado', 'wp-booking-plugin'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="the-list">
                        <?php if (empty($services)) : ?>
                            <tr class="no-items">
                                <td class="colspanchange" colspan="6"><?php echo esc_html__('No se encontraron servicios.', 'wp-booking-plugin'); ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($services as $service) : ?>
                                <tr id="service-<?php echo esc_attr($service->id); ?>">
                                    <td class="title column-title has-row-actions column-primary" data-colname="Título">
                                        <strong><a class="row-title" href="#" aria-label="<?php echo esc_attr($service->title); ?>"><?php echo esc_html($service->title); ?></a></strong>
                                        <div class="row-actions">
                                            <span class="edit"><a href="#" class="edit-service" data-id="<?php echo esc_attr($service->id); ?>" aria-label="<?php echo esc_attr__('Editar este servicio', 'wp-booking-plugin'); ?>"><?php echo esc_html__('Editar', 'wp-booking-plugin'); ?></a> | </span>
                                            <span class="delete"><a href="#" class="delete-service" data-id="<?php echo esc_attr($service->id); ?>" aria-label="<?php echo esc_attr__('Eliminar este servicio', 'wp-booking-plugin'); ?>"><?php echo esc_html__('Eliminar', 'wp-booking-plugin'); ?></a></span>
                                        </div>
                                    </td>
                                    <td class="category column-category" data-colname="Categoría"><?php echo esc_html($service->category_name); ?></td>
                                    <td class="price column-price" data-colname="Precio"><?php echo esc_html(number_format($service->price, 2) . ' €'); ?></td>
                                    <td class="date column-date" data-colname="Fecha">
                                        <?php 
                                        if (!empty($service->service_date)) {
                                            try {
                                                $date = new DateTime($service->service_date);
                                                echo esc_html($date->format('d/m/Y H:i'));
                                            } catch (Exception $e) {
                                                echo esc_html__('Fecha inválida', 'wp-booking-plugin');
                                            }
                                        } else {
                                            echo esc_html__('No definida', 'wp-booking-plugin');
                                        }
                                        ?>
                                    </td>
                                    <td class="capacity column-capacity" data-colname="Capacidad">
                                        <?php 
                                        if ($service->max_capacity > 0) {
                                            $current_bookings = isset($service->current_bookings) ? $service->current_bookings : 0;
                                            echo esc_html($current_bookings . ' / ' . $service->max_capacity);
                                        } else {
                                            echo esc_html__('Ilimitada', 'wp-booking-plugin');
                                        }
                                        ?>
                                    </td>
                                    <td class="status column-status" data-colname="Estado">
                                        <?php if ($service->status == 1) : ?>
                                            <span class="wp-booking-status wp-booking-status-active"><?php echo esc_html__('Activo', 'wp-booking-plugin'); ?></span>
                                        <?php else : ?>
                                            <span class="wp-booking-status wp-booking-status-inactive"><?php echo esc_html__('Inactivo', 'wp-booking-plugin'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="col" class="manage-column column-title column-primary"><?php echo esc_html__('Título', 'wp-booking-plugin'); ?></th>
                            <th scope="col" class="manage-column column-category"><?php echo esc_html__('Categoría', 'wp-booking-plugin'); ?></th>
                            <th scope="col" class="manage-column column-price"><?php echo esc_html__('Precio', 'wp-booking-plugin'); ?></th>
                            <th scope="col" class="manage-column column-date"><?php echo esc_html__('Fecha', 'wp-booking-plugin'); ?></th>
                            <th scope="col" class="manage-column column-capacity"><?php echo esc_html__('Capacidad', 'wp-booking-plugin'); ?></th>
                            <th scope="col" class="manage-column column-status"><?php echo esc_html__('Estado', 'wp-booking-plugin'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div><!-- /col-right -->
    </div><!-- /col-container -->
</div><!-- /wrap -->

