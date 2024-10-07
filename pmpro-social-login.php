<?php
/*
Plugin Name: Paid Memberships Pro - Social Login Add On
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-social-login/
Description: Allow users to create membership account via social networks.
Version: 1.0
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
Text Domain: pmpro-social-login
Domain Path: /languages
*/

define( 'PMPROSL_VERSION', '1.0' );

/**
 * pmprommpu_load_plugin_text_domain
 *
 * @since 0.6.5
 */
function pmprosl_load_plugin_text_domain() {
	load_plugin_textdomain( 'pmpro-social-login', false, dirname( __FILE__ ) . '/languages' );
}
add_action( 'init', 'pmprosl_load_plugin_text_domain' );

/**
 * include admin notices 
 */ 
require_once( dirname(__FILE__) . '/includes/notices.php' );

/**
 * Check what plugins are active and update settings.
 */
function pmprosl_check_plugins() {
	// Don't waste resources on the frontend.
	if( ! is_admin() || ! defined( 'PMPRO_VERSION') ) {
		return;
	}

	/**
	 * Array of plugin arrays.
	 * Required keys are name, shortcode, and a constant we can use to check if plugin is installed.
	 */
	$plugins = array(
		array(
		'name' => 'Nextend Social Login',
		'shortcode' => '[nextend_social_login]',
		'constant' => 'NSL_PATH_FILE',
		),
		array( 
			'name' => 'Super Socializer',
			'shortcode' => '[TheChamp-Login]',
			'constant' => 'THE_CHAMP_SS_VERSION',
		),
		array(
			'name' => 'WordPress Social Login',
			'shortcode' => '[wordpress_social_login]',
			'constant' => 'WORDPRESS_SOCIAL_LOGIN_ABS_PATH',
		)
	);
	
	$active_plugins = array();
	foreach( $plugins as $plugin ) {
		// is the plugin installed? if so, add to list of active plugins
		if( defined( $plugin['constant'] ) ) {
			$active_plugins[] = $plugin;
		}
	}
		
	$active_plugin_count = count( $active_plugins );
	
	if( $active_plugin_count > 1 ) {
		// more than one plugin installed, let's warn them
		$notice = esc_html__( "The following plugins are activated", 'pmpro-social-login') . ":<br/>";
		for( $i = 0; $i < $active_plugin_count; $i++ ) {
			$notice .= $active_plugins[$i]['name'] . "<br/>";
		}
		$notice .= sprintf( esc_html__( 'Paid Memberships Pro Social Login will use %s for social login integration. Deactivate the plugins you don\'t want to use or use the pmprosl_login_shortcode filter to change this behavior.', 'pmpro-social-login' ), esc_html( $active_plugins[0]['name'] ) );
	} elseif( $active_plugin_count < 1 ) {
		// no plugins installed, warn about that
		/* translators: %1$s is a link to the Nextend plugin, %2$s is a link to the Super Socializer plugin */
		$notice = sprintf( esc_html__( 'The Social Login Add On for Paid Memberships Pro requires either the %1$s or %2$s plugin to be installed and configured.', 'pmpro-social-login' ), '<a target="_blank" href="https://wordpress.org/plugins/nextend-facebook-connect/">Nextend Social Login</a>', '<a target="_blank" href="https://wordpress.org/plugins/super-socializer/">Super Socializer</a>' );
	} else {
		// Just one plugin installed. Remove the notice.
		$notice = '';
	}
	
	// Set the notice.
	update_option( 'pmpro_social_login_notice', $notice );
	
	// Use first plugin we find.
	if( $active_plugin_count ) {
		update_option( 'pmpro_social_login_shortcode', $active_plugins[0]['shortcode'] );
	}
}
add_action( 'plugins_loaded', 'pmprosl_check_plugins' );

/**
 * Check if a default level is set and if so, set it for new users created via social login.
 */
