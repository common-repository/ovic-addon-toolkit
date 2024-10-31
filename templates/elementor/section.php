<?php
/**
 * @package ovic-addon-toolkit/templates
 * @var $shortcode
 * @var $template
 * @var $element
 */

$is_dom_optimization_active = true;

if (version_compare(ELEMENTOR_VERSION, '3.1.0', '>=')) {
    $is_dom_optimization_active = Elementor\Plugin::instance()->experiments->is_feature_active( 'e_dom_optimization' );
}
?>
<#
if ( settings.background_video_link ) {
    let videoAttributes = 'autoplay muted playsinline';

    if ( ! settings.background_play_once ) {
        videoAttributes += ' loop';
    }

    view.addRenderAttribute( 'background-video-container', 'class', 'elementor-background-video-container' );

    if ( ! settings.background_play_on_mobile ) {
        view.addRenderAttribute( 'background-video-container', 'class', 'elementor-hidden-phone' );
    }
#>
    <div {{{ view.getRenderAttributeString( 'background-video-container' ) }}}>
        <div class="elementor-background-video-embed"></div>
        <video class="elementor-background-video-hosted elementor-html5-video" {{ videoAttributes }}></video>
    </div>
<# } #>
<div class="elementor-background-overlay"></div>
<div class="elementor-shape elementor-shape-top"></div>
<div class="elementor-shape elementor-shape-bottom"></div>
<#
view.addRenderAttribute( 'slide-container', 'class', 'elementor-container elementor-column-gap-' + settings.gap );
view.addRenderAttribute( 'slide-row', 'class', 'elementor-row' );

<?php if ($is_dom_optimization_active) { ?>
    view.addRenderAttribute( 'slide-container', 'class', 'elementor-row-wrap' );
<?php } ?>

if ( settings._use_slide == 'yes' ) {
    var slide_data = {
        slidesToShow:parseInt(settings.slides_to_show),
        slidesMargin:parseInt(settings.slides_margin),
        rows:parseInt(settings.slides_rows),
        arrows:false,
        dots:false,
    };
    var slide_inner = 'slide-container';
    if ( settings.slides_vertical == 'yes' ) {
        slide_data.vertical = true;
    }
    if ( settings.slides_navigation == 'both' || settings.slides_navigation == 'arrows' ) {
        slide_data.arrows = true;
    }
    if ( settings.slides_navigation == 'both' || settings.slides_navigation == 'dots' ) {
        slide_data.dots = true;
    }
    slide_data = JSON.stringify(slide_data);

    <?php if (!$is_dom_optimization_active) { ?>
        slide_inner = 'slide-row';
    <?php } ?>

    view.addRenderAttribute( slide_inner, 'class', 'owl-slick' );
    view.addRenderAttribute( slide_inner, 'class', settings.slides_rows_space );
    view.addRenderAttribute( slide_inner, 'data-slick', slide_data );
}
#>
<div {{{ view.getRenderAttributeString( 'slide-container' ) }}}>
    <?php if (!$is_dom_optimization_active) { ?>
        <div {{{ view.getRenderAttributeString( 'slide-row' ) }}}></div>
    <?php } ?>
</div>