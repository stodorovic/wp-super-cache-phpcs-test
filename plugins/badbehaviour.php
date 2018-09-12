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
	if ( $cache_badbehaviour_file ) {
		return $cache_badbehaviour_file;
	}

	if ( file_exists( WP_PLUGIN_DIR . '/bad-behavior/bad-behavior-generic.php' ) ) {
		$bbfile = WP_PLUGIN_DIR . '/bad-behavior/bad-behavior-generic.php';
	} elseif ( file_exists( WP_PLUGIN_DIR . '/Bad-Behavior/bad-behavior-generic.php' ) ) {
		$bbfile = WP_PLUGIN_DIR . '/Bad-Behavior/bad-behavior-generic.php';
	} else {
		$bbfile = false;
	}
	return $bbfile;
}

function wp_supercache_badbehaviour_admin() {
	global $cache_badbehaviour, $wp_cache_config_file, $valid_nonce;

	$cache_badbehaviour = ( '' === $cache_badbehaviour || 'no' === $cache_badbehaviour ) ? 0 : (int) $cache_badbehaviour;

	$changed = false;
	$err_msg = '';
	if ( isset( $_POST['cache_badbehaviour'] ) && $valid_nonce ) {
		$bbfile = get_bb_file_loc();
		if ( ! $bbfile ) {
			$changed = false;
			$err_msg = __( 'Bad Behaviour not found. Please check your install.', 'wp-super-cache' );
		} elseif ( $cache_badbehaviour !== (int) $_POST['cache_badbehaviour'] ) {
			$changed = true;
		}
	}

	if ( $changed ) {
		$cache_badbehaviour = (int) $_POST['cache_badbehaviour'];
		wp_cache_replace_line( '^\s*\$cache_compression\s*=', '$cache_compression = 0;', $wp_cache_config_file );
		wp_cache_replace_line( '^\s*\$cache_badbehaviour\s*=', "\$cache_badbehaviour = $cache_badbehaviour;", $wp_cache_config_file );
		wp_cache_replace_line( '^\s*\$cache_badbehaviour_file\s*=', "\$cache_badbehaviour_file = '$bbfile';", $wp_cache_config_file );
	}

	$id = 'badbehavior-section';
	?>
	<fieldset id="<?php echo esc_attr( $id ); ?>" class="options">

		<h4><?php esc_html_e( 'Bad Behavior', 'wp-super-cache' ); ?></h4>

		<form name="wp_manager" action="" method="post">
		<label><input type="radio" name="cache_badbehaviour" value="1" <?php checked( $cache_badbehaviour ); ?>/> <?php esc_html_e( 'Enabled', 'wp-super-cache' ); ?></label>
		<label><input type="radio" name="cache_badbehaviour" value="0" <?php checked( ! $cache_badbehaviour ); ?>/> <?php esc_html_e( 'Disabled', 'wp-super-cache' ); ?></label>
		<?php
		if ( $changed ) {
			echo '<p><strong>' . sprintf(
					esc_html__( 'Bad Behavior support is now %s', 'wp-super-cache' ),
					esc_html( $cache_badbehaviour ? __( 'enabled', 'wp-super-cache' ) : __( 'disabled', 'wp-super-cache' ) )
				) . '</strong>&nbsp;' . sprintf(
					__( '(Only WPCache caching supported, disabled compression and requires <a href="http://www.bad-behavior.ioerror.us/">Bad Behavior</a> in "%s/bad-behavior/") ', 'wp-super-cache' ),
					esc_attr( WP_PLUGIN_DIR )
				) . '</p>';
		}
		echo '<div class="submit"><input class="button-primary" ' . SUBMITDISABLED . ' type="submit" value="' . __( 'Update', 'wp-super-cache' ) . '" /></div>';
		wp_nonce_field( 'wp-cache' );
		?>
		</form>

	</fieldset>
	<?php

	if ( $err_msg ) {
		echo '<p><strong>' . esc_html__( 'Warning!', 'wp-super-cache' ) . '</strong>&nbsp;' . esc_html( $err_msg ) . '</p>';
	}
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
