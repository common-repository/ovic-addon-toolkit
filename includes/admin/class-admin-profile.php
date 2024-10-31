<?php
/**
 * Add extra profile fields for users in admin
 *
 * @author   KuteThemes
 * @category Admin
 * @package  Ovic/Admin
 * @version  1.0.1
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('Ovic_Admin_Profile')) :
    /**
     * Ovic_Admin_Profile Class.
     */
    class Ovic_Admin_Profile
    {
        public $placeholder = OVIC_PLUGIN_URL.'assets/images/avatar.jpg';

        /**
         * Hook in tabs.
         */
        public function __construct()
        {
            add_filter('user_contactmethods', array($this, 'author_social'));
            /* UPDATE AVATAR */
            add_filter('get_avatar', array($this, 'get_avatar'), 10, 5);
            add_action('show_user_profile', array($this, 'extra_profile_fields'));
            add_action('edit_user_profile', array($this, 'extra_profile_fields'));
            add_action('profile_update', array($this, 'profile_update'));
            add_action('user_register', array($this, 'profile_update'));
        }

        function author_social()
        {
            $contactmethods              = array();
            $contactmethods['twitter']   = esc_html__('Twitter', 'ovic-addon-toolkit');
            $contactmethods['facebook']  = esc_html__('Facebook', 'ovic-addon-toolkit');
            $contactmethods['instagram'] = esc_html__('Instagram', 'ovic-addon-toolkit');
            $contactmethods['youtube']   = esc_html__('Youtube', 'ovic-addon-toolkit');
            $contactmethods['google']    = esc_html__('Google Plus', 'ovic-addon-toolkit');
            $contactmethods['linkedin']  = esc_html__('Linkedin', 'ovic-addon-toolkit');
            $contactmethods['pinterest'] = esc_html__('Pinterest', 'ovic-addon-toolkit');

            return $contactmethods;
        }

        function extra_profile_fields($user)
        {
            if (isset($user->roles) && in_array('dc_vendor', $user->roles)) {
                return;
            }

            $avatar_id = 0;

            if (!empty($user->ID)) {

                $data = $this->get_image($user->ID, 'thumbnail', true);

                $avatar_id = $data['id'];
                $image_url = $data['avatar'];

            } else {

                $image_url = $this->placeholder;

            }
            ?>
            <script type="application/javascript">
                /* SELECT IMAGE */
                jQuery(document).on('click', '.upload_avatar_button', function (event) {

                    event.preventDefault();

                    var _file_frame,
                        _this   = jQuery(this),
                        _parent = _this.closest('.field-image-select'),
                        _input  = _parent.find('.avatar_thumbnail_id'),
                        _img    = _parent.find('.avatar_thumbnail');

                    // If the media frame already exists, reopen it.
                    if (_file_frame) {
                        _file_frame.open();
                        return;
                    }

                    // Create the media frame.
                    _file_frame = wp.media.frames.downloadable_file = wp.media({
                        title   : 'Choose an image',
                        button  : {
                            text: 'Use image'
                        },
                        multiple: false
                    });

                    // When an image is selected, run a callback.
                    _file_frame.on('select', function () {
                        var attachment           = _file_frame.state().get('selection').first().toJSON();
                        var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

                        _input.val(attachment.id);
                        _img.find('img').attr('src', attachment_thumbnail.url);
                        _parent.find('.remove_image_button').show();
                    });

                    // Finally, open the modal.
                    _file_frame.open();
                });
                jQuery(document).on('click', '.remove_avatar_button', function (e) {
                    jQuery(this).closest('.field-image-select').find('img').attr('src', '<?php echo esc_js($this->placeholder); ?>');
                    jQuery(this).closest('.field-image-select').find('.avatar_thumbnail_id').val(0);
                    jQuery(this).closest('.field-image-select').find('.remove_image_button').hide();
                    e.preventDefault();
                });
            </script>
            <table class="form-table fh-profile-upload-options">
                <tr>
                    <th>
                        <label for="image"><?php _e('Avatar Profile', 'ovic-addon-toolkit') ?></label>
                    </th>

                    <td>
                        <div class="field-image-select">
                            <div class="avatar_thumbnail" style="float: left; margin-right: 10px;">
                                <img src="<?php echo esc_url($image_url); ?>" width="60px" height="60px" alt="Avatar"/>
                            </div>
                            <div style="line-height: 60px;">
                                <input type="hidden" class="avatar_thumbnail_id" name="avatar_user_id"
                                       value="<?php echo esc_attr($avatar_id); ?>"/>
                                <input type="hidden" name="avatar_site_id"
                                       value="<?php echo get_current_blog_id(); ?>"/>
                                <button type="button" class="upload_avatar_button button">
                                    <?php _e('Upload/Add image', 'ovic-addon-toolkit'); ?>
                                </button>
                                <button type="button" class="remove_avatar_button button">
                                    <?php _e('Remove image', 'ovic-addon-toolkit'); ?>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <?php
        }

        function profile_update($user_id)
        {
            if (current_user_can('edit_users')) {
                $profile_pic = !empty($_POST['avatar_user_id']) ? absint($_POST['avatar_user_id']) : '';
                $site_id     = !empty($_POST['avatar_site_id']) ? absint($_POST['avatar_site_id']) : '';
                update_user_meta($user_id, 'avatar_user_id', $profile_pic);
                update_user_meta($user_id, 'avatar_site_id', $site_id);
            }
        }

        function get_image($user_id, $size, $return_url = false)
        {
            $size       = is_string($size) ? $size : array($size, $size);
            $avatar_id  = get_user_meta($user_id, 'avatar_user_id', true);
            $site_id    = get_user_meta($user_id, 'avatar_site_id', true);
            $size_class = $size;

            if (is_array($size_class)) {
                $size_class = join('x', $size_class);
            }

            $attr = array(
                'class' => "avatar attachment-$size_class size-$size_class"
            );

            if ($avatar_id) {

                if ($return_url) {
                    $avatar = wp_get_attachment_image_url($avatar_id, $size);
                } else {
                    $avatar = wp_get_attachment_image($avatar_id, $size, false, $attr);
                }

                if (empty($avatar) && is_multisite() && !empty($site_id) && get_current_blog_id() != $site_id) {

                    switch_to_blog($site_id);

                    if ($return_url) {
                        $avatar = wp_get_attachment_image_url($avatar_id, $size);
                    } else {
                        $avatar = wp_get_attachment_image($avatar_id, $size, false, $attr);
                    }

                    restore_current_blog();

                }

                return array(
                    'id'     => $avatar_id,
                    'avatar' => $avatar,
                );
            }

            if ($return_url) {
                $avatar = $this->placeholder;
            } else {
                $avatar = "<img src='{$this->placeholder}' width='60px' height='60px' alt='Placeholder Avatar'/>";
            }

            return array(
                'id'     => 0,
                'avatar' => $avatar,
            );
        }

        function get_avatar($avatar, $id_or_email, $size, $default, $alt)
        {
            $user = false;

            if (is_numeric($id_or_email)) {
                $id   = (int) $id_or_email;
                $user = get_user_by('id', $id);
            } elseif (is_object($id_or_email)) {
                if (!empty($id_or_email->user_id)) {
                    $id   = (int) $id_or_email->user_id;
                    $user = get_user_by('id', $id);
                }
            } else {
                $user = get_user_by('email', $id_or_email);
            }

            if ($user && is_object($user) && !empty($user->ID)) {

                $data = $this->get_image($user->ID, $size);

                if ($data['id'] > 0) {
                    return $data['avatar'];
                }

            }

            return $avatar;
        }
    }
endif;

return new Ovic_Admin_Profile();
