<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly
echo '<p>';
esc_html_e( 'Would you like to boost your website sharing abilities?', 'IWORKS_RATE_TEXTDOMAIN' );
echo '</p>';
echo '<p>';
printf(
	/* translators: %s is a plugin name with url */
	esc_html__( 'Don\'t wait, install plugin %s!', 'IWORKS_RATE_TEXTDOMAIN' ),
	/* translators: %s is a plugin wp home name, %s plugin name */
	sprintf(
		'<a href="%s" target="_blank"><strong>%s</strong></a>',
		esc_url( $args['plugin_wp_home'] ),
		esc_html( $args['plugin_name'] )
	)
);
echo '</p>';
?>
<p class="iworks-rate-center"><a href="<?php echo esc_url( $args['install_plugin_url'] ); ?>" class="iworks-rate-button iworks-rate-button--green dashicons-admin-plugins
"><?php echo esc_html( __( 'Install', 'IWORKS_RATE_TEXTDOMAIN' ) ); ?></a></p>

