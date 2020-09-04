<?php
namespace WWOPN_PRs;
?>

<article
	id="post-<?php \the_ID() ?>"
	<?php \post_class( $attr['article_class'] ) ?>
>
    <?php if (\get_post_meta(\get_the_ID(), '_' . PREFIX . '_meta_prlink', true)): ?>
        <a href="<?php echo \esc_url(\get_post_meta(\get_the_ID(), '_' . PREFIX . '_meta_prlink', true)) ?>" title="<?php \esc_attr(\get_the_title()) ?>" target="_blank" rel="nofollow noopener">
    <?php else: ?>
        <a href="<?php \the_permalink() ?>" title="<?php echo \esc_attr(\get_the_title()) ?>">
    <?php endif ?>
		<?php if ($attr['show_image'] && \has_post_thumbnail()): ?>
			<figure class="featured-image">
                <?php
                    \the_post_thumbnail('full', [ 'alt' => \esc_attr(\get_the_title()) ]);
                ?>
            </figure>
		<?php endif ?>
		<header>
			<<?php echo $attr['title_tag'] ?>>
				<?php \the_title() ?>
			</<?php echo $attr['title_tag'] ?>>
			<?php if ($attr['show_date'] || $attr['show_source']): ?>
			<div class="meta">
				<?php if ($attr['show_date']): ?>
				<time datetime="<?php echo \get_the_date('Y-m-d', \get_the_ID()) ?>">
					<?php echo \get_the_date('F j, Y', \get_the_ID())?>
				</time>
				<?php endif ?>
				<?php if ($attr['show_source'] && \get_post_meta(\get_the_ID(), '_' . PREFIX . '_meta_prsource', true)): ?>
					<div class="source">
						From: <?php echo \esc_html(\get_post_meta(\get_the_ID(), '_' . PREFIX . '_meta_prsource', true)) ?>
					</div>
				<?php endif ?>
			</div>
			<?php endif ?>
		</header>
		<div class="body">
			<?php echo \wp_trim_excerpt(\get_the_excerpt()) ?>
		</div>
	</a>
</article>