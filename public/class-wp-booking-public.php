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
        global $wpdb;

        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_public_actions_nonce')) {
            wp_send_json_error(['message' => 'Error de seguridad. Por favor, recarga la página.']);
            return;
        }

        // Validar datos requeridos
        $required_fields = ['service_id', 'customer_name', 'customer_email', 'customer_phone', 'num_people'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                wp_send_json_error(['message' => 'Por favor, completa todos los campos requeridos.']);
                return;
            }
        }

        // Obtener servicio
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}booking_services WHERE id = %d AND status = 1",
            intval($_POST['service_id'])
        ));

        if (!$service) {
            wp_send_json_error(['message' => 'El servicio seleccionado no está disponible.']);
            return;
        }

        // Generar código de reserva
        $reservation_code = 'RES-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        // Insertar reserva
        $result = $wpdb->insert(
            $wpdb->prefix . 'booking_reservations',
            [
                'service_id' => $service->id,
                'reservation_code' => $reservation_code,
                'customer_name' => sanitize_text_field($_POST['customer_name']),
                'customer_email' => sanitize_email($_POST['customer_email']),
                'customer_phone' => sanitize_text_field($_POST['customer_phone']),
                'num_people' => intval($_POST['num_people']),
                'total_price' => floatval($service->price) * intval($_POST['num_people']),
                'reservation_date' => current_time('mysql'),
                'status' => 'pending'
            ],
            ['%d', '%s', '%s', '%s', '%s', '%d', '%f', '%s', '%s']
        );

        if ($result === false) {
            wp_send_json_error(['message' => 'Error al guardar la reserva.']);
            return;
        }

        // Enviar email al cliente
        $to = sanitize_email($_POST['customer_email']);
        $subject = 'Confirmación de Reserva - ' . $reservation_code;
        $message = "Hola " . sanitize_text_field($_POST['customer_name']) . ",\n\n";
        $message .= "Tu reserva ha sido registrada con éxito.\n\n";
        $message .= "Detalles de la reserva:\n";
        $message .= "Código: " . $reservation_code . "\n";
        $message .= "Servicio: " . $service->title . "\n";
        $message .= "Personas: " . intval($_POST['num_people']) . "\n";
        $message .= "Total: €" . number_format(floatval($service->price) * intval($_POST['num_people']), 2) . "\n\n";
        $message .= "Estado: Pendiente de confirmación\n\n";
        $message .= "Gracias por tu reserva.";

        $headers = array('Content-Type: text/plain; charset=UTF-8');
        wp_mail($to, $subject, $message, $headers);

        // Enviar respuesta exitosa
        wp_send_json_success([
            'message' => '¡Reserva realizada con éxito!',
            'reservation_code' => $reservation_code,
            'details' => [
                'service_name' => $service->title,
                'customer_name' => $_POST['customer_name'],
                'customer_email' => $_POST['customer_email'],
                'customer_phone' => $_POST['customer_phone'],
                'num_people' => $_POST['num_people'],
                'total_price' => number_format(floatval($service->price) * intval($_POST['num_people']), 2),
                'status' => 'pending'
            ]
        ]);
    }
            }
        }
    }

    /**
     * Obtiene y valida los datos del servicio
     * @return object
     * @throws Exception
     */
    private function get_and_validate_service() {
        global $wpdb;
        
        $service_id = intval($_POST['service_id']);
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}booking_services WHERE id = %d AND status = 1",
            $service_id
        ));

        if (!$service) {
            throw new Exception(__('El servicio seleccionado no existe o no está disponible.', 'wp-booking-plugin'));
        }

        // Validar capacidad
        $num_people = intval($_POST['num_people']);
        if ($service->max_capacity > 0) {
            $available = $service->max_capacity - $service->current_bookings;
            if ($num_people > $available) {
                throw new Exception(sprintf(__('No hay suficiente capacidad disponible. Capacidad actual: %d.', 'wp-booking-plugin'), $available));
            }
        }

        return $service;
    }

    /**
     * Crea una nueva reserva en la base de datos
     * @param object $service
     * @return array
     * @throws Exception
     */
    private function create_reservation($service) {
        global $wpdb;
        
        // Generar código único
        $reservation_code = $this->generate_reservation_code();
        
        // Calcular precio total
        $total_price = $this->calculate_total_price($service);
        
        // Iniciar transacción
        $wpdb->query('START TRANSACTION');
        
        try {
            // Insertar reserva
            $result = $wpdb->insert(
                $wpdb->prefix . 'booking_reservations',
                [
                    'service_id' => $service->id,
                    'reservation_code' => $reservation_code,
                    'customer_name' => sanitize_text_field($_POST['customer_name']),
                    'customer_email' => sanitize_email($_POST['customer_email']),
                    'customer_phone' => sanitize_text_field($_POST['customer_phone']),
                    'num_people' => intval($_POST['num_people']),
                    'total_price' => $total_price,
                    'reservation_date' => current_time('mysql'),
                    'status' => 'pending'
                ],
                ['%d', '%s', '%s', '%s', '%s', '%d', '%f', '%s', '%s']
            );

            if ($result === false) {
                throw new Exception(__('Error al guardar la reserva.', 'wp-booking-plugin'));
            }

            $reservation_id = $wpdb->insert_id;

            // Procesar artículos adicionales
            $this->process_reservation_items($reservation_id);

            // Actualizar capacidad del servicio
            $this->update_service_capacity($service->id, intval($_POST['num_people']));

            // Confirmar transacción
            $wpdb->query('COMMIT');

            return [
                'id' => $reservation_id,
                'reservation_code' => $reservation_code,
                'customer_name' => $_POST['customer_name'],
                'customer_email' => $_POST['customer_email'],
                'customer_phone' => $_POST['customer_phone'],
                'num_people' => intval($_POST['num_people']),
                'total_price' => $total_price
            ];

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            throw $e;
        }
    }

    /**
     * Genera un código de reserva único
     * @return string
     */
    private function generate_reservation_code() {
        return 'RES-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    }

    /**
     * Calcula el precio total incluyendo artículos adicionales
     * @param object $service
     * @return float
     */
    private function calculate_total_price($service) {
        $num_people = intval($_POST['num_people']);
        $total_price = floatval($service->price) * $num_people;

        // Añadir precios de artículos adicionales
        if (!empty($_POST['items']) && is_array($_POST['items'])) {
            global $wpdb;
            $items = array_map('intval', $_POST['items']);
            $items_str = implode(',', $items);
            
            $items_prices = $wpdb->get_results(
                "SELECT id, price FROM {$wpdb->prefix}booking_items 
                 WHERE id IN ($items_str) AND status = 1"
            );

            foreach ($items_prices as $item) {
                $total_price += floatval($item->price);
            }
        }

        return $total_price;
    }

    /**
     * Procesa los artículos adicionales de la reserva
     * @param int $reservation_id
     * @throws Exception
     */
    private function process_reservation_items($reservation_id) {
        if (empty($_POST['items']) || !is_array($_POST['items'])) {
            return;
        }

        global $wpdb;
        $items = array_map('intval', $_POST['items']);
        
        foreach ($items as $item_id) {
            $result = $wpdb->insert(
                $wpdb->prefix . 'booking_reservation_items',
                [
                    'reservation_id' => $reservation_id,
                    'item_id' => $item_id,
                    'quantity' => 1,
                    'price' => 0
                ],
                ['%d', '%d', '%d', '%f']
            );

            if ($result === false) {
                throw new Exception(__('Error al procesar los artículos adicionales.', 'wp-booking-plugin'));
            }
        }
    }

    /**
     * Actualiza la capacidad disponible del servicio
     * @param int $service_id
     * @param int $num_people
     * @throws Exception
     */
    private function update_service_capacity($service_id, $num_people) {
        global $wpdb;
        
        $result = $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}booking_services 
             SET current_bookings = current_bookings + %d 
             WHERE id = %d",
            $num_people,
            $service_id
        ));

        if ($result === false) {
            throw new Exception(__('Error al actualizar la capacidad del servicio.', 'wp-booking-plugin'));
        }
    }

    /**
     * Genera los códigos QR para la reserva
     * @param array $reservation_data
     * @return array
     */
    private function generate_qr_codes($reservation_data) {
        $qr_codes = [];
        
        // Generar un código QR por persona
        for ($i = 1; $i <= $reservation_data['num_people']; $i++) {
            $qr_data = sprintf(
                'Reserva:%s-Persona:%d',
                $reservation_data['reservation_code'],
                $i
            );
            
            $qr_codes[] = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qr_data);
        }
        
        return $qr_codes;
    }

    /**
     * Envía el email de confirmación al cliente
     * @param string $customer_email
     * @param string $customer_name
     * @param object $service
     * @param string $reservation_code
     * @param array $qr_codes
     */
    private function send_confirmation_email($customer_email, $customer_name, $service, $reservation_code, $qr_codes) {
        $subject = sprintf(__('Confirmación de reserva #%s', 'wp-booking-plugin'), $reservation_code);
        
        // Construir el cuerpo del email
        $message = sprintf(
            __('Hola %s,\n\nGracias por tu reserva. Aquí están los detalles:\n\nServicio: %s\nCódigo de reserva: %s\n\n', 'wp-booking-plugin'),
            $customer_name,
            $service->title,
            $reservation_code
        );

        // Añadir códigos QR si existen
        if (!empty($qr_codes)) {
            $message .= __("\nTus códigos QR:\n", 'wp-booking-plugin');
            
            // Adjuntar los códigos QR como imágenes
            add_action('phpmailer_init', function($phpmailer) use ($qr_codes) {
                foreach ($qr_codes as $index => $qr_url) {
                    $qr_image = file_get_contents($qr_url);
                    $phpmailer->addStringAttachment(
                        $qr_image,
                        "qr-code-{$index}.png",
                        'base64',
                        'image/png'
                    );
                }
            });
        }
        
        // Enviar el email
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($customer_email, $subject, $message, $headers);
    }
}