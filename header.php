<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Skip to content</a>
<?php
$cta_label = rb_opt( 'cta_label', 'Get a quote' );
$cta_url   = rb_opt( 'cta_url', home_url( '/contact/' ) );
$portal    = rb_opt( 'portal_url' );
$phone     = rb_opt( 'phone' );
?>
<header class="site-header" data-header>
	<div class="container site-header__inner">
		<a class="site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> home">
			<?php echo rb_logo( 'longform', '', 'logo--dark' ); ?>
			<?php echo rb_logo( 'longform', 'white', 'logo--light' ); ?>
		</a>

		<nav class="site-nav" id="site-nav" aria-label="Main">
			<?php
			wp_nav_menu( [
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'menu',
				'fallback_cb'    => 'rb_menu_fallback',
				'depth'          => 2,
			] );
			?>
			<div class="site-nav__extra">
				<?php if ( $portal ) : ?>
					<a class="portal-link" href="<?php echo esc_url( $portal ); ?>"><?php echo rb_icon( 'monitor' ); ?><?php echo esc_html( rb_opt( 'portal_label', 'Client portal' ) ); ?></a>
				<?php endif; ?>
				<?php if ( $phone ) : ?>
					<a class="nav-phone" href="<?php echo esc_attr( rb_tel( $phone ) ); ?>"><?php echo rb_icon( 'phone' ); ?><?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
			</div>
		</nav>

		<div class="site-header__actions">
			<?php if ( $portal ) : ?>
				<a class="portal-link portal-link--desktop" href="<?php echo esc_url( $portal ); ?>"><?php echo rb_icon( 'monitor' ); ?><span><?php echo esc_html( rb_opt( 'portal_label', 'Client portal' ) ); ?></span></a>
			<?php endif; ?>
			<a class="btn btn--primary btn--sm header-cta" href="<?php echo esc_url( $cta_url ); ?>"><span><?php echo esc_html( $cta_label ); ?></span><?php echo rb_icon( 'arrow', 'btn__icon' ); ?></a>
			<button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" data-nav-toggle>
				<span class="nav-toggle__bars" aria-hidden="true"><span></span><span></span><span></span></span>
				<span class="screen-reader-text">Menu</span>
			</button>
		</div>
	</div>
</header>
<main id="main" class="site-main">