function pmprosl_pmpro_default_registration_level($user_id) {
	global $pmpro_level;	

	if ( ! defined( 'PMPRO_VERSION' ) ) {
		return;
	}
	
	//if default is set and we're not otherwise checking out
	$default_level = get_option('pmpro_social_login_default_level');
	if (!empty($default_level) && empty($pmpro_level) && empty($_REQUEST['level']))
	{	
		pmpro_changeMembershipLevel($default_level, $user_id);

		$user = get_userdata($user_id);
		$user->membership_level = pmpro_getMembershipLevelForUser($user->ID);
		
		//send email to member
		$pmproemail = new PMProEmail();
		$pmproemail->sendCheckoutEmail($user, false);

		//send email to admin
		$pmproemail = new PMProEmail();
		$pmproemail->sendCheckoutAdminEmail($user, false);
	}
}
add_action( 'wsl_hook_process_login_after_wp_insert_user', 'pmprosl_pmpro_default_registration_level' );
add_action( 'nsl_register_new_user', 'pmprosl_pmpro_default_registration_level' ); // Nextend Social Login new user hook.

//checkbox to allow social login for this level on edit level page
function pmprosl_pmpro_membership_level_before_content_settings()
{
	$level = $_REQUEST['edit'];	
	$social_login_default_level = get_option('pmpro_social_login_default_level');
	$hide_social_login = get_option("level_" . $level . "_hide_social_login");
	?>
	<div id="social-login-settings" class="pmpro_section" data-visibility="shown" data-activated="true">
		<div class="pmpro_section_toggle">
			<button class="pmpro_section-toggle-button" type="button" aria-expanded="true">
				<span class="dashicons dashicons-arrow-up-alt2"></span>
				<?php esc_html_e( 'Social Login Settings', 'pmpro-approvals' ); ?>
			</button>
		</div>
		<div class="pmpro_section_inside">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" valign="top"><?php esc_html_e( 'Default Level', 'pmpro-social-login' ); ?></th>
						<td>
							<label for="social_login_default_level"><input name="social_login_default_level" type="checkbox" id="social_login_default_level" <?php checked( $social_login_default_level, $level ); ?> value="1"> <?php esc_html_e('Make this the default level for users who log in the first time via social login.','pmpro-social-login'); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top"><?php esc_html_e( 'Hide Social Login', 'pmpro-social-login' ); ?></th>
						<td>
							<label for="hide_social_login"><input name="hide_social_login" type="checkbox" id="hide_social_login" <?php checked( $hide_social_login, 1 ); ?> value="1"> <?php esc_html_e('Hide social login at checkout for this membership level.','pmpro-social-login'); ?></label>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}
add_action("pmpro_membership_level_before_content_settings", "pmprosl_pmpro_membership_level_before_content_settings");

//update the setting on save
function pmprosl_pmpro_save_membership_level($saveid)
{
	//update hide social login setting
	if(!empty($_REQUEST['hide_social_login']))
	{
		delete_option('level_' . $saveid . '_hide_social_login');
		add_option("level_" . $saveid . "_hide_social_login", 1, '', 'no');
	}
	else
	{
		delete_option('level_' . $saveid . '_hide_social_login');
	}
		
	//update default level options
	if(!empty($_REQUEST['social_login_default_level']))
	{
		delete_option('pmpro_social_login_default_level');
		add_option('pmpro_social_login_default_level', $saveid, '', 'no');
	}
	else
	{
		$default_level = get_option('pmpro_social_login_default_level');
		if($default_level == $saveid)
			delete_option('pmpro_social_login_default_level');
	}
	
}
add_action("pmpro_save_membership_level", "pmprosl_pmpro_save_membership_level");

