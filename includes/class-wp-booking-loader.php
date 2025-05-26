<?php
/**
 * La clase responsable de orquestar las acciones y filtros del núcleo del plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/includes
 */

/**
 * La clase responsable de orquestar las acciones y filtros del núcleo del plugin.
 *
 * Mantiene la lista de todos los hooks que están registrados en todo el plugin,
 * y los registra con WordPress.
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/includes
 * @author     Manus
 */
class WP_Booking_Loader {

    /**
     * El array de acciones registradas con WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    Las acciones registradas con WordPress para ejecutar cuando el plugin se carga.
     */
    protected $actions;

    /**
     * El array de filtros registrados con WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    Los filtros registrados con WordPress para ejecutar cuando el plugin se carga.
     */
    protected $filters;

    /**
     * Inicializa las colecciones utilizadas para mantener las acciones y filtros.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }

    /**
     * Añade una nueva acción al array de acciones que se registrarán con WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             El nombre de la acción de WordPress que se está registrando.
     * @param    object               $component        Una referencia a la instancia del objeto en el que se define la acción.
     * @param    string               $callback         El nombre de la definición de función en el $component.
     * @param    int                  $priority         Opcional. La prioridad en la que se debe ejecutar la función. Por defecto es 10.
     * @param    int                  $accepted_args    Opcional. El número de argumentos que se deben pasar a la $callback. Por defecto es 1.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Añade un nuevo filtro al array de filtros que se registrarán con WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             El nombre del filtro de WordPress que se está registrando.
     * @param    object               $component        Una referencia a la instancia del objeto en el que se define el filtro.
     * @param    string               $callback         El nombre de la definición de función en el $component.
     * @param    int                  $priority         Opcional. La prioridad en la que se debe ejecutar la función. Por defecto es 10.
     * @param    int                  $accepted_args    Opcional. El número de argumentos que se deben pasar a la $callback. Por defecto es 1.
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Una utilidad para registrar los hooks en las colecciones.
     *
     * @since    1.0.0
     * @access   private
     * @param    array                $hooks            La colección de hooks que se está registrando (es decir, acciones o filtros).
     * @param    string               $hook             El nombre del filtro de WordPress que se está registrando.
     * @param    object               $component        Una referencia a la instancia del objeto en el que se define el filtro.
     * @param    string               $callback         El nombre de la definición de función en el $component.
     * @param    int                  $priority         La prioridad en la que se debe ejecutar la función.
     * @param    int                  $accepted_args    El número de argumentos que se deben pasar a la $callback.
     * @return   array                                  La colección de acciones y filtros registrados con WordPress.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Registra los filtros y acciones con WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }
}
