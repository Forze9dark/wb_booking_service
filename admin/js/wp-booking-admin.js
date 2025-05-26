/**
 * Scripts para el área de administración del plugin WP Booking.
 *
 * @since      1.0.0
 * @package    WP_Booking_Plugin
 * @subpackage WP_Booking_Plugin/admin
 */

jQuery(document).ready(function($) {
    'use strict';

    // --- Verificación Inicial --- 
    if (typeof wp_booking_admin_ajax === 'undefined') {
        console.error('WP Booking Admin Error: El objeto wp_booking_admin_ajax no está definido. Verifica wp_localize_script en class-wp-booking-admin.php.');
        // Mostrar un mensaje de error persistente en la interfaz podría ser útil
        $('#wpbody-content .wrap').prepend('<div class="notice notice-error is-dismissible"><p><strong>WP Booking Error:</strong> No se pudo cargar la configuración necesaria para las operaciones. Por favor, contacta al administrador.</p></div>');
        return; // Detener la ejecución si falta la configuración crítica
    }
    console.log('WP Booking Admin: Objeto wp_booking_admin_ajax cargado:', wp_booking_admin_ajax);

    // --- Inicialización --- 
    // Inicializar datepickers (si es necesario)
    // if ($.fn.datepicker) { ... }

    // --- Manejadores de Eventos --- 

    // Mostrar formulario para añadir nuevo servicio
    $('.add-new-service').on('click', function(e) {
        e.preventDefault();
        resetServiceForm();
        $('#service-form-title').text(wp_booking_admin_ajax.l10n.addNewService || 'Añadir nuevo servicio');
        $('#wp-booking-service-form').show();
        $('#col-left').show();
        $('#col-right').removeClass('wp-booking-full-width');
    });
    
    // Cancelar formulario
    $('.cancel-form').on('click', function(e) {
        e.preventDefault();
        $('#wp-booking-service-form').hide();
        $('#col-left').hide();
        $('#col-right').addClass('wp-booking-full-width');
    });
    
    // Editar servicio
    $('#the-list').on('click', '.edit-service', function(e) {
        e.preventDefault();
        var serviceId = $(this).data('id');
        var $formContainer = $('#wp-booking-service-form');
        var $formWrap = $formContainer.find('.form-wrap');
        
        // Mostrar formulario y overlay de carga
        resetServiceForm(); // Limpiar antes de cargar
        $formContainer.show();
        $('#col-left').show();
        $('#col-right').removeClass('wp-booking-full-width');
        $formWrap.prepend('<div class="loading-overlay"><span class="spinner is-active" style="float:none; display:inline-block;"></span></div>');
        $('#service-form-title').text(wp_booking_admin_ajax.l10n.editService || 'Editar Servicio');
        
        // Obtener datos del servicio mediante AJAX
        $.ajax({
            url: wp_booking_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_booking_get_service',
                nonce: wp_booking_admin_ajax.get_service_nonce,
                id: serviceId
            },
            dataType: 'json', // Esperar JSON
            success: function(response) {
                if (response.success) {
                    populateServiceForm(response.data);
                } else {
                    alert('Error al cargar: ' + (response.data.message || 'Error desconocido'));
                    console.error('Error en ajax_get_service:', response.data);
                    $('#wp-booking-service-form').hide(); // Ocultar si falla la carga
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error de conexión al cargar los datos del servicio: ' + textStatus);
                console.error("AJAX Error [get_service]: ", textStatus, errorThrown, jqXHR.responseText);
                $('#wp-booking-service-form').hide(); // Ocultar si falla la carga
            },
            complete: function() {
                // Eliminar indicador de carga
                $formWrap.find('.loading-overlay').remove();
            }
        });
    });
    
    // Eliminar servicio
    $('#the-list').on('click', '.delete-service', function(e) {
        e.preventDefault();
        if (confirm(wp_booking_admin_ajax.l10n.confirmDelete || '¿Estás seguro de que quieres eliminar este servicio?')) {
            var serviceId = $(this).data('id');
            var $row = $(this).closest('tr');
            
            // Mostrar indicador visual
            $row.css('opacity', '0.5');
            
            $.ajax({
                url: wp_booking_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_booking_delete_service',
                    nonce: wp_booking_admin_ajax.delete_service_nonce,
                    id: serviceId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() { $(this).remove(); });
                        // Opcional: mostrar un notice de éxito
                    } else {
                        alert('Error al eliminar: ' + (response.data.message || 'Error desconocido'));
                        console.error('Error en ajax_delete_service:', response.data);
                        $row.css('opacity', '1');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error de conexión al eliminar el servicio: ' + textStatus);
                    console.error("AJAX Error [delete_service]: ", textStatus, errorThrown, jqXHR.responseText);
                    $row.css('opacity', '1');
                }
            });
        }
    });
    
    // Subir/Seleccionar imagen principal
    $('#upload-main-image').on('click', function(e) {
        e.preventDefault();
        var frame = wp.media({
            title: wp_booking_admin_ajax.l10n.selectImageTitle || 'Seleccionar imagen principal',
            button: {
                text: wp_booking_admin_ajax.l10n.useThisImage || 'Usar esta imagen'
            },
            multiple: false
        });
        
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#service-main-image-id').val(attachment.id);
            $('#main-image-preview').html('<img src="' + attachment.url + '" alt="" style="max-width: 150px; max-height: 150px; display: block; margin-bottom: 10px;">');
            $('#remove-main-image').show();
        });
        
        frame.open();
    });

    // Eliminar imagen principal
    $('#remove-main-image').on('click', function(e) {
        e.preventDefault();
        $('#service-main-image-id').val(0);
        $('#main-image-preview').empty();
        $(this).hide();
    });
    
    // Enviar formulario de servicio (Crear/Actualizar)
    $('#wp-booking-service-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitButton = $form.find('#submit');
        var $spinner = $form.find('.spinner');

        // --- Validación Frontend --- 
        var title = $('#service-title').val().trim();
        var category = $('#service-category').val();
        var price = $('#service-price').val();
        
        if (!title) {
            alert(wp_booking_admin_ajax.l10n.errorTitleRequired || 'El título del servicio es obligatorio.');
            $('#service-title').focus();
            return;
        }
        if (!category) {
            alert(wp_booking_admin_ajax.l10n.errorCategoryRequired || 'Debes seleccionar una categoría.');
            $('#service-category').focus();
            return;
        }
        if (price === '' || isNaN(parseFloat(price)) || parseFloat(price) < 0) {
            alert(wp_booking_admin_ajax.l10n.errorPriceInvalid || 'El precio debe ser un número válido mayor o igual a cero.');
            $('#service-price').focus();
            return;
        }
        // Añadir más validaciones si es necesario (fecha, capacidad, etc.)
        
        // Mostrar indicador de carga
        $spinner.addClass('is-active');
        $submitButton.prop('disabled', true);
        
        // Obtener datos del formulario (serialize() incluye el nonce si está como hidden input)
        var formData = $form.serialize();
        formData += '&action=wp_booking_save_service'; // Añadir la acción AJAX
        
        $.ajax({
            url: wp_booking_admin_ajax.ajax_url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Servicio guardado correctamente.');
                    location.reload(); // Recargar para ver cambios en la tabla
                } else {
                    alert('Error al guardar: ' + (response.data.message || 'Error desconocido'));
                    console.error('Error en ajax_save_service:', response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error de conexión al guardar el servicio: ' + textStatus);
                console.error("AJAX Error [save_service]: ", textStatus, errorThrown, jqXHR.responseText);
            },
            complete: function() {
                // Ocultar indicador de carga
                $spinner.removeClass('is-active');
                $submitButton.prop('disabled', false);
            }
        });
    });

    // --- Funciones Auxiliares --- 

    // Función para resetear el formulario de servicio
    function resetServiceForm() {
        var $form = $('#wp-booking-service-form');
        $form[0].reset();
        $form.find('#service-id').val(0);
        $form.find('#service-category').val(''); // Asegurar reset del select
        $form.find('#service-status').val(1); // Valor por defecto
        $form.find('#main-image-preview').empty();
        $form.find('#remove-main-image').hide();
        $form.find('#service-form-title').text(wp_booking_admin_ajax.l10n.addNewService || 'Añadir nuevo servicio');
        $form.find('.form-wrap .loading-overlay').remove(); // Limpiar overlays si quedaron
    }

    // Función para llenar el formulario con datos del servicio
    function populateServiceForm(data) {
        var service = data.service;
        var groupIds = data.group_ids;
        var mainImageId = data.main_image_id;
        var mainImageUrl = data.main_image_url;

        $('#service-id').val(service.id);
        $('#service-title').val(service.title);
        $('#service-category').val(service.category_id);
        $('#service-description').val(service.description);
        $('#service-price').val(service.price);
        
        // Formatear fecha y hora para datetime-local
        var formattedDate = '';
        if (service.service_date && service.service_date !== '0000-00-00 00:00:00') {
            try {
                var dateStr = service.service_date.replace(' ', 'T');
                var dateObj = new Date(dateStr);
                if (!isNaN(dateObj.getTime())) {
                    var year = dateObj.getFullYear();
                    var month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
                    var day = dateObj.getDate().toString().padStart(2, '0');
                    var hours = dateObj.getHours().toString().padStart(2, '0');
                    var minutes = dateObj.getMinutes().toString().padStart(2, '0');
                    formattedDate = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
                }
            } catch (e) { console.error("Error parsing date for form: ", service.service_date, e); }
        }
        $('#service-date').val(formattedDate);
        
        $('#service-max-capacity').val(service.max_capacity);
        $('#service-status').val(service.status);
        $('#service-enable-qr').prop('checked', service.enable_qr == 1);
        
        // Imagen principal
        $('#service-main-image-id').val(mainImageId || 0);
        if (mainImageUrl) {
            $('#main-image-preview').html('<img src="' + mainImageUrl + '" alt="" style="max-width: 150px; max-height: 150px; display: block; margin-bottom: 10px;">');
            $('#remove-main-image').show();
        } else {
            $('#main-image-preview').empty();
            $('#remove-main-image').hide();
        }
        
        // Grupos de artículos
        $('input[name="item_groups[]"]').prop('checked', false);
        if (groupIds && groupIds.length > 0) {
            groupIds.forEach(function(groupId) {
                $('input[name="item_groups[]"][value="' + groupId + '"]').prop('checked', true);
            });
        }
    }

    // --- Inicialización de la Interfaz --- 

    // Ocultar formulario al inicio si no se está editando
    if ($('#service-id').val() == 0) {
         $('#col-left').hide();
         $('#col-right').addClass('wp-booking-full-width');
    }

    // Asegurar que l10n exista (fallback por si acaso)
    if (typeof wp_booking_admin_ajax.l10n === 'undefined') {
        console.warn('WP Booking Admin: Textos localizados (l10n) no encontrados en wp_booking_admin_ajax. Usando valores por defecto.');
        wp_booking_admin_ajax.l10n = {
            addNewService: 'Añadir nuevo servicio',
            editService: 'Editar Servicio',
            confirmDelete: '¿Estás seguro de que quieres eliminar este servicio?',
            selectImageTitle: 'Seleccionar imagen principal',
            useThisImage: 'Usar esta imagen',
            errorTitleRequired: 'El título del servicio es obligatorio.',
            errorCategoryRequired: 'Debes seleccionar una categoría.',
            errorPriceInvalid: 'El precio debe ser un número válido mayor o igual a cero.',
            errorSaving: 'Error al guardar el servicio.',
            errorLoading: 'Error al cargar los datos del servicio.',
            errorDeleting: 'Error al eliminar el servicio.',
            errorConnection: 'Error de conexión. Por favor, inténtalo de nuevo.'
        };
    }

});

