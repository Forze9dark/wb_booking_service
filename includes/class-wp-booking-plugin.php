<?php
/**
 * La clase principal del plugin.
 *
 * Esta es la clase que orquesta todo el plugin. Define los hooks de
 * internacionalización, hooks de admin y hooks públicos del plugin.
 *
 * @since      1.0.0
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/includes
 * @author     Manus
 */
class WP_Booking_Plugin {

    /**
     * El cargador que es responsable de mantener y registrar todos los hooks del plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WP_Booking_Loader    $loader    Mantiene y registra todos los hooks para el plugin.
     */
    protected $loader;

    /**
     * El identificador único de este plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    El string usado para identificar este plugin.
     */
    protected $plugin_name;

    /**
     * La versión actual del plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    La versión actual del plugin.
     */
    protected $version;

    /**
     * Define la funcionalidad principal del plugin.
     *
     * Establece el nombre y la versión del plugin que se puede utilizar en todo el plugin.
     * Carga las dependencias, define la configuración regional y
     * registra los hooks con WordPress.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('WP_BOOKING_VERSION')) {
            $this->version = WP_BOOKING_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'wp-booking-plugin';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Carga las dependencias requeridas para este plugin.
     *
     * Incluye los siguientes archivos que componen el plugin:
     *
     * - WP_Booking_Loader. Orquesta los hooks del plugin.
     * - WP_Booking_i18n. Define la funcionalidad de internacionalización.
     * - WP_Booking_Admin. Define todos los hooks del área de administración.
     * - WP_Booking_Public. Define todos los hooks del área pública.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * La clase responsable de orquestar las acciones y filtros del
         * núcleo del plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-booking-loader.php';

        /**
         * La clase responsable de definir la funcionalidad de internacionalización
         * del plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-booking-i18n.php';

        /**
         * La clase responsable de definir todas las acciones que ocurren en el área de administración.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-booking-admin.php';

        /**
         * La clase responsable de definir todas las acciones que ocurren en el área pública del sitio.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-booking-public.php';

        $this->loader = new WP_Booking_Loader();
    }

    /**
     * Define la configuración regional del plugin para la internacionalización.
     *
     * Utiliza la clase WP_Booking_i18n para establecer el dominio y registrar el hook
     * con WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new WP_Booking_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Registra todos los hooks relacionados con la funcionalidad de administración
     * del plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new WP_Booking_Admin($this->get_plugin_name(), $this->get_version());

        // Estilos y scripts de administración
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Menú de administración
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');

        // Registrar ajustes - Aseguramos que el método existe antes de registrarlo
        if (method_exists($plugin_admin, 'register_settings')) {
            $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        }

        // Registrar hooks AJAX
        if (method_exists($plugin_admin, 'register_ajax_hooks')) {
            $plugin_admin->register_ajax_hooks();
        }
    }

    /**
     * Registra todos los hooks relacionados con la funcionalidad pública del plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new WP_Booking_Public($this->get_plugin_name(), $this->get_version());

        // Estilos y scripts públicos
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Shortcodes - Aseguramos que el método existe antes de registrarlo
        if (method_exists($plugin_public, 'register_shortcodes')) {
            $this->loader->add_action('init', $plugin_public, 'register_shortcodes');
        }

        // Template para la página personalizada
        $this->loader->add_filter('template_include', $plugin_public, 'load_custom_template');

        // AJAX handlers para operaciones públicas
        $this->loader->add_action('wp_ajax_wp_booking_make_reservation', $plugin_public, 'process_reservation');
        $this->loader->add_action('wp_ajax_nopriv_wp_booking_make_reservation', $plugin_public, 'process_reservation');
        $this->loader->add_action('wp_ajax_wp_booking_get_service_details', $plugin_public, 'ajax_get_service_details');
        $this->loader->add_action('wp_ajax_nopriv_wp_booking_get_service_details', $plugin_public, 'ajax_get_service_details');
        // Añadir hooks para la conexión de prueba AJAX pública
        $this->loader->add_action('wp_ajax_wp_booking_test_connection', $plugin_public, 'test_ajax_connection');
        $this->loader->add_action('wp_ajax_nopriv_wp_booking_test_connection', $plugin_public, 'test_ajax_connection');
    }

    /**
     * Ejecuta el cargador para ejecutar todos los hooks con WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * El nombre del plugin utilizado para identificarlo dentro de WordPress.
     *
     * @since     1.0.0
     * @return    string    El nombre del plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * La referencia a la clase que orquesta los hooks del plugin.
     *
     * @since     1.0.0
     * @return    WP_Booking_Loader    Orquesta los hooks del plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Recupera el número de versión del plugin.
     *
     * @since     1.0.0
     * @return    string    El número de versión del plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
