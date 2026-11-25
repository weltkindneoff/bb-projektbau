<?php
/**
 * Dynamische Bausteine als Shortcodes (werden in Patterns/Templates genutzt).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Zentrale Liste der Gewerke: slug => [Bezeichnung, Seitenslug, Icon].
 */
function bb_gewerke(): array {
	return array(
		'bad'       => array( 'Bad komplett', 'badsanierung', 'bad', 'Badsanierung komplett' ),
		'fliesen'   => array( 'Fliesen', 'fliesenarbeiten', 'fliesen', 'Fliesenarbeiten' ),
		'sanitaer'  => array( 'Sanitär', 'sanitaer', 'sanitaer', 'Sanitär' ),
		'elektrik'  => array( 'Elektrik', 'elektrik', 'elektrik', 'Elektrik' ),
		'maler'     => array( 'Maler', 'malerarbeiten', 'maler', 'Malerarbeiten' ),
		'boden'     => array( 'Bodenbeläge', 'bodenbelaege', 'boden', 'Bodenbeläge' ),
		'container' => array( 'Container', 'container-entsorgung', 'container', 'Container & Entsorgung' ),
		'barriere'  => array( 'Barrierefreies Bad', 'barrierefreies-bad', 'barriere', 'Barrierefreies Bad' ),
	);
}

function bb_anfrage_url( string $gewerk = '' ): string {
	$url = home_url( '/anfrage/' );
	if ( $gewerk ) {
		$url = add_query_arg( 'gewerk', $gewerk, $url );
	}
	return $url . '#anfrage';
}

function bb_tel_href( string $phone ): string {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', $phone );
}

/**
 * Buttons: Angebot + Anruf/WhatsApp (nur wenn hinterlegt).
 */
add_shortcode(
	'bb_cta',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'gewerk' => '', 'label' => 'Kostenloses Angebot anfordern', 'secondary' => '1' ), $atts );
		$html  = '<div class="bb-cta">';
		$html .= '<a class="bb-btn bb-btn--primary" href="' . esc_url( bb_anfrage_url( $atts['gewerk'] ) ) . '">' . esc_html( bb_t( $atts['label'] ) ) . '</a>';
		if ( '1' === $atts['secondary'] ) {
			$phone = bb_opt( 'telefon' );
			$wa    = bb_opt( 'whatsapp' );
			if ( $phone ) {
				$html .= '<a class="bb-btn bb-btn--ghost" href="' . esc_attr( bb_tel_href( $phone ) ) . '"><span class="bb-i bb-i--phone" aria-hidden="true"></span>' . esc_html( $phone ) . '</a>';
			}
			if ( $wa ) {
				$html .= '<a class="bb-btn bb-btn--ghost" href="' . esc_url( 'https://wa.me/' . preg_replace( '/\D/', '', $wa ) ) . '" rel="noopener" target="_blank"><span class="bb-i bb-i--chat" aria-hidden="true"></span>WhatsApp</a>';
			}
		}
		return $html . '</div>';
	}
);

add_shortcode( 'bb_antwort', fn() => esc_html( bb_opt( 'antwort' ) ) );
add_shortcode( 'bb_year', fn() => esc_html( wp_date( 'Y' ) ) );
add_shortcode( 'bb_firma', fn() => esc_html( bb_opt( 'firma' ) ) );

/**
 * Hero-Karte: „Was soll gemacht werden?“ mit Gewerk-Chips.
 */
add_shortcode(
	'bb_gewerk_chips',
	function () {
		$html = '<ul class="bb-chips">';
		foreach ( bb_gewerke() as $slug => $g ) {
			$html .= '<li><a class="bb-chip" href="' . esc_url( bb_anfrage_url( $slug ) ) . '"><span class="bb-ico bb-ico--' . esc_attr( $g[2] ) . '" aria-hidden="true"></span>' . esc_html( bb_t( $g[0] ) ) . '</a></li>';
		}
		return $html . '</ul>';
	}
);

/**
 * Leistungskarten (Startseite und Übersicht).
 */
add_shortcode(
	'bb_leistungen',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'ohne' => '' ), $atts );
		$ohne  = array_filter( array_map( 'trim', explode( ',', $atts['ohne'] ) ) );
		$texte = array(
			'bad'       => 'Vom Abriss bis zur letzten Fuge: Sanitär, Fliesen, Elektrik und Entsorgung aus einer Hand – mit einem festen Ansprechpartner.',
			'fliesen'   => 'Bad, Küche, Flur oder Terrasse: Fliesenleger aus Ihrer Region verlegen Wand- und Bodenfliesen sauber und fachgerecht.',
			'sanitaer'  => 'Neue Dusche, WC, Waschtisch oder Leitungen: Installateur-Fachbetriebe übernehmen Montage und Anschluss.',
			'elektrik'  => 'Steckdosen, Beleuchtung, Unterverteilung oder Komplettsanierung – ausgeführt von eingetragenen Elektrofachbetrieben.',
			'maler'     => 'Streichen, Tapezieren, Spachteln: für einzelne Räume, ganze Wohnungen oder vor dem Einzug.',
			'boden'     => 'Laminat, Vinyl, Parkett oder Teppich – verlegt mit passendem Untergrund und sauberen Abschlüssen.',
			'container' => 'Bauschutt, Sperrmüll oder Renovierungsabfälle: Container in passender Größe, geliefert und abgeholt.',
			'barriere'  => 'Bodengleiche Dusche, Haltegriffe, mehr Bewegungsfläche – mit Hilfe beim Zuschuss der Pflegekasse.',
		);
		$html = '<div class="bb-cards">';
		foreach ( bb_gewerke() as $slug => $g ) {
			if ( in_array( $slug, $ohne, true ) ) {
				continue;
			}
			$featured = 'bad' === $slug ? ' bb-card--featured' : '';
			$html    .= '<a class="bb-card' . $featured . '" href="' . esc_url( home_url( '/' . $g[1] . '/' ) ) . '">';
			$html    .= '<span class="bb-ico bb-ico--' . esc_attr( $g[2] ) . '" aria-hidden="true"></span>';
			$html    .= '<h3>' . esc_html( bb_t( $g[3] ) ) . '</h3>';
			$html    .= '<p>' . esc_html( bb_t( $texte[ $slug ] ) ) . '</p>';
			$html    .= '<span class="bb-card__more">' . esc_html( bb_t( 'Mehr erfahren' ) ) . ' →</span>';
			$html    .= '</a>';
		}
		return $html . '</div>';
	}
);

