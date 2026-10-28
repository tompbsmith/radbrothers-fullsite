<?php
/**
 * Fallback template (posts, archives, search).
 */
get_header();
?>
<section class="section section--page-title bg-navy" data-animate>
	<div class="container"><h1 class="section-title reveal"><?php echo is_home() ? esc_html__( 'News', 'radbrothers' ) : wp_kses_post( get_the_archive_title() ); ?></h1></div>
</section>
<section class="section bg-white" data-animate>
	<div class="container container--narrow">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<article class="post-item reveal">
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<?php if ( is_singular() ) : ?>
					<div class="prose"><?php the_content(); ?></div>
				<?php else : ?>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</article>
		<?php endwhile; the_posts_pagination(); else : ?>
			<p>Nothing here yet.</p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
