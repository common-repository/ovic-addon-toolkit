(function ($) {
    'use strict';

    $.fn.add_question = function () {
        var $content = $(this);
        $content.addClass('loading');
        $.ajax({
            type   : 'POST',
            url    : question_params.ajax_url,
            data   : {
                action          : 'ovic_add_question',
                security        : question_params.security,
                question        : $content.find('.ask-question').val(),
                post_id         : $content.data('post_id'),
                ovic_raw_content: true,
            },
            success: function (response) {
                if (response.status == true) {
                    $content.find('.notice').html('<div class="woocommerce-message">' + response.message + '</div>');
                    $content.get_question(true);
                } else {
                    $content.find('.notice').html('<div class="woocommerce-message woocommerce-error">' + response.message + '</div>');
                }
                $content.removeClass('loading');
            }
        });
    }

    $.fn.get_question = function (force) {
        var $content = $(this);
        if (!$content.hasClass('loaded') || force == true) {
            $content.addClass('loading');
            $.ajax({
                type   : 'POST',
                url    : question_params.ajax_url,
                data   : {
                    action          : 'ovic_get_question',
                    security        : question_params.security,
                    post_id         : $content.data('post_id'),
                    ovic_raw_content: true,
                },
                success: function (response) {
                    if (response.status == true) {
                        $content.find('.list-question').html('');
                        for (var i in response.data) {
                            var $html = '';

                            $html += '<div class="item-question">';
                            $html += '  <div class="question"><span class="icon"></span>';
                            $html += '      <div class="text paragraph">' + response.data[i].question + '</div>';
                            $html += '  </div>';
                            $html += '  <div class="answers"><span class="icon"></span>';
                            $html += '      <div class="text paragraph">' + response.data[i].answers + '</div>';
                            $html += '  </div>';
                            $html += '</div>';

                            $content.find('.list-question').append($html);
                        }
                    } else {
                        $content.find('.notice').html('<div class="woocommerce-message woocommerce-error">' + response.data + '</div>');
                    }
                    $content.removeClass('loading');
                    $content.addClass('loaded');
                }
            });
        }
    }

    $(document).on('click', '.ovic-question-answers .add-question', function () {

        $(this).closest('.ovic-question-answers').add_question();

        return false;
    });

    $(document).on('click', '.ovic-question-answers .close-question,.ovic-question-answers .overlay-question', function () {
        var $button  = $(this),
            $content = $button.closest('.ovic-question-answers');

        $content.removeClass('open');
        $('body').removeClass('overlay-question');

        return false;
    });

    $(document).on('click', '.ovic-question-answers .load-question', function () {
        var $button  = $(this),
            $content = $button.closest('.ovic-question-answers');

        if ($content.hasClass('popup-on')) {
            $content.addClass('open');
            $('body').addClass('overlay-question');
        }

        if ($content.hasClass('load-ajax')) {
            $content.get_question(false);
        }

        return false;
    });

})(window.jQuery);