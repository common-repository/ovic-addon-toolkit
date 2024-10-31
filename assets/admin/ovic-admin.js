(function ($) {
    'use strict';

    $(document).on('click', '.type-ovic_menu .row-actions .edit > a,.type-ovic_menu .row-actions .edit_vc > a', function (e) {
        var url = $(this).attr('href');
        window.open(url, '_blank');
        e.preventDefault();
    });

})(window.jQuery);