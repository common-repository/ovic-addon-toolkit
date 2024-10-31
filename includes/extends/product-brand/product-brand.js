(function ( $ ) {
    'use strict';

    // Only show the "remove image" button when needed
    jQuery( '.product_brand_thumbnail_id' ).each( function () {
        if ( !jQuery( this ).val() || '0' === jQuery( this ).val() ) {
            jQuery( this ).closest( '.field-image-select' ).find( '.remove_image_button' ).hide();
        }
    } );

    /* SELECT IMAGE */
    jQuery( document ).on( 'click', '.upload_image_button', function ( event ) {

        event.preventDefault();

        var file_frame,
            button = jQuery( this ),
            parent = button.closest( '.field-image-select' ),
            input  = parent.find( '.product_brand_thumbnail_id' ),
            img    = parent.find( '.product_brand_thumbnail' );

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.downloadable_file = wp.media( {
            title: 'Choose an image',
            button: {
                text: 'Use image'
            },
            multiple: false
        } );

        // When an image is selected, run a callback.
        file_frame.on( 'select', function () {
            var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
            var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

            input.val( attachment.id );
            img.find( 'img' ).attr( 'src', attachment_thumbnail.url );
            parent.find( '.remove_image_button' ).show();
        } );

        // Finally, open the modal.
        file_frame.open();
    } );

    jQuery( document ).on( 'click', '.remove_image_button', function ( e ) {
        jQuery( this ).closest( '.field-image-select' ).find( 'img' ).attr( 'src', product_brand_params.placeholder );
        jQuery( this ).closest( '.field-image-select' ).find( '.product_brand_thumbnail_id' ).val( 0 );
        jQuery( this ).closest( '.field-image-select' ).find( '.remove_image_button' ).hide();
        e.preventDefault();
    } );

    jQuery( document ).ajaxComplete( function ( event, request, options ) {
        if ( request && 4 === request.readyState && 200 === request.status
            && options.data && 0 <= options.data.indexOf( 'action=add-tag' ) ) {

            var res = wpAjax.parseAjaxResponse( request.responseXML, 'ajax-response' );
            if ( !res || res.errors ) {
                return;
            }
            // Clear Thumbnail fields on submit
            jQuery( event.target ).find( '.product_brand_thumbnail' ).find( 'img' ).attr( 'src', product_brand_params.placeholder );
            jQuery( event.target ).find( '.product_brand_thumbnail_id' ).val( '' );
            jQuery( event.target ).find( '.remove_image_button' ).hide();
            return;
        }
    } );

})( window.jQuery );