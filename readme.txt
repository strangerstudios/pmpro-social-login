=== Paid Memberships Pro - Social Login Add On ===
Contributors: strangerstudios
Tags: pmpro, paid memberships pro, members, social login
Requires at least: 4.8
Tested up to: 6.2
Stable tag: 1.0

Allow users to create membership account via social networks as configured via NextEnd Social Login or Super Socializer plugin.

== Description ==

The Paid Memberships Pro Social Login Add On adds a Social Login option to Membership Checkout. You can set a level to "Hide" social login from the Memberships > Membership Levels > Edit Level admin page. You can set a level as the default level to users logging in for the first time via Social Login.

Requires Paid Memberships Pro and either NextEnd Social Login or Super Socializer installed and activated.

== Installation ==

= Prerequisites =
1. You must have Paid Memberships Pro and either NextEnd Social Login (https://wordpress.org/plugins/nextend-facebook-connect/) or Super Socializer (https://wordpress.org/plugins/super-socializer/) installed and activated on your site.

= Download, Install and Activate! =
1. Download the latest version of the plugin.
1. Unzip the downloaded file to your computer.
1. Upload the /pmpro-social-login/ directory to the /wp-content/plugins/ directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.

== How to Use ==

1. After activation and proper configuration of the WordPress Social Login plugin, you can navigate to the membership checkout page as a visitor to see the Social Login options. See http://www.paidmembershipspro.com/add-ons/plus-add-ons/social-login-add-on/ for application setup with social networks.
2. To hide Social Login at Membership Checkout for a specifc level, edit the Membership Level and check the box to "Hide Social Login at Checkout for this Level".
3. To set the default level to users logging in for the first time via Social Login, edit the Membership Level and check the box to "Make this the default level to users logging in for the first time via Social Login".

== Changelog ==
= 1.0 - 2023-08-11 =
* BUG FIX/ENHANCEMENT: Updated localization and escaping of strings. #14 (@JarrydLong)
* BUG FIX/ENHANCEMENT: Updated some `<h3>` tags to `<h2>` tags for better accessibility. #17 (@ipokkel)
* BUG FIX: Fixed PHP errors when the core Paid Memberships Pro plugin is not active. #13 (@JarrydLong)
* REFACTOR: Now using `get_option()` instead of `pmpro_getOption()` to get options. #16 (@JarrydLong)

= .3 =
* ENHANCEMENT: Now supports NextEnd Social Login and Super Socializer plugins.
* CHANGE: Wordpress Social Login support is deprecated as the plugin is no longer being actively maintained. Will still use WSL for login if it's the only supported social login plugin installed.
* ADDED FILTER: Added'pmprosl_login_shortcode' filter to change the shortcode used to display social login. Takes one parameter, which is a string containing the full shortcode, including brackets.

= .2 =
* ENHANCEMENT: No longer hiding the submit button until users login or enter account fields.
* ENHANCEMENT: Now hiding the social login options and showing the default user account fields if the user submits the form and there are missing field errors.

= .1 =
* Initial commit.