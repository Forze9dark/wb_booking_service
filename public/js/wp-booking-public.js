/**
 * Scripts para el área pública del plugin WP Booking.
 *
 * @since      1.0.0
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/public
 */

jQuery(document).ready(function($) {
    'use strict';

    // Verificar si el objeto wp_booking_ajax existe
    if (typeof wp_booking_ajax === 'undefined') {
        console.error('WP Booking: El objeto wp_booking_ajax no está definido. Verifica wp_localize_script.');
        // Mostrar un error al usuario podría ser útil aquí
        showError('Error de configuración interna. Por favor, contacta al administrador.');
        return; // Detener la ejecución si falta la configuración
    }

    // Ocultar indicador de carga cuando la página esté lista
    $('.wp-booking-loading').hide();
    $('.wp-booking-services').show();

    // Filtrar servicios por categoría
    $('.wp-booking-category-btn').on('click', function() {
        var category = $(this).data('category');
        
        // Actualizar botones activos
        $('.wp-booking-category-btn').removeClass('active');
        $(this).addClass('active');
        
        // Mostrar indicador de carga
        $('.wp-booking-services').hide();
        $('.wp-booking-loading').show();
        
        // Simular carga (para mejor UX)
        setTimeout(function() {
            if (category === 'all') {
                $('.wp-booking-service-card').show();
            } else {
                $('.wp-booking-service-card').hide();
                $('.wp-booking-service-card[data-category="' + category + '"]').show();
            }
            
            // Ocultar indicador de carga
            $('.wp-booking-loading').hide();
            $('.wp-booking-services').show();
        }, 300); // Reducir tiempo de espera simulado
    });
    
    // Abrir modal de servicio al hacer clic en botón de reserva o en la tarjeta
    // Selector corregido: escucha clics en el botón o en la tarjeta misma
    $('.wp-booking-services').on('click', '.wp-booking-reserve-btn, .wp-booking-service-card', function(e) {
        // Si el evento se originó en un botón que NO es el de reservar, salir.
        // Esto evita que otros botones dentro de la tarjeta (si existieran) abran el modal.
        if ($(e.target).closest('button').length && !$(e.target).closest('.wp-booking-reserve-btn').length) {
            return;
        }
    
        // Si el evento se originó en el botón de reservar O en la tarjeta (y no en otro botón)
        e.preventDefault();
        var serviceId = $(this).closest('.wp-booking-service-card').data('service-id');
    
        // Verificar si serviceId se obtuvo correctamente desde closest()
        if (typeof serviceId !== 'undefined' && serviceId !== '') {
            openServiceModal(serviceId);
        } else {
            // Si closest() falló, intentar obtenerlo directamente del elemento clickeado (útil si se hizo clic en el botón)
            if ($(this).hasClass('wp-booking-reserve-btn')) {
                 serviceId = $(this).data('service-id'); // El botón también tiene el data attribute en la plantilla
                 if (typeof serviceId !== 'undefined' && serviceId !== '') {
                     openServiceModal(serviceId);
                 } else {
                     console.error('WP Booking: No se pudo obtener serviceId desde el botón de reserva.');
                     showError('Error al obtener detalles del servicio (ID no encontrado en botón).');
                 }
            } else if ($(this).hasClass('wp-booking-service-card')) {
                 serviceId = $(this).data('service-id'); // Intentar obtenerlo de la tarjeta directamente
                 if (typeof serviceId !== 'undefined' && serviceId !== '') {
                     openServiceModal(serviceId);
                 } else {
                     console.error('WP Booking: No se pudo obtener serviceId desde la tarjeta (intento directo).');
                     showError('Error al obtener detalles del servicio (ID no encontrado en tarjeta).');
                 }
            } else {
                 // Si no es ni el botón ni la tarjeta, buscar el ID en el padre más cercano
                 var $card = $(this).closest('.wp-booking-service-card');
                 if ($card.length) {
                     serviceId = $card.data('service-id');
                     if (typeof serviceId !== 'undefined' && serviceId !== '') {
                         openServiceModal(serviceId);
                     } else {
                         console.error('WP Booking: No se pudo obtener serviceId desde la tarjeta padre.');
                         showError('Error al obtener detalles del servicio (ID no encontrado en tarjeta padre).');
                     }
                 } else {
                     console.error('WP Booking: No se pudo encontrar la tarjeta de servicio asociada al clic.');
                     showError('Error al obtener detalles del servicio (Tarjeta no encontrada).');
                 }
            }
        }
    });
    
    // Cerrar modales
    $('.wp-booking-modal-close, .wp-booking-modal-overlay, .wp-booking-close-btn').on('click', function() {
        closeAllModals();
    });
    
    // Volver a la lista de servicios
    $('.wp-booking-back-btn').on('click', function(e) {
        e.preventDefault();
        closeAllModals();
    });
    
    // Actualizar precio total al cambiar número de personas o artículos
    // Usar delegación de eventos para elementos dentro del modal
    $('#wp-booking-service-modal').on('change input', '#wp-booking-num-people, .wp-booking-item-checkbox', function() {
        updateTotalPrice();
    });
    
    // Enviar formulario de reserva
    $('#wp-booking-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $submitButton = $form.find('button[type="submit"]');
        var $processingModal = $('#wp-booking-processing-modal');
        var $serviceModal = $('#wp-booking-service-modal');
        
        // Validar formulario
        var customerName = $('#wp-booking-customer-name').val().trim();
        var customerEmail = $('#wp-booking-customer-email').val().trim();
        var customerPhone = $('#wp-booking-customer-phone').val().trim();
        var numPeople = parseInt($('#wp-booking-num-people').val());

        if (!customerName) {
            showError('Por favor, introduce tu nombre completo.');
            $('#wp-booking-customer-name').focus();
            return;
        }
        if (!customerEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(customerEmail)) { // Validación básica de email
            showError('Por favor, introduce un email válido.');
            $('#wp-booking-customer-email').focus();
            return;
        }
        if (!customerPhone) {
            showError('Por favor, introduce tu número de teléfono.');
            $('#wp-booking-customer-phone').focus();
            return;
        }
        if (isNaN(numPeople) || numPeople < 1) {
            showError('El número de personas debe ser al menos 1.');
            $('#wp-booking-num-people').focus();
            return;
        }
        
        // Recopilar artículos seleccionados
        var selectedItems = [];
        var selectedQuantities = [];

        $('.wp-booking-item-checkbox:checked').each(function() {
            var itemId = $(this).val();
            var quantity = 1; // Asumiendo cantidad 1 por ahora
            selectedItems.push(itemId);
            selectedQuantities.push(quantity);
        });

        // Mostrar modal de procesamiento y deshabilitar botón
        $serviceModal.hide();
        $processingModal.show();
        $submitButton.prop('disabled', true);

        // Enviar datos mediante AJAX usando el objeto localizado
        $.ajax({
            url: wp_booking_ajax.ajax_url, // Corregido
            type: 'POST',
            data: {
                action: 'wp_booking_make_reservation',
                nonce: wpBookingAjaxNonce, // Usar el nonce correcto
                service_id: $('#wp-booking-service-id').val(),
                customer_name: customerName,
                customer_email: customerEmail,
                customer_phone: customerPhone,
                num_people: numPeople,
                items: selectedItems,
                quantities: selectedQuantities
            },
            dataType: 'json',
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            success: function(response) {
                if (response.success) {
                    // Mostrar detalles de la reserva
                    var reservationDetails = '<p><strong>ID de Reserva:</strong> ' + response.data.reservation_code + '</p>'; // Usar reservation_code
                    reservationDetails += '<p><strong>Nombre:</strong> ' + customerName + '</p>';
                    reservationDetails += '<p><strong>Email:</strong> ' + customerEmail + '</p>';
                    reservationDetails += '<p><strong>Teléfono:</strong> ' + customerPhone + '</p>';
                    reservationDetails += '<p><strong>Personas:</strong> ' + numPeople + '</p>';
                    reservationDetails += '<p><strong>Total:</strong> ' + $('#wp-booking-total-price').text() + '</p>';

                    $('#wp-booking-reservation-details').html(reservationDetails);

                    // Mostrar código QR si está disponible
                    if (response.data.qr_code) {
                        // Asumiendo que qr_code contiene la URL o los datos para generar
                        // Ejemplo con qrserver.com
                        var qrData = response.data.qr_code; // Podría ser la URL directa o datos
                        var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(qrData);
                        $('#wp-booking-qr-code').html('<img src="' + qrUrl + '" alt="Código QR de la Reserva">');
                    } else {
                        $('#wp-booking-qr-code').empty();
                    }

                    // Mostrar modal de éxito
                    $('#wp-booking-success-modal').show();

                    // Resetear formulario
                    $form[0].reset();
                    updateTotalPrice(); // Resetear precio total
                } else {
                    // Mostrar error y volver al modal de servicio
                    showError(response.data.message || 'Ocurrió un error desconocido.');
                    $serviceModal.show(); // Volver al modal anterior
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Mostrar error y volver al modal de servicio
                var errorMessage = '';
                try {
                    var response = JSON.parse(jqXHR.responseText);
                    errorMessage = response.data ? response.data.message : 'Error desconocido';
                } catch(e) {
                    errorMessage = 'Error de conexión: ' + textStatus;
                }
                showError(errorMessage + '. Por favor, inténtalo de nuevo más tarde.');
                console.error("AJAX Error: ", textStatus, errorThrown, jqXHR);
                $serviceModal.show(); // Volver al modal anterior
            },
            complete: function() {
                // Ocultar modal de procesamiento y habilitar botón
                $processingModal.hide();
                $submitButton.prop('disabled', false);
            }
        });
    });
    
    // Probar conexión AJAX al cargar la página (opcional, bueno para depuración)
    $.ajax({
        url: wp_booking_ajax.ajax_url, // Corregido
        type: 'POST',
        data: {
            action: 'wp_booking_test_connection',
            nonce: wp_booking_ajax.nonce // Corregido
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                console.log('WP Booking: Conexión AJAX pública exitosa.');
            } else {
                console.warn('WP Booking: Fallo en test de conexión AJAX pública.', response.data.message);
                // Podría indicar un problema de nonce o permisos si el usuario no está logueado
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('WP Booking: Error crítico en test de conexión AJAX pública.', textStatus, errorThrown);
            // Mostrar un error genérico podría ser útil si esto falla
            // showError('No se pudo conectar con el servidor de reservas.');
        }
    });
    
    // Función para abrir el modal de servicio
    function openServiceModal(serviceId) {
        // Asegurarse de que los datos de servicios están disponibles
        if (typeof wp_booking_ajax.services === 'undefined' || wp_booking_ajax.services.length === 0) {
            showError('Datos de servicios no disponibles.');
            console.error('WP Booking: Variable wp_booking_ajax.services no definida o vacía.');
            return;
        }

        // Buscar servicio por ID
        var service = wp_booking_ajax.services.find(function(s) { return s.id == serviceId; });
        
        if (!service) {
            showError('Servicio no encontrado.');
            console.error('WP Booking: Servicio con ID ' + serviceId + ' no encontrado en wp_booking_ajax.services.');
            return;
        }
        
        // Llenar datos del servicio
        $('#wp-booking-service-id').val(service.id);
        $('#wp-booking-service-title').text(service.title || 'Servicio sin título');
        $('#wp-booking-service-category').text(service.category_name || 'Sin categoría');
        $('#wp-booking-service-description').html(service.description || '');
        $('#wp-booking-service-price').text(parseFloat(service.price || 0).toFixed(2) + ' €');
        
        // Formatear fecha
        var serviceDateText = 'Fecha no disponible';
        if (service.service_date && service.service_date !== '0000-00-00 00:00:00') {
            try {
                var dateStr = service.service_date.replace(' ', 'T');
                var date = new Date(dateStr);
                if (!isNaN(date.getTime())) {
                    var options = { /*weekday: 'long',*/ year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                    serviceDateText = date.toLocaleDateString('es-ES', options);
                }
            } catch (e) { console.error("Error parsing service date: ", service.service_date, e); }
        }
        $('#wp-booking-service-date').text(serviceDateText);
        
        // Mostrar capacidad
        var capacityText = 'Ilimitada';
        var maxAttr = null;
        if (service.max_capacity > 0) {
            var currentBookings = parseInt(service.current_bookings || 0);
            var available = service.max_capacity - currentBookings;
            capacityText = available + ' / ' + service.max_capacity + ' disponibles';
            maxAttr = available > 0 ? available : 0; // Poner 0 si no hay disponibles
        } 
        $('#wp-booking-service-capacity').text(capacityText);
        if (maxAttr !== null) {
             $('#wp-booking-num-people').attr('max', maxAttr);
             if (maxAttr === 0) {
                 $('#wp-booking-num-people').val(0).prop('disabled', true);
                 $('#wp-booking-form button[type="submit"]').prop('disabled', true); // Deshabilitar reserva si no hay capacidad
             } else {
                 $('#wp-booking-num-people').prop('disabled', false);
                 $('#wp-booking-form button[type="submit"]').prop('disabled', false);
                 // Ajustar valor si el actual excede el máximo
                 if (parseInt($('#wp-booking-num-people').val()) > maxAttr) {
                     $('#wp-booking-num-people').val(maxAttr);
                 }
             }
        } else {
            $('#wp-booking-num-people').removeAttr('max').prop('disabled', false);
            $('#wp-booking-form button[type="submit"]').prop('disabled', false);
        }
        // Asegurar que el valor mínimo sea 1 si está habilitado
        if (!$('#wp-booking-num-people').prop('disabled')) {
             $('#wp-booking-num-people').attr('min', 1);
             if (parseInt($('#wp-booking-num-people').val()) < 1) {
                 $('#wp-booking-num-people').val(1);
             }
        }
        
        // Mostrar si tiene códigos QR
        $('#wp-booking-service-qr').text(service.enable_qr == 1 ? 'Incluido' : 'No disponible');
        
        // Mostrar imagen
        if (service.image_url) { // Usar image_url que viene del backend
            $("#wp-booking-service-image").html("<img src=\"" + service.image_url + "\" alt=\"" + service.title + "\">");
        } else {
            $("#wp-booking-service-image").html("<div class=\"wp-booking-placeholder-image-large\"><i class=\"fas fa-image\"></i></div>");
        }

        // Mostrar artículos adicionales
        var itemsHtml = '';
        // Asegurarse de que los datos de grupos y artículos están disponibles
        if (typeof wp_booking_ajax.service_groups !== 'undefined' && typeof wp_booking_ajax.group_items !== 'undefined') {
            var serviceGroups = wp_booking_ajax.service_groups[service.id] || [];
            if (serviceGroups.length > 0) {
                serviceGroups.forEach(function(group) {
                    var groupItems = wp_booking_ajax.group_items[group.id] || [];
                    if (groupItems.length > 0) {
                        itemsHtml += '<div class="wp-booking-item-group">';
                        itemsHtml += '<h5>' + (group.name || 'Artículos Adicionales') + '</h5>';
                        groupItems.forEach(function(item) {
                            itemsHtml += '<div class="wp-booking-item">';
                            itemsHtml += '<label>';
                            itemsHtml += '<input type="checkbox" class="wp-booking-item-checkbox" name="items[]" value="' + item.id + '" data-price="' + (item.price || 0) + '">';
                            itemsHtml += '<span class="wp-booking-item-name">' + (item.name || 'Artículo sin nombre') + '</span>';
                            itemsHtml += '<span class="wp-booking-item-price">' + parseFloat(item.price || 0).toFixed(2) + ' €</span>';
                            itemsHtml += '</label>';
                            itemsHtml += '</div>';
                        });
                        itemsHtml += '</div>';
                    }
                });
            }
        }
        
        if (itemsHtml) {
            $('#wp-booking-items-list').html(itemsHtml);
            $('#wp-booking-items-container').show();
        } else {
            $('#wp-booking-items-list').html('<p>No hay artículos adicionales disponibles para este servicio.</p>');
            $('#wp-booking-items-container').show(); // Mostrar aunque esté vacío para consistencia
        }
        
        // Resetear formulario y actualizar precio total
        $('#wp-booking-form')[0].reset();
        updateTotalPrice();
        
        // Mostrar modal
        $('#wp-booking-service-modal').show();
    }
    
    // Función para cerrar todos los modales
    function closeAllModals() {
        $('.wp-booking-modal').hide();
    }
    
    // Función para mostrar error en modal específico
    function showError(message) {
        $('#wp-booking-error-message').text(message);
        $('#wp-booking-error-modal').show();
    }
    
    // Función para actualizar el precio total
    function updateTotalPrice() {
        var serviceId = $('#wp-booking-service-id').val();
        var numPeople = parseInt($('#wp-booking-num-people').val()) || 1;
        
        // Asegurarse de que los datos de servicios están disponibles
        if (typeof wp_booking_ajax.services === 'undefined') return;
        var service = wp_booking_ajax.services.find(function(s) { return s.id == serviceId; });
        
        if (!service) return;
        
        // Calcular precio base
        var basePrice = parseFloat(service.price || 0) * numPeople;
        
        // Añadir precio de artículos seleccionados
        var itemsPrice = 0;
        $('.wp-booking-item-checkbox:checked').each(function() {
            itemsPrice += parseFloat($(this).data('price') || 0);
        });
        
        // Actualizar precio total
        var totalPrice = basePrice + itemsPrice;
        $('#wp-booking-total-price').text(totalPrice.toFixed(2) + ' €');
    }
});
