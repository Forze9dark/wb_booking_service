<?php
/**
 * La funcionalidad pública del plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/public
 */

/**
 * La clase de funcionalidad pública del plugin.
 *
 * Define el nombre del plugin, versión y hooks para el área pública.
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/public
 * @author     Your Name <email@example.com>
 */
class WP_Booking_Public {

    /**
     * El ID del plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    El ID del plugin.
     */
    private $plugin_name;

    /**
     * La versión del plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    La versión actual del plugin.
     */
    private $version;

    /**
     * Inicializa la clase y establece sus propiedades.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       El nombre del plugin.
     * @param      string    $version    La versión del plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Registra los archivos de estilo para el área pública.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Solo encolar si se usa el shortcode o la página específica
        global $post;
        $booking_page_id = get_option("wp_booking_page_id");
        
        if (is_a($post, "WP_Post") && (has_shortcode($post->post_content, "wp_booking_reservations") || $post->ID == $booking_page_id)) {
            // Cargar Font Awesome desde CDN
            wp_enqueue_style("font-awesome", "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css", array(), "6.4.0", "all");
            
            // Cargar estilos base del plugin
            wp_enqueue_style($this->plugin_name . "-public-style", plugin_dir_url(__FILE__) . "css/wp-booking-public.css", array(), $this->version, "all");
        }
    }

    /**
     * Registra los archivos JavaScript para el área pública.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Solo encolar si se usa el shortcode o la página específica
        global $post;
        $booking_page_id = get_option("wp_booking_page_id");
        
        if (is_a($post, "WP_Post") && (has_shortcode($post->post_content, "wp_booking_reservations") || $post->ID == $booking_page_id)) {
            // Cargar jQuery (ya incluido en WordPress)
            wp_enqueue_script("jquery");
            
            // Cargar scripts base del plugin
            $script_handle = "wp-booking-public-script"; // Usar handle explícito
            wp_enqueue_script($script_handle, plugin_dir_url(__FILE__) . "js/wp-booking-public.js", array("jquery"), $this->version, true);
            
            // Pasar variables AJAX al script - ¡IMPORTANTE: usar el mismo handle!
            wp_localize_script($script_handle, "wp_booking_ajax", array(
                "ajax_url" => admin_url("admin-ajax.php"),
                "nonce" => wp_create_nonce("wp_booking_public_actions_nonce"), // Nonce general para acciones públicas
                "plugin_url" => plugin_dir_url(__FILE__),
                "site_url" => site_url(),
                // Pasar datos de servicios, categorías, grupos y artículos
                "services" => $this->get_all_services_data(),
                "categories" => $this->get_all_categories_data(),
                "service_groups" => $this->get_all_service_groups_data(),
                "group_items" => $this->get_all_group_items_data(),
                // Textos localizados para JS
                "l10n" => array(
                    "errorLoadingConfig" => __("Error: No se pudo cargar la configuración de reservas.", "wp-booking-plugin"),
                    "errorServiceNotFound" => __("Error: Servicio no encontrado.", "wp-booking-plugin"),
                    "errorInvalidEmail" => __("Por favor, introduce un email válido.", "wp-booking-plugin"),
                    "errorRequiredField" => __("Por favor, completa este campo.", "wp-booking-plugin"),
                    "errorMinPeople" => __("El número de personas debe ser al menos 1.", "wp-booking-plugin"),
                    "errorMaxCapacity" => __("La cantidad excede la capacidad disponible.", "wp-booking-plugin"),
                    "errorSavingReservation" => __("Error al guardar la reserva.", "wp-booking-plugin"),
                    "errorConnection" => __("Error de conexión. Por favor, inténtalo de nuevo más tarde.", "wp-booking-plugin"),
                    "processing" => __("Procesando...", "wp-booking-plugin"),
                    "reservationSuccessTitle" => __("¡Reserva Confirmada!", "wp-booking-plugin"),
                    "reservationIdLabel" => __("ID de Reserva:", "wp-booking-plugin"),
                    "customerNameLabel" => __("Nombre:", "wp-booking-plugin"),
                    "customerEmailLabel" => __("Email:", "wp-booking-plugin"),
                    "customerPhoneLabel" => __("Teléfono:", "wp-booking-plugin"),
                    "numPeopleLabel" => __("Personas:", "wp-booking-plugin"),
                    "totalPriceLabel" => __("Total:", "wp-booking-plugin"),
                    "qrCodeAlt" => __("Código QR de la Reserva", "wp-booking-plugin"),
                    "noItemsAvailable" => __("No hay artículos adicionales disponibles para este servicio.", "wp-booking-plugin"),
                    "additionalItems" => __("Artículos Adicionales", "wp-booking-plugin"),
                    "dateNotAvailable" => __("Fecha no disponible", "wp-booking-plugin"),
                    "capacityUnlimited" => __("Ilimitada", "wp-booking-plugin"),
                    "capacityAvailable" => __("disponibles", "wp-booking-plugin"),
                    "qrIncluded" => __("Incluido", "wp-booking-plugin"),
                    "qrNotAvailable" => __("No disponible", "wp-booking-plugin"),
                )
            ));
        }
    }
    
    /**
     * Registra los shortcodes del plugin.
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode("wp_booking_reservations", array($this, "render_reservations_shortcode"));
    }
    
    /**
     * Renderiza el shortcode para el formulario de reservas.
     *
     * @since    1.0.0
     * @param    array     $atts    Atributos del shortcode.
     * @return   string    El contenido HTML del formulario de reservas.
     */
    public function render_reservations_shortcode($atts) {
        // Encolar scripts y estilos aquí también para asegurar que se carguen
        $this->enqueue_styles();
        $this->enqueue_scripts();
        
        // Iniciar buffer de salida
        ob_start();
        
        // Incluir la plantilla del formulario de reservas
        include_once plugin_dir_path(__FILE__) . "partials/wp-booking-public-reservations.php";
        
        // Obtener el contenido del buffer y limpiarlo
        $output = ob_get_clean();
        
        return $output;
    }
    
