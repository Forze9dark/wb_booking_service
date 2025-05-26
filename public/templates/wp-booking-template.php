<?php
/**
 * Plantilla de página personalizada para WP Booking Plugin.
 *
 * Esta plantilla proporciona un lienzo limpio, eliminando la cabecera, 
 * pie de página y barras laterales del tema, mostrando solo el contenido 
 * de la página (que debería ser el shortcode de reservas).
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/public/templates
 */

// Si este archivo es llamado directamente, abortar.
if (!defined(\'WPINC\')) {
    die;
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo(\'charset\'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); // Esencial para que funcionen los scripts y estilos del plugin y WP ?>
    <style>
        /* Estilos básicos para asegurar el lienzo limpio */
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow-x: hidden; /* Evitar scroll horizontal */
        }
        /* Puedes añadir más estilos aquí si necesitas un contenedor específico */
        .wp-booking-template-content {
             padding: 20px; /* Añadir algo de espacio alrededor */
             max-width: 1200px; /* Opcional: limitar ancho máximo */
             margin: 0 auto; /* Centrar contenido si se limita el ancho */
        }
    </style>
</head>
<body <?php body_class(); ?>>
<div id="page" class="site wp-booking-template-container">
    <div id="content" class="site-content">
        <div id="primary" class="content-area">
            <main id="main" class="site-main wp-booking-template-content" role="main">
                <?php
                // Iniciar el Loop
                while (have_posts()) : the_post();
                    // Mostrar el contenido de la página (aquí debería estar el shortcode)
                    the_content();
                endwhile; // Fin del Loop.
                ?>
            </main><!-- #main -->
        </div><!-- #primary -->
    </div><!-- #content -->
</div><!-- #page -->
<?php wp_footer(); // Esencial para scripts encolados en el pie ?>
</body>
</html>

