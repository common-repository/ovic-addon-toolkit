<?php
/**
 * Notice Popup
 *
 * @package ovic-addon-toolkit/templates
 * @version 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<script type="text/template" id="tmpl-ovic-notice-popup">
    <# if ( data.img_url != '' ) { #>
    <figure>
        <img src="{{data.img_url}}" alt="{{data.title}}" class="growl-thumb"/>
    </figure>
    <# } #>
    <p class="growl-content">
        <# if ( data.title != '' ) { #>
        <span>{{data.title}}</span>
        <# } #>
        {{{data.content}}}
    </p>
</script>
