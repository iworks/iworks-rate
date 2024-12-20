<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
echo '<p>';
echo wp_kses_post(
	__( 'Do you have a technical problem? Please contact us. We will be happy to help you. Or maybe you have an idea for a new feature? Please let us know about it by filling the support form. We will try to add it!', 'IWORKS_RATE_TEXTDOMAIN' )
);
echo '</p>';
echo '<p>';
echo wp_kses_post(
	sprintf(
		/* translators: %1$s: open anchor tag, %2$s: close anchor tag, %3$s: open anchor tag, %4$s: close anchor tag */
		__( 'Please %1$scheck our FAQ%2$s before adding a thread with technical problem. If you do not find help there, %3$scheck support forum%4$s for similar problems.', 'IWORKS_RATE_TEXTDOMAIN' ),
		'<a href="' . esc_url( $args['url'] ) . '#faq" target="_blank">',
		'</a>',
		'<a href="' . esc_url( $args['support_url'] ) . '" target="_blank">',
		'</a>'
	)
);
echo '</p>';
echo '<p class="iworks-rate-center">';
?>
<a href="<?php echo esc_url( $args['support_url'] ); ?>" target="_blank" class="iworks-rate-button iworks-rate-button--blue" ><?php echo esc_html( __( 'Get help', 'IWORKS_RATE_TEXTDOMAIN' ) ); ?></a>
<?php
echo '</p>';
echo '<p>';
echo wp_kses_post(
	__( 'Do you like our plugin? Could you rate him? Please let us know what you think about our plugin. It is important that we can develop this tool. Thank you for all the ratings, reviews and donates.', 'IWORKS_RATE_TEXTDOMAIN' )
);
echo '</p>';
echo '<p class="iworks-rate-center">';
?>
<a href="<?php echo esc_url( add_query_arg( 'rate', '5', $args['support_url'] . '/reviews/' ) ); ?>#new-post" target="_blank" class="iworks-rate-button iworks-rate-button--blue" ><?php echo esc_html( __( 'Add review', 'IWORKS_RATE_TEXTDOMAIN' ) ); ?></a>
</p>
