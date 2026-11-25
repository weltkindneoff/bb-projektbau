<?php
/**
 * Vor dem Start: „Bald online“ für Besucher (503 + Retry-After, noindex).
 * Durchgelassen: Eingeloggte, Vorschau-Link (?vorschau=<schlüssel>, Cookie 30 Tage),
 * Rechtsseiten, Login/Admin, Formular-Versand, robots.txt, Favicon.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const BB_PREVIEW_COOKIE = 'bb_vorschau';

add_action(
	'init',
	function () {
		if ( bb_is_launched() || empty( $_GET['vorschau'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		if ( hash_equals( bb_preview_key(), sanitize_text_field( wp_unslash( $_GET['vorschau'] ) ) ) ) { // phpcs:ignore
			setcookie( BB_PREVIEW_COOKIE, wp_hash( bb_preview_key() ), time() + 30 * DAY_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true );
			$_COOKIE[ BB_PREVIEW_COOKIE ] = wp_hash( bb_preview_key() );
			nocache_headers();
		}
	},
	1
);

function bb_has_preview(): bool {
	return isset( $_COOKIE[ BB_PREVIEW_COOKIE ] ) && hash_equals( wp_hash( bb_preview_key() ), (string) $_COOKIE[ BB_PREVIEW_COOKIE ] ); // phpcs:ignore
}

add_action(
	'template_redirect',
	function () {
		if ( bb_is_launched() ) {
			return;
		}
		// Vor dem Start nicht indexieren.
		header( 'X-Robots-Tag: noindex, nofollow', true );
		if ( is_user_logged_in() || bb_has_preview() || is_robots() || is_favicon() ) {
			if ( bb_has_preview() ) {
				nocache_headers();
			}
			return;
		}
		if ( is_page( bb_legal_slugs() ) ) {
			return;
		}
		status_header( 503 );
		header( 'Retry-After: 86400' );
		nocache_headers();
		bb_render_coming_soon();
		exit;
	},
	0
);

add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( ! bb_is_launched() ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}
);

function bb_render_coming_soon(): void {
	$css   = esc_url( BB_URI . '/assets/css/main.css?ver=' . BB_VERSION );
	$phone = bb_opt( 'telefon' );
	$mail  = bb_opt( 'email' );
	?>
<!doctype html>
<html lang="de" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc_html( bb_opt( 'firma' ) ); ?> – Bald online</title>
<link rel="icon" href="<?php echo esc_url( BB_URI . '/assets/icons/favicon.svg' ); ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?php echo $css; // phpcs:ignore ?>">
</head>
<body class="bb-soon">
<main class="bb-soon__box">
	<?php echo do_shortcode( '[bb_logo]' ); // phpcs:ignore ?>
	<h1>Bald online.</h1>
	<p>Hier entsteht die neue Website der <?php echo esc_html( bb_opt( 'firma' ) ); ?> – Ihre Handwerkervermittlung für Niedersachsen.</p>
	<?php if ( $phone || $mail ) : ?>
		<p class="bb-soon__contact">
			<?php if ( $phone ) : ?><a href="<?php echo esc_attr( bb_tel_href( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a><?php endif; ?>
			<?php if ( $mail ) : ?><a href="mailto:<?php echo esc_attr( antispambot( $mail ) ); ?>"><?php echo esc_html( antispambot( $mail ) ); ?></a><?php endif; ?>
		</p>
	<?php endif; ?>
	<nav class="bb-soon__legal"><a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>">Impressum</a> · <a href="<?php echo esc_url( home_url( '/datenschutz/' ) ); ?>">Datenschutz</a></nav>
</main>
</body>
</html>
	<?php
}
