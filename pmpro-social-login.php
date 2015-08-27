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
	/*
	 * Array of configuration settings. Amend each line as needed.
	 *
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 * Only uncomment the strings in the config array if you want to customize the strings.
	 */
	$config = array(
		'id'           => 'tgmpa',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'parent_slug'  => 'themes.php',            // Parent menu slug.
		'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.

		/*
		'strings'      => array(
			'page_title'                      => __( 'Install Required Plugins', 'theme-slug' ),
			'menu_title'                      => __( 'Install Plugins', 'theme-slug' ),
			'installing'                      => __( 'Installing Plugin: %s', 'theme-slug' ), // %s = plugin name.
			'oops'                            => __( 'Something went wrong with the plugin API.', 'theme-slug' ),
			'notice_can_install_required'     => _n_noop(
				'This theme requires the following plugin: %1$s.',
				'This theme requires the following plugins: %1$s.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_can_install_recommended'  => _n_noop(
				'This theme recommends the following plugin: %1$s.',
				'This theme recommends the following plugins: %1$s.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_cannot_install'           => _n_noop(
				'Sorry, but you do not have the correct permissions to install the %1$s plugin.',
				'Sorry, but you do not have the correct permissions to install the %1$s plugins.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_ask_to_update'            => _n_noop(
				'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.',
				'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_ask_to_update_maybe'      => _n_noop(
				'There is an update available for: %1$s.',
				'There are updates available for the following plugins: %1$s.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_cannot_update'            => _n_noop(
				'Sorry, but you do not have the correct permissions to update the %1$s plugin.',
				'Sorry, but you do not have the correct permissions to update the %1$s plugins.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_can_activate_required'    => _n_noop(
				'The following required plugin is currently inactive: %1$s.',
				'The following required plugins are currently inactive: %1$s.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_can_activate_recommended' => _n_noop(
				'The following recommended plugin is currently inactive: %1$s.',
				'The following recommended plugins are currently inactive: %1$s.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_cannot_activate'          => _n_noop(
				'Sorry, but you do not have the correct permissions to activate the %1$s plugin.',
				'Sorry, but you do not have the correct permissions to activate the %1$s plugins.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'install_link'                    => _n_noop(
				'Begin installing plugin',
				'Begin installing plugins',
				'theme-slug'
			),
			'update_link' 					  => _n_noop(
				'Begin updating plugin',
				'Begin updating plugins',
				'theme-slug'
			),
			'activate_link'                   => _n_noop(
				'Begin activating plugin',
				'Begin activating plugins',
				'theme-slug'
			),
			'return'                          => __( 'Return to Required Plugins Installer', 'theme-slug' ),
			'plugin_activated'                => __( 'Plugin activated successfully.', 'theme-slug' ),
			'activated_successfully'          => __( 'The following plugin was activated successfully:', 'theme-slug' ),
			'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'theme-slug' ),  // %1$s = plugin name(s).
			'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'theme-slug' ),  // %1$s = plugin name(s).
			'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'theme-slug' ), // %s = dashboard link.
			'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'tgmpa' ),

			'nag_type'                        => 'updated', // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
		),
		*/
	);
	tgmpa( $plugins, $config );
}

//checkbox to allow social login for this level on edit level page
function pmprosl_pmpro_membership_level_after_other_settings()
{
	$level = $_REQUEST['edit'];	
	$hide_social_login = pmpro_getOption("level_" . $level . "_hide_social_login");
	?>
	<h3 class="topborder"><?php _e('Social Login','pmprosl'); ?></h3>
	<label for="hide_social_login"><input name="hide_social_login" type="checkbox" id="hide_social_login" <?php checked( $hide_social_login, 1 ); ?> value="1"> <?php _e('Hide Social Login at Checkout for this Level','pmprosl'); ?></label>
	<?php
}
add_action("pmpro_membership_level_after_other_settings", "pmprosl_pmpro_membership_level_after_other_settings");

//update the setting on save
function pmprosl_pmpro_save_membership_level($saveid)
{
	$hide_social_login = $_REQUEST['hide_social_login'];
	pmpro_setOption("level_" . $saveid . "_hide_social_login", $hide_social_login);
}
add_action("pmpro_save_membership_level", "pmprosl_pmpro_save_membership_level");

//add social login to the checkout page
function pmprosl_pmpro_user_fields() {
	global $pmpro_level;
	$hide_social_login = pmpro_getOption("level_" . $pmpro_level->id . "_hide_social_login");
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