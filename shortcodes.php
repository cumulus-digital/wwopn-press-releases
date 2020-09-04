<?php
namespace WWOPN_PRs;

function shortcode_media_contact_name() {
	if (\get_option('wpn_prs_mediacontact')) {
		$contact = \get_option('wpn_prs_mediacontact');
		return \esc_html($contact['mc_name']);
	}
	return '';
};
\add_shortcode('media-contact-name', __NAMESPACE__ . '\shortcode_media_contact_name');

function shortcode_media_contact_email() {
	if (\get_option('wpn_prs_mediacontact')) {
		$contact = \get_option('wpn_prs_mediacontact');
		return \esc_html($contact['mc_email']);
	}
	return '';
};
\add_shortcode('media-contact-email', __NAMESPACE__ . '\shortcode_media_contact_email');

function shortcode_media_contact_twitter() {
	if (\get_option('wpn_prs_mediacontact')) {
		$contact = \get_option('wpn_prs_mediacontact');
		return \esc_html($contact['mc_twitter']);
	}
	return '';
};
\add_shortcode('media-contact-twitter', __NAMESPACE__ . '\shortcode_media_contact_twitter');

function shortcode_releases($attr) {
	$attr = \shortcode_atts([
		'type' => 'all',
		'limit' => 3,
		'container_class' => 'inline-archive cards',
		'article_class' => 'archive excerpt',
		'orderby' => 'post_date',
		'order' => 'DESC',
		'title_tag' => 'h3',
		'show_image' => false,
		'show_date' => false,
		'show_source' => false
	], $attr, 'releases');
	
	$q = array(
		'post_type' => PREFIX,
		'post_status' => 'publish',
		'numberposts' => $attr['limit'],
		'orderby' => $attr['orderby'],
		'order' => $attr['order'],
		'tax_query' => $attr['type'] == 'all' ? null : array(
			array(
				'taxonomy' => Type::$prefix,
				'field' => 'slug',
				'terms' => $attr['type'],
				'include_children' => false
			)
		)
	);

	$releases = \get_posts($q);
	global $post;
	ob_start();
	?>
	<div class="<?php echo \esc_attr($attr['container_class']) ?>">
		<?php foreach($releases as $post): \setup_postdata($post) ?>
			<?php include __DIR__ . '/templates/release.php' ?>
		<?php endforeach; ?>
	</div>
	<?php
	\wp_reset_query();
	\wp_reset_postdata();

	return ob_get_clean();
}
\add_shortcode('releases', __NAMESPACE__ . '\shortcode_releases');

function shortcode_release_archive_url($attr) {
	$attr = \shortcode_atts([
		'type' => 'release',
	], $attr, 'release-archive-url');
	return \get_tag_link(\get_term_by('slug', $attr['type'], Type::$prefix));
}
\add_shortcode('release-archive-url', __NAMESPACE__ . '\shortcode_release_archive_url');