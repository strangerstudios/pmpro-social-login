<?php
/*
Plugin Name: Paid Memberships Pro - WordPress Social Login Add On
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-social-login/
Description: Allow users to create membership account via social networks as configured via WordPress Social Login by Miled.
Version: .1
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/

/**
 * Include the TGM_Plugin_Activation class.
 */
require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';

/**
 * Register the required plugins for this theme.
 */
add_action( 'tgmpa_register', 'pmprosl_tgmpa_register' );
function pmprosl_tgmpa_register() {
	/**
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = array(
		// Paid Memberships Pro
		array(
			'name' 		=> 'Paid Memberships Pro',
			'slug' 		=> 'paid-memberships-pro',
			'required' 	=> true
		),
		// WordPress Social Login
		array(
			'name' 		=> 'WordPress Social Login',
			'slug' 		=> 'wordpress-social-login',
			'required' 	=> true
		)
	);

	// Change this to your theme text domain, used for internationalising strings
	$theme_text_domain = 'pmpro';

	$config = array(
		'domain'       		=> $theme_text_domain,         	// Text domain - likely want to be the same as your theme.
		'default_path' 		=> '',                         	// Default absolute path to pre-packaged plugins
		'menu'         		=> 'install-required-plugins', 	// Menu slug
		'has_notices'      	=> true,                       	// Show admin notices or not
		'is_automatic'    	=> false,					   	// Automatically activate plugins after installation or not
		'message' 			=> '',							// Message to output right before the plugins table
		'strings'      		=> array(
			'page_title'                       			=> __( 'Install Required Plugins', $theme_text_domain ),
			'menu_title'                       			=> __( 'Install Plugins', $theme_text_domain ),
			'installing'                       			=> __( 'Installing Plugin: %s', $theme_text_domain ), // %1$s = plugin name
			'oops'                             			=> __( 'Something went wrong with the plugin API.', $theme_text_domain ),
			'notice_can_install_required'     			=> _n_noop( 'PMPro Social Login requires the following plugin: %1$s.', 'PMPro Social Login requires the following plugins: %1$s.' ), // %1$s = plugin name(s)
			'notice_can_install_recommended'			=> _n_noop( 'PMPro Social Login recommends the following plugin: %1$s.', 'PMPro Social Login recommends the following plugins: %1$s.' ), // %1$s = plugin name(s)
			'notice_cannot_install'  					=> _n_noop( 'PMPro Social Login: Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'PMPro Social Login: Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s)
			'notice_can_activate_required'    			=> _n_noop( 'PMPro Social Login: The following required plugin is currently inactive: %1$s.', 'PMPro Social Login: The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
			'notice_can_activate_recommended'			=> _n_noop( 'PMPro Social Login: The following recommended plugin is currently inactive: %1$s.', 'PMPro Social Login: The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
			'notice_cannot_activate' 					=> _n_noop( 'PMPro Social Login: Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'PMPro Social Login: Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s)
			'notice_ask_to_update' 						=> _n_noop( 'PMPro Social Login: The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'PMPro Social Login: The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ), // %1$s = plugin name(s)
			'notice_cannot_update' 						=> _n_noop( 'PMPro Social Login: Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'PMPro Social Login: Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s)
			'install_link' 					  			=> _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
			'activate_link' 				  			=> _n_noop( 'Activate installed plugin', 'Activate installed plugins' ),
			'return'                           			=> __( 'Return to Required Plugins Installer', $theme_text_domain ),
			'plugin_activated'                 			=> __( 'Plugin activated successfully.', $theme_text_domain ),
			'complete' 									=> __( 'All plugins installed and activated successfully. %s', $theme_text_domain ), // %1$s = dashboard link
			'nag_type'									=> 'updated' // Determines admin notice type - can only be 'updated' or 'error'
		)
	);

	tgmpa( $plugins, $config );
}

/*
	If a PMPROSL_DEFAULT_LEVEL constant is set
	give new users logging in and registering
	via social login that default level.
*/
function pmprosl_pmpro_default_registration_level($user_id) {
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
	global $pmpro_level;
	$hide_social_login = get_option("level_" . $pmpro_level->id . "_hide_social_login");
	if(empty($hide_social_login) && !is_user_logged_in() )
	{
		?>
		<style>#pmpro_user_fields, #pmpro_submit_span{display: none; }</style>
		<div id="pmpro_social_login" class="pmpro_checkout">
			<?php echo do_shortcode( '[wordpress_social_login]' ); ?>
			<div class="pmpro_clear"></div>
			<div id="pmpro_user_fields_show"><?php _e('or, <a id="pmpro_user_fields_a" href="javascript:void()">Click here to create a username and password</a>','pmpro'); ?></div>
		</div>
		<script>
			//show username and password fields 
			jQuery('#pmpro_user_fields_a').attr('href', 'javascript:void(0);');
			jQuery('#pmpro_user_fields_a').click(function() {
				jQuery('#pmpro_user_fields').show();
				jQuery('#pmpro_submit_span').show();
				jQuery('#pmpro_user_fields_show').hide();
			});
		</script>	
		<?php
	}
}
add_action('pmpro_checkout_after_pricing_fields','pmprosl_pmpro_user_fields');
