<?php
/**
 * Review Average
 *
 * @var $average
 * @var $stars
 * @var $review_count
 * @var $rating_count
 *
 * @package ovic-addon-toolkit/templates
 * @version 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="ovic-panel-rating">
    <div class="average">
        <span><?php echo esc_html( $average ); ?>★</span>
        <p><?php esc_html_e( 'Rating', 'ovic-addon-toolkit' ); ?></p>
    </div>
    <ul class="detail">
		<?php foreach ( $stars as $key => $rating ): ?>
			<?php
			$process = 0;
			if ( $rating > 0 ) {
				$process = ( $rating / $rating_count ) * 100;
			}
			?>
            <li>
                <span class="star"><?php echo esc_html( $key ); ?>★</span>
                <span class="process">
                    <span class="process-bar" style="width:<?php echo esc_attr( $process ); ?>%"></span>
                </span>
                <span class="count"><?php echo esc_html( $rating ); ?></span>
            </li>
		<?php endforeach; ?>
    </ul>
</div>
