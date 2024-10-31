<?php
/**
 * Ovic Megamenu Form
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Megamenu_Form
 * @since    1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

include_once dirname(__FILE__).'/icon-fonts.php';
/*
 * Param
 * */
$icons        = ovic_megamenu_font_icons();
$options_menu = Ovic_Megamenu_Settings::get_post_megamenu();
$button       = esc_html__('LOAD EDITOR', 'ovic-addon-toolkit');
$descriptions = esc_html__('Click "Enable Mega Builder" in the Settings tab before building content.', 'ovic-addon-toolkit');
?>
<div class="ovic-content-tmp-menu"></div>
<script id="tmpl-ovic-megamenu-settings" type="text/template">
    <form id="ovic-menu-popup-settings-{{data.item_id}}"
          class="ovic-menu-popup-settings"
          data-item_id="{{data.item_id}}"
          data-button_txt="<?php echo esc_attr($button); ?>"
          data-desc_txt="<?php echo esc_attr($descriptions); ?>"
          method="post">
        <div class="head">
            <span class="menu-title"><?php esc_html_e('Menu: ', 'ovic-addon-toolkit'); ?>{{data.title}}</span>
            <div class="control">
                <button class="ovic-menu-save-settings button button-primary">
                    <?php esc_html_e('Save All', 'ovic-addon-toolkit'); ?>
                </button>
            </div>
        </div>
        <div class="tabs-settings">
            <ul>
                <li class="active">
                    <a href=".ovic-menu-tab-settings">
                        <span class="icon dashicons dashicons-admin-generic"></span>
                        <?php esc_html_e('Settings', 'ovic-addon-toolkit'); ?>
                    </a>
                </li>
                <li>
                    <a href=".ovic-menu-tab-icons">
                        <span class="icon dashicons dashicons-image-filter"></span>
                        <?php esc_html_e('Icons', 'ovic-addon-toolkit'); ?>
                    </a>
                </li>
                <# if ( data.item_depth == 0 ) { #>
                <li class="ovic-menu-setting-for-depth-0">
                    <a class="link-open-menu-buider" href=".ovic-menu-tab-builder">
                        <span class="icon dashicons dashicons-welcome-widgets-menus"></span>
                        <?php esc_html_e('Content', 'ovic-addon-toolkit'); ?>
                    </a>
                </li>
                <# } #>
            </ul>
        </div>
        <div class="tab-container">
            <div class="ovic-menu-tab-content active ovic-menu-tab-settings">
                <div class="vc_col-xs-12 vc_column wpb_el_type_checkbox">
                    <div class="wpb_element_label"><?php esc_html_e('Top Level Item Settings', 'ovic-addon-toolkit'); ?></div>
                    <# if ( data.item_depth == 0 ) { #>
                    <div class="edit_form_line submenu-item-bg ovic-menu-setting-for-depth-0">
                        <div class="heading">
                            <span class="title">
                                <?php esc_html_e('Class Megamenu Responsive', 'ovic-addon-toolkit'); ?>
                            </span>
                        </div>
                        <div class="value">
                            <input value="{{data.settings.mega_responsive}}"
                                   class="wpb_vc_param_value wpb-textinput el_class textfield"
                                   name="mega_responsive" type="text">
                            <?php esc_html_e("Field empty value is default ( .ovic-menu-wapper )", 'ovic-addon-toolkit'); ?>
                        </div>
                    </div>
                    <div class="edit_form_line ovic-menu-setting-for-depth-0">
                        <div class="heading">
                            <span class="title"><?php esc_html_e('Enable Mega', 'ovic-addon-toolkit'); ?></span>
                        </div>
                        <div class="value">
                            <label class="switch">
                                <input value="1"
                                       class="wpb_vc_param_value wpb-textinput enable_mega"
                                       name="enable_mega" <# if ( data.settings.enable_mega == 1 ) { #> checked <# } #>
                                type="checkbox">
                                <span class="slider round"></span>
                            </label>
                            <label class="select-menu">
                                <select name="menu_content_id"
                                        class="select_id_megamenu <# if ( data.settings.enable_mega != 1 ) { #>hidden<# } #>">
                                    <?php if (!empty($options_menu)): ?>
                                        <?php foreach ($options_menu as $id => $title): ?>
                                            <option value="<?php echo esc_attr($id); ?>"
                                            <# if ( data.settings.menu_content_id == <?php echo esc_js($id); ?> ) { #> selected <# } #>>
                                            <?php echo esc_html($title); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <button class="remove_megamenu button ovic-warning-primary <# if ( data.settings.enable_mega != 1 ) { #>hidden<# } #>"
                                        title="<?php esc_attr_e('Trash this selected megamenu content.', 'ovic-addon-toolkit'); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                    <?php esc_html_e('Trash', 'ovic-addon-toolkit'); ?>
                                </button>
                                <a href="{{data.iframe}}" target="_blank"
                                   class="edit_megamenu button button-primary <# if ( data.settings.enable_mega != 1 ) { #>hidden<# } #>"
                                   title="<?php esc_attr_e('Edit this selected megamenu in new window.', 'ovic-addon-toolkit'); ?>">
                                    <span class="dashicons dashicons-edit"></span>
                                    <?php esc_html_e('Edit', 'ovic-addon-toolkit'); ?>
                                </a>
                                <span class="spinner"></span>
                            </label>
                        </div>
                    </div>
                    <# } #>
                    <div class="edit_form_line">
                        <div class="heading">
							<span class="title">
								<?php esc_html_e('Hide title', 'ovic-addon-toolkit'); ?>
							</span>
                            <span class="description">
								<?php esc_html_e('Whether to display item without text or not.', 'ovic-addon-toolkit'); ?>
							</span>
                        </div>
                        <div class="value">
                            <label class="switch">
                                <input value="1" class="wpb_vc_param_value wpb-textinput"
                                       name="hide_title" <# if ( data.settings.hide_title == 1 ) { #> checked <# } #>
                                type="checkbox">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="edit_form_line">
                        <div class="heading">
							<span class="title">
								<?php esc_html_e('Disable link', 'ovic-addon-toolkit'); ?>
							</span>
                            <span class="description">
								<?php esc_html_e('Whether to disable item hyperlink or not.', 'ovic-addon-toolkit'); ?>
							</span>
                        </div>
                        <div class="value">
                            <label class="switch">
                                <input value="1" class="wpb_vc_param_value wpb-textinput"
                                       name="disable_link" type="checkbox" <# if ( data.settings.disable_link == 1 ) {
                                #> checked <# } #>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <# if ( data.item_depth == 0 ) { #>
                    <div class="wpb_element_label">
                        <?php esc_html_e('Sub Menu Item Settings', 'ovic-addon-toolkit'); ?>
                    </div>
                    <div class="edit_form_line submenu-item-with ovic-menu-setting-for-depth-0">
                        <div class="heading">
                            <span class="title">
                                <?php esc_html_e('Sub menu item width (px only)', 'ovic-addon-toolkit'); ?>
                            </span>
                        </div>
                        <div class="value">
                            <input value="{{data.settings.menu_width}}"
                                   class="wpb_vc_param_value wpb-textinput el_class textfield"
                                   name="menu_width" type="text">
                        </div>
                    </div>
                    <div class="edit_form_line submenu-item-bg ovic-menu-setting-for-depth-0">
                        <div class="heading">
                            <span class="title"><?php esc_html_e('Menu Background', 'ovic-addon-toolkit'); ?></span>
                        </div>
                        <div class="value field-image-select">
                            <div class="preview_thumbnail">
                                <img src="{{data.settings.bg_thumbnail}}" width="60px" height="60px"/>
                            </div>
                            <div style="line-height: 60px;">
                                <input type="hidden" class="process_custom_images" name="menu_bg"
                                       value="{{data.settings.menu_bg}}"/>
                                <button type="button"
                                        class="upload_image_button button">
                                    <?php _e('Upload/Add image', 'ovic-addon-toolkit'); ?>
                                </button>
                                <button type="button" class="remove_image_button button"
                                <# if ( data.settings.menu_bg == 0 ) { #> style="display:none;" <# } #>>
                                <?php _e('Remove image', 'ovic-addon-toolkit'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="edit_form_line submenu-item-bg ovic-menu-setting-for-depth-0">
                        <div class="heading">
								<span class="title">
									<?php esc_html_e('Background Position', 'ovic-addon-toolkit'); ?>
								</span>
                        </div>
                        <div class="value">
                            <select name="bg_position" class="wpb_vc_param_value">
                                <option value="center"
                                <# if ( data.settings.bg_position === 'center' ) { #> selected <# } #>>
                                <?php esc_html_e('Center', 'ovic-addon-toolkit'); ?>
                                </option>
                                <option value="left"
                                <# if ( data.settings.bg_position === 'left' ) { #> selected <# } #>>
                                <?php esc_html_e('Left', 'ovic-addon-toolkit'); ?>
                                </option>
                                <option value="right"
                                <# if ( data.settings.bg_position === 'right' ) { #> selected <# } #>>
                                <?php esc_html_e('Right', 'ovic-addon-toolkit'); ?>
                                </option>
                                <option value="top"
                                <# if ( data.settings.bg_position === 'top' ) { #> selected <# } #>>
                                <?php esc_html_e('Top', 'ovic-addon-toolkit'); ?>
                                </option>
                                <option value="bottom"
                                <# if ( data.settings.bg_position === 'bottom' ) { #> selected <# } #>>
                                <?php esc_html_e('Bottom', 'ovic-addon-toolkit'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                    <# } #>
                </div>
            </div>
            <div class="ovic-menu-tab-content ovic-menu-tab-icons">
                <div class="wpb_element_label">
                    <?php esc_html_e('Icon Settings', 'ovic-addon-toolkit'); ?>
                </div>
                <div class="radio-inline">
                    <select class="menu_icon_type" name="menu_icon_type">
                        <option
                        <# if ( data.menu_icon_type === 'font-icon' ) { #> selected <# } #> value="font-icon">
                        <?php esc_html_e('Use Font Icon', 'ovic-addon-toolkit'); ?>
                        </option>
                        <option
                        <# if ( data.menu_icon_type === 'image' ) { #> selected <# } #> value="image">
                        <?php esc_html_e('Use Image', 'ovic-addon-toolkit'); ?>
                        </option>
                    </select>
                </div>

                <div class="edit_form_line field-icon-settings icon-setting-tab"
                <# if ( data.menu_icon_type === 'font-icon' ) { #> style="display: block;" <# } #>>
                <input class="ovic_menu_settings_menu_icon" type="hidden" name="menu_icon"
                       value="{{data.settings.menu_icon}}">
                <div class="selector">
						<span class="selected-icon">
							<i class="{{data.settings.menu_icon}}"></i>
						</span>
                    <span class="selector-button remove">
							<i class="fip-fa dashicons dashicons-no-alt"></i>
						</span>
                </div>
                <div class="selector-popup">
                    <div class="tab-icons">
                        <?php foreach ($icons as $key => $icon) : ?>
                            <?php
                            $id    = '.container-icon-'.$key;
                            $class = ($key == 0) ? 'tab active' : 'tab';
                            if (!empty($icon['icons'])):
                                ?>
                                <a href="<?php echo esc_attr($id); ?>"
                                   class="<?php echo esc_attr($class); ?>">
                                    <?php echo esc_html($icon['title']); ?>
                                </a>
                            <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                    <div class="selector-search">
                        <input type="text" class="icons-search-input"
                               placeholder="<?php esc_html_e('Search Icon', 'ovic-addon-toolkit'); ?>"
                               value="" name="">
                    </div>
                    <div class="fip-icons-container"
                         data-selected="{{data.settings.menu_icon}}">
                        <?php foreach ($icons as $key => $icon) : ?>
                            <?php
                            $classes = 'contain container-icon-'.$key;
                            if ($key == 0) {
                                $classes .= ' active';
                            }
                            ?>
                            <?php if (!empty($icon['icons'])): ?>
                                <div class="<?php echo esc_attr($classes); ?>">
                                    <?php foreach ($icon['icons'] as $icon_array) : ?>
                                        <?php foreach ($icon_array as $class => $name) : ?>
                                            <span class="icon"
                                                  data-value="<?php echo esc_attr($class); ?>"
                                                  title="<?php echo esc_attr($name); ?>">
											<i class="<?php echo esc_attr($class); ?>"></i>
										</span>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="edit_form_line field-image-settings icon-setting-tab field-image-select"
            <# if ( data.menu_icon_type === 'image' ) { #> style="display: block;" <# } #>>
            <div class="preview_thumbnail">
                <img src="{{data.settings.icon_image_thumb}}" width="60px" height="60px"/>
            </div>
            <div style="line-height: 60px;">
                <input type="hidden" class="process_custom_images" name="icon_image"
                       value="{{data.settings.icon_image}}"/>
                <button type="button"
                        class="upload_image_button button">
                    <?php _e('Upload/Add image', 'ovic-addon-toolkit'); ?>
                </button>
                <button type="button" class="remove_image_button button"
                <# if ( data.settings.icon_image == 0 ) { #> style="display:none;" <# } #>>
                <?php _e('Remove image', 'ovic-addon-toolkit'); ?>
                </button>
            </div>
        </div>
        <div class="label-image-settings edit_form_line field-image-select">
            <div class="wpb_element_label"><?php esc_html_e('Label Settings', 'ovic-addon-toolkit'); ?></div>
            <div class="preview_thumbnail">
                <img src="{{data.settings.label_image_thumb}}" width="60px" height="60px"/>
            </div>
            <div style="line-height: 60px;">
                <input type="hidden" class="process_custom_images" name="label_image"
                       value="{{data.settings.label_image}}"/>
                <button type="button"
                        class="upload_image_button button">
                    <?php _e('Upload/Add image', 'ovic-addon-toolkit'); ?>
                </button>
                <button type="button" class="remove_image_button button"
                <# if ( data.settings.label_image == 0 ) { #> style="display:none;" <# } #>>
                <?php _e('Remove image', 'ovic-addon-toolkit'); ?>
                </button>
            </div>
        </div>
        </div>
        <# if ( data.item_depth == 0 ) { #>
        <div class="ovic-menu-tab-content ovic-menu-tab-builder ovic-menu-setting-for-depth-0">
            <# if ( data.settings.enable_mega !== 0 ) { #>
            <p class="button-builder">
                <a href="{{data.iframe}}"
                   data-post_id="{{data.settings.menu_content_id}}"
                   class="button button-primary button-hero button-updater load-content-iframe">
                    <?php echo esc_html($button); ?>
                </a>
            </p>
            <# } else { #>
            <div class="desc-builder">
                <?php echo esc_html($descriptions); ?>
            </div>
            <# } #>
        </div>
        <# } #>
        </div>
        <button title="Close (Esc)" type="button" class="content-menu-close">×</button>
    </form>
</script>