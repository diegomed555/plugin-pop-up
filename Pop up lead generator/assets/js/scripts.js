jQuery(document).ready(function ($) {
    // Mostrar el pop-up después de 10 segundos en la página
    setTimeout(function () {
        $('#plg-popup').removeClass('hidden');
    }, 10000);

    // Cerrar el pop-up cuando se hace clic en la 'x'
    $('.plg-close').on('click', function () {
        $('#plg-popup').addClass('hidden');
    });

    // Captura el envío del formulario mediante AJAX
    $('#plg-lead-form').on('submit', function (e) {
        e.preventDefault();

        // Enviar los datos usando AJAX
        $.ajax({
            url: plg_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'plg_capture_lead',
                name: $('input[name="name"]').val(),
                email: $('input[name="email"]').val()
            },
            success: function (response) {
                if (response.success) {
                    alert(response.data);
                    $('#plg-popup').addClass('hidden');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function () {
                alert('Ocurrió un error al enviar los datos.');
            }
        });
    });
});
