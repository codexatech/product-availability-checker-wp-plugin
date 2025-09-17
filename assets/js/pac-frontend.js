(function ($) {

    $('#pac-check-btn').on('click', function (e) {
        e.preventDefault();

        var zip = $('#pac-zip').val().trim();

        if (!zip) {
            $('#pac-result').text(PAC_Ajax.unavailable_text);
            return;
        }

        $('#pac-result').text('Checking...');

        $.post(PAC_Ajax.url, {
            action: 'pac_check_zip',
            zip: zip,
            nonce: PAC_Ajax.nonce
        }, function (response) {
            if (response.success) {
                $('#pac-result').text(response.data.message);
                $('.single_add_to_cart_button').prop('disabled', response.data.status === 'unavailable');
            } else {
                $('#pac-result').text(response.data.message || PAC_Ajax.unavailable_text);
            }
        }).fail(function () {
            $('#pac-result').text(PAC_Ajax.unavailable_text);
        });
    });

})(jQuery);
