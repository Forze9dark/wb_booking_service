/**
 * Scripts para el área pública del plugin WP Booking con Bootstrap.
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
        console.log('WP Booking Plugin: Inicializando scripts públicos con Bootstrap');
        
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
        
        // Inicializar tarjetas de servicios
        initializeServiceCards();
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
        $('.filter-btn').on('click', function() {
            var category = $(this).data('category');
            
            // Actualizar botones activos
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            // Mostrar indicador de carga
            $('.loading').removeClass('d-none');
            
            // Simular carga (para efecto visual)
            setTimeout(function() {
                if (category === 'all') {
                    $('.service-card').fadeIn(300);
                } else {
                    $('.service-card').hide();
                    $('.service-card[data-category="' + category + '"]').fadeIn(300);
                }
                
                // Ocultar indicador de carga
                $('.loading').addClass('d-none');
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
            var processingModal = new bootstrap.Modal(document.getElementById('processingModal'));
            processingModal.show();
            
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
                    processingModal.hide();
                    
                    if (response.success) {
                        // Mostrar modal de éxito
                        $('#successModal .modal-message').text(response.data.message);
                        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();
                        
                        // Limpiar formulario después de éxito
                        $form[0].reset();
                        
                        // Redirigir a la página principal después de 3 segundos
                        setTimeout(function() {
                            window.location.href = window.location.href.split('?')[0];
                        }, 3000);
                    } else {
                        // Mostrar modal de error
                        $('#errorModal .modal-message').text(response.data.message || 'Ha ocurrido un error al procesar la reserva.');
                        var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                        errorModal.show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', error);
                    processingModal.hide();
                    
                    // Mostrar modal de error
                    $('#errorModal .modal-message').text('Error de conexión. Por favor, inténtalo de nuevo más tarde.');
                    var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                }
            });
        });
        
        // Manejar cambios en checkboxes de artículos
        $form.find('input[type="checkbox"][name="items[]"]').on('change', function() {
            var $quantityInput = $(this).closest('.item').find('input[name="quantities[]"]');
            $quantityInput.prop('disabled', !this.checked);
            
            if (this.checked) {
                $quantityInput.focus();
            }
        }).trigger('change');
        
        // Inicializar todos los checkboxes
        $form.find('input[type="checkbox"][name="items[]"]').each(function() {
            $(this).trigger('change');
        });
        
        // Calcular precio total cuando cambian las cantidades o selecciones
        $form.find('input[name="quantities[]"], input[name="items[]"], #num_people').on('change', function() {
            updateTotalPrice();
        });
        
        // Inicializar precio total
        updateTotalPrice();
    }

    /**
     * Actualiza el precio total basado en las selecciones
     */
    function updateTotalPrice() {
        var $form = $('#wp-booking-form');
        if ($form.length === 0) return;
        
        var basePrice = parseFloat($('.modal-price').text().replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
        var numPeople = parseInt($('#num_people').val()) || 1;
        var totalPrice = basePrice * numPeople;
        
        // Sumar precios de artículos adicionales seleccionados
        $form.find('input[type="checkbox"][name="items[]"]:checked').each(function() {
            var $item = $(this).closest('.item');
            var itemPriceText = $item.find('label').text().match(/\(([^)]+)\)/)[1];
            var itemPrice = parseFloat(itemPriceText.replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
            var quantity = parseInt($item.find('input[name="quantities[]"]').val()) || 1;
            totalPrice += itemPrice * quantity;
        });
        
        // Actualizar precio mostrado
        $('.modal-price').text(totalPrice.toFixed(2) + ' €');
    }

    /**
     * Inicializa las tarjetas de servicios
     */
    function initializeServiceCards() {
        $('.category-card').hover(
            function() {
                $(this).addClass('shadow-lg');
            },
            function() {
                $(this).removeClass('shadow-lg');
            }
        );
    }

    /**
     * Valida el formulario de reserva
     * @return {boolean} True si el formulario es válido, false en caso contrario
     */
    function validateBookingForm() {
        var $form = $('#wp-booking-form');
        var isValid = true;
        
        // Limpiar mensajes de error previos
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').empty();
        
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
        $field.next('.invalid-feedback').text(message);
    }

    /**
     * Elimina el mensaje de error de un campo
     * @param {jQuery} $field El campo a limpiar
     */
    function clearFieldError($field) {
        $field.removeClass('is-invalid');
        $field.next('.invalid-feedback').empty();
    }

})(jQuery);