//add social login to the checkout page
function pmprosl_pmpro_user_fields() {
	global $pmpro_level, $pmpro_error_fields, $pmpro_review;
	$hide_social_login = get_option( "level_" . $pmpro_level->id . "_hide_social_login" );
	$login_shortcode = do_shortcode( pmprosl_get_login_shortcode() );

	// don't show this if we don't have a shortcode or the shortcode is empty
	if ( empty( get_option( 'pmpro_social_login_shortcode' ) ) || empty( $login_shortcode ) ) {
		return;
	}

	if ( empty( $hide_social_login ) && !is_user_logged_in() && empty( $pmpro_error_fields ) && empty( $pmpro_review ) ) {
		?>
		<style>#pmpro_user_fields { display: none; }</style>
		<fieldset id="pmpro_social_login" class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_fieldset' ) ); ?>">
			<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_card' ) ); ?>">
				<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_card_content' ) ); ?>">
					<legend class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_legend', 'pmpro_social_login' ) ); ?>"><h2 class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_heading pmpro_font-large', 'pmpro_social_login' ) ); ?>"><?php esc_html_e( 'Sign In', 'pmpro-social-login' ); ?></h2></legend>
					<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_fields' ) ); ?>">
						<p class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_fields-description pmpro_social_login_help' ) ); ?>"><?php esc_html_e( 'You can sign in using your social media accounts for a faster and more convenient experience. Click on one of the buttons below to log in with your preferred platform.', 'pmpro-social-login' ); ?></p>
						<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_field' ) ); ?>">
							<?php echo $login_shortcode; ?>
						</div>
					</div> <!-- end pmpro_form_fields -->
				</div> <!-- end pmpro_card_content -->
				<div id="pmpro_card_actions-social_login" class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_card_actions', 'pmpro_social_login' ) ); ?>"><div id="pmpro_user_fields_show"><?php echo '<a id="pmpro_user_fields_a" href="javascript:void()">' . esc_html__( 'Click here to login or create a username and password', 'pmpro-social-login' ) . '</a>' ; ?></div></div>
			</div>
		</fieldset>
		<script>
			//show username and password fields 
			jQuery('#pmpro_user_fields_a').attr('href', 'javascript:void(0);');
			jQuery('#pmpro_user_fields_a').click(function() {
				jQuery('#pmpro_user_fields').show();
				jQuery('#pmpro_user_fields_show').hide();
				jQuery('#pmpro_card_actions-social_login').hide();
			});
		</script>	
		<?php
	}
}
add_action( 'pmpro_checkout_after_pricing_fields','pmprosl_pmpro_user_fields' );

// Choose which shortcode to display
function pmprosl_get_login_shortcode() {
	global $wp, $pmpro_pages;
	$plugin = get_option( 'pmpro_social_login_shortcode' );
	// if using Nextend and coming from checkout, make sure we redirect back to checkout
	if($plugin === '[nextend_social_login]' && is_page( $pmpro_pages['checkout'] ) ){
		$plugin === '[nextend_social_login redirect=' . 	home_url(add_query_arg(array($_GET), $wp->request)) . ']';
	}
	$plugin = apply_filters( 'pmprosl_login_shortcode', $plugin );
	return $plugin;
}

// use the requested redirect if we're logging in with Nextend
function pmprosl_preserve_redirect($redirect_to, $request, $user) {
	if( isset($_REQUEST["loginSocial"]) ) {
		$redirect_to = $request;
	}
	return $redirect_to;
}
add_filter( 'pmpro_login_redirect_url', 'pmprosl_preserve_redirect', 10, 3 );

/**
 * Improve Nextend Social Login compatibility if showing on the login form.
 * @since TBD
 */
function pmprosl_nsl_login_form_tweaks( $content, $args ) {
	
	// Only load on the PMPro Login page.
	if ( ! is_page( pmpro_getOption( 'pmpro_login_page_id' ) ) ) {
		return $content;
	}

	// Bail if Nextend Social Login isn't installed.
	if ( ! class_exists( 'NextendSocialLogin' ) ) {
		return $content;
	}

	if ( NextendSocialLogin::$settings->get('show_embedded_login_form') != 'hide' ) {
		remove_filter( 'login_form_bottom', 'NextendSocialLogin::filterAddEmbeddedLoginFormButtons' );
		
		// Let's add our own style and "OR" separator.
		?>
			<style>#pmpro_login .nsl-container-buttons{width:100%;}</style>
		<?php
		$content .= '<div class="' . esc_attr( pmpro_get_element_class( 'pmpro_spacer' ) ) . '"></div>';
		$content .= '<div style="display: flex; align-items: center; text-align: center;">
						<div style="flex: 1; height: 1px; background-color: var(--pmpro--color--contrast);"></div>
							<span style="margin: 0 10px; color: var(--pmpro--color--contrast);">' . esc_html__( 'OR', 'pmpro-social-login' ) . '</span>
						<div style="flex: 1; height: 1px; background-color: var(--pmpro--color--contrast);"></div>
					</div>';

		// Put the Nextend Social Login filter back.
		add_filter( 'login_form_bottom', 'NextendSocialLogin::filterAddEmbeddedLoginFormButtons' );
	}

	return $content;

}
add_action( 'login_form_bottom', 'pmprosl_nsl_login_form_tweaks', 5, 2 );
