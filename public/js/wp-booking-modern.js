/**
 * Scripts para el área pública del plugin WP Booking con diseño moderno.
 *
 * @since      1.0.0
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/public
 */

(function($) {
    'use strict';

    /**
     * Inicialización cuando el DOM está listo
     */
    $(document).ready(function() {
        console.log('WP Booking Plugin: Inicializando scripts modernos');
        
        // Inicializar funcionalidad de reservas
        initializeBookingFunctionality();
    });

    /**
     * Inicializa la funcionalidad principal de reservas
     */
    function initializeBookingFunctionality() {
        // Probar conexión AJAX
        testAjaxConnection();
        
        // Inicializar filtros de categorías
        initializeCategoryFilters();
        
        // Inicializar botones de reserva
        initializeBookingButtons();
        
        // Inicializar formulario de reserva
        initializeBookingForm();
        
        // Inicializar modales
        initializeModals();
        
        // Inicializar efectos visuales
        initializeVisualEffects();
    }

    /**
     * Prueba la conexión AJAX para verificar que todo funciona correctamente
     */
    function testAjaxConnection() {
        if (typeof wp_booking_ajax === 'undefined') {
            console.error('Error: Variables AJAX no definidas');
            return;
        }

        $.ajax({
            url: wp_booking_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_booking_test_connection',
                nonce: wp_booking_ajax.nonce
            },
            success: function(response) {
                console.log('Conexión AJAX exitosa:', response);
            },
            error: function(xhr, status, error) {
                console.error('Error de conexión AJAX:', error);
            }
        });
    }

    /**
     * Inicializa los filtros de categorías
     */
    function initializeCategoryFilters() {
        $('.wp-booking-filter').on('click', function() {
            var category = $(this).data('category');
            
            // Actualizar botones activos
            $('.wp-booking-filter').removeClass('active');
            $(this).addClass('active');
            
            // Mostrar indicador de carga
            $('.wp-booking-loading').fadeIn(300);
            
            // Simular carga (para efecto visual)
            setTimeout(function() {
                if (category === 'all') {
                    $('.wp-booking-service').fadeIn(300);
                } else {
                    $('.wp-booking-service').hide();
                    $('.wp-booking-service[data-category="' + category + '"]').fadeIn(300);
                }
                
                // Ocultar indicador de carga
                $('.wp-booking-loading').fadeOut(300);
            }, 500);
        });
    }

    /**
     * Inicializa los botones de reserva
     */
    function initializeBookingButtons() {
        // Botón de reserva en tarjetas de servicio
        $('.wp-booking-service-button').on('click', function() {
            var serviceId = $(this).data('id');
            loadServiceDetails(serviceId);
        });
        
        // Botón de volver al catálogo
        $('#wp-booking-back-to-catalog').on('click', function(e) {
            e.preventDefault();
            $('#wp-booking-service-details').hide();
            $('.wp-booking-catalog').show();
        });
    }

    /**
     * Carga los detalles de un servicio
     * @param {number} serviceId - ID del servicio a cargar
     */
    function loadServiceDetails(serviceId) {
        // Mostrar indicador de carga
        $('.wp-booking-loading').fadeIn(300);
        
        // Obtener datos del servicio
        var $service = $('.wp-booking-service[data-id="' + serviceId + '"]');
        var title = $service.find('.wp-booking-service-title').text();
        var category = $service.find('.wp-booking-service-category').text();
        var date = $service.find('.wp-booking-service-meta-item:first-child').text().trim();
        var capacity = $service.find('.wp-booking-service-meta-item:nth-child(2)').text().trim();
        var price = $service.find('.wp-booking-service-price').text().trim();
        var imageUrl = $service.find('.wp-booking-service-image img').attr('src');
        
        // Actualizar detalles del servicio
        $('#wp-booking-service-details-title').text(title);
        $('#wp-booking-service-details-category').text(category);
        $('#wp-booking-service-details-date').text(date);
        $('#wp-booking-service-details-capacity').text(capacity);
        $('#wp-booking-service-details-price').text(price);
        $('#wp-booking-service-details-img').attr('src', imageUrl);
        
        // Actualizar formulario
        $('#service_id').val(serviceId);
        $('#wp-booking-total-price').text(price.replace(/[^\d.,]/g, '').replace(',', '.'));
        
        // Cargar artículos adicionales (si existen)
        loadAdditionalItems(serviceId);
        
        // Ocultar catálogo y mostrar detalles
        $('.wp-booking-catalog').hide();
        $('#wp-booking-service-details').fadeIn(300);
        
        // Ocultar indicador de carga
        $('.wp-booking-loading').fadeOut(300);
        
        // Scroll al inicio de los detalles
        $('html, body').animate({
            scrollTop: $('#wp-booking-service-details').offset().top - 50
        }, 500);
    }

    /**
     * Carga los artículos adicionales para un servicio
     * @param {number} serviceId - ID del servicio
     */
    function loadAdditionalItems(serviceId) {
        // Simulación de artículos adicionales
        // En un caso real, estos datos vendrían de una llamada AJAX
        var items = [
            { id: 1, name: 'Seguro de viaje', price: 15.00 },
            { id: 2, name: 'Guía turístico', price: 25.00 },
            { id: 3, name: 'Transporte privado', price: 35.00 }
        ];
        
        if (items.length > 0) {
            var itemsHtml = '';
            
            $.each(items, function(index, item) {
                itemsHtml += '<div class="wp-booking-item">';
                itemsHtml += '<input type="checkbox" class="wp-booking-item-checkbox" id="item_' + item.id + '" name="items[]" value="' + item.id + '">';
                itemsHtml += '<div class="wp-booking-item-info">';
                itemsHtml += '<label for="item_' + item.id + '" class="wp-booking-item-name">' + item.name + '</label>';
                itemsHtml += '<div class="wp-booking-item-price">€ ' + item.price.toFixed(2) + '</div>';
                itemsHtml += '</div>';
                itemsHtml += '<div class="wp-booking-item-quantity">';
                itemsHtml += '<input type="number" class="wp-booking-item-quantity-input" name="quantities[]" min="1" value="1" disabled>';
                itemsHtml += '</div>';
                itemsHtml += '</div>';
            });
            
            $('#wp-booking-items-list').html(itemsHtml);
            $('#wp-booking-items').show();
            
            // Inicializar eventos de artículos
            initializeItemEvents();
        } else {
            $('#wp-booking-items').hide();
        }
    }

    /**
     * Inicializa eventos para los artículos adicionales
     */
    function initializeItemEvents() {
        // Manejar cambios en checkboxes de artículos
        $('.wp-booking-item-checkbox').on('change', function() {
            var $quantityInput = $(this).closest('.wp-booking-item').find('.wp-booking-item-quantity-input');
            $quantityInput.prop('disabled', !this.checked);
            
            if (this.checked) {
                $quantityInput.focus();
            }
            
            // Actualizar precio total
            updateTotalPrice();
        });
        
        // Calcular precio total cuando cambian las cantidades
        $('.wp-booking-item-quantity-input').on('change', function() {
            updateTotalPrice();
        });
    }

    /**
     * Inicializa el formulario de reserva
     */
    function initializeBookingForm() {
        var $form = $('#wp-booking-form');
        
        if ($form.length === 0) {
            return;
        }
        
        // Manejar envío del formulario
        $form.on('submit', function(e) {
            e.preventDefault();
            
            // Validar formulario
            if (!validateBookingForm()) {
                return;
            }
            
            // Mostrar modal de procesamiento
            showModal('processingModal');
            
            // Recopilar datos del formulario
            var formData = $(this).serialize();
            formData += '&action=wp_booking_make_reservation';
            formData += '&nonce=' + wp_booking_ajax.nonce;
            
            // Enviar solicitud AJAX
            $.ajax({
                url: wp_booking_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    hideModal('processingModal');
                    
                    if (response.success) {
                        // Mostrar modal de éxito
                        $('#successModal .wp-booking-modal-message').text(response.data.message || 'Reserva realizada con éxito');
                        showModal('successModal');
                        
                        // Limpiar formulario después de éxito
                        $form[0].reset();
                        
                        // Redirigir a la página principal después de 3 segundos
                        setTimeout(function() {
                            window.location.href = window.location.href.split('?')[0];
                        }, 3000);
                    } else {
                        // Mostrar modal de error
                        $('#errorModal .wp-booking-modal-message').text(response.data.message || 'Ha ocurrido un error al procesar la reserva.');
                        showModal('errorModal');
                    }
                },
                error: function(xhr, status, error) {
                    hideModal('processingModal');
                    
                    // Mostrar modal de error
                    $('#errorModal .wp-booking-modal-message').text('Error de conexión. Por favor, inténtalo de nuevo más tarde.');
                    showModal('errorModal');
                }
            });
        });
        
        // Calcular precio total cuando cambia el número de personas
        $('#num_people').on('change', function() {
            updateTotalPrice();
        });
        
        // Validación en tiempo real
        $('.wp-booking-form-input').on('blur', function() {
            validateField($(this));
        });
    }

    /**
     * Actualiza el precio total basado en las selecciones
     */
    function updateTotalPrice() {
        var basePrice = parseFloat($('#wp-booking-service-details-price').text().replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
        var numPeople = parseInt($('#num_people').val()) || 1;
        var totalPrice = basePrice * numPeople;
        
        // Sumar precios de artículos adicionales seleccionados
        $('.wp-booking-item-checkbox:checked').each(function() {
            var $item = $(this).closest('.wp-booking-item');
            var itemPrice = parseFloat($item.find('.wp-booking-item-price').text().replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
            var quantity = parseInt($item.find('.wp-booking-item-quantity-input').val()) || 1;
            totalPrice += itemPrice * quantity;
        });
        
        // Actualizar precio mostrado
        $('#wp-booking-total-price').text(totalPrice.toFixed(2));
    }

    /**
     * Inicializa los modales
     */
    function initializeModals() {
        // Cerrar modal al hacer clic en el botón de cerrar o en el botón de aceptar
        $('.wp-booking-modal-close, .wp-booking-modal-btn').on('click', function() {
            $(this).closest('.wp-booking-modal-overlay').removeClass('active');
        });
        
        // Cerrar modal al hacer clic fuera del contenido
        $('.wp-booking-modal-overlay').on('click', function(e) {
            if ($(e.target).hasClass('wp-booking-modal-overlay')) {
                $(this).removeClass('active');
            }
        });
    }
    
    /**
     * Inicializa efectos visuales adicionales
     */
    function initializeVisualEffects() {
        // Efecto de hover en tarjetas de servicios
        $('.wp-booking-service').hover(
            function() {
                $(this).find('.wp-booking-service-image img').css('transform', 'scale(1.05)');
            },
            function() {
                $(this).find('.wp-booking-service-image img').css('transform', 'scale(1)');
            }
        );
        
        // Animación de entrada para elementos
        $('.wp-booking-service').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(20px)'
            });
            
            setTimeout(function() {
                $(this).css({
                    'opacity': '1',
                    'transform': 'translateY(0)',
                    'transition': 'opacity 0.5s ease, transform 0.5s ease'
                });
            }.bind(this), 100 * index);
        });
    }

    /**
     * Muestra un modal
     * @param {string} modalId - ID del modal a mostrar
     */
    function showModal(modalId) {
        $('#' + modalId).addClass('active');
    }

    /**
     * Oculta un modal
     * @param {string} modalId - ID del modal a ocultar
     */
    function hideModal(modalId) {
        $('#' + modalId).removeClass('active');
    }

    /**
     * Valida el formulario de reserva
     * @return {boolean} True si el formulario es válido, false en caso contrario
     */
    function validateBookingForm() {
        var $form = $('#wp-booking-form');
        var isValid = true;
        
        // Validar cada campo
        $form.find('.wp-booking-form-input').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    /**
     * Valida un campo específico
     * @param {jQuery} $field - El campo a validar
     * @return {boolean} True si el campo es válido, false en caso contrario
     */
    function validateField($field) {
        var fieldId = $field.attr('id');
        var value = $field.val().trim();
        var isValid = true;
        
        // Limpiar estado previo
        clearFieldError($field);
        
        // Validar según el tipo de campo
        switch (fieldId) {
            case 'customer_name':
                if (value === '') {
                    showFieldError($field, 'El nombre es obligatorio');
                    isValid = false;
                }
                break;
                
            case 'customer_email':
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (value === '') {
                    showFieldError($field, 'El email es obligatorio');
                    isValid = false;
                } else if (!emailRegex.test(value)) {
                    showFieldError($field, 'El email no es válido');
                    isValid = false;
                }
                break;
                
            case 'customer_phone':
                if (value === '') {
                    showFieldError($field, 'El teléfono es obligatorio');
                    isValid = false;
                }
                break;
                
            case 'num_people':
                var numPeopleValue = parseInt(value, 10);
                
                if (isNaN(numPeopleValue) || numPeopleValue < 1) {
                    showFieldError($field, 'El número de personas debe ser al menos 1');
                    isValid = false;
                }
                break;
        }
        
        return isValid;
    }

    /**
     * Muestra un mensaje de error para un campo
     * @param {jQuery} $field El campo con error
     * @param {string} message El mensaje de error
     */
    function showFieldError($field, message) {
        $field.addClass('is-invalid');
        $field.next('.wp-booking-form-error').text(message);
    }

    /**
     * Elimina el mensaje de error de un campo
     * @param {jQuery} $field El campo a limpiar
     */
    function clearFieldError($field) {
        $field.removeClass('is-invalid');
        $field.next('.wp-booking-form-error').empty();
    }

})(jQuery);
