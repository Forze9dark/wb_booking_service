# Corrección de Shortcodes en el Plugin de Reservas

## Problema Identificado

Se ha detectado que el shortcode `[wp_booking_reservations]` no muestra el formulario de reservas, mientras que el shortcode `[wp_booking_form]` sí funciona correctamente.

## Causa del Problema

El problema se debía a que en la clase `WP_Booking_Public` solo se había registrado el shortcode `[wp_booking_form]`, pero no el shortcode `[wp_booking_reservations]` que estaba siendo utilizado en las páginas.

## Solución Implementada

Se ha modificado el método `register_shortcodes()` en la clase `WP_Booking_Public` para registrar ambos shortcodes:

```php
/**
 * Registra los shortcodes para mostrar el formulario de reservas.
 *
 * @since    1.0.0
 */
public function register_shortcodes() {
    add_shortcode('wp_booking_form', array($this, 'render_booking_form'));
    add_shortcode('wp_booking_reservations', array($this, 'render_booking_form'));
}
```

Ahora, tanto el shortcode `[wp_booking_form]` como el shortcode `[wp_booking_reservations]` mostrarán el formulario de reservas correctamente.

## Verificación

Se ha verificado que:

1. Ambos shortcodes están correctamente registrados en la clase `WP_Booking_Public`
2. Ambos shortcodes apuntan al mismo método `render_booking_form()` para mostrar el formulario de reservas
3. El formulario de reservas se muestra correctamente con cualquiera de los dos shortcodes

## Instrucciones de Actualización

1. Desactiva la versión anterior del plugin en WordPress
2. Elimina la carpeta del plugin anterior del directorio `/wp-content/plugins/`
3. Descomprime el archivo `wp-booking-plugin-shortcode-fix.zip`
4. Sube la carpeta `wp-booking-plugin` al directorio `/wp-content/plugins/`
5. Activa el plugin desde el panel de administración de WordPress

## Notas Adicionales

Esta corrección mantiene todas las mejoras y correcciones implementadas anteriormente:

- Diseño moderno y profesional para la página de reservas
- Modal personalizado para confirmación de reservas
- Indicador de procesamiento durante la reserva
- Corrección del bug de recarga y vaciado de la página tras procesar una reserva
- Corrección del error fatal relacionado con el método `load_custom_template`

Ahora puedes usar cualquiera de estos shortcodes en tus páginas:
- `[wp_booking_form]`
- `[wp_booking_reservations]`

Ambos mostrarán el mismo formulario de reservas con todas las funcionalidades implementadas.
