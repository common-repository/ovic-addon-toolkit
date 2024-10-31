/*! Copyright 2012, Ben Lin (http://dreamerslab.com/)
 * Licensed under the MIT License (LICENSE.txt).
 *
 * Version: 1.0.19
 *
 * Requires: jQuery >= 1.2.3
 */
;(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register module depending on jQuery using requirejs define.
        define(['jquery'], factory);
    } else {
        // No AMD.
        factory(jQuery);
    }
}(function ($) {
    $.fn.addBack = $.fn.addBack || $.fn.andSelf;

    $.fn.extend({

        actual: function (method, options) {
            // check if the jQuery method exist
            if (!this[method]) {
                throw '$.actual => The jQuery method "' + method + '" you called does not exist';
            }

            var defaults = {
                absolute     : false,
                clone        : false,
                includeMargin: false,
                display      : 'block'
            };

            var configs = $.extend(defaults, options);

            var $target = this.eq(0);
            var fix, restore;

            if (configs.clone === true) {
                fix = function () {
                    var style = 'position: absolute !important; top: -1000 !important; ';

                    // this is useful with css3pie
                    $target = $target.clone().attr('style', style).appendTo('body');
                };

                restore = function () {
                    // remove DOM element after getting the width
                    $target.remove();
                };
            } else {
                var tmp   = [];
                var style = '';
                var $hidden;

                fix = function () {
                    // get all hidden parents
                    $hidden = $target.parents().addBack().filter(':hidden');
                    style += 'visibility: hidden !important; display: ' + configs.display + ' !important; ';

                    if (configs.absolute === true) style += 'position: absolute !important; ';

                    // save the origin style props
                    // set the hidden el css to be got the actual value later
                    $hidden.each(function () {
                        // Save original style. If no style was set, attr() returns undefined
                        var $this     = $(this);
                        var thisStyle = $this.attr('style');

                        tmp.push(thisStyle);
                        // Retain as much of the original style as possible, if there is one
                        $this.attr('style', thisStyle ? thisStyle + ';' + style : style);
                    });
                };

                restore = function () {
                    // restore origin style values
                    $hidden.each(function (i) {
                        var $this = $(this);
                        var _tmp  = tmp[i];

                        if (_tmp === undefined) {
                            $this.removeAttr('style');
                        } else {
                            $this.attr('style', _tmp);
                        }
                    });
                };
            }

            fix();
            // get the actual value with user specific methed
            // it can be 'width', 'height', 'outerWidth', 'innerWidth'... etc
            // configs.includeMargin only works for 'outerWidth' and 'outerHeight'
            var actual = /(outer)/.test(method) ?
                         $target[method](configs.includeMargin) :
                         $target[method]();

            restore();
            // IMPORTANT, this plugin only return the value of the first element
            return actual;
        }
    });
}));
(function ($) {
    "use strict"; // Start of use strict

    var Mobile_Detect = {
        Mobile: function () {
            return navigator.userAgent.match(
                /(iPhone|iPod|Android|Phone|DROID|ZuneWP7|silk|BlackBerry|BB10|Windows Phone|Tizen|Bada|webOS|IEMobile|Opera Mini)/
            );
        },
        Tablet: function () {
            return navigator.userAgent.match(
                /(Tablet|iPad|Kindle|Playbook|Nexus|Xoom|SM-N900T|GT-N7100|SAMSUNG-SGH-I717|SM-T330NU)/
            );
        },
        any   : function () {
            return (Mobile_Detect.Mobile() || Mobile_Detect.Tablet());
        }
    };

    /* ---------------------------------------------
     Resize mega menu
     --------------------------------------------- */

    function scrollbar_width() {
        var $inner = jQuery('<div style="width: 100%; height:200px;">test</div>'),
            $outer = jQuery('<div style="width:200px;height:150px; position: absolute; top: 0; left: 0; visibility: hidden; overflow:hidden;"></div>').append($inner),
            inner  = $inner[0],
            outer  = $outer[0];

        jQuery('body').append(outer);

        var width1 = inner.offsetWidth;

        $outer.css('overflow', 'scroll');

        var width2 = outer.clientWidth;

        $outer.remove();

        return (width1 - width2);
    }

    function responsive_megamenu(container, element) {
        if (container !== 'undefined') {
            var left             = 0,
                container_width  = 0,
                container_offset = container.offset();

            if (typeof container_offset != 'undefined') {
                container_width = container.innerWidth();
                setTimeout(function () {
                    element.children('.megamenu').css({
                        'max-width': container_width + 'px'
                    });
                    var sub_menu_width = element.children('.megamenu').outerWidth(),
                        item_width     = element.outerWidth();
                    element.children('.megamenu').css({
                        'left': '-' + (sub_menu_width / 2 - item_width / 2) + 'px'
                    });
                    var container_left  = container_offset.left,
                        container_right = (container_left + container_width),
                        item_left       = element.offset().left,
                        overflow_left   = (sub_menu_width / 2 > (item_left - container_left)),
                        overflow_right  = ((sub_menu_width / 2 + item_left) > container_right);

                    if (overflow_left) {
                        left = (item_left - container_left);
                        element.children('.megamenu').css({
                            'left': -left + 'px'
                        });
                    }
                    if (overflow_right && !overflow_left) {
                        left = (item_left - container_left);
                        left = left - (container_width - sub_menu_width);
                        element.children('.megamenu').css({
                            'left': -left + 'px'
                        });
                    }
                }, 100);
            }
        }
    }

    $.fn.ovic_resize_megamenu = function () {

        var megamenu = $(this);

        megamenu.on('ovic_resize_megamenu', function () {

            var window_size = jQuery('body').innerWidth();

            window_size += scrollbar_width();

            if ($(this).length > 0 && window_size > 991) {
                $(this).each(function () {
                    var _class_responsive = $(this).children('.megamenu').data('responsive'),
                        _container        = $(this).closest('.ovic-menu-wapper');

                    if (_class_responsive !== '' && $(this).closest(_class_responsive).length) {
                        _container = $(this).closest(_class_responsive);
                    }

                    responsive_megamenu(_container, $(this));
                });
            }
        }).trigger('ovic_resize_megamenu');

        $(window).on('resize', function () {
            megamenu.trigger('ovic_resize_megamenu');
        });

    };

    /**==============================
     Auto width Vertical menu
     ===============================**/
    $.fn.ovic_vertical_megamenu = function () {

        var vertical_menu = $(this);

        vertical_menu.on('ovic_vertical_megamenu', function () {
            $(this).each(function () {
                var menu        = $(this),
                    menu_offset = menu.offset().left > 0 ? menu.offset().left : 0,
                    menu_width  = parseInt(menu.actual('width')),
                    menu_left   = menu_offset + menu_width;

                menu.find('.megamenu').each(function () {
                    var megamenu          = $(this),
                        class_responsive  = megamenu.data('responsive'),
                        element_caculator = megamenu.closest('.container');

                    if (class_responsive !== '') {
                        element_caculator = megamenu.closest(class_responsive);
                    }

                    if (element_caculator.length > 0) {
                        var container_width  = parseInt(element_caculator.innerWidth()) - 30,
                            container_offset = element_caculator.offset(),
                            container_left   = container_offset.left + container_width,
                            width            = (container_width - menu_width);

                        if (menu_offset > container_left || menu_left < container_offset.left)
                            width = container_width;
                        if (menu_left > container_left)
                            width = container_width - (menu_width - (menu_left - container_left)) - 30;

                        if (width > 0) {
                            $(this).css('max-width', width + 'px');
                        }
                    }
                });
            });
        }).trigger('ovic_vertical_megamenu');

        $(window).on('resize', function () {
            vertical_menu.trigger('ovic_vertical_megamenu');
        });

    };

    /* ---------------------------------------------
     MOBILE MENU
     --------------------------------------------- */
    function load_mobile_menu($menu) {
        if (!$menu.hasClass('loaded')) {
            $.ajax({
                type    : 'POST',
                url     : ovic_ajax_megamenu.ajaxurl,
                data    : {
                    action   : 'ovic_load_mobile_menu',
                    security : ovic_ajax_megamenu.security,
                    locations: $menu.data('locations'),
                    default  : $menu.data('default'),
                },
                success : function (response) {
                    if (response.success == true) {
                        $menu.children('.ovic-menu-panels').html(response.data);
                    }
                },
                complete: function (response) {
                    $menu.children('.loader-mobile').remove();
                    $menu.addClass('loaded');
                },
            });
        }
    }

    if (ovic_ajax_megamenu.load_megamenu == true) {
        $('.ovic-menu-wapper .menu-item.item-megamenu').hover(function () {
            var $menu     = $(this),
                $id       = $menu.children('a').data('megamenu'),
                $megamenu = $menu.children('.sub-menu.megamenu');

            if (!$menu.hasClass('loaded')) {
                $menu.addClass('loaded');
                $.ajax({
                    type   : 'POST',
                    url    : ovic_ajax_megamenu.ajaxurl,
                    data   : {
                        action     : 'ovic_load_mega_menu',
                        security   : ovic_ajax_megamenu.security,
                        megamenu_id: $menu.children('a').data('megamenu'),
                    },
                    success: function (response) {
                        if (response.success == true) {
                            $megamenu.html(response.data);
                        }
                    },
                });
            }

            return false;
        });
    }

    // Open box menu
    $(document).on('click', '.menu-toggle', function () {
        var $button = $(this),
            $index  = $button.data('index'),
            $menu   = $('.ovic-menu-clone-wrap');

        if ($index != undefined && $('#ovic-menu-mobile-' + $index).length) {
            $menu = $('#ovic-menu-mobile-' + $index);
        }

        $('body').addClass('ovic-open-mobile-menu');
        $menu.addClass('open');

        if (ovic_ajax_megamenu.load_menu == 'click') {
            load_mobile_menu($menu);
        }

        return false;
    });
    // Close box menu
    $(document).on('click', '.ovic-menu-clone-wrap .ovic-menu-close-panels', function () {
        $('body').removeClass('ovic-open-mobile-menu');
        $('.ovic-menu-clone-wrap').removeClass('open');
        return false;
    });
    $(document).on('click', function (event) {
        var menu_mobile = $('.ovic-menu-clone-wrap');
        if ($('body').hasClass('rtl')) {
            if (event.offsetX < 0) {
                menu_mobile.removeClass('open');
                $('body').removeClass('ovic-open-mobile-menu');
            }
        } else {
            if (event.offsetX > menu_mobile.width()) {
                menu_mobile.removeClass('open');
                $('body').removeClass('ovic-open-mobile-menu');
            }
        }
    });

    // Open next panel
    $(document).on('click', '.ovic-menu-next-panel', function (e) {
        var thisButton   = $(this),
            targetID     = thisButton.attr('href'),
            thisItem     = thisButton.closest('.menu-item'),
            thisPanel    = thisButton.closest('.ovic-menu-panel'),
            thisMenu     = thisButton.closest('.ovic-menu-clone-wrap'),
            currentTitle = thisMenu.find('.ovic-menu-current-panel-title'),
            actionsWrap  = thisMenu.find('.ovic-menu-panels-actions-wrap'),
            targetElem   = thisMenu.find(targetID);

        if (targetElem.length) {

            // Insert current panel title
            var itemTitle      = thisItem.children('.menu-link').html(),
                prevPanel      = $('<a class="ovic-menu-prev-panel"></a>'),
                firstItemTitle = '';

            thisPanel.addClass('ovic-menu-sub-opened');
            targetElem.removeClass('ovic-menu-hidden').addClass('ovic-menu-panel-opened').attr('data-parent-title', itemTitle).attr('data-parent-panel', thisPanel.attr('id'));

            if (currentTitle.length > 0) {
                firstItemTitle = currentTitle.clone();
            }

            if (typeof itemTitle != 'undefined' && typeof itemTitle !== false) {
                if (!currentTitle.length) {
                    actionsWrap.prepend('<span class="ovic-menu-current-panel-title"></span>');
                }
                currentTitle.html(itemTitle);
            } else {
                currentTitle.remove();
            }

            // Back to previous panel
            prevPanel.attr('data-current-panel', targetID);
            prevPanel.attr('href', '#' + thisPanel.attr('id'));
            actionsWrap.find('.ovic-menu-prev-panel').remove();
            actionsWrap.prepend(prevPanel);
        }

        e.preventDefault();
    });

    // Go to previous panel
    $(document).on('click', '.ovic-menu-prev-panel', function (e) {
        var thisButton   = $(this),
            thisMenu     = thisButton.closest('.ovic-menu-clone-wrap'),
            currentPanel = thisButton.attr('data-current-panel'),
            targetID     = thisButton.attr('href'),
            actionsWrap  = thisMenu.find('.ovic-menu-panels-actions-wrap'),
            currentTitle = thisMenu.find('.ovic-menu-current-panel-title'),
            targetElem   = thisMenu.find(targetID),
            mainTitle    = currentTitle.attr('data-main-title');

        thisMenu.find(currentPanel).removeClass('ovic-menu-panel-opened').addClass('ovic-menu-hidden');
        targetElem.addClass('ovic-menu-panel-opened').removeClass('ovic-menu-sub-opened');

        // Set new back button
        var itemTitle   = targetElem.attr('data-parent-title'),
            parentPanel = targetElem.attr('data-parent-panel');

        if (typeof parentPanel == 'undefined' || typeof parentPanel === false) {
            thisButton.remove();
            currentTitle.html(mainTitle);
        } else {
            thisButton.attr('href', '#' + parentPanel).attr('data-current-panel', targetID);

            // Insert new panel title
            if (typeof itemTitle != 'undefined' && typeof itemTitle !== false) {
                if (!currentTitle.length) {
                    actionsWrap.prepend('<span class="ovic-menu-current-panel-title"></span>');
                }
                currentTitle.html(itemTitle);
            } else {
                currentTitle.remove();
            }
        }

        e.preventDefault();
    });

    // Menu item next panel
    $(document).on('click', '.ovic-menu-clone-wrap .menu-item.disable-link > .menu-link', function (e) {
        $(this).prev('.ovic-menu-next-panel').trigger('click');
        e.preventDefault();
    });

    /* ---------------------------------------------
     Scripts load
     --------------------------------------------- */
    $(document).ready(function () {

        var enable_resize = true;

        if (ovic_ajax_megamenu.resize == 'mobile' && Mobile_Detect.Mobile()) {
            enable_resize = false;
        }

        if (ovic_ajax_megamenu.resize == 'tablet' && Mobile_Detect.any()) {
            enable_resize = false;
        }

        if (enable_resize) {

            var horizontal = $('.ovic-menu-wapper.horizontal'),
                vertical   = $('.ovic-menu-wapper.vertical');

            if (horizontal.length) {
                horizontal.each(function () {
                    if ($(this).find('.item-megamenu').length) {
                        $(this).find('.item-megamenu').ovic_resize_megamenu();
                    }
                });
            }
            if (vertical.length) {
                vertical.ovic_vertical_megamenu();
            }

        }

    });

    if (ovic_ajax_megamenu.load_menu == 'last') {

        window.addEventListener("load", function load() {
            /**
             * remove listener, no longer needed
             * */
            window.removeEventListener("load", load, false);
            /**
             * start functions
             * */

            setTimeout(function () {
                $('.ovic-menu-clone-wrap').each(function () {
                    load_mobile_menu($(this));
                });
            }, ovic_ajax_megamenu.delay);

        }, false);

    }

})(jQuery); // End of use strict