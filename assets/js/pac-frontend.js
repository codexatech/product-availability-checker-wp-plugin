(function ($) {
    'use strict';

    $(document).ready(function () {

        var $zipInput = $('#pac-zip');
        var $checkBtn = $('#pac-check-btn');
        var $result = $('#pac-result');
        var $cartBtn = $('.single_add_to_cart_button');

        // Hidden input to store ZIP for Add to Cart
        var $hiddenZip = $('#pac-zip-hidden');

        $checkBtn.on('click', function (e) {
            e.preventDefault();

            var zip = $zipInput.val().trim();

            if (!zip) {
                $result.text(PAC_Ajax.unavailable_text);
                $hiddenZip.val('');
                $cartBtn.prop('disabled', false);
                return;
            }

            $result.text('Checking...');

            $.post(PAC_Ajax.url, {
                action: 'pac_check_zip',
                zip: zip,
                nonce: PAC_Ajax.nonce
            })
                .done(function (response) {
                    if (response.success) {
                        $result.text(response.data.message);

                        // Update hidden input for Add to Cart
                        $hiddenZip.val(zip);

                        // Disable Add to Cart if unavailable
                        $cartBtn.prop('disabled', response.data.status === 'unavailable');

                    } else {
                        $result.text(response.data.message || PAC_Ajax.unavailable_text);
                        $hiddenZip.val('');
                        $cartBtn.prop('disabled', false);
                    }
                })
                .fail(function () {
                    $result.text(PAC_Ajax.unavailable_text);
                    $hiddenZip.val('');
                    $cartBtn.prop('disabled', false);
                });
        });

    });

})(jQuery);
