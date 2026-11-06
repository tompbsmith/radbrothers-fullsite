<?php
/**
 * Branded holding page shown to visitors while the site is private (see inc/private.php).
 */

defined( 'ABSPATH' ) || exit;

$heading = rb_opt( 'private_heading', 'Our new website is nearly ready' );
$message = rb_opt( 'private_message', "We're putting the finishing touches to the new Rad Brothers website. In the meantime, we're here as usual." );
$phone   = rb_opt( 'private_contact', 1 ) ? rb_opt( 'phone' ) : '';
$email   = rb_opt( 'private_contact', 1 ) ? rb_opt( 'email', 'contact@radbrothers.co.uk' ) : '';
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
<?php wp_head(); ?>
</head>
<body class="rb-private">
<main class="private" id="main">
	<div class="private__mark" aria-hidden="true"><?php echo rb_arrow_mark(); ?></div>
	<div class="private__box">
		<?php echo rb_logo( 'condensed', '', 'private__logo' ); ?>
		<h1 class="private__title"><?php echo esc_html( $heading ); ?></h1>
		<p class="private__text"><?php echo wp_kses( $message, [ 'br' => [] ] ); ?></p>
		<?php if ( $phone || $email ) : ?>
			<ul class="private__contact">
				<?php if ( $phone ) : ?><li><a href="<?php echo esc_attr( rb_tel( $phone ) ); ?>"><?php echo rb_icon( 'phone' ); ?><span><?php echo esc_html( $phone ); ?></span></a></li><?php endif; ?>
				<?php if ( $email ) : ?><li><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo rb_icon( 'mail' ); ?><span><?php echo esc_html( $email ); ?></span></a></li><?php endif; ?>
			</ul>
		<?php endif; ?>
		<p class="private__login"><a href="<?php echo esc_url( wp_login_url( home_url( '/' ) ) ); ?>">Team login</a></p>
	</div>
</main>
</body>
</html>
