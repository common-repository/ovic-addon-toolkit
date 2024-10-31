<?php
/**
 * Template Share Button
 *
 * @return string
 * @var $id
 *
 */
?>
<?php
if ($id != null) {
    $id = get_the_ID();
}
$share_image_url  = wp_get_attachment_image_url(get_post_thumbnail_id($id), 'full');
$share_link_url   = get_permalink($id);
$share_link_title = get_the_title();
$share_summary    = get_the_excerpt();
$twitter          = 'https://twitter.com/share?url='.$share_link_url.'&text='.$share_summary;
$facebook         = 'https://www.facebook.com/sharer.php?u='.$share_link_url;
$google           = 'https://plus.google.com/share?url='.$share_link_url.'&title='.$share_link_title;
$pinterest        = 'https://pinterest.com/pin/create/button/?url='.$share_link_url.'&description='.$share_summary.'&media='.$share_image_url;
?>
<div class="ovic-share-socials">
    <div class="inner">
        <a class="facebook"
           href="<?php echo esc_url($facebook); ?>"
           onclick='window.open(this.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600");return false;'>
            <span class="icon fa fa-facebook"></span>
            <span class="text"><?php echo esc_html__('Facebook', 'ovic-addon-toolkit'); ?></span>
        </a>
        <a class="twitter"
           href="<?php echo esc_url($twitter); ?>"
           onclick='window.open(this.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600");return false;'>
            <span class="icon fa fa-twitter"></span>
            <span class="text"><?php echo esc_html__('Twitter', 'ovic-addon-toolkit'); ?></span>
        </a>
        <a class="pinterest"
           href="<?php echo esc_url($pinterest); ?>"
           onclick='window.open(this.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600");return false;'>
            <span class="icon fa fa-pinterest-square"></span>
            <span class="text"><?php echo esc_html__('Pinterest', 'ovic-addon-toolkit'); ?></span>
        </a>
        <?php do_action('ovic_add_share_button'); ?>
    </div>
</div>