<?php
/*
Plugin Name: Paid Memberships Pro - Social Login Add On
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-social-login/
Description: Allow users to create membership account via social networks.
Version: .3
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/

define( 'PMPROSL_VERSION', '0.3' );

/**
 * include admin notices 
 */ 
require_once( dirname(__FILE__) . '/includes/notices.php' );

/**
 * Check what plugins are active and update settings.
 */
function pmprosl_check_plugins() {
	// Don't waste resources on the frontend.
	if( ! is_admin() || ! defined( 'PMPRO_VERSION' ) ) {
		return;
	}

	/**
	 * Array of plugin arrays.
	 * Required keys are name, shortcode, and a constant we can use to check if plugin is installed.
	 */
	$plugins = array(
		array(
		'name' => 'NextEnd Social Login',
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
		$notice = __( "The following plugins are activated: <br/>", 'paid-memberships-pro');
		for( $i = 0; $i < $active_plugin_count; $i++ ) {
			$notice .= $active_plugins[$i]['name'] . "<br/>";
		}
		$notice .= __( "Paid Memberships Pro Social Login will use ", 'paid-memberships-pro' ) . $active_plugins[0]['name'];
		$notice .= __( " for social login integration. Deactivate the plugins you don't want to use or use the pmprosl_login_shortcode filter to change this behavior.", 'paid-memberships-pro' );
	} elseif( $active_plugin_count < 1 ) {
		// no plugins installed, warn about that
		$notice = __( 'The Social Login Add On for Paid Memberships Pro requires either the <a href="https://wordpress.org/plugins/nextend-facebook-connect/">NextEnd Social Login</a> or <a href="https://wordpress.org/plugins/super-socializer/">Super Socializer</a> plugin to be installed and configured.', 'paid-memberships-pro' );
	} else {
		// Just one plugin installed. Remove the notice.
		$notice = '';
	}
	
	// Set the notice.
	pmpro_setOption( 'social_login_notice', $notice );
	
	// Use first plugin we find.
	if( $active_plugin_count ) {
		pmpro_setOption( 'social_login_shortcode', $active_plugins[0]['shortcode'] );
	}
}
add_action( 'plugins_loaded', 'pmprosl_check_plugins' );

/*
	If a PMPROSL_DEFAULT_LEVEL constant is set
	give new users logging in and registering
	via social login that default level.
*/
function pmprosl_pmpro_default_registration_level($user_id) {

	if ( ! defined( 'PMPRO_VERSION' ) ) {
		return;
	}

	global $pmpro_level;
	
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
add_action('wsl_hook_process_login_after_wp_insert_user', 'pmprosl_pmpro_default_registration_level');

//checkbox to allow social login for this level on edit level page
function pmprosl_pmpro_membership_level_after_other_settings()
{
	$level = $_REQUEST['edit'];	
	$social_login_default_level = get_option('pmpro_social_login_default_level');
	$hide_social_login = get_option("level_" . $level . "_hide_social_login");
	?>
	<h3 class="topborder"><?php _e('Social Login','pmprosl'); ?></h3>
	
	<p><label for="social_login_default_level"><input name="social_login_default_level" type="checkbox" id="social_login_default_level" <?php checked( $social_login_default_level, $level ); ?> value="1"> <?php _e('Make this the default level to users logging in for the first time via Social Login','pmprosl'); ?></label></p>
	
	<p><label for="hide_social_login"><input name="hide_social_login" type="checkbox" id="hide_social_login" <?php checked( $hide_social_login, 1 ); ?> value="1"> <?php _e('Hide Social Login at Checkout for this Level','pmprosl'); ?></label></p>
	<?php
}
add_action("pmpro_membership_level_after_other_settings", "pmprosl_pmpro_membership_level_after_other_settings");

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
	$hide_social_login = get_option("level_" . $pmpro_level->id . "_hide_social_login");
	$login_shortcode = do_shortcode( pmprosl_get_login_shortcode() );
	// don't show this if we don't have a shortcode or the shortcode is empty
	if( empty( pmpro_getOption( 'social_login_shortcode' ) ) || empty( $login_shortcode ) )
		return;
	if(empty($hide_social_login) && !is_user_logged_in() && empty($pmpro_error_fields) && empty($pmpro_review))
	{
		?>
		<style>#pmpro_user_fields {display: none; }</style>
		<div id="pmpro_social_login" class="pmpro_checkout">
			<?php echo $login_shortcode; ?>
			<div class="pmpro_clear"></div>
			<div id="pmpro_user_fields_show"><?php _e('or, <a id="pmpro_user_fields_a" href="javascript:void()">Click here to login, create a username and password</a>','pmpro'); ?></div>
		</div>
		<script>
			//show username and password fields 
			jQuery('#pmpro_user_fields_a').attr('href', 'javascript:void(0);');
			jQuery('#pmpro_user_fields_a').click(function() {
				jQuery('#pmpro_user_fields').show();
				jQuery('#pmpro_user_fields_show').hide();
			});
		</script>	
		<?php
	}
}
add_action('pmpro_checkout_after_pricing_fields','pmprosl_pmpro_user_fields');

// Choose which shortcode to display
function pmprosl_get_login_shortcode() {
	global $wp, $pmpro_pages;
	$plugin = pmpro_getOption( 'social_login_shortcode' );
	// if using NextEnd and coming from checkout, make sure we redirect back to checkout
	if($plugin === '[nextend_social_login]' && is_page( $pmpro_pages['checkout'] ) ){
		$plugin === '[nextend_social_login redirect=' . 	home_url(add_query_arg(array($_GET), $wp->request)) . ']';
	}
	$plugin = apply_filters( 'pmprosl_login_shortcode', $plugin );
	return $plugin;
}

// use the requested redirect if we're logging in with NextEnd
function pmprosl_preserve_redirect($redirect_to, $request, $user) {
	if( isset($_REQUEST["loginSocial"]) ) {
		$redirect_to = $request;
	}
	return $redirect_to;
}
add_filter('pmpro_login_redirect_url', 'pmprosl_preserve_redirect', 10, 3);