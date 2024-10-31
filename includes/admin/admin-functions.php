<?php
/**
 * Ovic Admin Functions
 *
 * @author   KuteThemes
 * @category Core
 * @package  Ovic/Admin/Functions
 * @version  2.4.0
 */
if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * TEMPLATE DEFAULT
 */
if ( ! function_exists('ovic_load_default_templates')) {
    function ovic_load_default_templates($templates_data)
    {
        $template = get_template_directory().'/vc_template.json';

        if (file_exists($template)) {
            $template_content = file_get_contents($template);
            if ($template_configs = json_decode($template_content, true)) {
                foreach ($template_configs as $template) {
                    $templates_data[] = array(
                        'name'     => $template['name'],
                        'disabled' => false,
                        'content'  => $template['content'],
                    );
                }
            }
        }

        return $templates_data;
    }

    add_filter('vc_load_default_templates', 'ovic_load_default_templates');
}
/**
 *
 * GET PREVIEW SHOTCODE
 **/
if ( ! function_exists('ovic_get_preview_shortcode')) {
    function ovic_get_preview_shortcode($name)
    {
        $path            = trailingslashit(get_template_directory())."vc_templates/{$name}/layout/";
        $preview_options = array();
        if (is_dir($path)) {
            $files = scandir($path);
            if ($files && is_array($files)) {
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..') {
                        $fileInfo = pathinfo($file);
                        if ($fileInfo['extension'] == 'jpg') {
                            $fileName = str_replace(
                                array('_', '-'),
                                array(' ', ' '),
                                $fileInfo['filename']
                            );
                            /* PRINT OPTION */
                            $preview_options[$fileInfo['filename']] = array(
                                'title'   => ucwords($fileName),
                                'preview' => get_theme_file_uri("vc_templates/{$name}/layout/{$fileInfo['filename']}.jpg"),
                            );
                        }
                    }
                }
            }
        }

        return $preview_options;
    }
}
/**
 * GET FILE OPTIONS
 **/
if ( ! function_exists('ovic_file_options')) {
    function ovic_file_options($path, $name)
    {
        $layoutDir      = get_template_directory().$path;
        $header_options = array();
        if (is_dir($layoutDir)) {
            $files = scandir($layoutDir);
            if ($files && is_array($files)) {
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..' && $file != 'style') {
                        $fileInfo  = pathinfo($file);
                        $file_data = get_file_data($layoutDir.$file,
                            array(
                                'Name' => 'Name',
                            )
                        );
                        if (isset($fileInfo['extension']) && $fileInfo['extension'] == 'php' && $fileInfo['basename'] != 'index.php') {
                            if ($file_data['Name'] != '') {
                                $file_name = $file_data['Name'];
                            } else {
                                $file_name = str_replace(array('_', '-', 'content'), array(
                                    ' ',
                                    ' ',
                                    ''
                                ), $fileInfo['filename']);
                            }
                            $preview = OVIC_PLUGIN_URL.'/assets/images/placeholder.jpg';
                            $file_id = $name != '' ? str_replace("{$name}-", '', $fileInfo['filename']) : $fileInfo['filename'];
                            if (is_file(get_template_directory()."{$path}{$fileInfo['filename']}.jpg")) {
                                $preview = get_theme_file_uri("{$path}{$fileInfo['filename']}.jpg");
                            }
                            $header_options[$file_id] = array(
                                'title'   => ucwords($file_name),
                                'preview' => $preview,
                            );
                        }
                    }
                }
            }
        }

        return $header_options;
    }
}
/**
 * GET PRODUCT OPTIONS
 **/
