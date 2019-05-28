<?php

function pmprosl_admin_init_notifications() {
	global $wpdb;
	// we want to avoid notices on some screens
	$script           = basename( $_SERVER['SCRIPT_NAME'] );
	$maybe_installing = $script == 'update.php' || $script == 'plugins.php';
	// 1. Show link to the welcome page the first time the theme is activated
	$welcome_link_dismissed = get_option( 'memberlite_notice_welcome_link_dismissed', false );
	if ( ! $welcome_link_dismissed && ! $maybe_installing ) {
		wp_enqueue_script( 'memberlite-admin-dismiss-notice', get_template_directory_uri() . '/js/admin-dismiss-notice.js', array( 'jquery' ), MEMBERLITE_VERSION, true );
		add_action( 'admin_notices', 'pmprosl_admin_notice' );
	}
}
add_action( 'admin_init', 'pmprosl_admin_init_notifications' );

// AJAX to handle notice dismissal
function pmprosl_wp_ajax_dismiss_notice() {
	// update option and leave
	update_option( 'pmprosl_wsl_notice_' . $notice . '_dismissed', 1, 'no' );
	exit;
}
add_action( 'wp_ajax_nopriv_pmprosl_dismiss_notice', 'pmprosl_wp_ajax_dismiss_notice' );
add_action( 'wp_ajax_pmprosl_dismiss_notice', 'pmprosl_wp_ajax_dismiss_notice' );

function pmprosl_pmprosl_admin_notice() {
	// notice HTML
	?>
	<div id="pmprosl-admin-notice-welcome_link" class="notice notice-error is-dismissible pmpro-notice">
		<p><strong><?php esc_html_e( 'PMPro Social Login', 'pmpro-social-login' ); ?>:</strong>
		<?php
			echo esc_html__( "We have documentation and recommended plugins to help you get started with Memberlite Theme.", 'pmpro-social-login' );
			echo ' <a href="' . $click_link . '">' . $click_text . '</a>';
		?>
		</p>
	</div>
	<?php
}