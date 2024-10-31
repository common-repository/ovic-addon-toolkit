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
$wrapper_element            = $is_dom_optimization_active ? 'widget' : 'column';
?>
<#
view.addRenderAttribute( 'slide-container', 'class', 'elementor-<?php echo $wrapper_element; ?>-wrap' );
view.addRenderAttribute( 'slide-row', 'class', 'elementor-widget-wrap' );

if ( settings._use_slide == 'yes' ) {
    var slide_inner = 'slide-container';
    var slide_data = {
        slidesToShow:parseInt(settings.slides_to_show),
        slidesMargin:parseInt(settings.slides_margin),
        rows:parseInt(settings.slides_rows),
        arrows:false,
        dots:false,
    };
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
    <# if ( settings.background_background == 'classic' || settings.background_background == 'gradient'  ) { #>
        <div class="elementor-background-overlay"></div>
    <# } #>
    <?php if (!$is_dom_optimization_active) { ?>
        <div {{{ view.getRenderAttributeString( 'slide-row' ) }}}></div>
    <?php } ?>
</div>