if ( ! function_exists('ovic_product_options')) {
    function ovic_product_options($allow = 'Theme Option')
    {
        $layoutDir       = get_template_directory().'/woocommerce/product-style/';
        $product_options = array();
        if (is_dir($layoutDir)) {
            $files = scandir($layoutDir);
            if ($files && is_array($files)) {
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..') {
                        $fileInfo  = pathinfo($file);
                        $file_data = get_file_data($layoutDir.$file,
                            array(
                                'Name'         => 'Name',
                                'Theme Option' => 'Theme Option',
                                'Shortcode'    => 'Shortcode',
                            )
                        );
                        $file_name = str_replace('content-product-', '', $fileInfo['filename']);
                        if ($fileInfo['extension'] == 'php' && $fileInfo['basename'] != 'index.php' && $file_data[$allow] == 'true') {
                            $product_options[$file_name] = array(
                                'title'   => $file_data['Name'],
                                'preview' => get_theme_file_uri('woocommerce/product-style/content-product-'.$file_name.'.jpg'),
                            );
                        }
                    }
                }
            }
        }
        if (empty($product_options)) {
            $product_options['no-product'] = array(
                'title' => esc_html__('No Product Found', 'ovic-addon-toolkit'),
            );
        }

        return $product_options;
    }
}
/**
 *
 * GET SLIDE REV
 */
