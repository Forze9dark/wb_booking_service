# Documentación Final - Plugin de Reservas para WordPress

## Descripción General

Este plugin de WordPress permite gestionar un sistema completo de reservas para servicios como tours, pasadías, resorts, etc. Incluye gestión de categorías, servicios, grupos de artículos adicionales, descuentos y reservas con códigos QR.

## Características Principales

- **Gestión de Categorías**: Organiza tus servicios en diferentes categorías (Tours, Pasadías, Resort, etc.)
- **Gestión de Servicios**: Crea y administra servicios con título, descripción, precio, fecha, capacidad máxima, etc.
- **Imágenes**: Asigna una imagen principal y hasta 5 imágenes de galería para cada servicio
- **Artículos Adicionales**: Configura grupos de artículos adicionales que se pueden vender junto con el servicio principal
- **Descuentos**: Aplica descuentos a los servicios
- **Reservas**: Sistema completo de reservas con validación de disponibilidad
- **Códigos QR**: Opción para generar códigos QR para las reservas
- **Página Personalizada**: Página de reservas con diseño personalizado sin elementos de WordPress
- **Interfaz Moderna**: Diseño responsivo y atractivo para la experiencia del usuario

## Estructura de Archivos

```
wp-booking-plugin/
├── admin/                      # Archivos para el panel de administración
│   ├── css/                    # Estilos para el admin
│   ├── js/                     # Scripts para el admin
│   ├── partials/               # Vistas parciales para el admin
│   └── class-wp-booking-admin.php  # Clase principal del admin
├── includes/                   # Archivos principales del plugin
│   ├── class-wp-booking-activator.php    # Activación del plugin
│   ├── class-wp-booking-deactivator.php  # Desactivación del plugin
│   ├── class-wp-booking-i18n.php         # Internacionalización
│   ├── class-wp-booking-loader.php       # Cargador de hooks
│   └── class-wp-booking-plugin.php       # Clase principal del plugin
├── public/                     # Archivos para el frontend
│   ├── css/                    # Estilos para el frontend
│   ├── js/                     # Scripts para el frontend
│   ├── partials/               # Vistas parciales para el frontend
│   ├── templates/              # Plantillas para páginas personalizadas
│   └── class-wp-booking-public.php  # Clase principal del frontend
└── wp-booking-plugin.php       # Archivo principal del plugin
```

## Estructura de la Base de Datos

El plugin crea las siguientes tablas en la base de datos:

1. **wp_booking_categories**: Almacena las categorías de servicios
2. **wp_booking_services**: Almacena los servicios con sus detalles
3. **wp_booking_images**: Almacena las imágenes de los servicios
4. **wp_booking_item_groups**: Almacena los grupos de artículos adicionales
5. **wp_booking_items**: Almacena los artículos individuales
6. **wp_booking_discounts**: Almacena los descuentos aplicables a los servicios
7. **wp_booking_reservations**: Almacena las reservas realizadas
8. **wp_booking_reservation_items**: Almacena los artículos adicionales seleccionados en cada reserva

## Mejoras Implementadas

### 1. Corrección de Archivos Faltantes
Se han creado todos los archivos de vistas parciales necesarios para el panel administrativo:
- wp-booking-admin-services.php
- wp-booking-admin-item-groups.php
- wp-booking-admin-items.php
- wp-booking-admin-discounts.php
- wp-booking-admin-reservations.php
- wp-booking-admin-settings.php

### 2. Corrección del Registro de Opciones
Se ha implementado correctamente el registro del grupo de opciones `wp_booking_options_group` para permitir guardar la configuración del plugin.

### 3. Mejora de la Experiencia de Usuario
- **Modal de Confirmación Personalizado**: Reemplazo del alert básico por un modal estético con iconos y animaciones
- **Indicador de Procesamiento**: Adición de un spinner animado durante el procesamiento de reservas
- **Diseño Moderno**: Actualización completa del diseño visual con estilos modernos y responsivos

### 4. Corrección del Bug de Recarga
Se ha implementado una solución para evitar que la página se vacíe después de procesar una reserva:
- Uso de AJAX para actualizar dinámicamente las categorías y servicios
- Implementación de la función `loadCategoriesAndServices()` que actualiza el contenido sin recargar la página
- Modificación del comportamiento del botón de confirmación para mantener la experiencia de usuario fluida

## Instalación

1. Descomprimir el archivo `wp-booking-plugin-final.zip`
2. Subir la carpeta `wp-booking-plugin` al directorio `/wp-content/plugins/` de WordPress
3. Activar el plugin desde el panel de administración de WordPress
4. Configurar las opciones del plugin en la sección "Configuración" del menú "WP Booking"

## Uso

### Panel de Administración
- **Dashboard**: Muestra estadísticas generales de reservas
- **Categorías**: Gestiona las categorías de servicios
- **Servicios**: Crea y edita servicios con sus detalles
- **Grupos de Artículos**: Gestiona los grupos de artículos adicionales
- **Artículos**: Gestiona los artículos individuales
- **Descuentos**: Configura descuentos para los servicios
- **Reservas**: Visualiza y gestiona las reservas realizadas
- **Configuración**: Configura las opciones generales del plugin

### Frontend
- La página de reservas muestra las categorías y servicios disponibles
- Los usuarios pueden ver detalles de los servicios y realizar reservas
- El proceso de reserva incluye selección de artículos adicionales
- Confirmación visual con modal personalizado
- Envío de email de confirmación con detalles de la reserva

## Personalización

El plugin puede personalizarse mediante:

1. **Hooks de WordPress**: Utiliza los filtros y acciones proporcionados por el plugin
2. **CSS Personalizado**: Modifica los estilos en los archivos CSS del plugin
3. **Plantillas**: Personaliza las plantillas en la carpeta `public/templates/`

## Soporte

Para soporte técnico o consultas sobre el plugin, contacta al desarrollador.

---

© 2025 WP Booking Plugin - Todos los derechos reservados
