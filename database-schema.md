# Esquema de Base de Datos para WP Booking Plugin

Este documento describe la estructura de las tablas de la base de datos para el plugin de reservas de WordPress.

## Prefijo de Tablas

Todas las tablas utilizarán el prefijo `wp_booking_` para evitar conflictos con otras tablas de WordPress.

## Tablas Principales

### 1. Categorías (`wp_booking_categories`)

Almacena las diferentes categorías de servicios (Tours, Pasadías, Resort, etc.).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único de la categoría (clave primaria, auto-incremento) |
| `name` | VARCHAR(100) | Nombre de la categoría |
| `description` | TEXT | Descripción de la categoría |
| `status` | TINYINT(1) | Estado de la categoría (1 = activo, 0 = inactivo) |
| `created_at` | DATETIME | Fecha de creación |
| `updated_at` | DATETIME | Fecha de última actualización |

### 2. Servicios (`wp_booking_services`)

Almacena la información principal de cada servicio.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único del servicio (clave primaria, auto-incremento) |
| `category_id` | INT | ID de la categoría (clave foránea) |
| `title` | VARCHAR(200) | Título del servicio |
| `description` | TEXT | Descripción detallada del servicio |
| `price` | DECIMAL(10,2) | Precio base del servicio |
| `service_date` | DATETIME | Fecha y hora de inicio del servicio |
| `max_capacity` | INT | Capacidad máxima de personas |
| `current_bookings` | INT | Número actual de reservas (para control de capacidad) |
| `status` | TINYINT(1) | Estado del servicio (1 = activo, 0 = inactivo) |
| `enable_qr` | TINYINT(1) | Habilitar códigos QR para este servicio (1 = sí, 0 = no) |
| `main_image_id` | INT | ID de la imagen principal (clave foránea) |
| `created_at` | DATETIME | Fecha de creación |
| `updated_at` | DATETIME | Fecha de última actualización |

### 3. Imágenes (`wp_booking_images`)

Almacena las imágenes asociadas a los servicios (principal y galería).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único de la imagen (clave primaria, auto-incremento) |
| `service_id` | INT | ID del servicio asociado (clave foránea) |
| `image_url` | VARCHAR(255) | URL de la imagen |
| `is_main` | TINYINT(1) | Indica si es la imagen principal (1 = sí, 0 = no) |
| `order` | INT | Orden de visualización en la galería |
| `created_at` | DATETIME | Fecha de creación |

### 4. Grupos de Artículos (`wp_booking_item_groups`)

Define los grupos de elementos adicionales que se pueden vender con el servicio.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único del grupo (clave primaria, auto-incremento) |
| `name` | VARCHAR(100) | Nombre del grupo |
| `description` | TEXT | Descripción del grupo |
| `status` | TINYINT(1) | Estado del grupo (1 = activo, 0 = inactivo) |
| `created_at` | DATETIME | Fecha de creación |
| `updated_at` | DATETIME | Fecha de última actualización |

### 5. Artículos (`wp_booking_items`)

Almacena los elementos individuales dentro de cada grupo.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único del artículo (clave primaria, auto-incremento) |
| `group_id` | INT | ID del grupo al que pertenece (clave foránea) |
| `name` | VARCHAR(100) | Nombre del artículo |
| `description` | TEXT | Descripción del artículo |
| `price` | DECIMAL(10,2) | Precio del artículo |
| `status` | TINYINT(1) | Estado del artículo (1 = activo, 0 = inactivo) |
| `created_at` | DATETIME | Fecha de creación |
| `updated_at` | DATETIME | Fecha de última actualización |

### 6. Relación Servicios-Grupos (`wp_booking_service_item_groups`)

Tabla de relación para asignar grupos de artículos a servicios.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único de la relación (clave primaria, auto-incremento) |
| `service_id` | INT | ID del servicio (clave foránea) |
| `group_id` | INT | ID del grupo de artículos (clave foránea) |
| `created_at` | DATETIME | Fecha de creación |

### 7. Descuentos (`wp_booking_discounts`)

Gestiona los descuentos aplicables a los servicios.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único del descuento (clave primaria, auto-incremento) |
| `service_id` | INT | ID del servicio asociado (clave foránea) |
| `name` | VARCHAR(100) | Nombre del descuento |
| `description` | TEXT | Descripción del descuento |
| `discount_type` | ENUM('percentage', 'fixed') | Tipo de descuento (porcentaje o monto fijo) |
| `discount_value` | DECIMAL(10,2) | Valor del descuento |
| `start_date` | DATETIME | Fecha de inicio del descuento |
| `end_date` | DATETIME | Fecha de finalización del descuento |
| `status` | TINYINT(1) | Estado del descuento (1 = activo, 0 = inactivo) |
| `created_at` | DATETIME | Fecha de creación |
| `updated_at` | DATETIME | Fecha de última actualización |

### 8. Reservas (`wp_booking_reservations`)

Registra las reservas realizadas por los clientes.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único de la reserva (clave primaria, auto-incremento) |
| `service_id` | INT | ID del servicio reservado (clave foránea) |
| `customer_name` | VARCHAR(100) | Nombre del cliente |
| `customer_email` | VARCHAR(100) | Email del cliente |
| `customer_phone` | VARCHAR(20) | Teléfono del cliente |
| `num_people` | INT | Número de personas |
| `total_price` | DECIMAL(10,2) | Precio total de la reserva |
| `reservation_date` | DATETIME | Fecha y hora de la reserva |
| `status` | ENUM('pending', 'confirmed', 'cancelled') | Estado de la reserva |
| `qr_codes` | TEXT | JSON con información de códigos QR generados |
| `created_at` | DATETIME | Fecha de creación |
| `updated_at` | DATETIME | Fecha de última actualización |

### 9. Detalles de Reserva (`wp_booking_reservation_items`)

Almacena los artículos adicionales incluidos en cada reserva.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único del detalle (clave primaria, auto-incremento) |
| `reservation_id` | INT | ID de la reserva (clave foránea) |
| `item_id` | INT | ID del artículo (clave foránea) |
| `quantity` | INT | Cantidad del artículo |
| `price` | DECIMAL(10,2) | Precio unitario del artículo al momento de la reserva |
| `created_at` | DATETIME | Fecha de creación |

## Relaciones entre Tablas

1. Un servicio pertenece a una categoría (relación muchos a uno)
2. Un servicio puede tener múltiples imágenes (relación uno a muchos)
3. Un servicio puede tener múltiples grupos de artículos (relación muchos a muchos)
4. Un grupo de artículos puede tener múltiples artículos (relación uno a muchos)
5. Un servicio puede tener múltiples descuentos (relación uno a muchos)
6. Un servicio puede tener múltiples reservas (relación uno a muchos)
7. Una reserva puede incluir múltiples artículos adicionales (relación uno a muchos)

## Índices

Para optimizar el rendimiento de las consultas, se crearán índices en:

1. Claves primarias de todas las tablas
2. Claves foráneas para facilitar las uniones
3. Campos de búsqueda frecuente como `status`, `service_date`, `reservation_date`
