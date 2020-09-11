<?php
/**
* Plugin Name: WWOPN Press Releases
* Plugin URI: github.com/cumulus-digital/wwopn-press-releases
* GitHub Plugin URI: cumulus-digital/wwopn-press-releases
* Description: A plugin to create and organize press releases
* Version:  0.16
* Author: Daniel Vena
* Author URI: westwoodone.com
* License: GPL2
*/
namespace WWOPN_PRs;

const PLUGIN_NAME = 'wwopn-press-releases';
const PREFIX = 'wpn_prs';
const TXTDOMAIN = PREFIX;
const BASEPATH = PLUGIN_NAME;
const BASE_FILENAME = PLUGIN_NAME . DIRECTORY_SEPARATOR . PLUGIN_NAME . '.php';

require_once __DIR__ . '/helpers.php';

require_once __DIR__ . '/options.php';

require_once __DIR__ . '/cpt.php';
require_once __DIR__ . '/type.php';

require_once __DIR__ . '/shortcodes.php';

/**
 * Flush permalinks on activation
 */
function plugin_activation() {
	if ( ! \get_option('permalink_structure')) {
		die(
			'<p style="font-family:sans-serif">' .
			sprintf(__('WWOPN Press Releases requires a <a href="%s" target="_top">permalink structure</a> be set to something other than "Plain".'), \admin_url('options-permalink.php'))
		);
	}

	// Create types
	if ( ! \term_exists('release', Type::$prefix)) {
		Type::register();
		\wp_insert_term('Press Release', Type::$prefix, ['slug' => 'release']);
	}
	if ( ! \term_exists('news', Type::$prefix)) {
		Type::register();
		\wp_insert_term('News Item', Type::$prefix, ['slug' => 'news']);
	}

	// Create page
	if ( ! \get_page_by_path('press')) {
		\wp_insert_post(
			array(
				'ID' => 0,
				'post_type' => 'page',
				'post_title' => 'Press',
				'post_name' => 'press',
				'post_content' => 'Press releases are managed via the Press Release admin menu to the left.',
				'post_status' => 'publish',
				'comment_status' => 'closed',
				'ping_status' => 'closed',

			)
		);
	}

	// Flush permalinks after activation
	\add_action( 'admin_init', 'flush_rewrite_rules', 20 );
}
\register_activation_hook( __FILE__, __NAMESPACE__ . '\plugin_activation');

/**
 * Ensure a permalink structure exists, 
 * otherwise display an error on all admin pages
 */
function plugin_checkPermalinks() {
	if (\get_option('permalink_structure')) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p>
		<?=sprintf(__('WWOPN Press Releases requires a <a href="%s">permalink structure</a> be set to something other than "Plain".'), \admin_url('options-permalink.php'))?>
		</p>
	</div>
	<?php
}
\add_action( 'admin_notices', __NAMESPACE__ . '\plugin_checkPermalinks' );

