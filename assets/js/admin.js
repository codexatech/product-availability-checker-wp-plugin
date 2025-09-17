(function ($) {
    'use strict';

    $(document).ready(function () {

        var $tableBody = $('#pac-zip-table tbody');

        // Add new zip code row
        $('#pac-add-row').on('click', function () {
            var uniqueId = Date.now();
            var row = `
                <tr>
                    <td><input type="text" name="pac_rules[new_${uniqueId}][zip]" /></td>
                    <td>
                        <select name="pac_rules[new_${uniqueId}][status]">
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </td>
                    <td><input type="text" name="pac_rules[new_${uniqueId}][message]" /></td>
                    <td><button type="button" class="button remove-zip">${PAC_Admin.remove_text}</button></td>
                </tr>`;
            $tableBody.append(row);
        });

        // Remove zip code row
        $tableBody.on('click', '.remove-zip', function () {
            $(this).closest('tr').remove();
        });

    });

})(jQuery);
