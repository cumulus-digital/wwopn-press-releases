<?php
/**
 * Type taxonomy and editor functions for Press Release CPT
 */
namespace WWOPN_PRs;

class Type {

	static $prefix;
	static $slug = 'press/type';

	static function init() {

		self::$prefix = PREFIX . '_type';

		\add_action('init', [__CLASS__, 'register']);

		\add_action('init', [__CLASS__, 'rewriteRule']);

		\add_action('pre_get_posts', [__CLASS__, 'orderBy']);

		// Podcast list filters
		\add_action('restrict_manage_posts', [__CLASS__, 'list_AddFilterDropdown']);
		\add_filter('parse_query', [__CLASS__, 'list_alterFilterQuery']);

		// Make Podcast list genre column sortable
		\add_action(
			'manage_edit-' . PREFIX . '_sortable_columns',
			[__CLASS__, 'list_sortableColumn']
		);

		\add_action('init', [__CLASS__, 'rewriteRule']);

	}

	static function register() {
		\register_taxonomy(
			self::$prefix,
			PREFIX,
			array(
				'label' => esc_html__( 'Release Type' ),
				'labels' => array(
					'name'               => esc_html__( 'Release Types' ),
					'items_list'         => esc_html__( 'Release Types' ),
					'singular_name'      => esc_html__( 'Release Type' ),
					'menu_name'          => esc_html__( 'Release Types' ),
					'name_admin_bar'     => esc_html__( 'Release Types' ),
					'all_items'          => esc_html__( 'All Release Types' ),
					'parent_item'        => esc_html__( 'Parent Release Type' ),
					'add_new'            => esc_html__( 'Add New' ),
					'add_new_item'       => esc_html__( 'Add New Release Type' ),
					'edit'               => esc_html__( 'Edit' ),
					'edit_item'          => esc_html__( 'Edit Release Type' ),
					'new_item'           => esc_html__( 'New Release Type' ),
					'view'               => esc_html__( 'View Release Type' ),
					'view_item'          => esc_html__( 'View Release Type' ),
					'search_items'       => esc_html__( 'Search Release Type' ),
					'not_found'          => esc_html__( 'No Release Types found' ),
					'not_found_in_trash' => esc_html__( 'No Release Types found in Trash' ),
					'no_terms'           => esc_html__( 'No Release Types' ),
				),
				'meta_box_cb' => [__CLASS__, 'editor_type_metaBox'],
				'hierarchical' => true,
				'rewrite' => array('slug' => self::$slug, 'with_front' => false),
				'show_in_rest' => true,
				'show_admin_column' => true,
				'query_var' => true,
			)
		);
	}

	/**
	 * Add a rewrite rules for press types
	 * @return void
	 */
	static function rewriteRule() {
		\add_rewrite_rule(
			'^' . self::$slug . '/([^/]+)/?$',
			'index.php?' . self::$prefix . '=$matches[1]',
			'top'
		);
	}

	static function orderBy($query) {
		if( ! is_admin() )
			return;

		$orderby = $query->get('orderby');

		if( 'slice' == $orderby ) {
			$query->set('meta_key',self::$prefix);
			$query->set('orderby','meta_value_num');
		}
	}

	static function editor_type_metaBox($post, $box) {
		\post_categories_meta_box($post, $box);
	}

	static function list_AddFilterDropdown() {
		global $typenow;
		$post_type = PREFIX;
		$taxonomy  = self::$prefix;
		if ($typenow == $post_type) {
			$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
			$info_taxonomy = \get_taxonomy($taxonomy);
			\wp_dropdown_categories(array(
				'show_option_all' => __("Show All {$info_taxonomy->label}"),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'show_count'      => true,
				'hide_empty'      => true,
			));
		};
	}

	static function list_alterFilterQuery($query) {
		global $pagenow;
		$post_type = PREFIX; // change to your post type
		$taxonomy  = self::$prefix; // change to your taxonomy
		$q_vars    = &$query->query_vars;
		if (
			$pagenow == 'edit.php' &&
			isset($q_vars['post_type']) &&
			$q_vars['post_type'] == $post_type &&
			isset($q_vars[$taxonomy]) &&
			is_numeric($q_vars[$taxonomy]) &&
			$q_vars[$taxonomy] != 0
		) {
			$term = \get_term_by('id', $q_vars[$taxonomy], $taxonomy);
			$q_vars[$taxonomy] = $term->slug;
		}
	}

	static function list_sortableColumn($columns) {
		$columns['taxonomy-' . self::$prefix] = self::$prefix;
		return $columns;
	}

}

Type::init();