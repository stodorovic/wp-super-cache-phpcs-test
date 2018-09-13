<?php

function wp_supercache_badbehaviour( $file ) {
	global $cache_badbehaviour;

	if ( 1 !== $cache_badbehaviour ) {
		return $file;
	}
	wp_supercache_badbehaviour_include();
	return $file;
}
add_cacheaction( 'wp_cache_served_cache_file', 'wp_supercache_badbehaviour' );

function wp_supercache_badbehaviour_include() {
	$bbfile = get_bb_file_loc();
	if ( ! $bbfile ) {
		require_once $bbfile;
	}
}

function get_bb_file_loc() {
	global $cache_badbehaviour_file;

	if ( ! empty( $cache_badbehaviour_file ) ) {
		return $cache_badbehaviour_file;
	}

	foreach ( array( 'bad-behavior/bad-behavior-generic.php', 'Bad-Behavior/bad-behavior-generic.php' ) as $bbfile ) {
		if ( file_exists( WP_PLUGIN_DIR . '/' . $bbfile ) ) {
			return $bbfile;
		}
	}

	return false;
}

function wp_supercache_badbehaviour_admin() {
	global $cache_badbehaviour, $cache_badbehaviour_file, $wp_cache_config_file, $valid_nonce;

	$requested_state    = isset( $_POST['cache_badbehaviour'] ) ? (int) $_POST['cache_badbehaviour'] : null;
	$cache_badbehaviour = (int) $cache_badbehaviour;

	$error_message           = '';
	$cache_badbehaviour_file = '';
	$current_bbfile          = get_bb_file_loc();
	if ( ( $cache_badbehaviour || $requested_state ) && ! $current_bbfile ) {
		$error_message = __( 'Bad Behaviour not found. Please check your install.', 'wp-super-cache' );
	}

	$changed = false;
	if ( null !== $requested_state && $cache_badbehaviour !== $requested_state && $valid_nonce ) {
		$cache_badbehaviour = $current_bbfile ? $requested_state : 0;

		// Disable compression and remove rewrite rules if plugin is enabled.
		if ( $cache_badbehaviour ) {
			wp_cache_setting( 'cache_compression', 0 );
			wp_cache_setting( 'wp_cache_mod_rewrite', 0 );
			remove_mod_rewrite_rules();
		}

		// Update wp-cache-config.php.
		wp_cache_replace_line( '^\s*\$cache_badbehaviour\s*=', '$cache_badbehaviour = ' . intval( $cache_badbehaviour ) . ';', $wp_cache_config_file );
		wp_cache_replace_line( '^\s*\$cache_badbehaviour_file\s*=', "\$cache_badbehaviour_file = '$current_bbfile';", $wp_cache_config_file );
		
		$changed = true;
	}

	$id = 'badbehavior-section';
	?>
	<fieldset id="<?php echo esc_attr( $id ); ?>" class="options">

		<h4><?php esc_html_e( 'Bad Behavior', 'wp-super-cache' ); ?></h4>

		<form name="wp_manager" action="" method="post">
		<label><input type="radio" name="cache_badbehaviour" value="1" <?php checked( $cache_badbehaviour ); ?>/> <?php esc_html_e( 'Enabled', 'wp-super-cache' ); ?></label>
		<label><input type="radio" name="cache_badbehaviour" value="0" <?php checked( ! $cache_badbehaviour ); ?>/> <?php esc_html_e( 'Disabled', 'wp-super-cache' ); ?></label>
		<?php
		echo '<p>' . sprintf(
			__( '(Only WPCache caching supported, disabled compression and requires <a href="http://www.bad-behavior.ioerror.us/">Bad Behavior</a> in "%s/plugins/bad-behavior/") ', 'wp-super-cache' ), 
			esc_attr( WP_CONTENT_DIR )
		) . '</p>';

		if ( $changed ) {
			echo '<p><strong>' . sprintf(
				esc_html__( 'Bad Behavior support is now %s', 'wp-super-cache' ),
				esc_html( $cache_badbehaviour ? __( 'enabled', 'wp-super-cache' ) : __( 'disabled', 'wp-super-cache' ) )
			) . '</strong></p>';
		}

		if ( $error_message ) {
			echo '<p><strong>' . esc_html__( 'Warning!', 'wp-super-cache' ) . '</strong>&nbsp;' . esc_html( $error_message ) . '</p>';
		}

		echo '<div class="submit"><input class="button-primary" ' . SUBMITDISABLED . ' type="submit" value="' . esc_html__( 'Update', 'wp-super-cache' ) . '" /></div>';
		wp_nonce_field( 'wp-cache' );
		?>
		</form>

	</fieldset>
	<?php

}
add_cacheaction( 'cache_admin_page', 'wp_supercache_badbehaviour_admin' );

function wpsc_badbehaviour_list( $list ) {
	$list['badbehaviour'] = array(
		'key'   => 'badbehaviour',
		'url'   => 'http://www.bad-behavior.ioerror.us/',
		'title' => esc_html__( 'Bad Behavior', 'wp-super-cache' ),
		'desc'  => sprintf( esc_html__( 'Support for Bad Behavior. (Only WPCache caching supported, disabled compression and requires Bad Behavior in "%s/bad-behavior/") ', 'wp-super-cache' ), esc_attr( WP_PLUGIN_DIR ) ),
	);
	return $list;
}
add_cacheaction( 'wpsc_filter_list', 'wpsc_badbehaviour_list' );
