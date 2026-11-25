<?php
/**
 * Theme-Setup: Supports, Assets, Head, Favicon.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'responsive-embeds' );
		add_editor_style( 'assets/css/main.css' );
		remove_theme_support( 'core-block-patterns' );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'bb-main', BB_URI . '/assets/css/main.css', array(), BB_VERSION );
		wp_enqueue_script( 'bb-app', BB_URI . '/assets/js/app.js', array(), BB_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	}
);

/**
 * Hell/Dunkel ohne Flackern: vor dem ersten Rendern setzen.
 */
add_action(
	'wp_head',
	function () {
		?>
<script>(function(){try{var t=localStorage.getItem('bb-theme');if(!t){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
<link rel="preload" href="<?php echo esc_url( BB_URI . '/assets/fonts/inter-latin-wght-normal.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
<link rel="icon" href="<?php echo esc_url( BB_URI . '/assets/icons/favicon.svg?v=' . BB_VERSION ); ?>" type="image/svg+xml">
<meta name="theme-color" content="#13254B">
		<?php
	},
	1
);

/**
 * Body-Klasse für Seitentypen.
 */
add_filter(
	'body_class',
	function ( array $classes ) {
		if ( is_page() ) {
			$classes[] = 'bb-page-' . sanitize_html_class( get_post_field( 'post_name', get_queried_object_id() ) );
		}
		return $classes;
	}
);

/**
 * Block-Pattern-Kategorie.
 */
add_action(
	'init',
	function () {
		register_block_pattern_category( 'bb-projektbau', array( 'label' => 'B&B Projektbau' ) );
	}
);
