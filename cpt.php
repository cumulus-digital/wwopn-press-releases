<?php
/**
 * Team Member Custom Post Type
 */
namespace WWOPN_PRs;

class CPT {

	static $slug = 'press';
	static $metakeys = [];
	static $meta_save_callbacks = [];

	static function init() {

		\add_action('init', [__CLASS__, 'register']);

		\add_action('init', [__CLASS__, 'rewriteRule']);

		\add_filter( 'post_type_link', [__CLASS__, 'resolvePostLink']);

		\add_filter( 'wp_insert_post_data', [__CLASS__, 'editor_stripWhitespace'], 9, 2 );

		\add_filter('gutenberg_can_edit_post_type', [__CLASS__, 'editor_disableGutenberg'], 10, 2);
		\add_filter('use_block_editor_for_post_type', [__CLASS__, 'editor_disableGutenberg'], 10, 2);

		\add_action('admin_enqueue_scripts', [__CLASS__, 'editor_loadScriptsAndStyles']);

		\add_action( 'wp_ajax_autosave_wwopn_pressreleases_meta', [__CLASS__, 'editor_meta_handleAutosave']);

		self::$metakeys['prLink'] = '_' . PREFIX . '_meta_prlink';
		\add_action('edit_form_after_title', [__CLASS__, 'editor_meta_prLink'], 10, 1);
		\add_action('save_post', [__CLASS__, 'editor_meta_prLink_save'], 10, 1);

		self::$metakeys['prSource'] = '_' . PREFIX . '_meta_prsource';
		\add_action('edit_form_after_title', [__CLASS__, 'editor_meta_prSource'], 10, 1);
		\add_action('save_post', [__CLASS__, 'editor_meta_prSource_save'], 10, 1);

		\add_action('edit_form_after_editor', [__CLASS__, 'editor_showShortcodes'], 10, 1);

		\add_filter('jetpack_sitemap_post_types', function($post_types) {
			$post_types[] = PREFIX;
			return $post_types;
		});

	}

	/**
	 * Register CPT
	 * @return void
	 */
	static function register() {
		\register_post_type( PREFIX, // Register Custom Post Type
			array(
				'labels'       => array(
					'name'                  => esc_html__( 'Press Releases' ),
					'singular_name'         => esc_html__( 'Press Release' ),
					'menu_name'             => esc_html__( 'Press Releases' ),
					'name_admin_bar'        => esc_html__( 'Press Release' ),
					'all_items'             => esc_html__( 'All Press Releases' ),
					'add_new'               => esc_html__( 'Add New' ),
					'add_new_item'          => esc_html__( 'Add New Press Release' ),
					'edit'                  => esc_html__( 'Edit' ),
					'edit_item'             => esc_html__( 'Edit Press Release' ),
					'new_item'              => esc_html__( 'New Press Release' ),
					'view'                  => esc_html__( 'View Press Release' ),
					'view_item'             => esc_html__( 'View Press Release' ),
					'search_items'          => esc_html__( 'Search Press Releases' ),
					'not_found'             => esc_html__( 'No Press Releases found' ),
					'not_found_in_trash'    => esc_html__( 'No Press Releases found in Trash' ),
					'featured_image'        => esc_html__( 'Press Release Photo' ),
					'set_featured_image'    => esc_html__( 'Set Press Release Photo' ),
					'remove_featured_image' => esc_html__( 'Remove Press Release Photo' ),
					'use_featured_image'    => esc_html__( 'Use as Press Release Photo' )
				),
				'description'           => 'Landing pages for Press Releases.',
				'public'                => true,
				'capability_type'       => 'post',
				'show_in_rest'          => true,
				'rest_base'             => self::$slug,
				'rest_controller_class' => '\WP_REST_Posts_Controller',
				'rewrite'               => array(
					'slug' => self::$slug . '/%release_type%/%year%/%monthnum%/%day%',
					'with_front' => true
				),
				'menu_position'         => 22,
				'menu_icon'             => 'dashicons-paperclip',
				'hierarchical'          => false,
				'has_archive'           => true,
				'can_export'            => true,
				'supports' => array(
					'title',
					'editor',
					'revisions',
					'thumbnail',
					'excerpt'
				),
				'taxonomies' => array(
					PREFIX . '_type',
				),
			)
		);
	}

