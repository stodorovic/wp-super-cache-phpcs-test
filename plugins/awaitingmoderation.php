<?php

function awaitingmoderation_action( $buffer ) {
	$buffer = str_replace( esc_html__( 'Your comment is awaiting moderation.', 'wp-super-cache' ), '', $buffer );
	return $buffer;
}

function awaitingmoderation_actions() {
	global $cache_awaitingmoderation;
	if ( '1' === $cache_awaitingmoderation ) {
		add_filter( 'wpsupercache_buffer', 'awaitingmoderation_action' );
	}
}
add_cacheaction( 'add_cacheaction', 'awaitingmoderation_actions' );

/**
 * Your comment is awaiting moderation.
 */
function wp_supercache_awaitingmoderation_admin() {
	global $cache_awaitingmoderation, $wp_cache_config_file, $valid_nonce;

	$cache_awaitingmoderation = '' === $cache_awaitingmoderation ? '0' : $cache_awaitingmoderation;

	if ( isset( $_POST['cache_awaitingmoderation'] ) && $valid_nonce ) {
		$cache_awaitingmoderation = (int) $_POST['cache_awaitingmoderation'];
		wp_cache_replace_line( '^ *\$cache_awaitingmoderation', "\$cache_awaitingmoderation = '$cache_awaitingmoderation';", $wp_cache_config_file );
		$changed = true;
	} else {
		$changed = false;
	}

	$id = 'awaitingmoderation-section';
	?>
	<fieldset id="<?php echo esc_attr( $id ); ?>" class="options">

		<h4><?php esc_html_e( 'Awaiting Moderation', 'wp-super-cache' ); ?></h4>

		<form name="wp_manager" action="" method="post">
		<label><input type="radio" name="cache_awaitingmoderation" value="1" <?php checked( $cache_awaitingmoderation ); ?>/> <?php esc_html_e( 'Enabled', 'wp-super-cache' ); ?></label>
		<label><input type="radio" name="cache_awaitingmoderation" value="0" <?php checked( ! $cache_awaitingmoderation ); ?>/> <?php esc_html_e( 'Disabled', 'wp-super-cache' ); ?></label>
		<p><?php esc_html_e( 'Enables or disables plugin to Remove the text "Your comment is awaiting moderation." when someone leaves a moderated comment.', 'wp-super-cache' ); ?></p>
		<?php
		if ( $changed ) {
			echo '<p><strong>' . sprintf( esc_html__( 'Awaiting Moderation is now %s', 'wp-super-cache' ),
				esc_html( $cache_awaitingmoderation ? __( 'enabled', 'wp-super-cache' ) : __( 'disabled', 'wp-super-cache' ) )
			) . '</strong></p>';
		}
		echo '<div class="submit"><input class="button-primary" ' . SUBMITDISABLED . 'ntype="submit" value="' . esc_html__( 'Update', 'wp-super-cache' ) . '" /></div>';
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
