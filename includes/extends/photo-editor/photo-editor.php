<?php
/**
 * Ovic Photo Editor
 *
 * http://fabricjs.com
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('Ovic_Photo_Editor')) {
    class Ovic_Photo_Editor
    {
        public $url  = '';
        public $path = '';

        public function __construct()
        {
            $this->setup_constants();

            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 999);

            add_shortcode('ovic_photo_editor', array($this, 'content'));
        }

        public function setup_constants()
        {
            $this->url  = trailingslashit(plugin_dir_url(__FILE__));
            $this->path = trailingslashit(plugin_dir_path(__FILE__));
        }

        public function fonts_url()
        {
            $subsets         = [
                'ru_RU' => 'cyrillic',
                'bg_BG' => 'cyrillic',
                'he_IL' => 'hebrew',
                'el'    => 'greek',
                'vi'    => 'vietnamese',
                'uk'    => 'cyrillic',
                'cs_CZ' => 'latin-ext',
                'ro_RO' => 'latin-ext',
                'pl_PL' => 'latin-ext',
                'hr_HR' => 'latin-ext',
                'hu_HU' => 'latin-ext',
                'sk_SK' => 'latin-ext',
                'tr_TR' => 'latin-ext',
                'lt_LT' => 'latin-ext',
            ];
            $subsets         = apply_filters('ovic_photo_editor_google_font_subsets', $subsets);
            $font_families   = array();
            $font_families[] = 'Oswald:300,400,500,600,700';
            $query_args      = array(
                'family'  => implode('%7C', $font_families),
                'display' => 'swap',
            );
            $fonts_url       = add_query_arg($query_args, '//fonts.googleapis.com/css');

            $locale = get_locale();

            if (isset($subsets[$locale])) {
                $fonts_url .= '&subset='.$subsets[$locale];
            }

            return esc_url($fonts_url);
        }

        public function enqueue_scripts()
        {
            wp_register_style('ovic-google-fonts', $this->fonts_url());
            wp_register_style('ovic-photo-editor', $this->url.'scripts/photo-editor.css');
            /* http://fabricjs.com/demos/ */
            wp_register_script('fabric', $this->url.'scripts/fabric.min.js', array(), false, true);
            wp_register_script('ovic-photo-editor', $this->url.'scripts/photo-editor.js', array(
                'fabric',
            ), false, true);
        }

        public function content($atts, $content = null)
        {
            wp_enqueue_style('ovic-google-fonts');
            wp_enqueue_style('ovic-photo-editor');
            wp_enqueue_script('ovic-photo-editor');

            $atts      = wp_parse_args($atts, [
                'id'       => uniqid('photo-editor-'),
                'width'    => 600,
                'height'   => 600,
                'images'   => '',
                'filename' => 'photo-editor.png',
            ]);
            $fonts     = array(
                'Arial',
                'Arial Black',
                'Helvetica',
                'Times New Roman',
                'Courier New',
                'Tahoma',
                'Verdana',
                'Impact',
                'Trebuchet MS',
                'Comic Sans MS',
                'Lucida Console',
                'Lucida Sans Unicode',
                'Georgia, serif',
                'Oswald, serif',
                'Palatino Linotype'
            );
            $galleries = array();
            if (!empty($atts['images'])) {
                $galleries = is_array($atts['images']) ? $atts['images'] : explode(',', $atts['images']);
            }
            $left   = file_get_contents($this->path.'images/left.svg');
            $center = file_get_contents($this->path.'images/center.svg');
            $right  = file_get_contents($this->path.'images/right.svg');
            $top    = file_get_contents($this->path.'images/top.svg');
            $middle = file_get_contents($this->path.'images/middle.svg');
            $bottom = file_get_contents($this->path.'images/bottom.svg');
            ob_start();
            ?>
            <div class="ovic-photo-editor">
                <div class="top-controls">
                    <div class="alignment-control">
                        <span>Alignment</span>
                        <div class="btn-horizontal">
                            <a href="#" data-value="left">
                                <?php echo wp_specialchars_decode($left); ?>
                            </a>
                            <a href="#" class="active" data-value="center">
                                <?php echo wp_specialchars_decode($center); ?>
                            </a>
                            <a href="#" data-value="right">
                                <?php echo wp_specialchars_decode($right); ?>
                            </a>
                        </div>
                        <div class="btn-vertical">
                            <a href="#" data-value="top">
                                <?php echo wp_specialchars_decode($top); ?>
                            </a>
                            <a href="#" class="active" data-value="middle">
                                <?php echo wp_specialchars_decode($middle); ?>
                            </a>
                            <a href="#" data-value="bottom">
                                <?php echo wp_specialchars_decode($bottom); ?>
                            </a>
                        </div>
                    </div>
                    <div class="text-control" hidden>
                        <span>Text Edit</span>
                        <label>
                            <select class="font-family">
                                <?php foreach ($fonts as $font) {
                                    echo '<option value="'.esc_attr($font).'">'.esc_html($font).'</option>';
                                } ?>
                            </select>
                        </label>
                        <label>
                            Text Color: <input type="color" class="text-color" size="10">
                        </label>
                        <label>
                            Background Color: <input type="color" class="background-color" size="10">
                        </label>
                        <div class="group-text">
                            <input type='checkbox' name='font_type' class="bold"> <b>Bold</b>
                            <input type='checkbox' name='font_type' class="italic"> <em>Italic</em>
                            <input type='checkbox' name='font_type' class="underline"> Underline
                            <input type='checkbox' name='font_type' class="linethrough"> Linethrough
                            <input type='checkbox' name='font_type' class="overline"> Overline
                        </div>
                    </div>
                </div>
                <div class="photo-container">
                    <?php if (!empty($galleries)): ?>
                        <div class="photo-galleries" style="max-height:<?php echo esc_attr($atts['height']) ?>px">
                            <?php foreach ($galleries as $gallery): ?>
                                <?php echo wp_get_attachment_image($gallery, array($atts['width'], $atts['height'])); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <canvas class="editor"
                            width="<?php echo esc_attr($atts['width']) ?>"
                            height="<?php echo esc_attr($atts['height']) ?>"></canvas>
                </div>
                <div class="bottom-controls">
                    <label title="Add an image" class="add-file button">
                        <span class="mdi mdi-image">Add Photo</span>
                        <input type="file" class="image"/>
                    </label>
                    <label title="Add a background" class="add-file button hidden">
                        <span class="mdi mdi-image">Add Background</span>
                        <input type="file" class="background"/>
                    </label>
                    <a class="add-text button" title="Add text">
                        <span class="mdi mdi-format-text">Add Text</span>
                    </a>
                    <a class="delete button" title="Delete Anything Selected">
                        <span class="mdi mdi-delete">Delete</span>
                    </a>
                    <a class="button" onclick="refresh()" title="Start fresh">
                        <span class="mdi mdi-shredder">Clear All</span>
                    </a>
                    <a class="download button" title="Save"
                       data-filename="<?php echo esc_attr($atts['filename']) ?>">
                        <span class="mdi mdi-download">Save</span>
                    </a>
                </div>
            </div>
            <?php

            return ob_get_clean();
        }
    }

    new Ovic_Photo_Editor();
}