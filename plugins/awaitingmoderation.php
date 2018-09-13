<?php

function awaitingmoderation_action( $buffer ) {
	$buffer = str_replace( esc_html__( 'Your comment is awaiting moderation.', 'wp-super-cache' ), '', $buffer );
	return $buffer;
}

function awaitingmoderation_actions() {
	global $cache_awaitingmoderation;

	if ( 1 !== $cache_awaitingmoderation ) {
		return;
	}

	add_filter( 'wpsupercache_buffer', 'awaitingmoderation_action' );
}
add_cacheaction( 'add_cacheaction', 'awaitingmoderation_actions' );

/**
 * Your comment is awaiting moderation.
 */
function wp_supercache_awaitingmoderation_admin() {
	global $cache_awaitingmoderation, $wp_cache_config_file, $valid_nonce;

	$requested_state          = isset( $_POST['cache_awaitingmoderation'] ) ? (int) $_POST['cache_awaitingmoderation'] : null;
	$cache_awaitingmoderation = (int) $cache_awaitingmoderation;

	$changed = false;
	if ( null !== $requested_state && $valid_nonce ) {
		$cache_awaitingmoderation = $requested_state;
		wp_cache_replace_line( '^\s*\$cache_awaitingmoderation\s*=', '$cache_awaitingmoderation = ' . intval( $cache_awaitingmoderation ) . ';', $wp_cache_config_file );
		$changed = true;
	}

	$id = 'awaitingmoderation-section';
	?>
	<fieldset id="<?php echo esc_attr( $id ); ?>" class="options">

		<h4><?php esc_html_e( 'Awaiting Moderation', 'wp-super-cache' ); ?></h4>

		<form name="wp_manager" action="" method="post">
		<label><input type="radio" name="cache_awaitingmoderation" value="1" <?php checked( $cache_awaitingmoderation ); ?>/> <?php esc_html_e( 'Enabled', 'wp-super-cache' ); ?></label>
		<label><input type="radio" name="cache_awaitingmoderation" value="0" <?php checked( ! $cache_awaitingmoderation ); ?>/> <?php esc_html_e( 'Disabled', 'wp-super-cache' ); ?></label>
		<?php
		echo '<p>' . esc_html__( 'Enables or disables plugin to Remove the text "Your comment is awaiting moderation." when someone leaves a moderated comment.', 'wp-super-cache' ) . '</p';

		if ( $changed ) {
			echo '<p><strong>' . sprintf(
				esc_html__( 'Awaiting Moderation is now %s', 'wp-super-cache' ),
				esc_html( $cache_awaitingmoderation ? __( 'enabled', 'wp-super-cache' ) : __( 'disabled', 'wp-super-cache' ) )
			) . '</strong></p>';
		}

		echo '<div class="submit"><input class="button-primary" ' . SUBMITDISABLED . ' type="submit" value="' . esc_html__( 'Update', 'wp-super-cache' ) . '" /></div>';
		wp_nonce_field( 'wp-cache' );
		?>
		</form>

	</fieldset>
	<?php
}
add_cacheaction( 'cache_admin_page', 'wp_supercache_awaitingmoderation_admin' );

function wpsc_awaiting_moderation_list( $list ) {
	$list['awaitingmoderation'] = array(
		'key'   => 'awaitingmoderation',
		'url'   => '',
		'title' => esc_html__( 'Awaiting Moderation', 'wp-super-cache' ),
		'desc'  => esc_html__( 'Enables or disables plugin to Remove the text "Your comment is awaiting moderation." when someone leaves a moderated comment.', 'wp-super-cache' ),
	);
	return $list;
}
add_cacheaction( 'wpsc_filter_list', 'wpsc_awaiting_moderation_list' );
