<?php
/**
 * Ovic Snow Effect setup
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Snow_Effect
 * @since    1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('Ovic_Snow_Effect')) {
    class Ovic_Snow_Effect
    {
        public function __construct()
        {
            add_action('wp_footer', array($this, 'content'));
            add_action('wp_enqueue_scripts', array($this, 'scripts'), 10);
        }

        public function scripts()
        {
            $speed = OVIC_CORE()->get_config('snow_speed');
            $limit = OVIC_CORE()->get_config('snow_limit');
            $color = OVIC_CORE()->get_config('snow_color');
            $size  = OVIC_CORE()->get_config('snow_size');
            $bg    = OVIC_CORE()->get_config('snow_background', 'transparent');
            $css   = '
            .snow-container {
                position: fixed;
                height: 100vh;
                width: 100vw;
                top: 0;
                bottom: 0;
                overflow: hidden;
                pointer-events: none;
                filter: drop-shadow(0 0 10px white);
                background: '.$bg.';
            }
            .snow-container .snow {
                position: absolute;
                color: '.$color.';
                font-size: '.rand($size['width'], $size['height']).$size['unit'].';
                width: '.$size['width'].$size['unit'].';
                height: '.$size['height'].$size['unit'].';
            }
            ';
            for ($i = 1; $i <= $limit; $i++) {
                $scale     = rand(30, 99);
                $opacity   = rand(10, 99);
                $translate = rand(0, 100);
                $css       .= '
                .snow-container .snow:nth-child('.$i.') {
                    opacity: 0.'.$opacity.';
                    transform: translate('.$translate.'vw, -10px) scale(0.'.$scale.');
                    animation: fall-'.$i.' '.rand(10, $speed).'s -'.rand(2, $speed).'s linear infinite;
                }
                
                @keyframes fall-'.$i.' {
                    '.rand(30, 90).'% {
                        transform: translate('.$translate.'vw, '.rand(30, 100).'vh) scale(0.'.$scale.');
                    }
                    to {
                        transform: translate('.$translate.'vw, 100vh) scale(0.'.$scale.');
                    }
                }
                ';
            }
            wp_add_inline_style('ovic-core', preg_replace('/\s+/', ' ', $css));
        }

        public function content()
        {
            $limit = OVIC_CORE()->get_config('snow_limit');
            $snow  = OVIC_CORE()->get_config('snow_text');

            echo '<div class="snow-container">';

            for ($i = 0; $i < $limit; $i++) {
                echo '<div class="snow">'.$snow.'</div>';
            }

            echo '</div>';
        }
    }

    new Ovic_Snow_Effect();
}