	/**
	 * Add a rewrite rule so /press/* goes to /press
	 * and press releases are date-coded
	 * @return void
	 */
	static function rewriteRule() {
		// year archives
		\add_rewrite_rule(
			'^' . self::$slug . '/([0-9]{4})/?$',
			'index.php?post_type=' . PREFIX . '&year=$matches[1]',
			'top'
		);
		\add_rewrite_rule(
			'^' . self::$slug . '/([0-9]{4})/page/([0-9]{1,})/?$',
			'index.php?post_type=' . PREFIX . '&year=$matches[1]&paged=$matches[2]',
			'top'
		);

		// handles /press/release/2020/10/09/the-post-slug
		\add_rewrite_rule(
			'^' . self::$slug . '/([a-z]*)?/?([0-9]{4})/([0-9]{2})/([0-9]{2})/(.*)/?',
			'index.php?post_type=' . PREFIX . '&name=$matches[5]',
			'top'
		);
	}

	/**
	 * Resolve tokens in our URL structure. Sitemaps don't seem to pass the post
	 * to the filter, so we have to be tricky about this...
	 *
	 * @param [type] $post_link
	 * @param [type] $this_post
	 * @return void
	 */
	static function resolvePostLink($post_link, $this_post=null) {
		if (strpos($post_link, '%release_type%')) {
			if ( ! $this_post) {
				preg_match(
					'@%release_type%/%year%/%monthnum%/%day%/(?P<slug>[^/]+)/?@i',
					$post_link,
					$parts
				);
				if ($parts && $parts['slug']) {
					$args = array(
						'post_type' => PREFIX,
						'numberposts' => 1,
						'fields' => 'ids',
						'name' => $parts['slug'],
					);
					$get_post = \get_posts($args);
					if (count($get_post)) {
						$this_post = $get_post[0];
					}
				}
			}

			$post_id = null;
			if (is_object($this_post)) {
				$post_id = $this_post->ID;
			} else if (is_array($this_post)) {
				$post_id = $this_post['ID'];
			} else if (is_int($this_post)) {
				$post_id = $this_post;
			} else {
				$post_id = \get_the_ID();
			}

			if ( ! $post_id) {
				//return $post_link;
			}

			$find_replace = array(
				'%release_type%' => null,
				'%year%' => \get_the_date('Y', $post_id),
				'%monthnum%' => \get_the_date('m', $post_id),
				'%day%' => \get_the_date('d', $post_id),
			);

			$terms = \get_the_terms($post_id, Type::$prefix);
			if ($terms && count($terms)) {
				$find_replace['%release_type%'] = $terms[0]->slug;
			} else {
				// if there's no release type, get rid of the whole type section
				unset($find_replace['%release_type%']);
				$find_replace['%release_type%/'] = '';
			}

			$post_link = str_replace(
				array_keys($find_replace),
				array_values($find_replace),
				$post_link
			);
		}

		return $post_link;
	}


	/**
	 * Register scripts and styles for the post editor
	 * @param  string $hook
	 * @return void
	 */
	static function editor_loadScriptsAndStyles($hook) {
		if ($hook !== 'post-new.php' && $hook !== 'post.php') {
			return;
		}
		$screen = \get_current_screen();
		if ($screen->id !== PREFIX) {
			return;
		}

		\wp_enqueue_script(
			PREFIX . '_editor_scripts',
			\plugin_dir_url(__FILE__) . 'assets/editor/scripts.js',
			['jquery'], //[ 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ],
			time(),
			true
		);
		/*
		\wp_enqueue_style( 'jquery-ui' );
		\wp_enqueue_style( 'jquery-ui-datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/flick/jquery-ui.css' );
		*/

		\wp_enqueue_style( PREFIX . '_editor_styles', \plugin_dir_url(__FILE__) . 'assets/editor/styles.css' );
	}


	/**
	 * Strip whitespace at the end of Podcast post content
	 * @param  string $data
	 * @param  object $post
	 * @return string
	 */
	static function editor_stripWhitespace($data, $post) {
		if ($post['post_type'] !== PREFIX) {
			return $data;
		}

		$clean = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $data['post_content']);
		$quotes = array(
		    "\xC2\xAB"     => '"', // « (U+00AB) in UTF-8
		    "\xC2\xBB"     => '"', // » (U+00BB) in UTF-8
		    "\xE2\x80\x98" => "'", // ‘ (U+2018) in UTF-8
		    "\xE2\x80\x99" => "'", // ’ (U+2019) in UTF-8
		    "\xE2\x80\x9A" => "'", // ‚ (U+201A) in UTF-8
		    "\xE2\x80\x9B" => "'", // ‛ (U+201B) in UTF-8
		    "\xE2\x80\x9C" => '"', // “ (U+201C) in UTF-8
		    "\xE2\x80\x9D" => '"', // ” (U+201D) in UTF-8
		    "\xE2\x80\x9E" => '"', // „ (U+201E) in UTF-8
		    "\xE2\x80\x9F" => '"', // ‟ (U+201F) in UTF-8
		    "\xE2\x80\xB9" => "'", // ‹ (U+2039) in UTF-8
		    "\xE2\x80\xBA" => "'", // › (U+203A) in UTF-8
		);
		$clean = strtr($clean, $quotes);
		$clean = str_replace('&nbsp;', '', $clean);

