<?php
/**
 * La funcionalidad específica de administración del plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/admin
 */

/**
 * La clase de funcionalidad específica de administración del plugin.
 *
 * Define el nombre del plugin, versión y hooks para el área de administración.
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/admin
 * @author     Your Name <email@example.com>
 */
class WP_Booking_Admin {

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
     * Registra los estilos para el área de administración.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name . "-admin-style", plugin_dir_url(__FILE__) . 'css/wp-booking-admin.css', array(), $this->version, 'all');
    }

    /**
     * Registra los scripts para el área de administración.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Dependencias
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_media();
        wp_enqueue_script('wp-util'); // Necesario para plantillas JS
        
        // Script principal de administración
        $script_handle = 'wp-booking-admin-script'; // Usar un handle explícito
        wp_enqueue_script($script_handle, plugin_dir_url(__FILE__) . 'js/wp-booking-admin.js', array('jquery', 'jquery-ui-datepicker', 'wp-util'), $this->version, true);
        
        // Pasar variables a JavaScript - ¡IMPORTANTE: usar el mismo handle que wp_enqueue_script!
        wp_localize_script($script_handle, 'wp_booking_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_booking_admin_nonce'), // Nonce general para admin
            'get_service_nonce' => wp_create_nonce('wp_booking_get_service_action'),
            'save_service_nonce' => wp_create_nonce('wp_booking_save_service_action'), // Este debe coincidir con el del formulario
            'delete_service_nonce' => wp_create_nonce('wp_booking_delete_service_action'),
            // Añadir textos localizados para JS
            'l10n' => array(
                'addNewService' => __('Añadir nuevo servicio', 'wp-booking-plugin'),
                'editService' => __('Editar Servicio', 'wp-booking-plugin'),
                'confirmDelete' => __('¿Estás seguro de que quieres eliminar este servicio?', 'wp-booking-plugin'),
                'selectImageTitle' => __('Seleccionar imagen principal', 'wp-booking-plugin'),
                'useThisImage' => __('Usar esta imagen', 'wp-booking-plugin'),
                'errorTitleRequired' => __('El título del servicio es obligatorio.', 'wp-booking-plugin'),
                'errorCategoryRequired' => __('Debes seleccionar una categoría.', 'wp-booking-plugin'),
                'errorPriceInvalid' => __('El precio debe ser un número válido mayor o igual a cero.', 'wp-booking-plugin'),
                'errorSaving' => __('Error al guardar el servicio.', 'wp-booking-plugin'),
                'errorLoading' => __('Error al cargar los datos del servicio.', 'wp-booking-plugin'),
                'errorDeleting' => __('Error al eliminar el servicio.', 'wp-booking-plugin'),
                'errorConnection' => __('Error de conexión. Por favor, inténtalo de nuevo.', 'wp-booking-plugin'),
            )
        ));
    }

    /**
     * Registra el menú de administración del plugin.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        // Menú principal
        add_menu_page(
            __('WP Booking', 'wp-booking-plugin'),
            __('WP Booking', 'wp-booking-plugin'),
            'manage_options',
            'wp-booking',
            array($this, 'display_plugin_admin_dashboard'),
            'dashicons-calendar-alt',
            26
        );
        
        // Submenús
        add_submenu_page(
            'wp-booking',
            __('Dashboard', 'wp-booking-plugin'),
            __('Dashboard', 'wp-booking-plugin'),
            'manage_options',
            'wp-booking',
            array($this, 'display_plugin_admin_dashboard')
        );
        
        add_submenu_page(
            'wp-booking',
            __('Categorías', 'wp-booking-plugin'),
            __('Categorías', 'wp-booking-plugin'),
            'manage_options',
            'wp-booking-categories',
            array($this, 'display_plugin_admin_categories')
        );
        
        add_submenu_page(
            'wp-booking',
            __('Servicios', 'wp-booking-plugin'),
            __('Servicios', 'wp-booking-plugin'),
            'manage_options',
            'wp-booking-services',
            array($this, 'display_plugin_admin_services')
        );
        
        add_submenu_page(
            'wp-booking',
            __('Grupos de Artículos', 'wp-booking-plugin'),
            __('Grupos de Artículos', 'wp-booking-plugin'),
            'manage_options',
            'wp-booking-item-groups',
            array($this, 'display_plugin_admin_item_groups')
        );
        
        add_submenu_page(
            'wp-booking',
            __('Artículos', 'wp-booking-plugin'),
            __('Artículos', 'wp-booking-plugin'),
            'manage_options',
            'wp-booking-items',
            array($this, 'display_plugin_admin_items')
        );
        
        add_submenu_page(
            'wp-booking',
            __('Descuentos', 'wp-booking-plugin'),
            __('Descuentos', 'wp-booking-plugin'),
            'manage_options',
            'wp-booking-discounts',
            array($this, 'display_plugin_admin_discounts')
        );
        
        add_submenu_page(
            'wp-booking',
            __('Reservas', 'wp-booking-plugin'),
            __('Reservas', 'wp-booking-plugin'),
            'manage_options',
            'wp-booking-reservations',
            array($this, 'display_plugin_admin_reservations')
        );
        
        add_submenu_page(
            'wp-booking',
            __('Configuración', 'wp-booking-plugin'),
            __('Configuración', 'wp-booking-plugin'),
            'manage_options',
            'wp-booking-settings',
            array($this, 'display_plugin_admin_settings')
        );
    }

    /**
     * Registra la configuración del plugin.
     * 
     * @since    1.0.0
     */
    public function register_settings() {
        // Registrar configuraciones
        register_setting(
            'wp_booking_options_group', // Corregido: Usar el mismo nombre que en settings_fields()
            'wp_booking_general_settings',
            array(
                'sanitize_callback' => array($this, 'sanitize_general_settings')
            )
        );
        
        // Añadir secciones de configuración
        add_settings_section(
            'wp_booking_general_section',
            __('Configuración General', 'wp-booking-plugin'),
            array($this, 'render_general_section'),
            'wp-booking-settings' // Nombre de la página
        );
        
        // Añadir campos de configuración
        add_settings_field(
            'wp_booking_page_id',
            __('Página de Reservas', 'wp-booking-plugin'),
            array($this, 'render_page_id_field'),
            'wp-booking-settings', // Nombre de la página
            'wp_booking_general_section' // ID de la sección
        );
        
        add_settings_field(
            'wp_booking_currency',
            __('Símbolo de moneda', 'wp-booking-plugin'), // Corrected label based on screenshot
            array($this, 'render_currency_field'),
            'wp-booking-settings',
            'wp_booking_general_section'
        );

        // Campo para Formato de Fecha
        add_settings_field(
            'wp_booking_date_format',
            __('Formato de Fecha', 'wp-booking-plugin'),
            array($this, 'render_date_format_field'),
            'wp-booking-settings',
            'wp_booking_general_section'
        );
    }
    
    /**
     * Sanitiza las configuraciones generales.
     *
     * @since    1.0.0
     * @param    array    $input    Valores de entrada.
     * @return   array              Valores sanitizados.
     */
    public function sanitize_general_settings($input) {
        $sanitized = array();
        
        if (isset($input['page_id'])) {
            $sanitized['page_id'] = absint($input['page_id']);
        }
        
        if (isset($input['currency'])) {
            $sanitized['currency'] = sanitize_text_field($input['currency']);
        }
        
        if (isset($input['date_format'])) {
            $sanitized['date_format'] = sanitize_text_field($input['date_format']);
        }
        
        return $sanitized;
    }
    
    /**
     * Renderiza la sección general.
     *
     * @since    1.0.0
     */
    public function render_general_section() {
        echo '<p>' . __('Configura los ajustes generales del plugin.', 'wp-booking-plugin') . '</p>';
    }
    
    /**
     * Renderiza el campo de página de reservas.
     *
     * @since    1.0.0
     */
    public function render_page_id_field() {
        $options = get_option('wp_booking_general_settings', array());
        $page_id = isset($options['page_id']) ? $options['page_id'] : 0;
        
        wp_dropdown_pages(array(
            'name' => 'wp_booking_general_settings[page_id]',
            'echo' => 1,
            'show_option_none' => __('Seleccionar página', 'wp-booking-plugin'),
            'option_none_value' => '0',
            'selected' => $page_id
        ));
        
        echo '<p class="description">' . __('Selecciona la página donde se mostrará el formulario de reservas.', 'wp-booking-plugin') . '</p>';
    }
    
    /**
     * Renderiza el campo de moneda.
     *
     * @since    1.0.0
     */
    public function render_currency_field() {
        $options = get_option('wp_booking_general_settings', array());
        $currency = isset($options['currency']) ? $options['currency'] : 'EUR';
        
        $currencies = array(
            'EUR' => __('Euro (€)', 'wp-booking-plugin'),
            'USD' => __('Dólar estadounidense ($)', 'wp-booking-plugin'),
            'GBP' => __('Libra esterlina (£)', 'wp-booking-plugin')
        );
        
        echo '<select name="wp_booking_general_settings[currency]">';
        foreach ($currencies as $code => $name) {
            echo '<option value="' . esc_attr($code) . '" ' . selected($currency, $code, false) . '>' . esc_html($name) . '</option>';
        }
        echo '</select>';
    }
    
    /**
     * Renderiza el campo de formato de fecha.
     *
     * @since    1.0.0
     */
    public function render_date_format_field() {
        $options = get_option('wp_booking_general_settings', array());
        $date_format = isset($options['date_format']) ? $options['date_format'] : 'd/m/Y';
        
        $formats = array(
            'd/m/Y' => date('d/m/Y'),
            'm/d/Y' => date('m/d/Y'),
            'Y-m-d' => date('Y-m-d')
        );
        
        echo '<select name="wp_booking_general_settings[date_format]">';
        foreach ($formats as $format => $example) {
            echo '<option value="' . esc_attr($format) . '" ' . selected($date_format, $format, false) . '>' . esc_html($example) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Renderiza la página de dashboard.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_dashboard() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wp-booking-admin-dashboard.php';
    }

    /**
     * Renderiza la página de categorías.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_categories() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wp-booking-admin-categories.php';
    }

    /**
     * Renderiza la página de servicios.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_services() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wp-booking-admin-services.php';
    }

    /**
     * Renderiza la página de grupos de artículos.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_item_groups() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wp-booking-admin-item-groups.php';
    }

    /**
     * Renderiza la página de artículos.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_items() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wp-booking-admin-items.php';
    }

    /**
     * Renderiza la página de descuentos.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_discounts() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wp-booking-admin-discounts.php';
    }

    /**
     * Renderiza la página de reservas.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_reservations() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wp-booking-admin-reservations.php';
    }

    /**
     * Renderiza la página de configuración.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_settings() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wp-booking-admin-settings.php';
    }

    /**
     * Registra los hooks de AJAX para el área de administración.
     *
     * @since    1.0.0
     */
    public function register_ajax_hooks() {
        // Categorías
        add_action('wp_ajax_wp_booking_save_category', array($this, 'ajax_save_category'));
        add_action('wp_ajax_wp_booking_delete_category', array($this, 'ajax_delete_category'));
        
        // Servicios
        add_action('wp_ajax_wp_booking_get_service', array($this, 'ajax_get_service'));
        add_action('wp_ajax_wp_booking_save_service', array($this, 'ajax_save_service'));
        add_action('wp_ajax_wp_booking_delete_service', array($this, 'ajax_delete_service'));
        
        // Grupos de artículos
        add_action('wp_ajax_wp_booking_save_item_group', array($this, 'ajax_save_item_group'));
        add_action('wp_ajax_wp_booking_delete_item_group', array($this, 'ajax_delete_item_group'));
        
        // Artículos
        add_action('wp_ajax_wp_booking_save_item', array($this, 'ajax_save_item'));
        add_action('wp_ajax_wp_booking_delete_item', array($this, 'ajax_delete_item'));
        
        // Descuentos
        add_action('wp_ajax_wp_booking_save_discount', array($this, 'ajax_save_discount'));
        add_action('wp_ajax_wp_booking_delete_discount', array($this, 'ajax_delete_discount'));
        
        // Reservas
        add_action('wp_ajax_wp_booking_get_reservation', array($this, 'ajax_get_reservation'));
        add_action('wp_ajax_wp_booking_update_reservation_status', array($this, 'ajax_update_reservation_status'));
        
        // NOTA: Las acciones AJAX públicas (test_connection, make_reservation) 
        // deben registrarse en la clase pública (WP_Booking_Public).
    }

    /**
     * Maneja la solicitud AJAX para guardar una categoría.
     *
     * @since    1.0.0
     */
    public function ajax_save_category() {
        // Verificar nonce y permisos
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_admin_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad.', 'wp-booking-plugin')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos.', 'wp-booking-plugin')), 403);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_categories';
        
        // Obtener y sanitizar datos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $status = isset($_POST['status']) ? intval($_POST['status']) : 0; // Asegurar que sea 0 o 1
        
        // Validar datos
        if (empty($name)) {
            wp_send_json_error(array('message' => __('El nombre de la categoría es obligatorio.', 'wp-booking-plugin')), 400);
        }
        
        // Preparar datos
        $data = array(
            'name' => $name,
            'description' => $description,
            'status' => $status
        );
        $format = array('%s', '%s', '%d');
        
        // Actualizar o insertar
        if ($id > 0) {
            $result = $wpdb->update($table_name, $data, array('id' => $id), $format, array('%d'));
            $message = __('Categoría actualizada correctamente.', 'wp-booking-plugin');
        } else {
            $result = $wpdb->insert($table_name, $data, $format);
            $id = $wpdb->insert_id;
            $message = __('Categoría creada correctamente.', 'wp-booking-plugin');
        }
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Error al guardar la categoría en la base de datos.', 'wp-booking-plugin') . ' ' . $wpdb->last_error), 500);
        }
        
        wp_send_json_success(array(
            'message' => $message,
            'id' => $id,
            'category' => array_merge($data, array('id' => $id))
        ));
    }

    /**
     * Maneja la solicitud AJAX para eliminar una categoría.
     *
     * @since    1.0.0
     */
    public function ajax_delete_category() {
        // Verificar nonce y permisos
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_admin_nonce')) {
             wp_send_json_error(array('message' => __('Error de seguridad.', 'wp-booking-plugin')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos.', 'wp-booking-plugin')), 403);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_categories';
        
        // Obtener y sanitizar datos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // Validar datos
        if ($id <= 0) {
            wp_send_json_error(array('message' => __('ID de categoría inválido.', 'wp-booking-plugin')), 400);
        }
        
        // Verificar si hay servicios asociados
        $services_table = $wpdb->prefix . 'booking_services';
        $services_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $services_table WHERE category_id = %d", $id));
        
        if ($services_count > 0) {
            wp_send_json_error(array('message' => __('No se puede eliminar la categoría porque tiene servicios asociados.', 'wp-booking-plugin')), 400);
        }
        
        // Eliminar categoría
        $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Error al eliminar la categoría.', 'wp-booking-plugin') . ' ' . $wpdb->last_error), 500);
        }
        
        wp_send_json_success(array(
            'message' => __('Categoría eliminada correctamente.', 'wp-booking-plugin')
        ));
    }

    /**
     * Maneja la solicitud AJAX para obtener un servicio.
     *
     * @since    1.0.0
     */
    public function ajax_get_service() {
        // Verificar nonce y permisos
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_get_service_action')) {
            wp_send_json_error(array('message' => __('Error de seguridad.', 'wp-booking-plugin')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos.', 'wp-booking-plugin')), 403);
        }
        
        global $wpdb;
        
        // Obtener y sanitizar datos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // Validar datos
        if ($id <= 0) {
            wp_send_json_error(array('message' => __('ID de servicio inválido.', 'wp-booking-plugin')), 400);
        }
        
        // Obtener servicio
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}booking_services WHERE id = %d",
            $id
        ), ARRAY_A);
        
        if (!$service) {
            wp_send_json_error(array('message' => __('Servicio no encontrado.', 'wp-booking-plugin')), 404);
        }
        
        // Obtener grupos de artículos asociados
        $service_item_groups = $wpdb->get_results($wpdb->prepare(
            "SELECT group_id FROM {$wpdb->prefix}booking_service_item_groups WHERE service_id = %d",
            $id
        ));
        
        $group_ids = array();
        if ($service_item_groups) {
            foreach ($service_item_groups as $group) {
                $group_ids[] = $group->group_id;
            }
        }
        
        // Obtener ID y URL de la imagen principal (usando main_image_id)
        $main_image_id = isset($service['main_image_id']) ? intval($service['main_image_id']) : 0;
        $main_image_url = '';
        if ($main_image_id > 0) {
            $main_image_url = wp_get_attachment_url($main_image_id);
        }

        wp_send_json_success(array(
            'service' => $service,
            'group_ids' => $group_ids,
            'main_image_id' => $main_image_id, // Enviar ID
            'main_image_url' => $main_image_url // Enviar URL
        ));
    }

    /**
     * Maneja la solicitud AJAX para guardar un servicio.
     *
     * @since    1.0.0
     */
    public function ajax_save_service() {
        // Verificar nonce y permisos
        if (!isset($_POST['wp_booking_save_service_nonce']) || !wp_verify_nonce($_POST['wp_booking_save_service_nonce'], 'wp_booking_save_service_action')) {
            wp_send_json_error(array('message' => __('Error de seguridad (nonce inválido). Por favor, recarga el formulario.', 'wp-booking-plugin')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos para realizar esta acción.', 'wp-booking-plugin')), 403);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_services';
        
        // Obtener y sanitizar datos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $description = isset($_POST['description']) ? wp_kses_post($_POST['description']) : '';
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        $service_date_str = isset($_POST['service_date']) ? sanitize_text_field($_POST['service_date']) : '';
        $max_capacity = isset($_POST['max_capacity']) ? intval($_POST['max_capacity']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 0; // Asegurar 0 o 1
        $enable_qr = isset($_POST['enable_qr']) ? 1 : 0; // Checkbox
        $main_image_id = isset($_POST['main_image_id']) ? intval($_POST['main_image_id']) : 0; // Usar main_image_id del formulario
        $group_ids = isset($_POST['item_groups']) && is_array($_POST['item_groups']) ? array_map('intval', $_POST['item_groups']) : array();
        
        // Validar datos
        if (empty($title)) {
            wp_send_json_error(array('message' => __('El título del servicio es obligatorio.', 'wp-booking-plugin')), 400);
        }
        if ($category_id <= 0) {
            wp_send_json_error(array('message' => __('Debes seleccionar una categoría.', 'wp-booking-plugin')), 400);
        }
        if ($price < 0) {
            wp_send_json_error(array('message' => __('El precio no puede ser negativo.', 'wp-booking-plugin')), 400);
        }
        if ($max_capacity < 0) {
             wp_send_json_error(array('message' => __('La capacidad máxima no puede ser negativa.', 'wp-booking-plugin')), 400);
        }

        // Formatear fecha para la base de datos (YYYY-MM-DD HH:MM:SS)
        $service_date_db = null;
        if (!empty($service_date_str)) {
            try {
                $date_obj = new DateTime($service_date_str);
                $service_date_db = $date_obj->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                // No hacer nada, se guardará como NULL
            }
        }
        
        // Preparar datos para la tabla 'booking_services'
        $data = array(
            'title' => $title,
            'description' => $description,
            'category_id' => $category_id,
            'price' => $price,
            'service_date' => $service_date_db,
            'max_capacity' => $max_capacity,
            'status' => $status,
            'enable_qr' => $enable_qr,
            'main_image_id' => ($main_image_id > 0) ? $main_image_id : null // Guardar ID de la imagen o NULL
        );
        
        // Definir formatos, incluyendo el de main_image_id
        $format = array('%s', '%s', '%d', '%f', '%s', '%d', '%d', '%d', '%d'); 
        
        // Iniciar transacción
        $wpdb->query('START TRANSACTION');
        
        try {
            // Actualizar o insertar servicio
            if ($id > 0) {
                $result = $wpdb->update($table_name, $data, array('id' => $id), $format, array('%d'));
                $message = __('Servicio actualizado correctamente.', 'wp-booking-plugin');
            } else {
                // Para inserción, añadir current_bookings inicial
                $data['current_bookings'] = 0;
                $format[] = '%d'; // Añadir formato para current_bookings
                $result = $wpdb->insert($table_name, $data, $format);
                $id = $wpdb->insert_id;
                $message = __('Servicio creado correctamente.', 'wp-booking-plugin');
            }
            
            if ($result === false) {
                $db_error = $wpdb->last_error;
                throw new Exception(__('Error al guardar el servicio en la base de datos.', 'wp-booking-plugin') . ' ' . $db_error);
            }
            
            // Actualizar grupos de artículos asociados
            $service_item_groups_table = $wpdb->prefix . 'booking_service_item_groups';
            $wpdb->delete($service_item_groups_table, array('service_id' => $id), array('%d'));
            
            if (!empty($group_ids)) {
                foreach ($group_ids as $group_id) {
                    $insert_group_result = $wpdb->insert(
                        $service_item_groups_table,
                        array('service_id' => $id, 'group_id' => $group_id),
                        array('%d', '%d')
                    );
                    if ($insert_group_result === false) {
                         throw new Exception(__('Error al asociar grupos de artículos.', 'wp-booking-plugin') . ' ' . $wpdb->last_error);
                    }
                }
            }
            
            // Confirmar transacción
            $wpdb->query('COMMIT');
            
            $saved_service = $wpdb->get_row($wpdb->prepare("SELECT s.*, c.name as category_name FROM $table_name s LEFT JOIN {$wpdb->prefix}booking_categories c ON s.category_id = c.id WHERE s.id = %d", $id), ARRAY_A);
            
            wp_send_json_success(array(
                'message' => $message,
                'id' => $id,
                'service' => $saved_service
            ));

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(array('message' => $e->getMessage()), 500);
        }
    }

    /**
     * Maneja la solicitud AJAX para eliminar un servicio.
     *
     * @since    1.0.0
     */
    public function ajax_delete_service() {
        // Verificar nonce y permisos
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_delete_service_action')) {
            wp_send_json_error(array('message' => __('Error de seguridad.', 'wp-booking-plugin')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos.', 'wp-booking-plugin')), 403);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_services';
        $service_item_groups_table = $wpdb->prefix . 'booking_service_item_groups';
        $reservations_table = $wpdb->prefix . 'booking_reservations';
        
        // Obtener y sanitizar datos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // Validar datos
        if ($id <= 0) {
            wp_send_json_error(array('message' => __('ID de servicio inválido.', 'wp-booking-plugin')), 400);
        }
        
        // Verificar si hay reservas asociadas (opcional)
        // $reservations_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $reservations_table WHERE service_id = %d", $id));
        // if ($reservations_count > 0) {
        //     wp_send_json_error(array('message' => __('No se puede eliminar el servicio porque tiene reservas asociadas.', 'wp-booking-plugin')), 400);
        // }
        
        // Iniciar transacción
        $wpdb->query('START TRANSACTION');
        
        try {
            // Eliminar asociaciones de grupos de artículos
            $wpdb->delete($service_item_groups_table, array('service_id' => $id), array('%d'));
            
            // Eliminar servicio
            $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));
            
            if ($result === false) {
                throw new Exception(__('Error al eliminar el servicio.', 'wp-booking-plugin') . ' ' . $wpdb->last_error);
            }
            
            // Confirmar transacción
            $wpdb->query('COMMIT');
            
            wp_send_json_success(array(
                'message' => __('Servicio eliminado correctamente.', 'wp-booking-plugin')
            ));

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(array('message' => $e->getMessage()), 500);
        }
    }

    // --- Métodos para otros CRUDs (Grupos, Artículos, Descuentos, Reservas) ---
    
    /**
     * Maneja la solicitud AJAX para guardar un grupo de artículos.
     *
     * @since    1.0.0
     */
    public function ajax_save_item_group() {
        // Verificar nonce y permisos
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_admin_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad.', 'wp-booking-plugin')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos.', 'wp-booking-plugin')), 403);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_item_groups';
        
        // Obtener y sanitizar datos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $status = isset($_POST['status']) ? intval($_POST['status']) : 0;
        
        // Validar datos
        if (empty($name)) {
            wp_send_json_error(array('message' => __('El nombre del grupo es obligatorio.', 'wp-booking-plugin')), 400);
        }
        
        // Preparar datos
        $data = array(
            'name' => $name,
            'description' => $description,
            'status' => $status
        );
        $format = array('%s', '%s', '%d');
        
        // Actualizar o insertar
        if ($id > 0) {
            $result = $wpdb->update($table_name, $data, array('id' => $id), $format, array('%d'));
            $message = __('Grupo de artículos actualizado correctamente.', 'wp-booking-plugin');
        } else {
            $result = $wpdb->insert($table_name, $data, $format);
            $id = $wpdb->insert_id;
            $message = __('Grupo de artículos creado correctamente.', 'wp-booking-plugin');
        }
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Error al guardar el grupo de artículos.', 'wp-booking-plugin') . ' ' . $wpdb->last_error), 500);
        }
        
        wp_send_json_success(array(
            'message' => $message,
            'id' => $id,
            'item_group' => array_merge($data, array('id' => $id))
        ));
    }

    /**
     * Maneja la solicitud AJAX para eliminar un grupo de artículos.
     *
     * @since    1.0.0
     */
    public function ajax_delete_item_group() {
        // Verificar nonce y permisos
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_admin_nonce')) {
             wp_send_json_error(array('message' => __('Error de seguridad.', 'wp-booking-plugin')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos.', 'wp-booking-plugin')), 403);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_item_groups';
        
        // Obtener y sanitizar datos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // Validar datos
        if ($id <= 0) {
            wp_send_json_error(array('message' => __('ID de grupo inválido.', 'wp-booking-plugin')), 400);
        }
        
        // Verificar si hay artículos asociados
        $items_table = $wpdb->prefix . 'booking_items';
        $items_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $items_table WHERE group_id = %d", $id));
        
        if ($items_count > 0) {
            wp_send_json_error(array('message' => __('No se puede eliminar el grupo porque tiene artículos asociados.', 'wp-booking-plugin')), 400);
        }
        
        // Eliminar grupo
        $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Error al eliminar el grupo de artículos.', 'wp-booking-plugin') . ' ' . $wpdb->last_error), 500);
        }
        
        wp_send_json_success(array(
            'message' => __('Grupo de artículos eliminado correctamente.', 'wp-booking-plugin')
        ));
    }

    /**
     * Maneja la solicitud AJAX para guardar un artículo.
     *
     * @since    1.0.0
     */
    public function ajax_save_item() {
        // Verificar nonce y permisos
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_admin_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad.', 'wp-booking-plugin')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos.', 'wp-booking-plugin')), 403);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_items';
        
        // Obtener y sanitizar datos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 0;
        
        // Validar datos
        if (empty($name)) {
            wp_send_json_error(array('message' => __('El nombre del artículo es obligatorio.', 'wp-booking-plugin')), 400);
        }
        if ($group_id <= 0) {
            wp_send_json_error(array('message' => __('Debes seleccionar un grupo.', 'wp-booking-plugin')), 400);
        }
        if ($price < 0) {
            wp_send_json_error(array('message' => __('El precio no puede ser negativo.', 'wp-booking-plugin')), 400);
        }
        
        // Preparar datos
        $data = array(
            'name' => $name,
            'group_id' => $group_id,
            'description' => $description,
            'price' => $price,
            'status' => $status
        );
        $format = array('%s', '%d', '%s', '%f', '%d');
        
        // Actualizar o insertar
        if ($id > 0) {
            $result = $wpdb->update($table_name, $data, array('id' => $id), $format, array('%d'));
            $message = __('Artículo actualizado correctamente.', 'wp-booking-plugin');
        } else {
            $result = $wpdb->insert($table_name, $data, $format);
            $id = $wpdb->insert_id;
            $message = __('Artículo creado correctamente.', 'wp-booking-plugin');
        }
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Error al guardar el artículo.', 'wp-booking-plugin') . ' ' . $wpdb->last_error), 500);
        }
        
        wp_send_json_success(array(
            'message' => $message,
            'id' => $id,
            'item' => array_merge($data, array('id' => $id))
        ));
    }

    /**
     * Maneja la solicitud AJAX para eliminar un artículo.
     *
     * @since    1.0.0
     */
    public function ajax_delete_item() {
        // Verificar nonce y permisos
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_admin_nonce')) {
             wp_send_json_error(array('message' => __('Error de seguridad.', 'wp-booking-plugin')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos.', 'wp-booking-plugin')), 403);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_items';
        
        // Obtener y sanitizar datos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // Validar datos
        if ($id <= 0) {
            wp_send_json_error(array('message' => __('ID de artículo inválido.', 'wp-booking-plugin')), 400);
        }
        
        // Eliminar artículo
        $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Error al eliminar el artículo.', 'wp-booking-plugin') . ' ' . $wpdb->last_error), 500);
        }
        
        wp_send_json_success(array(
            'message' => __('Artículo eliminado correctamente.', 'wp-booking-plugin')
        ));
    }

    /**
     * Maneja la solicitud AJAX para guardar un descuento.
     *
     * @since    1.0.0
     */
    public function ajax_save_discount() {
        // Verificar nonce y permisos
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_admin_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad.', 'wp-booking-plugin')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos.', 'wp-booking-plugin')), 403);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_discounts';
        
        // Obtener y sanitizar datos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $discount_type = isset($_POST['discount_type']) && in_array($_POST['discount_type'], ['percentage', 'fixed']) ? $_POST['discount_type'] : 'percentage';
        $discount_value = isset($_POST['discount_value']) ? floatval($_POST['discount_value']) : 0;
        $start_date_str = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date_str = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        $status = isset($_POST['status']) ? intval($_POST['status']) : 0;
        
        // Validar datos
        if (empty($name)) {
            wp_send_json_error(array('message' => __('El nombre del descuento es obligatorio.', 'wp-booking-plugin')), 400);
        }
        if ($service_id <= 0) {
            wp_send_json_error(array('message' => __('Debes seleccionar un servicio.', 'wp-booking-plugin')), 400);
        }
        if ($discount_value < 0) {
            wp_send_json_error(array('message' => __('El valor del descuento no puede ser negativo.', 'wp-booking-plugin')), 400);
        }
        if ($discount_type === 'percentage' && $discount_value > 100) {
            wp_send_json_error(array('message' => __('El porcentaje de descuento no puede ser mayor a 100.', 'wp-booking-plugin')), 400);
        }

        // Formatear fechas para la base de datos (YYYY-MM-DD HH:MM:SS)
        $start_date_db = null;
        if (!empty($start_date_str)) {
            try {
                $date_obj = new DateTime($start_date_str);
                $start_date_db = $date_obj->format('Y-m-d H:i:s');
            } catch (Exception $e) { /* Ignorar fecha inválida */ }
        }
        $end_date_db = null;
        if (!empty($end_date_str)) {
            try {
                $date_obj = new DateTime($end_date_str);
                $end_date_db = $date_obj->format('Y-m-d H:i:s');
            } catch (Exception $e) { /* Ignorar fecha inválida */ }
        }
        
        // Preparar datos
        $data = array(
            'name' => $name,
            'service_id' => $service_id,
            'description' => $description,
            'discount_type' => $discount_type,
            'discount_value' => $discount_value,
            'start_date' => $start_date_db,
            'end_date' => $end_date_db,
            'status' => $status
        );
        $format = array('%s', '%d', '%s', '%s', '%f', '%s', '%s', '%d');
        
        // Actualizar o insertar
        if ($id > 0) {
            $result = $wpdb->update($table_name, $data, array('id' => $id), $format, array('%d'));
            $message = __('Descuento actualizado correctamente.', 'wp-booking-plugin');
        } else {
            $result = $wpdb->insert($table_name, $data, $format);
            $id = $wpdb->insert_id;
            $message = __('Descuento creado correctamente.', 'wp-booking-plugin');
        }
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Error al guardar el descuento.', 'wp-booking-plugin') . ' ' . $wpdb->last_error), 500);
        }
        
        wp_send_json_success(array(
            'message' => $message,
            'id' => $id,
            'discount' => array_merge($data, array('id' => $id))
        ));
    }

    /**
     * Maneja la solicitud AJAX para eliminar un descuento.
     *
     * @since    1.0.0
     */
    public function ajax_delete_discount() {
        // Verificar nonce y permisos
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_admin_nonce')) {
             wp_send_json_error(array('message' => __('Error de seguridad.', 'wp-booking-plugin')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos.', 'wp-booking-plugin')), 403);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_discounts';
        
        // Obtener y sanitizar datos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // Validar datos
        if ($id <= 0) {
            wp_send_json_error(array('message' => __('ID de descuento inválido.', 'wp-booking-plugin')), 400);
        }
        
        // Eliminar descuento
        $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Error al eliminar el descuento.', 'wp-booking-plugin') . ' ' . $wpdb->last_error), 500);
        }
        
        wp_send_json_success(array(
            'message' => __('Descuento eliminado correctamente.', 'wp-booking-plugin')
        ));
    }

    /**
     * Maneja la solicitud AJAX para obtener detalles de una reserva.
     *
     * @since    1.0.0
     */
    public function ajax_get_reservation() {
        // Verificar nonce y permisos
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_admin_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad.', 'wp-booking-plugin')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos.', 'wp-booking-plugin')), 403);
        }

        global $wpdb;
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id <= 0) {
            wp_send_json_error(array('message' => __('ID de reserva inválido.', 'wp-booking-plugin')), 400);
        }

        // Obtener datos de la reserva
        $reservation = $wpdb->get_row($wpdb->prepare(
            "SELECT r.*, s.title as service_title 
             FROM {$wpdb->prefix}booking_reservations r 
             LEFT JOIN {$wpdb->prefix}booking_services s ON r.service_id = s.id 
             WHERE r.id = %d", 
             $id
        ), ARRAY_A);

        if (!$reservation) {
            wp_send_json_error(array('message' => __('Reserva no encontrada.', 'wp-booking-plugin')), 404);
        }

        // Obtener artículos asociados a la reserva
        $reservation_items = $wpdb->get_results($wpdb->prepare(
            "SELECT ri.*, i.name as item_name, i.price as item_price 
             FROM {$wpdb->prefix}booking_reservation_items ri 
             LEFT JOIN {$wpdb->prefix}booking_items i ON ri.item_id = i.id 
             WHERE ri.reservation_id = %d", 
             $id
        ), ARRAY_A);

        wp_send_json_success(array(
            'reservation' => $reservation,
            'items' => $reservation_items
        ));
    }

    /**
     * Maneja la solicitud AJAX para actualizar el estado de una reserva.
     *
     * @since    1.0.0
     */
    public function ajax_update_reservation_status() {
        // Verificar nonce y permisos
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_booking_admin_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad.', 'wp-booking-plugin')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos.', 'wp-booking-plugin')), 403);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_reservations';

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        // Validar datos
        if ($id <= 0) {
            wp_send_json_error(array('message' => __('ID de reserva inválido.', 'wp-booking-plugin')), 400);
        }
        $allowed_statuses = array('pending', 'confirmed', 'cancelled', 'completed');
        if (!in_array($status, $allowed_statuses)) {
            wp_send_json_error(array('message' => __('Estado de reserva inválido.', 'wp-booking-plugin')), 400);
        }

        // Actualizar estado
        $result = $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $id),
            array('%s'),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => __('Error al actualizar el estado de la reserva.', 'wp-booking-plugin') . ' ' . $wpdb->last_error), 500);
        }

        wp_send_json_success(array(
            'message' => __('Estado de la reserva actualizado correctamente.', 'wp-booking-plugin')
        ));
    }

}