    /**
     * Carga la plantilla personalizada para la página de reservas si está asignada.
     *
     * @since    1.0.0
     * @param    string    $template    La ruta de la plantilla por defecto.
     * @return   string    La ruta de la plantilla a usar.
     */
    public function load_custom_template($template) {
        global $post;
        
        // Verificar si estamos en una página singular
        if (!is_singular("page")) {
            return $template;
        }
        
        // Obtener el ID de la página de reservas desde las opciones
        $booking_page_id = get_option("wp_booking_page_id");
        
        // Verificar si la página actual es la página de reservas designada
        if ($post->ID == $booking_page_id) {
            // Buscar la plantilla dentro del plugin
            $plugin_template = plugin_dir_path(__FILE__) . "templates/wp-booking-template.php";
            
            // Si la plantilla existe en el plugin, usarla
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        // Si no es la página de reservas o la plantilla no existe, devolver la original
        return $template;
    }
    
    /**
     * Obtiene todos los datos de servicios activos para pasar a JS.
     *
     * @since    1.0.0
     * @return   array
     */
    private function get_all_services_data() {
        global $wpdb;
        $services_table = $wpdb->prefix . "booking_services";
        $categories_table = $wpdb->prefix . "booking_categories";
        
        $services = $wpdb->get_results(
            "SELECT s.*, c.name as category_name 
             FROM {$services_table} s 
             LEFT JOIN {$categories_table} c ON s.category_id = c.id 
             WHERE s.status = 1", 
            ARRAY_A
        );
        
        // Añadir URL de imagen principal a cada servicio
        if ($services) {
            foreach ($services as $key => $service) {
                $image_url = '';
                if (!empty($service['main_image_id'])) {
                    $image_url = wp_get_attachment_url($service['main_image_id']);
                }
                $services[$key]['image_url'] = $image_url ? $image_url : ''; // Añadir campo image_url
            }
        }
        
        return $services ? $services : array();
    }

    /**
     * Obtiene todas las categorías activas para pasar a JS.
     *
     * @since    1.0.0
     * @return   array
     */
    private function get_all_categories_data() {
        global $wpdb;
        $categories_table = $wpdb->prefix . "booking_categories";
        
        $categories = $wpdb->get_results(
            "SELECT id, name FROM {$categories_table} WHERE status = 1 ORDER BY name ASC", 
            ARRAY_A
        );
        
        return $categories ? $categories : array();
    }

    /**
     * Obtiene las asociaciones entre servicios y grupos de artículos para pasar a JS.
     *
     * @since    1.0.0
     * @return   array Mapeo service_id => [group_data, ...]
     */
    private function get_all_service_groups_data() {
        global $wpdb;
        $service_groups_table = $wpdb->prefix . "booking_service_item_groups";
        $groups_table = $wpdb->prefix . "booking_item_groups";
        
        $results = $wpdb->get_results(
            "SELECT sg.service_id, g.id, g.name 
             FROM {$service_groups_table} sg 
             JOIN {$groups_table} g ON sg.group_id = g.id 
             WHERE g.status = 1", 
            ARRAY_A
        );
        
        $service_groups = array();
        if ($results) {
            foreach ($results as $row) {
                if (!isset($service_groups[$row["service_id"]])) {
                    $service_groups[$row["service_id"]] = array();
                }
                $service_groups[$row["service_id"]][] = array("id" => $row["id"], "name" => $row["name"]);
            }
        }
        
        return $service_groups;
    }

    /**
     * Obtiene los artículos activos agrupados por grupo para pasar a JS.
     *
     * @since    1.0.0
     * @return   array Mapeo group_id => [item_data, ...]
     */
    private function get_all_group_items_data() {
        global $wpdb;
        $items_table = $wpdb->prefix . "booking_items";
        
        $results = $wpdb->get_results(
            "SELECT id, group_id, name, price 
             FROM {$items_table} 
             WHERE status = 1", 
            ARRAY_A
        );
        
        $group_items = array();
        if ($results) {
            foreach ($results as $row) {
                if (!isset($group_items[$row["group_id"]])) {
                    $group_items[$row["group_id"]] = array();
                }
                $group_items[$row["group_id"]][] = array(
                    "id" => $row["id"],
                    "name" => $row["name"],
                    "price" => $row["price"]
                );
            }
        }
        
        return $group_items;
    }
    
    /**
     * Prueba la conexión AJAX (registrada en la clase principal).
     *
     * @since    1.0.0
     */
    public function test_ajax_connection() {
        // Verificar nonce
        if (!isset($_POST["nonce"]) || !wp_verify_nonce($_POST["nonce"], "wp_booking_public_actions_nonce")) {
            wp_send_json_error(array("message" => __("Error de seguridad (nonce inválido).", "wp-booking-plugin")));
        }
        
        wp_send_json_success(array("message" => "Conexión AJAX pública exitosa"));
    }
    
    /**
     * Procesa una reserva (registrada en la clase principal).
     *
     * @since    1.0.0
     */
    public function process_reservation() {
        // Asegurarse de que no haya salida antes de los headers
        ob_start();
        
        try {
            // Verificar nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_public_actions_nonce')) {
                wp_send_json_error(array('message' => __('Error de seguridad. Por favor, recarga la página.', 'wp-booking-plugin')));
                ob_end_clean();
                return;
            }

            global $wpdb;
            
            // Validar datos requeridos
            $required_fields = array('service_id', 'customer_name', 'customer_email', 'customer_phone', 'num_people');
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    wp_send_json_error(array('message' => __('Faltan datos requeridos. Por favor, completa todos los campos.', 'wp-booking-plugin')));
                    ob_end_clean();
                    return;
                }
            }
        
