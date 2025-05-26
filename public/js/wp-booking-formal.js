/**
 * Scripts para el área pública del plugin WP Booking con diseño formal.
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
        console.log('WP Booking Plugin: Inicializando scripts formales');
        
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
        $('.booking-filter-btn').on('click', function() {
            var category = $(this).data('category');
            
            // Actualizar botones activos
            $('.booking-filter-btn').removeClass('active');
            $(this).addClass('active');
            
            // Mostrar indicador de carga
            $('.booking-loading').fadeIn(300);
            
            // Simular carga (para efecto visual)
            setTimeout(function() {
                if (category === 'all') {
                    $('.booking-service-card').fadeIn(300);
                } else {
                    $('.booking-service-card').hide();
                    $('.booking-service-card[data-category="' + category + '"]').fadeIn(300);
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
        $('.booking-form-price').text(totalPrice.toFixed(2) + ' €');
    }

    /**
     * Valida el formulario de reserva
     * @return {boolean} True si el formulario es válido, false en caso contrario
     */
    function validateBookingForm() {
        var $form = $('#wp-booking-form');
        var isValid = true;
        
        // Limpiar mensajes de error previos
        $form.find('.booking-form-input').removeClass('is-invalid');
        $form.find('.booking-form-error').empty();
        
        // Validar nombre
        var $name = $form.find('#customer_name');
        if ($name.val().trim() === '') {
            showFieldError($name, 'El nombre es obligatorio');
            isValid = false;
        } else {
            clearFieldError($name);
        }
        
        // Validar email
        var $email = $form.find('#customer_email');
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if ($email.val().trim() === '') {
            showFieldError($email, 'El email es obligatorio');
            isValid = false;
        } else if (!emailRegex.test($email.val().trim())) {
            showFieldError($email, 'El email no es válido');
            isValid = false;
        } else {
            clearFieldError($email);
        }
        
        // Validar teléfono
        var $phone = $form.find('#customer_phone');
        if ($phone.val().trim() === '') {
            showFieldError($phone, 'El teléfono es obligatorio');
            isValid = false;
        } else {
            clearFieldError($phone);
        }
        
        // Validar número de personas
        var $numPeople = $form.find('#num_people');
        var numPeopleValue = parseInt($numPeople.val(), 10);
        var maxCapacity = parseInt($numPeople.attr('max'), 10) || 0;
        
        if (isNaN(numPeopleValue) || numPeopleValue < 1) {
            showFieldError($numPeople, 'El número de personas debe ser al menos 1');
            isValid = false;
        } else if (maxCapacity > 0 && numPeopleValue > maxCapacity) {
            showFieldError($numPeople, 'Excede la disponibilidad máxima de ' + maxCapacity + ' personas');
            isValid = false;
        } else {
            clearFieldError($numPeople);
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
