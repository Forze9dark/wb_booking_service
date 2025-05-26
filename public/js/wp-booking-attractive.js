/**
 * Scripts para el área pública del plugin WP Booking con diseño atractivo.
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
        console.log('WP Booking Plugin: Inicializando scripts atractivos');
        
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
        $('.booking-filter').on('click', function() {
            var category = $(this).data('category');
            
            // Actualizar botones activos
            $('.booking-filter').removeClass('active');
            $(this).addClass('active');
            
            // Mostrar indicador de carga
            $('.booking-loading').fadeIn(300);
            
            // Simular carga (para efecto visual)
            setTimeout(function() {
                if (category === 'all') {
                    $('.booking-service').fadeIn(300);
                } else {
                    $('.booking-service').hide();
                    $('.booking-service[data-category="' + category + '"]').fadeIn(300);
                }
                
                // Ocultar indicador de carga
                $('.booking-loading').fadeOut(300);
            }, 500);
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
            console.log('Formulario de reserva enviado');
            
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
            
            console.log('Datos del formulario:', formData);
            
            // Enviar solicitud AJAX
            $.ajax({
                url: wp_booking_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    console.log('Respuesta de reserva:', response);
                    hideModal('processingModal');
                    
                    if (response.success) {
                        // Mostrar modal de éxito
                        $('#successModal .booking-modal-message').text(response.data.message || 'Reserva realizada con éxito');
                        showModal('successModal');
                        
                        // Limpiar formulario después de éxito
                        $form[0].reset();
                        
                        // Redirigir a la página principal después de 3 segundos
                        setTimeout(function() {
                            window.location.href = window.location.href.split('?')[0];
                        }, 3000);
                    } else {
                        // Mostrar modal de error
                        $('#errorModal .booking-modal-message').text(response.data.message || 'Ha ocurrido un error al procesar la reserva.');
                        showModal('errorModal');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', error);
                    hideModal('processingModal');
                    
                    // Mostrar modal de error
                    $('#errorModal .booking-modal-message').text('Error de conexión. Por favor, inténtalo de nuevo más tarde.');
                    showModal('errorModal');
                }
            });
        });
        
        // Manejar cambios en checkboxes de artículos
        $('.booking-item-checkbox').on('change', function() {
            var $quantityInput = $(this).closest('.booking-item').find('.booking-item-quantity-input');
            $quantityInput.prop('disabled', !this.checked);
            
            if (this.checked) {
                $quantityInput.focus();
            }
            
            // Actualizar precio total
            updateTotalPrice();
        });
        
        // Inicializar todos los checkboxes
        $('.booking-item-checkbox').each(function() {
            var $quantityInput = $(this).closest('.booking-item').find('.booking-item-quantity-input');
            $quantityInput.prop('disabled', !this.checked);
        });
        
        // Calcular precio total cuando cambian las cantidades o selecciones
        $('.booking-item-quantity-input, .booking-item-checkbox, #num_people').on('change', function() {
            updateTotalPrice();
        });
        
        // Inicializar precio total
        updateTotalPrice();
        
        // Validación en tiempo real
        $('.booking-form-input').on('blur', function() {
            validateField($(this));
        });
    }

    /**
     * Inicializa los modales
     */
    function initializeModals() {
        // Cerrar modal al hacer clic en el botón de cerrar o en el botón de aceptar
        $('.booking-modal-close, .booking-modal-btn').on('click', function() {
            $(this).closest('.booking-modal-overlay').removeClass('active');
        });
        
        // Cerrar modal al hacer clic fuera del contenido
        $('.booking-modal-overlay').on('click', function(e) {
            if ($(e.target).hasClass('booking-modal-overlay')) {
                $(this).removeClass('active');
            }
        });
    }
    
    /**
     * Inicializa efectos visuales adicionales
     */
    function initializeVisualEffects() {
        // Efecto de hover en tarjetas de servicios
        $('.booking-service').hover(
            function() {
                $(this).find('.booking-service-image img').css('transform', 'scale(1.05)');
            },
            function() {
                $(this).find('.booking-service-image img').css('transform', 'scale(1)');
            }
        );
        
        // Animación de entrada para elementos
        $('.booking-service').each(function(index) {
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
     * Actualiza el precio total basado en las selecciones
     */
    function updateTotalPrice() {
        var $form = $('#wp-booking-form');
        if ($form.length === 0) return;
        
        var basePrice = parseFloat($('.booking-form-price').text().replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
        var numPeople = parseInt($('#num_people').val()) || 1;
        var totalPrice = basePrice * numPeople;
        
        // Sumar precios de artículos adicionales seleccionados
        $('.booking-item-checkbox:checked').each(function() {
            var $item = $(this).closest('.booking-item');
            var itemPrice = parseFloat($item.find('.booking-item-price').text().replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
            var quantity = parseInt($item.find('.booking-item-quantity-input').val()) || 1;
            totalPrice += itemPrice * quantity;
        });
        
        // Actualizar precio mostrado
        $('.booking-form-price').html('<span class="booking-form-price-currency">€</span> ' + totalPrice.toFixed(2));
    }

    /**
     * Valida el formulario de reserva
     * @return {boolean} True si el formulario es válido, false en caso contrario
     */
    function validateBookingForm() {
        var $form = $('#wp-booking-form');
        var isValid = true;
        
        // Validar cada campo
        $form.find('.booking-form-input').each(function() {
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
                var maxCapacity = parseInt($field.attr('max'), 10) || 0;
                
                if (isNaN(numPeopleValue) || numPeopleValue < 1) {
                    showFieldError($field, 'El número de personas debe ser al menos 1');
                    isValid = false;
                } else if (maxCapacity > 0 && numPeopleValue > maxCapacity) {
                    showFieldError($field, 'Excede la disponibilidad máxima de ' + maxCapacity + ' personas');
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
        $field.next('.booking-form-error').text(message);
    }

    /**
     * Elimina el mensaje de error de un campo
     * @param {jQuery} $field El campo a limpiar
     */
    function clearFieldError($field) {
        $field.removeClass('is-invalid');
        $field.next('.booking-form-error').empty();
    }

})(jQuery);