            // Obtener y sanitizar datos del formulario
            $service_id = intval($_POST["service_id"]);
            $customer_name = sanitize_text_field($_POST["customer_name"]);
            $customer_email = sanitize_email($_POST["customer_email"]);
            $customer_phone = sanitize_text_field($_POST["customer_phone"]);
            $num_people = intval($_POST["num_people"]);
        
            // Validar email
            if (!is_email($customer_email)) {
                wp_send_json_error(array("message" => __("El formato del email no es válido.", "wp-booking-plugin")));
                ob_end_clean();
                return;
            }
        
            // Validar número de personas
            if ($num_people <= 0) {
                wp_send_json_error(array("message" => __("El número de personas debe ser mayor que cero.", "wp-booking-plugin")));
                ob_end_clean();
                return;
            }
        
            // Validar servicio
            $service = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}booking_services WHERE id = %d AND status = 1",
                $service_id
            ));
        
            if (!$service) {
                wp_send_json_error(array("message" => __("El servicio seleccionado no existe o no está disponible.", "wp-booking-plugin")));
                ob_end_clean();
                return;
            }
        
            // Validar capacidad
            if ($service->max_capacity > 0) {
                $available = $service->max_capacity - $service->current_bookings;
                if ($num_people > $available) {
                    wp_send_json_error(array("message" => sprintf(__("No hay suficiente capacidad disponible. Capacidad actual: %d.", "wp-booking-plugin"), $available)));
                    ob_end_clean();
                    return;
                }
            }
        
            // Calcular precio base
            $total_price = floatval($service->price) * $num_people;
        
            // Procesar artículos adicionales
            $items = isset($_POST["items"]) && is_array($_POST["items"]) ? array_map("intval", $_POST["items"]) : array();
            $quantities = isset($_POST["quantities"]) && is_array($_POST["quantities"]) ? array_map("intval", $_POST["quantities"]) : array();
        
            $reservation_items_data = array();
        
            if (!empty($items)) {
                $item_ids_placeholders = implode(",", array_fill(0, count($items), "%d"));
                $items_details = $wpdb->get_results($wpdb->prepare(
                    "SELECT id, price FROM {$wpdb->prefix}booking_items WHERE id IN ({$item_ids_placeholders}) AND status = 1",
                    $items
                ), OBJECT_K);

                foreach ($items as $index => $item_id) {
                    if (isset($items_details[$item_id])) {
                        $item_detail = $items_details[$item_id];
                        $quantity = 1;
                        $item_price = floatval($item_detail->price) * $quantity;
                        $total_price += $item_price;
                    
                        $reservation_items_data[] = array(
                            "item_id" => $item_id,
                            "quantity" => $quantity,
                            "price" => $item_price
                        );
                    }
                }
            }
        
            // Generar código de reserva único
            $reservation_code = "RES-" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        
            // Iniciar transacción
            $wpdb->query("START TRANSACTION");
        
            // Insertar reserva en la base de datos
            $result = $wpdb->insert(
                $wpdb->prefix . "booking_reservations",
                array(
                    "service_id" => $service_id,
                    "reservation_code" => $reservation_code,
                    "customer_name" => $customer_name,
                    "customer_email" => $customer_email,
                    "customer_phone" => $customer_phone,
                    "num_people" => $num_people,
                    "total_price" => $total_price,
                    "reservation_date" => current_time("mysql"), // Usar fecha actual
                    "status" => "pending" // Estado inicial pendiente
                ),
                array(
                    "%d", "%s", "%s", "%s", "%s", "%d", "%f", "%s", "%s"
                )
            );
            
            if ($result === false) {
                throw new Exception(__("Error al guardar la reserva principal.", "wp-booking-plugin") . " " . $wpdb->last_error);
            }
            
            $reservation_id = $wpdb->insert_id;
            
            // Insertar artículos de la reserva
            if (!empty($reservation_items_data)) {
                foreach ($reservation_items_data as $item_data) {
                    $item_insert_result = $wpdb->insert(
                        $wpdb->prefix . "booking_reservation_items",
                        array(
                            "reservation_id" => $reservation_id,
                            "item_id" => $item_data["item_id"],
                            "quantity" => $item_data["quantity"],
                            "price" => $item_data["price"]
                        ),
                        array("%d", "%d", "%d", "%f")
                    );
                    if ($item_insert_result === false) {
                        throw new Exception(__("Error al guardar los artículos de la reserva.", "wp-booking-plugin") . " " . $wpdb->last_error);
                    }
                }
            }
            
            // Actualizar contador de reservas en el servicio
            $update_bookings_result = $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}booking_services SET current_bookings = current_bookings + %d WHERE id = %d",
                $num_people,
                $service_id
            ));
            
            if ($update_bookings_result === false) {
                throw new Exception(__("Error al actualizar la capacidad del servicio.", "wp-booking-plugin") . " " . $wpdb->last_error);
            }
            
            // Confirmar transacción
            $wpdb->query("COMMIT");
            
            // Generar códigos QR para cada persona
            $qr_codes_html = '';
            for ($i = 1; $i <= $num_people; $i++) {
                $qr_data = array(
                    'reservation_code' => $reservation_code,
                    'customer_name' => $customer_name,
                    'customer_email' => $customer_email,
                    'service' => $service->title,
                    'person_number' => $i,
                    'total_people' => $num_people
                );
                
                $qr_data_encoded = urlencode(json_encode($qr_data));
                $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . $qr_data_encoded;
                
                $qr_codes_html .= sprintf(
                    '<div style="margin: 30px 0; text-align: center; background-color: #f8f9fa; padding: 20px; border-radius: 10px;">
                        <h3 style="color: #2c3e50; margin-bottom: 15px; font-size: 18px;">Código QR para persona %d de %d</h3>
                        <img src="%s" alt="Código QR" style="max-width: 200px; height: auto; display: block; margin: 0 auto; border: 8px solid white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <p style="margin-top: 15px; font-style: italic; color: #666; font-size: 14px; background-color: #fff3cd; padding: 10px; border-radius: 5px; display: inline-block;">Este código es personal e intransferible</p>
                    </div>',
                    $i,
                    $num_people,
                    $qr_url
                );
            }

            // Enviar correo de confirmación
            $subject = sprintf(__('Reserva Confirmada - %s', 'wp-booking-plugin'), $service->title);
            
            // Plantilla de correo de confirmación
            $confirmation_message = sprintf('
                <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; color: #2c3e50;">
                    <div style="background-color: #3498db; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                        <h1 style="margin: 0; font-size: 28px;">¡Reserva Confirmada!</h1>
                        <p style="margin: 10px 0 0 0; font-size: 16px;">Gracias por confiar en nosotros</p>
                    </div>
                    
                    <div style="background-color: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <p style="font-size: 16px; line-height: 1.6;">Hola <strong>%s</strong>,</p>
                        
                        <p style="font-size: 16px; line-height: 1.6; color: #27ae60;">Tu reserva para <strong>%s</strong> ha sido confirmada exitosamente.</p>
                        
                        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
                            <h2 style="color: #2c3e50; font-size: 20px; margin-top: 0;">Detalles de la Reserva</h2>
                            <table style="width: 100%%; border-collapse: collapse; margin-top: 15px;">
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6; color: #666;">Código de reserva:</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><strong style="color: #3498db;">%s</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6; color: #666;">Servicio:</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><strong>%s</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6; color: #666;">Personas:</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><strong>%d</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6; color: #666;">Total:</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><strong style="color: #27ae60;">%.2f €</strong></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div style="background-color: #fff3cd; padding: 15px; border-radius: 10px; margin: 20px 0;">
                            <p style="color: #856404; margin: 0; font-size: 14px;">
                                <strong>Importante:</strong> Guarda este correo, ya que contiene los códigos QR necesarios para acceder al servicio.
                            </p>
                        </div>
                        
                        <div style="margin: 30px 0;">
                            <h2 style="color: #2c3e50; font-size: 20px;">Códigos QR</h2>
                            <p style="color: #666; margin-bottom: 20px;">Utiliza estos códigos para acceder al servicio:</p>
                            %s
                        </div>
                        
                        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                            <p style="color: #666; font-size: 14px;">Si tienes alguna pregunta, no dudes en contactarnos</p>
                            <p style="color: #666; font-size: 14px; margin-top: 10px;">
                                <strong>%s</strong><br>
                                <a href="mailto:info@example.com" style="color: #3498db; text-decoration: none;">info@example.com</a>
                            </p>
                        </div>
                    </div>
                </div>',
                $customer_name,
                $service->title,
                $reservation_code,
                $service->title,
                $num_people,
                $total_price,
                $qr_codes_html,
                get_bloginfo('name')
            );

            // Plantilla de correo de cancelación
            $cancellation_message = sprintf('
                <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; color: #2c3e50;">
                    <div style="background-color: #e74c3c; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                        <h1 style="margin: 0; font-size: 28px;">Reserva Cancelada</h1>
                        <p style="margin: 10px 0 0 0; font-size: 16px;">Lamentamos que hayas tenido que cancelar</p>
                    </div>
                    
                    <div style="background-color: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <p style="font-size: 16px; line-height: 1.6;">Hola <strong>%s</strong>,</p>
                        
                        <p style="font-size: 16px; line-height: 1.6; color: #e74c3c;">Tu reserva ha sido cancelada según lo solicitado.</p>
                        
                        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
                            <h2 style="color: #2c3e50; font-size: 20px; margin-top: 0;">Detalles de la Reserva Cancelada</h2>
                            <table style="width: 100%%; border-collapse: collapse; margin-top: 15px;">
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6; color: #666;">Código de reserva:</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><strong style="color: #e74c3c;">%s</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6; color: #666;">Servicio:</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><strong>%s</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6; color: #666;">Fecha de cancelación:</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><strong>%s</strong></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div style="background-color: #f8d7da; padding: 15px; border-radius: 10px; margin: 20px 0;">
                            <p style="color: #721c24; margin: 0; font-size: 14px;">
                                <strong>Nota:</strong> Los códigos QR asociados a esta reserva han sido desactivados.
                            </p>
                        </div>
                        
                        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                            <p style="color: #666; font-size: 14px;">Esperamos verte pronto de nuevo</p>
                            <p style="color: #666; font-size: 14px; margin-top: 10px;">
                                <strong>%s</strong><br>
                                <a href="mailto:info@example.com" style="color: #3498db; text-decoration: none;">info@example.com</a>
                            </p>
                        </div>
                    </div>
                </div>',
                $customer_name,
                $reservation_code,
                $service->title,
                date_i18n(get_option('date_format') . ' ' . get_option('time_format')),
                get_bloginfo('name')
            );

            $headers = array('Content-Type: text/html; charset=UTF-8');
            
            if (wp_mail($customer_email, $subject, $confirmation_message, $headers)) {
                if (WP_DEBUG === true) {
                    error_log('WP Booking: Correo de confirmación enviado correctamente a ' . $customer_email);
                }
            } else {
                if (WP_DEBUG === true) {
                    error_log('WP Booking: Error al enviar correo de confirmación a ' . $customer_email);
                }
            }
            
            // Enviar respuesta exitosa
            wp_send_json_success(array(
                "message" => __("¡Reserva realizada con éxito!", "wp-booking-plugin"),
                "reservation_id" => $reservation_id,
                "reservation_code" => $reservation_code,
                "details" => array(
                    "service_name" => $service->title,
                    "customer_name" => $customer_name,
                    "customer_email" => $customer_email,
                    "customer_phone" => $customer_phone,
                    "num_people" => $num_people,
                    "total_price" => number_format($total_price, 2)
                )
            ));
            ob_end_clean();
            
        } catch (Exception $e) {
            $wpdb->query("ROLLBACK");
            wp_send_json_error(array("message" => __("Error al procesar la reserva: ", "wp-booking-plugin") . $e->getMessage()));
            ob_end_clean();
        }
    }
    
    /**
     * Obtiene detalles de un servicio específico para AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_get_service_details() {
        global $wpdb;
        
        // Verificar nonce
        if (!isset($_POST["nonce"]) || !wp_verify_nonce($_POST["nonce"], "wp_booking_public_actions_nonce")) {
            wp_send_json_error(array("message" => __("Error de seguridad.", "wp-booking-plugin")));
        }
        
        $service_id = isset($_POST["service_id"]) ? intval($_POST["service_id"]) : 0;
        
        if ($service_id <= 0) {
            wp_send_json_error(array("message" => __("ID de servicio inválido.", "wp-booking-plugin")));
        }
        
        // Obtener datos del servicio
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}booking_services WHERE id = %d AND status = 1",
            $service_id
        ), ARRAY_A);
        
        if (!$service) {
            wp_send_json_error(array("message" => __("Servicio no encontrado o no disponible.", "wp-booking-plugin")));
        }
        
        // Obtener grupos de artículos asociados
        $service_groups = $this->get_service_groups_data_for_service($service_id);
        
        // Obtener artículos de esos grupos
        $group_items = array();
        if (!empty($service_groups)) {
            $group_ids = wp_list_pluck($service_groups, "id");
            $group_items = $this->get_group_items_data_for_groups($group_ids);
        }
        
        // Obtener URL de imagen principal
        $main_image_url = "";
        if (!empty($service["main_image_id"])) {
            $main_image_url = wp_get_attachment_url($service["main_image_id"]);
        }
        
        // Obtener URLs de galería de imágenes
        $gallery_image_urls = array();
        if (!empty($service["gallery_image_ids"])) {
            $gallery_ids = explode(",", $service["gallery_image_ids"]);
            foreach ($gallery_ids as $image_id) {
                $url = wp_get_attachment_url(intval($image_id));
                if ($url) {
                    $gallery_image_urls[] = $url;
                }
            }
        }
        
        // Preparar datos para la respuesta
        $response_data = array(
            "service" => $service,
            "main_image_url" => $main_image_url,
            "gallery_image_urls" => $gallery_image_urls,
            "groups" => $service_groups,
            "items" => $group_items
        );
        
        wp_send_json_success($response_data);
    }
    
    /**
     * Obtiene los grupos de artículos asociados a un servicio específico.
     *
     * @since    1.0.0
     * @param    int $service_id ID del servicio.
     * @return   array
     */
    private function get_service_groups_data_for_service($service_id) {
        global $wpdb;
        $service_groups_table = $wpdb->prefix . "booking_service_item_groups";
        $groups_table = $wpdb->prefix . "booking_item_groups";
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT g.id, g.name 
             FROM {$service_groups_table} sg 
             JOIN {$groups_table} g ON sg.group_id = g.id 
             WHERE sg.service_id = %d AND g.status = 1", 
            $service_id
        ), ARRAY_A);
        
        return $results ? $results : array();
    }

    /**
     * Obtiene los artículos activos para una lista de IDs de grupo.
     *
     * @since    1.0.0
     * @param    array $group_ids IDs de los grupos.
     * @return   array Mapeo group_id => [item_data, ...]
     */
    private function get_group_items_data_for_groups($group_ids) {
        global $wpdb;
        $items_table = $wpdb->prefix . "booking_items";
        
        if (empty($group_ids)) {
            return array();
        }
        
        $ids_placeholders = implode(",", array_fill(0, count($group_ids), "%d"));
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT id, group_id, name, price 
             FROM {$items_table} 
             WHERE group_id IN ({$ids_placeholders}) AND status = 1", 
            $group_ids
        ), ARRAY_A);
        
        $group_items = array();
        if ($results) {
            foreach ($results as $row) {
                if (!isset($group_items[$row["group_id"]])) {
                    $group_items[$row["group_id"]] = array();
                }
                $group_items[$row["group_id"]][] = array(
                    "id" => $row["id"],
                    "name" => $row["name"],
                    "price" => $row["price"]
                );
            }
        }
        
        return $group_items;
    }
}