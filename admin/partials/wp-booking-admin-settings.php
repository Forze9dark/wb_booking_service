<?php
/**
 * Proporciona la vista de "Configuración" para el área de administración.
 *
 * Esta vista permite configurar los ajustes generales del plugin.
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

// Obtener opciones de configuración
$options = get_option('wp_booking_options', array(
    'reservation_page_id' => 0,
    'email_notifications' => 1,
    'admin_email' => get_option('admin_email'),
    'email_subject' => __('Nueva reserva recibida', 'wp-booking-plugin'),
    'email_template' => __('Hola {customer_name},

Gracias por tu reserva. A continuación encontrarás los detalles:

Servicio: {service_title}
Fecha: {reservation_date}
Número de personas: {num_people}
Precio total: {total_price}

{qr_codes}

Si tienes alguna pregunta, no dudes en contactarnos.

Saludos,
El equipo de {site_name}', 'wp-booking-plugin'),
    'enable_qr' => 1,
    'currency_symbol' => '€',
    'date_format' => 'd/m/Y',
    'time_format' => 'H:i'
));

// Obtener páginas para el selector
$pages = get_pages();
?>

<div class="wrap">
    <h1><?php _e('Configuración', 'wp-booking-plugin'); ?></h1>
    
    <div class="notice notice-info is-dismissible">
        <p><?php _e('Configura los ajustes generales del plugin de reservas.', 'wp-booking-plugin'); ?></p>
    </div>
    
    <form method="post" action="options.php" id="wp-booking-settings-form">
        <?php settings_fields('wp_booking_options_group'); ?>
        
        <div class="wp-booking-tabs">
            <div class="wp-booking-tabs-nav">
                <a href="#general" class="active"><?php _e('General', 'wp-booking-plugin'); ?></a>
                <a href="#emails"><?php _e('Notificaciones por Email', 'wp-booking-plugin'); ?></a>
                <a href="#appearance"><?php _e('Apariencia', 'wp-booking-plugin'); ?></a>
            </div>
            
            <!-- Pestaña General -->
            <div id="general" class="wp-booking-tab-content active">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="reservation_page_id"><?php _e('Página de reservas', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <select id="reservation_page_id" name="wp_booking_options[reservation_page_id]">
                                <option value="0"><?php _e('Seleccionar página', 'wp-booking-plugin'); ?></option>
                                <?php foreach ($pages as $page) : ?>
                                    <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($options['reservation_page_id'], $page->ID); ?>><?php echo esc_html($page->post_title); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Selecciona la página donde se mostrará el formulario de reservas.', 'wp-booking-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="enable_qr"><?php _e('Códigos QR', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="enable_qr" name="wp_booking_options[enable_qr]" value="1" <?php checked($options['enable_qr'], 1); ?>>
                                <?php _e('Habilitar generación de códigos QR para las reservas', 'wp-booking-plugin'); ?>
                            </label>
                            <p class="description"><?php _e('Si está habilitado, se generarán códigos QR para las reservas que lo tengan configurado.', 'wp-booking-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="currency_symbol"><?php _e('Símbolo de moneda', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <input type="text" id="currency_symbol" name="wp_booking_options[currency_symbol]" value="<?php echo esc_attr($options['currency_symbol']); ?>" class="small-text">
                            <p class="description"><?php _e('Símbolo de moneda para mostrar en los precios.', 'wp-booking-plugin'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Pestaña Notificaciones por Email -->
            <div id="emails" class="wp-booking-tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="email_notifications"><?php _e('Notificaciones por email', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="email_notifications" name="wp_booking_options[email_notifications]" value="1" <?php checked($options['email_notifications'], 1); ?>>
                                <?php _e('Habilitar notificaciones por email', 'wp-booking-plugin'); ?>
                            </label>
                            <p class="description"><?php _e('Si está habilitado, se enviarán notificaciones por email al cliente y al administrador cuando se realice una reserva.', 'wp-booking-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="admin_email"><?php _e('Email del administrador', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <input type="email" id="admin_email" name="wp_booking_options[admin_email]" value="<?php echo esc_attr($options['admin_email']); ?>" class="regular-text">
                            <p class="description"><?php _e('Email donde se enviarán las notificaciones de nuevas reservas.', 'wp-booking-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="email_subject"><?php _e('Asunto del email', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <input type="text" id="email_subject" name="wp_booking_options[email_subject]" value="<?php echo esc_attr($options['email_subject']); ?>" class="regular-text">
                            <p class="description"><?php _e('Asunto del email de confirmación de reserva.', 'wp-booking-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="email_template"><?php _e('Plantilla de email', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <textarea id="email_template" name="wp_booking_options[email_template]" class="large-text" rows="10"><?php echo esc_textarea($options['email_template']); ?></textarea>
                            <p class="description">
                                <?php _e('Plantilla para el email de confirmación. Puedes usar las siguientes variables:', 'wp-booking-plugin'); ?><br>
                                <code>{customer_name}</code> - <?php _e('Nombre del cliente', 'wp-booking-plugin'); ?><br>
                                <code>{service_title}</code> - <?php _e('Título del servicio', 'wp-booking-plugin'); ?><br>
                                <code>{reservation_date}</code> - <?php _e('Fecha de la reserva', 'wp-booking-plugin'); ?><br>
                                <code>{num_people}</code> - <?php _e('Número de personas', 'wp-booking-plugin'); ?><br>
                                <code>{total_price}</code> - <?php _e('Precio total', 'wp-booking-plugin'); ?><br>
                                <code>{qr_codes}</code> - <?php _e('Códigos QR (si están habilitados)', 'wp-booking-plugin'); ?><br>
                                <code>{site_name}</code> - <?php _e('Nombre del sitio', 'wp-booking-plugin'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Pestaña Apariencia -->
            <div id="appearance" class="wp-booking-tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="date_format"><?php _e('Formato de fecha', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <select id="date_format" name="wp_booking_options[date_format]">
                                <option value="d/m/Y" <?php selected($options['date_format'], 'd/m/Y'); ?>><?php echo date('d/m/Y'); ?> (d/m/Y)</option>
                                <option value="m/d/Y" <?php selected($options['date_format'], 'm/d/Y'); ?>><?php echo date('m/d/Y'); ?> (m/d/Y)</option>
                                <option value="Y-m-d" <?php selected($options['date_format'], 'Y-m-d'); ?>><?php echo date('Y-m-d'); ?> (Y-m-d)</option>
                                <option value="F j, Y" <?php selected($options['date_format'], 'F j, Y'); ?>><?php echo date('F j, Y'); ?> (F j, Y)</option>
                            </select>
                            <p class="description"><?php _e('Formato de fecha para mostrar en el frontend.', 'wp-booking-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="time_format"><?php _e('Formato de hora', 'wp-booking-plugin'); ?></label></th>
                        <td>
                            <select id="time_format" name="wp_booking_options[time_format]">
                                <option value="H:i" <?php selected($options['time_format'], 'H:i'); ?>><?php echo date('H:i'); ?> (H:i)</option>
                                <option value="h:i A" <?php selected($options['time_format'], 'h:i A'); ?>><?php echo date('h:i A'); ?> (h:i A)</option>
                            </select>
                            <p class="description"><?php _e('Formato de hora para mostrar en el frontend.', 'wp-booking-plugin'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" class="button button-primary" value="<?php _e('Guardar cambios', 'wp-booking-plugin'); ?>">
        </p>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Pestañas
    $('.wp-booking-tabs-nav a').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        
        // Activar pestaña
        $('.wp-booking-tabs-nav a').removeClass('active');
        $(this).addClass('active');
        
        // Mostrar contenido
        $('.wp-booking-tab-content').removeClass('active');
        $(target).addClass('active');
    });
});
</script>

<style>
.wp-booking-tabs-nav {
    display: flex;
    border-bottom: 1px solid #ccc;
    margin-bottom: 20px;
}

.wp-booking-tabs-nav a {
    padding: 10px 15px;
    text-decoration: none;
    color: #23282d;
    font-weight: 600;
}

.wp-booking-tabs-nav a.active {
    border-bottom: 2px solid #0073aa;
    color: #0073aa;
}

.wp-booking-tab-content {
    display: none;
}

.wp-booking-tab-content.active {
    display: block;
}
</style>
