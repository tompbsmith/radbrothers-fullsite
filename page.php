<?php
/**
 * Pages: render flexible content sections. Falls back to the editor content.
 */
get_header();

while ( have_posts() ) :
	the_post();
	$sections = rb_sections( get_the_ID() );
	if ( $sections ) {
		rb_render_sections( $sections );
	} else {
		?>
		<section class="section section--page-title bg-navy" data-animate>
			<div class="container"><h1 class="section-title reveal"><?php the_title(); ?></h1></div>
		</section>
		<section class="section bg-white" data-animate>
			<div class="container container--narrow prose reveal"><?php the_content(); ?></div>
		</section>
		<?php
	}
endwhile;

get_footer();
