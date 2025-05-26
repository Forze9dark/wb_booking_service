# Manual de Instalación y Uso - WP Booking Plugin

## Descripción General

WP Booking Plugin es una solución completa para gestionar reservas de servicios turísticos, tours, pasadías y más en WordPress. El plugin permite crear categorías, servicios, grupos de artículos adicionales, y gestionar todo el proceso de reservas desde el panel administrativo.

## Requisitos

- WordPress 5.0 o superior
- PHP 7.2 o superior
- MySQL 5.6 o superior

## Instalación

1. Descomprima el archivo `wp-booking-plugin.zip`
2. Suba la carpeta `wp-booking-plugin` al directorio `/wp-content/plugins/` de su instalación de WordPress
3. Active el plugin desde el menú 'Plugins' en WordPress
4. Al activar el plugin, se crearán automáticamente las tablas necesarias en la base de datos y una página de reservas

## Estructura del Plugin

El plugin está organizado en las siguientes secciones principales:

- **Categorías**: Para organizar los servicios (Tours, Pasadías, Resort, etc.)
- **Servicios**: Los productos principales que se ofrecen para reserva
- **Grupos de Artículos**: Conjuntos de elementos adicionales que se pueden vender con los servicios
- **Artículos**: Elementos individuales dentro de cada grupo (agua, refrescos, etc.)
- **Descuentos**: Promociones aplicables a los servicios
- **Reservas**: Gestión de las reservas realizadas por los clientes

## Uso del Plugin

### Panel de Administración

Después de activar el plugin, encontrará un nuevo menú "WP Booking" en el panel de administración de WordPress con las siguientes opciones:

1. **Dashboard**: Muestra un resumen de las estadísticas y acciones rápidas
2. **Categorías**: Gestión de categorías de servicios
3. **Servicios**: Creación y edición de servicios
4. **Grupos de Artículos**: Gestión de grupos de elementos adicionales
5. **Artículos**: Gestión de elementos individuales
6. **Descuentos**: Configuración de promociones
7. **Reservas**: Visualización y gestión de reservas
8. **Configuración**: Ajustes generales del plugin

### Configuración Inicial

Antes de comenzar a usar el plugin, se recomienda seguir estos pasos:

1. Crear categorías para organizar los servicios
2. Configurar grupos de artículos y artículos adicionales
3. Crear servicios y asignarles categorías y grupos de artículos
4. Configurar la página de reservas según sus necesidades

### Creación de Servicios

Para crear un nuevo servicio:

1. Vaya a WP Booking > Servicios
2. Haga clic en "Añadir nuevo"
3. Complete la información del servicio:
   - Título y descripción
   - Categoría
   - Precio
   - Fecha del servicio
   - Capacidad máxima
   - Estado (activo/inactivo)
   - Habilitar códigos QR (si desea que se generen códigos QR para las reservas)
   - Imagen principal y galería (máximo 5 imágenes)
   - Grupos de artículos asociados
4. Guarde el servicio

### Gestión de Reservas

Las reservas se pueden gestionar desde WP Booking > Reservas. Desde aquí puede:

- Ver todas las reservas
- Filtrar por estado (pendiente, confirmada, cancelada)
- Ver detalles de cada reserva
- Cambiar el estado de las reservas
- Ver los artículos adicionales incluidos en cada reserva

### Página de Reservas

El plugin crea automáticamente una página de reservas con el shortcode `[wp_booking_reservations]`. Esta página utiliza una plantilla personalizada que no muestra elementos de WordPress para dar la impresión de ser una página independiente.

Los visitantes pueden:

1. Ver las categorías y servicios disponibles
2. Ver detalles de cada servicio
3. Realizar reservas seleccionando:
   - Número de personas
   - Artículos adicionales
4. Recibir confirmación por email con los detalles de la reserva y códigos QR (si están habilitados)

## Personalización

### Plantilla de Página

La plantilla personalizada para la página de reservas se encuentra en:
`/wp-content/plugins/wp-booking-plugin/public/templates/wp-booking-template.php`

Puede copiar este archivo a su tema en:
`/wp-content/themes/su-tema/wp-booking-template.php`

Y personalizarlo según sus necesidades.

### Estilos CSS

Los estilos del plugin se encuentran en:
- `/wp-content/plugins/wp-booking-plugin/public/css/wp-booking-public.css` (frontend)
- `/wp-content/plugins/wp-booking-plugin/admin/css/wp-booking-admin.css` (admin)

Puede sobrescribir estos estilos desde su tema para personalizar la apariencia.

## Características Principales

- Gestión completa de servicios y categorías
- Soporte para artículos adicionales
- Sistema de descuentos
- Generación de códigos QR para reservas
- Notificaciones por email
- Control de capacidad máxima
- Interfaz administrativa intuitiva
- Página de reservas personalizada

## Soporte

Para soporte técnico o consultas, contacte a través de:
- Email: soporte@ejemplo.com
- Web: https://ejemplo.com/soporte

## Licencia

Este plugin está licenciado bajo GPL v2 o posterior.

---

© 2025 WP Booking Plugin. Todos los derechos reservados.