		$data['post_content'] = trim($clean);
		return $data;
	}

	/**
	 * Disable Gutenberg for this CPT
	 * @param  boolean $is_enabled
	 * @param  string $post_type
	 * @return boolean
	 */
	static function editor_disableGutenberg($is_enabled, $post_type = null) {
		if ($post_type === PREFIX) {
			return false;
		}

		return $is_enabled;
	}

	/**
	 * Determine if request is safe to save metadata
	 * @return boolean
	 */
	static function editor_meta_safeToSave() {
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( ! isPost()) {
			return false;
		}

		if ( ! \current_user_can('edit_pages')) {
			return false;
		}

		return true;
	}

	/**
	 * Handle custom autosave event
	 */
	static function editor_meta_handleAutosave() {
		if ( ! isPOST()) {
			return;
		}

		if ( ! testPostValue('post_ID')) {
			return;
		}
		
		foreach(self::$meta_save_callbacks as $cb) {
			$cb($_POST['post_ID']);
		}
		return true;
	}

	/**
	 * Meta box for PR link
	 */
	static function editor_meta_prLink($post) {
		if ($post->post_type !== PREFIX) {
			return;
		}
		$key = self::$metakeys['prLink'];
		$prlink = \get_post_meta($post->ID, $key, true);
		?>
		<div class="newsitem_meta-container">
			<div class="meta_prlink">
				<?=\wp_nonce_field($key, $key . '-nonce');?>
				<label for="meta_prlink">External Link:</label>
				<input type="url" name="<?=$key?>" size="30" value="<?=esc_attr($prlink)?>" id="meta_prlink" spellcheck="false" autocomplete="off" placeholder="https://&hellip;">
			</div>
			<p class="howto">If the external link is blank, this release will have a landing page. Be sure to write an excerpt!</p>
		</div>
		<?php
	}

	static function editor_meta_prLink_save($post_id) {
		if ( ! self::editor_meta_safeToSave()) {
			return;
		}

		$key = self::$metakeys['prLink'];

		if (testPostValue($key, true)) {
			$value = (string) \esc_url_raw($_POST[$key]);
			\update_post_meta($post_id, $key, $value);
			return;
		}

		\delete_post_meta($post_id, $key);
	}

	/**
	 * Meta box for PR link
	 */
	static function editor_meta_prSource($post) {
		if ($post->post_type !== PREFIX) {
			return;
		}
		$key = self::$metakeys['prSource'];
		$prsource = \get_post_meta($post->ID, $key, true);
		?>
		<div class="newsitem_meta-container">
			<div class="meta_prlink">
				<?=\wp_nonce_field($key, $key . '-nonce');?>
				<label for="meta_prsource">Source Name:</label>
				<input type="text" name="<?=$key?>" size="30" value="<?=esc_attr($prsource)?>" id="meta_prsource" spellcheck="true" autocomplete="off" placeholder="Politico">
			</div>
		</div>
		<?php
	}

	static function editor_meta_prSource_save($post_id) {
		if ( ! self::editor_meta_safeToSave()) {
			return;
		}

		$key = self::$metakeys['prSource'];

		if (testPostValue($key, true)) {
			$value = (string) \sanitize_text_field($_POST[$key]);
			\update_post_meta($post_id, $key, $value);
			return;
		}

		\delete_post_meta($post_id, $key);
	}

	/** Display shortcodes */
	static function editor_showShortcodes($post) {
		if ($post->post_type !== PREFIX) {
			return;
		}
		?>
		<p>Helpful shortcodes:</p>
		<ul>
			<li>[media-contact-name]</li>
			<li>[media-contact-email]</li>
			<li>[media-contact-phone]</li>
			<li>[media-contact-twitter]</li>
		</ul>
		<?php
	}

	/**
	 * Replace permalink with external link if set
	 * @param string $url
	 * @param integer $post_id
	 * @return string The new URL
	 */
	static function public_replacePermalink($url, $post_id) {
		$key = self::$metakeys['prLink'];
		$prlink = \get_post_meta($post_id, $key, true);
		if ($prlink && strlen($prlink) > 2) {
			return $prlink;
		}
		return $url;
	}

}

CPT::init();