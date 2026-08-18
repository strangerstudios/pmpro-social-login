<?php

function pmprosl_admin_init_notifications() {

	if ( ! defined( 'PMPRO_VERSION' ) ) {
		return;
	}
	
	// we want to avoid notices on some screens
	$script           = basename( $_SERVER['SCRIPT_NAME'] );
	$maybe_installing = $script == 'update.php' || $script == 'plugins.php';
	$admin_notice = get_option( 'pmpro_social_login_notice' );
	$admin_notice_dismissed = get_option( 'pmpro_social_login_notice_dismiss' );
	if ( $admin_notice && ! $admin_notice_dismissed && ! $maybe_installing ) {
		wp_enqueue_script( 'pmprosl-admin-dismiss-notice', plugin_dir_url(dirname(__FILE__)) . '/js/admin-dismiss-notice.js', array( 'jquery' ), PMPROSL_VERSION, true );
		wp_localize_script( 'pmprosl-admin-dismiss-notice', 'pmprosl_dismiss_notice', array( 'nonce' => wp_create_nonce( 'pmprosl_dismiss_notice' ) ) );
		add_action( 'admin_notices', 'pmprosl_admin_notice' );
	}
}
add_action( 'admin_init', 'pmprosl_admin_init_notifications' );

// AJAX to handle notice dismissal
function pmprosl_wp_ajax_dismiss_notice() {
	check_ajax_referer( 'pmprosl_dismiss_notice', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		exit;
	}

	// update option and leave
	update_option('pmpro_social_login_notice_dismiss', 1);
	exit;
}
add_action( 'wp_ajax_pmprosl_dismiss_notice', 'pmprosl_wp_ajax_dismiss_notice' );

function pmprosl_admin_notice() {
	// notice HTML
	?>
	<div id="pmprosl-admin-notice" class="notice notice-error is-dismissible pmprosl-notice">
		<p>
		<?php
			echo wp_kses_post( get_option( 'pmpro_social_login_notice' ) );
		?>
		</p>
	</div>
	<?php
}