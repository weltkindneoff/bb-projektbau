<?php
/**
 * Übersetzungen. Deutsch ist die Quelle; weitere Sprachen später über Polylang + Karte.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Text übersetzen (aktuell nur Deutsch).
 */
function bb_t( string $text ): string {
	$lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'de';
	$map  = apply_filters( 'bb_i18n_map', array() );
	if ( 'de' !== $lang && isset( $map[ $lang ][ $text ] ) ) {
		return $map[ $lang ][ $text ];
	}
	return $text;
}

function bb_e( string $text ): void {
	echo esc_html( bb_t( $text ) );
}
