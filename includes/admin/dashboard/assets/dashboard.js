(function ($) {
    'use strict';

    $(document).on('click', '.ovic-addon-dashboard .nav-tab-wrapper .nav-tab', function (e) {
        e.preventDefault();
        var $button      = $(this),
            $newurl      = $button.attr('href'),
            $contain     = $button.closest('.ovic-addon-dashboard'),
            $tab_content = $contain.find($button.data('tab'));

        $button.addClass('nav-tab-active').siblings().removeClass('nav-tab-active');
        $tab_content.addClass('active').siblings().removeClass('active');
        window.history.pushState({path: $newurl}, '', $newurl);
    });

    $(document).on('click', '.plugin-tabs .actions .button-action', function (e) {
        e.preventDefault();
        var $install     = 'yes',
            $this        = $(this),
            $plugin      = $this.closest('.plugin'),
            $plugin_slug = $plugin.data('plugin');

        if ($this.hasClass('uninstall')) {
            $install = 'no';
        }
        $plugin.find('.spinner').addClass('is-active');
        $.ajax({
            type   : 'POST',
            url    : ajaxurl,
            data   : {
                action     : 'plugin_action',
                plugin_slug: $plugin_slug,
                install    : $install,
            },
            success: function (xhr, textStatus) {
                $plugin.find('.spinner').removeClass('is-active');
                if (textStatus === 'success') {
                    if ($this.hasClass('uninstall')) {
                        $plugin.addClass('not_active');
                        $plugin.removeClass('is_active');
                    } else {
                        $plugin.removeClass('not_active');
                        $plugin.addClass('is_active');
                    }
                } else {
                    alert('Error');
                }
            }
        });
    });

    $('.tab-content.settings form,.tab-content.envato_license form').submit(function () {
        var data   = $(this).serialize(),
            button = $(this).find('#submit'),
            value  = button.val(),
            text   = button.attr('data-text');

        button.val(text).trigger('change');

        $.post('options.php', data).error(function (error) {
            alert(error);
            button.val(value).trigger('change');
        }).success(function (response) {
            location.reload();
        });

        return false;
    });

    $(document).on('click', '.tab-content.settings .clear-cache', function (e) {
        e.preventDefault();

        var $this    = $(this),
            $text    = $this.data('text-done'),
            $success = $this.parent().find('.ovic-text-success'),
            $spinner = $this.parent().find('.spinner');

        $spinner.addClass('is-active');

        $.post(
            ajaxurl,
            {
                action: 'ovic_clear_cache',
            },
            function (response) {
                $spinner.removeClass('is-active');
                $success.html($text.replace('%n', response.data));
            }
        );
    });

})(window.jQuery);