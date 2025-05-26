<?php
/**
 * Plantilla limpia para el catálogo de servicios.
 * 
 * Esta plantilla no incluye ningún elemento de WordPress,
 * solo muestra el contenido del catálogo.
 *
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/public/templates
 */

// Si este archivo es llamado directamente, abortar
if (!defined('WPINC')) {
    die;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html(get_the_title()); ?></title>
    <?php wp_head(); ?>
</head>
<body>
    <?php 
    while (have_posts()) : 
        the_post();
        the_content();
    endwhile;
    ?>
    <?php wp_footer(); ?>
</body>
</html>