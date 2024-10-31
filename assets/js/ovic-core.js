(function ($) {
    'use strict';

    var get_url = function (endpoint) {
        return ovic_core_params.ovic_ajax_url.toString().replace(
            '%%endpoint%%',
            endpoint
        );
    };

    /* NOIFICATIONS */
    if (typeof ovic_core_params.growl_notice != "undefined") {

        $.fn.ovic_add_notify = function ($text_content) {
            var $config        = [],
                $img_url       = '',
                $template_html = '',
                $this          = $(this),
                $img           = $this.closest('.product-item').find('img.wp-post-image'),
                $title         = $this.attr('aria-label'),
                template       = wp.template('ovic-notice-popup');

            $config.duration = ovic_core_params.growl_notice.growl_duration;
            $config.title    = ovic_core_params.growl_notice.growl_notice_text;

            $this.removeClass('loading');

            // if from mini cart
            if ($this.closest('.mini_cart_item').length) {
                $img   = $this.closest('.mini_cart_item').find('a > img');
                $title = $this.closest('.mini_cart_item').find('a:not(.remove)').clone().children().remove().end().text();
            }
            // if from wishlist
            if (!$img.length && $this.closest('.wishlist_table').length) {
                $img   = $this.closest('tr').find('.product-thumbnail img');
                $title = $this.closest('tr').find('.product-name a').text();
            }
            // if from pinmap
            if (!$img.length && $this.closest('.ovic-pin').length) {
                $img = $this.closest('.ovic-pin').find('.ovic-product-thumbnail img');
            }
            // if from single product page
            if (!$img.length && $this.closest('.single-product').length) {
                $img = $this.closest('.single-product').find('.product .woocommerce-product-gallery__wrapper img.wp-post-image');
            }
            // if from default woocommerce
            if (!$img.length && $this.closest('.product').length) {
                $img = $this.closest('.product').find('img');
            }
            if (typeof $title === 'undefined' || $title === '') {
                $title = $this.closest('.product').find('.summary .product_title').text();
            }

            // reset state after 5 sec
            setTimeout(function () {
                $this.removeClass('added').removeClass('recent-added');
                $this.next('.added_to_cart').remove();
            }, 3000, $this);

            if (typeof $title === 'undefined' || $title === '') {
                $title = $this.closest('.product-item').find('.product-title:first a').text().trim();
            }

            if (typeof $title !== 'undefined' && $title !== '') {
                var string_start = $title.indexOf("“") + 1,
                    string_end   = $title.indexOf("”");

                $title = string_start > 1 ? $title.slice(string_start, string_end) : $title;
            } else {
                $title = '';
            }

            if ($img.length) {
                $img_url = $img.attr('src');
            }

            $template_html = template({
                img_url: $img_url,
                content: $text_content,
                title  : $title
            });
            $template_html = $template_html.replace('/*<![CDATA[*/', '');
            $template_html = $template_html.replace('/*]]>*/', '');

            $config.message = $template_html;

            $.growl.notice($config);
        };

        $(document).on('removed_from_cart', function (event, fragments, cart_hash, $button) {

            $button.ovic_add_notify(
                ovic_core_params.growl_notice.removed_cart_text
            );

        });

        $(document).on('added_to_cart', function (event, fragments, cart_hash, $button) {

            $button.ovic_add_notify(
                ovic_core_params.growl_notice.added_to_cart_text + '</br>' +
                '<a href="' + ovic_core_params.cart_url + '">' +
                ovic_core_params.growl_notice.view_cart + '</a>'
            );

        });

        $(document).on('added_to_wishlist removed_from_wishlist', function (event, $button, $wrap) {

            var html       = '',
                product_id = $button.data('product-id'),
                target     = product_id !== undefined ? $('.add-to-wishlist-' + product_id).first() : $button,
                is_remove  = $button.hasClass('delete_item') || $button.hasClass('remove') ? true : false,
                message    = ovic_core_params.growl_notice.added_to_wishlist_text;

            if (is_remove === true) {
                message = ovic_core_params.growl_notice.removed_from_wishlist_text;
            }

            html += message + '</br>';
            html += '<a href="' + ovic_core_params.growl_notice.wishlist_url + '">';
            if (is_remove === false) {
                html += ovic_core_params.growl_notice.browse_wishlist_text;
            }
            html += '</a>';

            target.ovic_add_notify(html);

            $button.removeClass('loading');

        });

        $(document).on('click', function (event) {
            var target = $(event.target).closest('#growls-default'),
                parent = $('#growls-default');

            if (!target.length) {
                $('.growl-close').trigger('click');
            }
        });

    }

    /* ADD TO CART SINGLE PRODUCT */

    if (ovic_core_params.ajax_single_add_to_cart && ovic_core_params.cart_redirect_after_add === 'no') {

        /**
         * Shows new notices on the page.
         *
         * @param {Object} The Notice HTML Element in string or object form.
         */
        var show_notice = function (html_element, $target) {
            if (!$target) {
                $target = $('.woocommerce-notices-wrapper:first') || $('.entry-summary').closest('.woocommerce');
            }
            $target.prepend(html_element);
        };

        /**
         * Removes duplicate notices.
         *
         * @param {JQuery Object} notices
         */
        var remove_duplicate_notices = function (notices) {
            var seen        = [];
            var new_notices = notices;

            notices.each(function (index) {
                var text = $(this).text();

                if ('undefined' === typeof seen[text]) {
                    seen[text] = true;
                } else {
                    new_notices.splice(index, 1);
                }
            });

            return new_notices;
        };

        var serializeObject = function (form) {
            var o = {};
            var a = form.serializeArray();
            $.each(a, function () {
                if (o[this.name]) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        };

        /*$(document).on('submit', '.product:not(.product-type-external) form.cart', function (e) {

            var form        = $(this),
                data        = serializeObject(form),
                $thisbutton = form.find('.single_add_to_cart_button');

            if (!$thisbutton.hasClass('disabled')) {

                if ($thisbutton.val()) {
                    data.product_id = $thisbutton.val();
                }

                $thisbutton.addClass('loading');

                // Trigger event.
                $(document.body).trigger('adding_to_cart', [$thisbutton, data]);

                // Ajax action.
                $.post(get_url('add_to_cart_single'), data, function (response) {

                    $thisbutton.removeClass('loading');

                    if (!response) {
                        return;
                    }

                    // Redirect to cart option
                    if (ovic_core_params.cart_redirect_after_add === 'yes' || $thisbutton.hasClass('buy-now')) {
                        window.location = ovic_core_params.cart_url;
                        return;
                    }

                    // Trigger event so themes can refresh other areas.
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);

                });
                e.preventDefault();

            }
        });*/

        $(document).on('submit', '.product:not(.product-type-external) form.cart', function (e) {
            e.preventDefault();
            const form   = $(this),
                  button = form.find('button.single_add_to_cart_button'),
                  data   = new FormData(form[0]);

            button.addClass('loading');

            if (button.val()) {
                data.append('add-to-cart', button.val());
            }

            // Ajax action.
            $.ajax({
                url        : form.attr('action'),
                type       : form.attr('method'),
                data       : data,
                dataType   : 'html',
                processData: false,
                contentType: false,
                success    : function (response) {
                    // Redirect to cart option
                    if (button.hasClass('buy-now')) {
                        window.location = ovic_core_params.cart_url;
                        return;
                    }

                    var $html    = $.parseHTML(response);
                    var $errors  = $('.woocommerce-error, .woocommerce-info', $html);
                    var $notices = remove_duplicate_notices($('.woocommerce-error, .woocommerce-message, .woocommerce-info', $html));

                    button.removeClass('loading');

                    // Display errors
                    if ($notices.length > 0) {
                        show_notice($notices);
                    }

                    // Trigger event so themes can refresh other areas.
                    if ($errors.length <= 0) {
                        $(document.body).trigger('wc_fragment_refresh');
                        $(document.body).trigger('added_to_cart', [null, null, button]);
                    }
                }
            });
        });

    }

    $(document).on('click', 'form.cart .product-buy-now', function (e) {
        var $this  = $(this),
            $form  = $this.closest('form'),
            $input = $form.find('[name="buy-now-redirect"]');

        if (ovic_core_params.ajax_single_add_to_cart) {
            $(document).on('added_to_cart', function (fragments, cart_hash, button) {
                window.location = ovic_core_params.cart_url;
            });
        } else {
            $input.val(1).trigger('change');
        }
        $form.find('[type="submit"]').trigger('click');

        e.preventDefault();
    });

    /* LOAD MORE POST */

    $.fn.ovic_load_post = function () {
        var is_busy        = false,
            previousScroll = 0,
            is_load        = function ($button, $url, $response, $pagination) {

                var $max_page = $pagination.find('.button-loadmore').data('total');

                $pagination.addClass('loading');
                $.ajax({
                    type    : 'GET',
                    url     : $url,
                    data    : {
                        ovic_raw_content: 1,
                    },
                    success : function (response) {
                        if (!response) {
                            return;
                        }
                        var $html    = $.parseHTML(response, document, true),
                            $nav     = $('.pagination-nav', $html).length ? $('.pagination-nav', $html)[0].innerHTML : '',
                            $content = $($button.data('response'), $html).length ? $($button.data('response'), $html)[0] : '',
                            $current = $('.pagination-nav', $html).find('.button-loadmore').data('current'),
                            $items   = $($content).children();

                        if ($content !== '') {
                            if ($button.data('animate') !== '') {
                                $items.each(function (i) {
                                    var $item  = $(this),
                                        $style = $item.attr('style'),
                                        $delay = i * 100;

                                    $style = ($style === undefined) ? '' : $style;
                                    $item.attr('style', $style +
                                        ';-webkit-animation-delay:' + $delay + 'ms;' +
                                        '-moz-animation-delay:' + $delay + 'ms;' +
                                        '-o-animation-delay:' + $delay + 'ms;' +
                                        'animation-delay:' + $delay + 'ms;'
                                    ).addClass($button.data('animate') + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
                                        $item.removeClass($button.data('animate') + ' animated');
                                        $item.attr('style', $style);
                                    });
                                    $response.append($item);
                                });
                            } else {
                                $response.append($content.innerHTML);
                            }
                            is_busy = false;
                            $response.trigger('ovic_success_load_more_post', [$content, $html]);
                        } else {
                            is_busy = true;
                        }
                        if ($current >= $max_page) {
                            $pagination.closest('.pagination-nav').remove();
                        } else {
                            $pagination.html($nav);
                            $pagination.removeClass('loading');
                        }
                    },
                    complete: function () {
                        $response.trigger('ovic_complete_load_more_post');
                    }
                });
            };

        $(document).on('click', '.pagination-nav.type-load_more .button-loadmore', function (e) {
            e.preventDefault();
            var $this       = $(this),
                $url        = $this.data('url'),
                $contain    = $this.closest($this.data('wrapper')),
                $response   = $contain.find($this.data('response')),
                $pagination = $this.closest('.pagination-nav');

            is_load($this, $url, $response, $pagination);
        });

        if ($('.pagination-nav.type-infinite .button-loadmore').length) {

            $(document).on('scroll', function () {

                var $this       = $('.pagination-nav.type-infinite .button-loadmore'),
                    $url        = $this.data('url'),
                    $contain    = $this.closest($this.data('wrapper')),
                    $response   = $contain.find($this.data('response')),
                    $pagination = $this.closest('.pagination-nav');

                var currentScroll = $(this).scrollTop();

                if (currentScroll > previousScroll) {

                    if ($pagination.length && $(window).scrollTop() + $(window).height() >= $pagination.offset().top) {

                        if (is_busy === false) {
                            is_load($this, $url, $response, $pagination);
                        }
                        is_busy = true;
                    }
                }

                previousScroll = currentScroll;

            });

        }
    };

    window.addEventListener("load", function load() {
        /**
         * remove listener, no longer needed
         * */
        window.removeEventListener("load", load, false);
        /**
         * start functions
         * */

        if ($('.pagination-nav .button-loadmore').length) {
            $('.pagination-nav .button-loadmore').ovic_load_post();
        }

        $(window).on('elementor/frontend/init', function () {
            elementorFrontend.hooks.addAction('frontend/element_ready/section', function ($scope, $) {
                if ($.fn.slick && $scope.hasClass('elementor-section-stretched') && $scope.find('.owl-slick').length > 0){
                    $scope.find('.owl-slick').slick('resize');
                }
            });
        });

    }, false);

})(window.jQuery);