/**
 * Regionen-Links (nur Städte mit eigener Seite werden verlinkt).
 */
function bb_regionen(): array {
	return array(
		'braunschweig' => 'Braunschweig',
		'wolfsburg'    => 'Wolfsburg',
		'salzgitter'   => 'Salzgitter',
		'wolfenbuettel' => 'Wolfenbüttel',
		'peine'        => 'Peine',
		'gifhorn'      => 'Gifhorn',
	);
}

add_shortcode(
	'bb_regionen',
	function () {
		$html = '<ul class="bb-regions">';
		foreach ( bb_regionen() as $slug => $name ) {
			$html .= '<li><a href="' . esc_url( home_url( '/handwerker-' . $slug . '/' ) ) . '">' . esc_html( $name ) . '</a></li>';
		}
		foreach ( array( 'Hannover', 'Hildesheim', 'Göttingen' ) as $name ) {
			$html .= '<li><span>' . esc_html( $name ) . '</span></li>';
		}
		return $html . '</ul>';
	}
);

/**
 * Umschalter Hell/Dunkel.
 */
add_shortcode(
	'bb_theme_toggle',
	fn() => '<button type="button" class="bb-theme-toggle" data-bb-theme-toggle aria-label="' . esc_attr( bb_t( 'Hell/Dunkel umschalten' ) ) . '"><span class="bb-i bb-i--sun" aria-hidden="true"></span><span class="bb-i bb-i--moon" aria-hidden="true"></span></button>'
);

/**
 * Logo (Wortmarke + Monogramm) als Link zur Startseite.
 */
add_shortcode(
	'bb_logo',
	function () {
		return '<a class="bb-logo" href="' . esc_url( home_url( '/' ) ) . '" aria-label="B&amp;B Projektbau – Startseite">'
			. '<span class="bb-logo__mark" aria-hidden="true">B<i>&amp;</i>B</span>'
			. '<span class="bb-logo__word">Projektbau<small>Handwerkervermittlung</small></span></a>';
	}
);

/**
 * Rechtlicher Vermittlungshinweis (Footer, Formulare).
 */
add_shortcode(
	'bb_vermittlungshinweis',
	fn() => '<p class="bb-legal-note">' . esc_html( bb_opt( 'firma' ) ) . ' ' . esc_html( bb_t( 'vermittelt Aufträge an selbstständige Handwerks- und Fachbetriebe. Der Vertrag über die Ausführung kommt ausschließlich zwischen Ihnen und dem jeweiligen Fachbetrieb zustande.' ) ) . '</p>'
);

/**
 * Kontaktzeile im Footer.
 */
add_shortcode(
	'bb_kontakt',
	function () {
		$out   = array();
		$phone = bb_opt( 'telefon' );
		$mail  = bb_opt( 'email' );
		if ( $phone ) {
			$out[] = '<a href="' . esc_attr( bb_tel_href( $phone ) ) . '">' . esc_html( $phone ) . '</a>';
		}
		if ( $mail ) {
			$out[] = '<a href="mailto:' . esc_attr( antispambot( $mail ) ) . '">' . esc_html( antispambot( $mail ) ) . '</a>';
		}
		if ( bb_opt( 'strasse' ) ) {
			$out[] = esc_html( bb_opt( 'strasse' ) . ', ' . bb_opt( 'ort' ) );
		}
		if ( ! $out ) {
			return is_user_logged_in() ? '<p>' . bb_missing( 'Telefon / E-Mail (Einstellungen → B&B Website)' ) . '</p>' : '';
		}
		return '<p class="bb-contact">' . implode( '<br>', $out ) . '</p>';
	}
);

/**
 * Patterns in Templates werden nach do_shortcode gerendert – Shortcodes darin hier auflösen.
 */
add_filter( 'render_block_core/pattern', 'do_shortcode', 11 );
add_filter( 'render_block_core/shortcode', 'do_shortcode', 11 );

/**
 * Mobile Leiste unten (Angebot + Anruf), erscheint nach dem Hero.
 */
add_action(
	'wp_footer',
	function () {
		if ( is_page( 'anfrage' ) ) {
			return;
		}
		$phone = bb_opt( 'telefon' );
		echo '<div class="bb-mobilebar" data-bb-mobilebar>';
		echo '<a class="bb-btn bb-btn--primary" href="' . esc_url( bb_anfrage_url() ) . '">' . esc_html( bb_t( 'Kostenloses Angebot' ) ) . '</a>';
		if ( $phone ) {
			echo '<a class="bb-btn bb-btn--ghost" href="' . esc_attr( bb_tel_href( $phone ) ) . '" aria-label="' . esc_attr( bb_t( 'Anrufen' ) ) . '"><span class="bb-i bb-i--phone" aria-hidden="true"></span></a>';
		}
		echo '</div>';
	}
);