if ( ! function_exists('ovic_get_rev_slide_options')) {
    function ovic_get_rev_slide_options()
    {
        $arrOutput     = array();
        $arrOutput[''] = esc_html__('--- Choose Revolution Slider ---', 'ovic-addon-toolkit');
        if (class_exists('RevSlider')) {
            $slider     = new RevSlider();
            $arrSliders = $slider->getArrSliders();
            if ( ! empty($arrSliders)) {
                foreach ($arrSliders as $slider) {
                    $arrOutput[$slider->getAlias()] = $slider->getTitle();
                }
            } else {
                $arrOutput = array(__('No sliders found', 'ovic-addon-toolkit'));
            }
        }

        return $arrOutput;
    }
}
if ( ! function_exists('ovic_generate_settings')) {
    function ovic_generate_settings($preflix = 'related', $name = 'Related')
    {
        $args = array(
            'title'  => esc_html__(''.$name.' Products', 'ovic-addon-toolkit'),
            'fields' => array(
                'woo_'.$preflix.'_enable'  => array(
                    'id'      => 'woo_'.$preflix.'_enable',
                    'type'    => 'button_set',
                    'default' => 'enable',
                    'options' => array(
                        'enable'  => esc_html__('Enable', 'ovic-addon-toolkit'),
                        'disable' => esc_html__('Disable', 'ovic-addon-toolkit'),
                    ),
                    'title'   => sprintf(esc_html__('Enable %s Products', 'ovic-addon-toolkit'), $name),
                ),
                'woo_'.$preflix.'_title'   => array(
                    'id'         => 'woo_'.$preflix.'_title',
                    'type'       => 'text',
                    'title'      => sprintf(esc_html__('%s products title', 'ovic-addon-toolkit'), $name),
                    'desc'       => sprintf(esc_html__('%s products title', 'ovic-addon-toolkit'), $name),
                    'dependency' => array('woo_'.$preflix.'_enable', '==', 'enable'),
                    'default'    => sprintf(esc_html__('%s Products', 'ovic-addon-toolkit'), $name),
                ),
                'woo_'.$preflix.'_style'   => array(
                    'id'         => 'woo_'.$preflix.'_style',
                    'type'       => 'select_preview',
                    'default'    => 'style-01',
                    'title'      => sprintf(esc_html__('Product %s Layout', 'ovic-addon-toolkit'), $name),
                    'options'    => ovic_product_options('Theme Option'),
                    'dependency' => array('woo_'.$preflix.'_enable', '==', 'enable'),
                ),
                'woo_'.$preflix.'_items'   => array(
                    'id'         => 'woo_'.$preflix.'_items',
                    'type'       => 'slider',
                    'title'      => sprintf(esc_html__('%s products items per row on Desktop', 'ovic-addon-toolkit'), $name),
                    'desc'       => esc_html__('(Screen resolution of device >= 1500px )', 'ovic-addon-toolkit'),
                    'min'        => 1,
                    'max'        => 6,
                    'step'       => 1,
                    'unit'       => esc_html__('item(s)', 'ovic-addon-toolkit'),
                    'default'    => '3',
                    'dependency' => array('woo_'.$preflix.'_enable', '==', 'enable'),
                ),
                'woo_'.$preflix.'_desktop' => array(
                    'id'         => 'woo_'.$preflix.'_desktop',
                    'type'       => 'slider',
                    'title'      => sprintf(esc_html__('%s products items per row on Desktop', 'ovic-addon-toolkit'), $name),
                    'desc'       => esc_html__('(Screen resolution of device >= 1200px < 1500px )', 'ovic-addon-toolkit'),
                    'min'        => 1,
                    'max'        => 6,
                    'step'       => 1,
                    'unit'       => esc_html__('item(s)', 'ovic-addon-toolkit'),
                    'default'    => '3',
                    'dependency' => array('woo_'.$preflix.'_enable', '==', 'enable'),
                ),
                'woo_'.$preflix.'_laptop'  => array(
                    'id'         => 'woo_'.$preflix.'_laptop',
                    'type'       => 'slider',
                    'title'      => sprintf(esc_html__('%s products items per row on Laptop', 'ovic-addon-toolkit'), $name),
                    'desc'       => esc_html__('(Screen resolution of device >=992px and < 1200px )', 'ovic-addon-toolkit'),
                    'min'        => 1,
                    'max'        => 6,
                    'step'       => 1,
                    'unit'       => esc_html__('item(s)', 'ovic-addon-toolkit'),
                    'default'    => '3',
                    'dependency' => array('woo_'.$preflix.'_enable', '==', 'enable'),
                ),
                'woo_'.$preflix.'_tablet'  => array(
                    'id'         => 'woo_'.$preflix.'_tablet',
                    'type'       => 'slider',
                    'title'      => sprintf(esc_html__('%s product items per row on portrait tablet', 'ovic-addon-toolkit'), $name),
                    'desc'       => esc_html__('(Screen resolution of device >=768px and < 992px )', 'ovic-addon-toolkit'),
                    'min'        => 1,
                    'max'        => 6,
                    'step'       => 1,
                    'unit'       => esc_html__('item(s)', 'ovic-addon-toolkit'),
                    'default'    => '2',
                    'dependency' => array('woo_'.$preflix.'_enable', '==', 'enable'),
                ),
                'woo_'.$preflix.'_ipad'    => array(
                    'id'         => 'woo_'.$preflix.'_ipad',
                    'type'       => 'slider',
                    'title'      => sprintf(esc_html__('%s products items per row on Ipad', 'ovic-addon-toolkit'), $name),
                    'desc'       => esc_html__('(Screen resolution of device >=480  add < 768px)', 'ovic-addon-toolkit'),
                    'min'        => 1,
                    'max'        => 6,
                    'step'       => 1,
                    'unit'       => esc_html__('item(s)', 'ovic-addon-toolkit'),
                    'default'    => '1',
                    'dependency' => array('woo_'.$preflix.'_enable', '==', 'enable'),
                ),
                'woo_'.$preflix.'_mobile'  => array(
                    'id'         => 'woo_'.$preflix.'_mobile',
                    'type'       => 'slider',
                    'title'      => sprintf(esc_html__('%s products items per row on Mobile', 'ovic-addon-toolkit'), $name),
                    'desc'       => esc_html__('(Screen resolution of device < 480px)', 'ovic-addon-toolkit'),
                    'min'        => 1,
                    'max'        => 6,
                    'step'       => 1,
                    'unit'       => esc_html__('item(s)', 'ovic-addon-toolkit'),
                    'default'    => '1',
                    'dependency' => array('woo_'.$preflix.'_enable', '==', 'enable'),
                ),
            ),
        );

        if ($preflix == 'related') {
            array_splice($args['fields'], 3, 0,
                array(
                    'woo_'.$preflix.'_perpage' => array(
                        'id'         => 'woo_'.$preflix.'_perpage',
                        'type'       => 'spinner',
                        'title'      => sprintf(esc_html__('%s products Items', 'ovic-addon-toolkit'), $name),
                        'desc'       => sprintf(esc_html__('Number %s products to show', 'ovic-addon-toolkit'), $name),
                        'dependency' => array('woo_'.$preflix.'_enable', '==', 'enable'),
                        'default'    => '6',
                        'unit'       => 'items',
                    ),
                )
            );
        }

        return $args;
    }
}