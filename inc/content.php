<?php
/**
 * Seiteninhalte anlegen (einmalig pro Inhaltsversion).
 * Seiten, die im Editor geändert wurden, werden nie überschrieben (Hash-Vergleich).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const BB_CONTENT_VERSION = 1;

add_action( 'after_switch_theme', 'bb_seed_content' );
add_action(
	'init',
	function () {
		if ( (int) get_option( 'bb_content_version', 0 ) < BB_CONTENT_VERSION ) {
			bb_seed_content();
		}
	},
	20
);

/* ---------- Block-Helfer ---------- */

function bb_b_p( string $text, string $class = '' ): string {
	$attr = $class ? ' {"className":"' . $class . '"}' : '';
	$cls  = $class ? ' class="' . $class . '"' : '';
	return "<!-- wp:paragraph{$attr} -->\n<p{$cls}>" . $text . "</p>\n<!-- /wp:paragraph -->\n";
}

function bb_b_h( string $text, int $level = 2 ): string {
	$attr = 2 === $level ? '' : ' {"level":' . $level . '}';
	return "<!-- wp:heading{$attr} -->\n<h{$level} class=\"wp-block-heading\">" . esc_html( $text ) . "</h{$level}>\n<!-- /wp:heading -->\n";
}

function bb_b_list( array $items, string $class = 'bb-check' ): string {
	$out = "<!-- wp:list {\"className\":\"{$class}\"} -->\n<ul class=\"wp-block-list {$class}\">";
	foreach ( $items as $item ) {
		$out .= "<!-- wp:list-item -->\n<li>" . $item . "</li>\n<!-- /wp:list-item -->";
	}
	return $out . "</ul>\n<!-- /wp:list -->\n";
}

function bb_b_sc( string $sc ): string {
	return "<!-- wp:shortcode -->\n{$sc}\n<!-- /wp:shortcode -->\n";
}

function bb_b_pattern( string $slug ): string {
	return '<!-- wp:pattern {"slug":"bb-projektbau/' . $slug . "\"} /-->\n";
}

function bb_b_group( string $inner, string $class, string $size = '1200px' ): string {
	return '<!-- wp:group {"tagName":"section","className":"' . $class . '","layout":{"type":"constrained","contentSize":"' . $size . "\"}} -->\n<section class=\"wp-block-group {$class}\">\n" . $inner . "</section>\n<!-- /wp:group -->\n";
}

function bb_b_faq( array $faq ): string {
	$out = '';
	foreach ( $faq as $q => $a ) {
		$out .= "<!-- wp:details {\"className\":\"bb-faq\"} -->\n<details class=\"wp-block-details bb-faq\"><summary>" . esc_html( $q ) . "</summary>" . bb_b_p( esc_html( $a ) ) . "</details>\n<!-- /wp:details -->\n";
	}
	return $out;
}

function bb_b_hero( string $eyebrow, string $h1, string $lead, string $gewerk = '' ): string {
	$inner  = bb_b_p( esc_html( $eyebrow ), 'bb-eyebrow' );
	$inner .= "<!-- wp:heading {\"level\":1} -->\n<h1 class=\"wp-block-heading\">" . esc_html( $h1 ) . "</h1>\n<!-- /wp:heading -->\n";
	$inner .= bb_b_p( esc_html( $lead ), 'bb-lead' );
	$inner .= bb_b_sc( '[bb_cta' . ( $gewerk ? ' gewerk="' . $gewerk . '"' : '' ) . ']' );
	return bb_b_group( $inner, 'bb-hero bb-hero--sub', '860px' );
}

function bb_b_form( string $title, string $gewerk = '', string $typ = 'kunde' ): string {
	$inner  = bb_b_h( $title );
	$inner .= bb_b_p( 'Kostenlos und unverbindlich – wir melden uns [bb_antwort].', 'bb-lead' );
	$inner .= bb_b_sc( '[bb_anfrage typ="' . $typ . '"' . ( $gewerk ? ' gewerk="' . $gewerk . '"' : '' ) . ']' );
	return bb_b_group( $inner, 'bb-section bb-section--form', '760px' );
}

/* ---------- Inhalte ---------- */

function bb_service_pages(): array {
	return array(
		'badsanierung'         => array(
			'gewerk' => 'bad',
			'title'  => 'Badsanierung komplett',
			'h1'     => 'Badsanierung komplett – ein Ansprechpartner für alle Gewerke',
			'lead'   => 'Sanitär, Fliesen, Elektrik und Entsorgung: Wir stimmen die Fachbetriebe aus Ihrer Region aufeinander ab, damit Ihr neues Bad ohne Leerlauf fertig wird.',
			'items'  => array( 'Rückbau von Wanne, Fliesen und alten Leitungen', 'Neue Wasser- und Abwasserleitungen, Dusche, WC, Waschtisch', 'Wand- und Bodenfliesen, auch großformatig', 'Elektrik: Licht, Spiegelschrank, Steckdosen nach Norm', 'Container für Bauschutt und Abholung', 'Auf Wunsch Bauendreinigung durch B&amp;B Glanzmomente' ),
			'tipp'   => 'Gut zu wissen: Bei einer Komplettsanierung entscheidet die Reihenfolge der Gewerke über die Dauer. Wir planen sie so, dass Installateur, Elektriker und Fliesenleger nicht aufeinander warten.',
			'faq'    => array(
				'Wie lange dauert eine Badsanierung?' => 'Das hängt von Größe und Umfang ab. Der Fachbetrieb nennt Ihnen nach der Besichtigung einen konkreten Zeitplan.',
				'Bekomme ich ein Gesamtangebot?'      => 'Sie erhalten die Angebote der beteiligten Fachbetriebe. Wir helfen Ihnen, sie zu vergleichen und abzustimmen.',
				'Kann ich das Bad barrierefrei umbauen?' => 'Ja. Mit Pflegegrad kann die Pflegekasse den Umbau bezuschussen – mehr dazu auf unserer Seite „Barrierefreies Bad“.',
			),
			'meta'   => 'Badsanierung in Niedersachsen: Sanitär, Fliesen, Elektrik und Entsorgung aus einer Hand. Geprüfte Fachbetriebe, Vermittlung kostenlos.',
		),
		'fliesenarbeiten'      => array(
			'gewerk' => 'fliesen',
			'title'  => 'Fliesenarbeiten',
			'h1'     => 'Fliesenleger in Ihrer Nähe – sauber verlegt, fair angeboten',
			'lead'   => 'Ob Bad, Küche, Flur oder Terrasse: Wir vermitteln Fliesenleger aus Ihrer Region für Wand- und Bodenfliesen.',
			'items'  => array( 'Wand- und Bodenfliesen in Bad, Küche und Flur', 'Großformate und Feinsteinzeug', 'Abdichtung im Nassbereich', 'Terrassen und Balkone', 'Ausbessern und Neuverfugen' ),
			'tipp'   => 'Gut zu wissen: Fliesenleger ist ein zulassungsfreies Handwerk. Wir achten trotzdem auf Erfahrung, Referenzen und eine Betriebshaftpflicht.',
			'faq'    => array(
				'Muss ich die Fliesen selbst kaufen?' => 'Nein. Das klären Sie mit dem Fachbetrieb – viele bringen Muster mit oder beraten im Fachhandel.',
				'Was kostet die Vermittlung?'         => 'Für Sie nichts. Sie zahlen nur die Arbeit des Fachbetriebs laut dessen Angebot.',
			),
			'meta'   => 'Fliesenleger in Niedersachsen finden: Bad, Küche, Terrasse. Geprüfte Fachbetriebe aus Ihrer Region, Vermittlung kostenlos.',
		),
		'sanitaer'             => array(
			'gewerk' => 'sanitaer',
			'title'  => 'Sanitär',
			'h1'     => 'Sanitär-Fachbetrieb gesucht? Wir vermitteln den passenden Installateur',
			'lead'   => 'Neue Dusche, WC, Waschtisch oder Leitungen: Eingetragene Installateur-Meisterbetriebe übernehmen Montage und Anschluss.',
			'items'  => array( 'Dusche, Badewanne, WC und Waschtisch montieren', 'Wasser- und Abwasserleitungen erneuern', 'Vorwandinstallation', 'Armaturen und Durchlauferhitzer', 'Umbau zur bodengleichen Dusche' ),
			'tipp'   => 'Gut zu wissen: Installateur ist ein zulassungspflichtiges Handwerk. Wir vermitteln nur Betriebe mit Eintrag in der Handwerksrolle.',
			'faq'    => array(
				'Vermitteln Sie auch Notdienste?' => 'Unser Schwerpunkt sind planbare Arbeiten. Für Notfälle wenden Sie sich bitte direkt an einen Notdienst.',
				'Wer stellt die Rechnung?'        => 'Der Fachbetrieb, mit dem Sie den Vertrag schließen.',
			),
			'meta'   => 'Installateur in Niedersachsen: Dusche, WC, Leitungen. Eingetragene Sanitär-Fachbetriebe, Vermittlung für Sie kostenlos.',
		),
		'elektrik'             => array(
			'gewerk' => 'elektrik',
			'title'  => 'Elektrik',
			'h1'     => 'Elektriker vermittelt – sicher, normgerecht, aus Ihrer Region',
			'lead'   => 'Von der neuen Steckdose bis zur kompletten Elektrosanierung: Wir vermitteln eingetragene Elektrofachbetriebe.',
			'items'  => array( 'Steckdosen, Schalter und Beleuchtung', 'Unterverteilung und Sicherungskasten', 'Elektrik im Bad nach Norm', 'Komplettsanierung bei Altbau-Renovierung', 'Anschlüsse für Küche und Herd' ),
			'tipp'   => 'Gut zu wissen: Arbeiten an der Hausinstallation dürfen nur eingetragene Elektrofachbetriebe ausführen. Genau solche vermitteln wir.',
			'faq'    => array(
				'Brauche ich einen Elektriker für die Badsanierung?' => 'Fast immer – für Licht, Spiegelschrank und Steckdosen im Nassbereich gelten besondere Vorschriften.',
				'Was kostet die Vermittlung?' => 'Für Sie nichts. Sie zahlen nur die Arbeit des Fachbetriebs.',
			),
			'meta'   => 'Elektriker in Niedersachsen finden: Steckdosen, Unterverteilung, Elektrosanierung. Eingetragene Fachbetriebe, kostenlose Vermittlung.',
		),
		'malerarbeiten'        => array(
			'gewerk' => 'maler',
			'title'  => 'Malerarbeiten',
			'h1'     => 'Maler für Wohnung und Haus – schnell vermittelt',
			'lead'   => 'Streichen, Tapezieren, Spachteln: für einzelne Räume, ganze Wohnungen oder vor dem Einzug.',
			'items'  => array( 'Wände und Decken streichen', 'Tapezieren, auch Vlies und Glasfaser', 'Spachteln und Q-Stufen', 'Renovierung vor Einzug oder Übergabe', 'Lackieren von Türen und Heizkörpern' ),
			'tipp'   => 'Gut zu wissen: Bei Wohnungsübergaben zählt oft der Termin. Sagen Sie uns Ihr Übergabedatum – wir suchen einen Betrieb, der es schafft.',
			'faq'    => array(
				'Vermitteln Sie auch für Vermieter und Hausverwaltungen?' => 'Ja, gern auch regelmäßig. Sprechen Sie uns an.',
				'Wie schnell kann es losgehen?' => 'Das hängt von der Auslastung in Ihrer Region ab. Wir nennen Ihnen nach der Anfrage einen realistischen Termin.',
			),
			'meta'   => 'Maler in Niedersachsen: Streichen, Tapezieren, Renovierung vor Einzug. Fachbetriebe aus Ihrer Region, Vermittlung kostenlos.',
		),
		'bodenbelaege'         => array(
			'gewerk' => 'boden',
			'title'  => 'Bodenbeläge',
			'h1'     => 'Bodenleger vermittelt – Laminat, Vinyl, Parkett',
			'lead'   => 'Neuer Boden mit passendem Untergrund und sauberen Abschlüssen – verlegt von Fachbetrieben aus Ihrer Region.',
			'items'  => array( 'Laminat und Klick-Vinyl', 'Parkett und Dielen', 'Teppich und Designbeläge', 'Untergrund ausgleichen', 'Sockelleisten und Übergänge' ),
			'tipp'   => 'Gut zu wissen: Ein ebener Untergrund ist die halbe Miete. Der Fachbetrieb prüft ihn bei der Besichtigung.',
			'faq'    => array(
				'Wird der alte Boden entfernt?' => 'Auf Wunsch ja. Für die Entsorgung vermitteln wir auch einen Container.',
				'Was kostet die Vermittlung?'  => 'Für Sie nichts.',
			),
			'meta'   => 'Bodenleger in Niedersachsen: Laminat, Vinyl, Parkett. Geprüfte Fachbetriebe aus Ihrer Region, Vermittlung kostenlos.',
		),
		'container-entsorgung' => array(
			'gewerk' => 'container',
			'title'  => 'Container & Entsorgung',
			'h1'     => 'Container für Bauschutt und Renovierung – geliefert und abgeholt',
			'lead'   => 'Bauschutt, Sperrmüll oder Renovierungsabfälle: Wir vermitteln Container in passender Größe.',
			'items'  => array( 'Container für Bauschutt', 'Container für gemischte Baustellenabfälle', 'Sperrmüll und Entrümpelung', 'Lieferung und Abholung zum Wunschtermin' ),
			'tipp'   => 'Gut zu wissen: Steht der Container auf öffentlichem Grund, ist meist eine Genehmigung der Stadt nötig. Fragen Sie uns – wir sagen Ihnen, was zu tun ist.',
			'faq'    => array(
				'Welche Größe brauche ich?' => 'Beschreiben Sie kurz Ihr Vorhaben. Wir empfehlen eine passende Größe.',
				'Darf ich alles in einen Container werfen?' => 'Nein. Bauschutt, Holz und Mischabfall werden meist getrennt. Schadstoffe wie Asbest gehören nie in einen normalen Container.',
			),
			'meta'   => 'Container für Bauschutt und Renovierung in Niedersachsen: passende Größe, Lieferung und Abholung. Schnell anfragen.',
		),
		'barrierefreies-bad'   => array(
			'gewerk' => 'barriere',
			'title'  => 'Barrierefreies Bad',
			'h1'     => 'Barrierefreies Bad – bis zu 4.180 € Zuschuss von der Pflegekasse',
			'lead'   => 'Bodengleiche Dusche, Haltegriffe, mehr Bewegungsfläche: Wir vermitteln Fachbetriebe und helfen beim Zusammenstellen der Unterlagen für den Antrag.',
			'items'  => array( 'Badewanne raus, bodengleiche Dusche rein', 'Haltegriffe, Klappsitz, erhöhtes WC', 'Breitere Tür und mehr Bewegungsfläche', 'Rutschhemmende Fliesen', 'Kostenvoranschlag für den Antrag bei der Pflegekasse' ),
			'tipp'   => 'Wichtig: Ab Pflegegrad 1 kann die Pflegekasse bis zu 4.180 € pro pflegebedürftiger Person bezuschussen (§ 40 SGB XI). Der Antrag muss vor Beginn der Arbeiten gestellt werden. Ob und in welcher Höhe gezahlt wird, entscheidet die Pflegekasse.',
			'faq'    => array(
				'Wer stellt den Antrag?' => 'Die pflegebedürftige Person oder ihre Angehörigen bei der Pflegekasse. Den nötigen Kostenvoranschlag erstellt der Fachbetrieb.',
				'Gilt der Zuschuss auch für Mieter?' => 'Ja, grundsätzlich auch für Mieter. Für Umbauten in der Mietwohnung brauchen Sie die Zustimmung des Vermieters.',
				'Gibt es weitere Förderung?' => 'Je nach Situation kommen weitere Programme infrage. Lassen Sie sich dazu bei Ihrer Pflegekasse oder einer Wohnberatungsstelle beraten.',
			),
			'meta'   => 'Barrierefreies Bad in Niedersachsen: bodengleiche Dusche, Haltegriffe. Bis zu 4.180 € Zuschuss der Pflegekasse. Fachbetriebe vermittelt.',
		),
	);
}

function bb_region_pages(): array {
	return array(
		'braunschweig'  => array( 'Braunschweig', 'Ob Altbau im Östlichen Ringgebiet, Reihenhaus in Lehndorf oder Wohnung in der Weststadt' ),
		'wolfsburg'     => array( 'Wolfsburg', 'Ob Wohnung in Detmerode und Westhagen oder Haus in Fallersleben und Vorsfelde' ),
		'salzgitter'    => array( 'Salzgitter', 'Ob in Lebenstedt, Salzgitter-Bad oder Thiede' ),
		'wolfenbuettel' => array( 'Wolfenbüttel', 'Ob in der Innenstadt, in Linden oder Groß Stöckheim' ),
		'peine'         => array( 'Peine', 'Ob in der Kernstadt, in Vöhrum oder Stederdorf' ),
		'gifhorn'       => array( 'Gifhorn', 'Ob in der Innenstadt, in Gamsen oder Kästorf' ),
	);
}

function bb_page_definitions(): array {
	$pages = array();

	$pages['startseite'] = array( 'title' => 'Startseite', 'content' => '', 'template' => '' );

	$pages['anfrage'] = array(
		'title'    => 'Angebot anfordern',
		'template' => 'page-landing',
		'content'  => bb_b_hero( 'Kostenlos & unverbindlich', 'Kostenloses Angebot anfordern', 'Beschreiben Sie Ihr Vorhaben in 2 Minuten. Wir melden uns mit dem passenden Fachbetrieb aus Ihrer Region.' )
			. bb_b_group( bb_b_sc( '[bb_anfrage typ="kunde"]' ), 'bb-section bb-section--form', '760px' )
			. bb_b_pattern( 'ablauf' ),
		'meta'     => 'Kostenloses Angebot für Ihr Handwerksprojekt in Niedersachsen anfordern: Bad, Fliesen, Sanitär, Elektrik, Maler, Boden, Container.',
	);

	$pages['leistungen'] = array(
		'title'    => 'Leistungen',
		'template' => 'page-landing',
		'content'  => bb_b_hero( 'Leistungen', 'Unsere Leistungen – einzeln oder als Komplettpaket', 'Wir vermitteln geprüfte Fachbetriebe für alle Arbeiten rund um Renovierung und Sanierung in Niedersachsen.' )
			. bb_b_group( bb_b_sc( '[bb_leistungen]' ), 'bb-section' )
			. bb_b_pattern( 'cta' ),
		'meta'     => 'Badsanierung, Fliesen, Sanitär, Elektrik, Maler, Bodenbeläge und Container in Niedersachsen – geprüfte Fachbetriebe, Vermittlung kostenlos.',
	);

	foreach ( bb_service_pages() as $slug => $s ) {
		$body  = bb_b_h( 'Was wir für Sie vermitteln' ) . bb_b_list( $s['items'] );
		$body .= bb_b_p( esc_html( $s['tipp'] ), 'bb-note' );
		$body  = str_replace( 'B&amp;amp;B', 'B&amp;B', $body );
		$faq   = bb_b_h( 'Häufige Fragen' ) . bb_b_faq( $s['faq'] );

		$pages[ $slug ] = array(
			'title'    => $s['title'],
			'template' => 'page-landing',
			'content'  => bb_b_hero( 'Leistung · Niedersachsen', $s['h1'], $s['lead'], $s['gewerk'] )
				. bb_b_group( $body, 'bb-section', '760px' )
				. bb_b_pattern( 'ablauf' )
				. bb_b_group( $faq, 'bb-section', '760px' )
				. bb_b_form( 'Jetzt kostenlos anfragen', $s['gewerk'] ),
			'meta'     => $s['meta'],
		);
	}

	foreach ( bb_region_pages() as $slug => $r ) {
		list( $city, $intro ) = $r;
		$links = array();
		foreach ( bb_gewerke() as $g ) {
			$links[] = '<a href="' . esc_url( home_url( '/' . $g[1] . '/' ) ) . '">' . esc_html( $g[3] ) . '</a>';
		}
		$body  = bb_b_h( 'Leistungen in ' . $city );
		$body .= bb_b_p( esc_html( $intro . ': Wir vermitteln Fachbetriebe aus ' . $city . ' und Umgebung, die Ihr Vorhaben kurzfristig besichtigen können.' ) );
		$body .= bb_b_list( $links, 'bb-linklist' );

		$pages[ 'handwerker-' . $slug ] = array(
			'title'    => 'Handwerker ' . $city,
			'template' => 'page-landing',
			'content'  => bb_b_hero( 'Region ' . $city, 'Handwerker in ' . $city . ' – geprüft und kostenlos vermittelt', 'Bad, Fliesen, Sanitär, Elektrik, Maler, Boden oder Container: ein Ansprechpartner für Ihr Projekt in ' . $city . '.' )
				. bb_b_group( $body, 'bb-section', '760px' )
				. bb_b_pattern( 'ablauf' )
				. bb_b_form( 'Anfrage für ' . $city ),
			'meta'     => 'Handwerker in ' . $city . ' finden: Badsanierung, Fliesen, Sanitär, Elektrik, Maler, Boden, Container. Geprüfte Fachbetriebe, Vermittlung kostenlos.',
		);
	}

	$partner  = bb_b_h( 'Ihre Vorteile als Partnerbetrieb' );
	$partner .= bb_b_list(
		array(
			'Qualifizierte Anfragen aus Ihrer Region – ohne eigene Akquise',
			'Vorab geklärt: Umfang, Ort, Zeitraum und auf Wunsch Fotos',
			'Sie entscheiden selbst, welche Aufträge Sie annehmen',
			'Faire, transparente Vergütung nur bei Vermittlung',
		)
	);
	$partner .= bb_b_h( 'Was wir erwarten' );
	$partner .= bb_b_list( array( 'Eintrag in der Handwerksrolle bei zulassungspflichtigen Gewerken', 'Gültige Betriebshaftpflichtversicherung', 'Gewerbeanmeldung', 'Zuverlässige Termine und saubere Arbeit' ) );

	$pages['fuer-handwerker'] = array(
		'title'    => 'Für Handwerker',
		'template' => 'page-landing',
		'content'  => bb_b_hero( 'Partner werden', 'Sie sind Handwerksbetrieb? Werden Sie Partner.', 'Wir suchen zuverlässige Fachbetriebe in ganz Niedersachsen. Sie erhalten passende Anfragen aus Ihrer Region – ohne Akquise-Aufwand.' )
			. bb_b_group( $partner, 'bb-section', '760px' )
			. bb_b_form( 'Jetzt Partner werden', '', 'partner' ),
		'meta'     => 'Handwerksbetrieb in Niedersachsen? Werden Sie Partner der B&B Projektbauvermittlung und erhalten Sie passende Aufträge aus Ihrer Region.',
	);

	foreach ( array( 'impressum' => 'Impressum', 'datenschutz' => 'Datenschutz', 'vermittlungsbedingungen' => 'Vermittlungsbedingungen' ) as $slug => $title ) {
		$pages[ $slug ] = array( 'title' => $title, 'template' => 'page-legal', 'content' => '' );
	}

	return $pages;
}

function bb_seed_content(): void {
	if ( wp_installing() ) {
		return;
	}
	update_option( 'bb_content_version', BB_CONTENT_VERSION );

	$ids = array();
	foreach ( bb_page_definitions() as $slug => $def ) {
		$parent_id = 0;
		if ( ! empty( $def['parent'] ) && ! empty( $ids[ $def['parent'] ] ) ) {
			$parent_id = $ids[ $def['parent'] ];
		}
		$path     = $parent_id ? $def['parent'] . '/' . $slug : $slug;
		$existing = get_page_by_path( $path ) ?: get_page_by_path( $slug );
		$content  = $def['content'];

		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			$hash         = get_post_meta( $existing->ID, '_bb_seed_hash', true );
			// Nur aktualisieren, wenn die Seite von uns stammt und nicht bearbeitet wurde.
			if ( $hash && md5( $existing->post_content ) === $hash && $existing->post_content !== $content ) {
				wp_update_post( array( 'ID' => $existing->ID, 'post_content' => $content ) );
				update_post_meta( $existing->ID, '_bb_seed_hash', md5( get_post_field( 'post_content', $existing->ID, 'raw' ) ) );
			}
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $def['title'],
				'post_name'    => $slug,
				'post_parent'  => 0,
				'post_content' => $content,
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			continue;
		}
		$ids[ $slug ] = $id;
		update_post_meta( $id, '_bb_seed_hash', md5( get_post_field( 'post_content', $id, 'raw' ) ) );
		if ( ! empty( $def['template'] ) ) {
			update_post_meta( $id, '_wp_page_template', $def['template'] );
		}
		if ( ! empty( $def['meta'] ) ) {
			update_post_meta( $id, '_bb_meta_description', $def['meta'] );
		}
	}

	if ( ! empty( $ids['startseite'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['startseite'] );
	}
	if ( ! empty( $ids['datenschutz'] ) ) {
		update_option( 'wp_page_for_privacy_policy', $ids['datenschutz'] );
	}
	update_option( 'blogname', 'B&B Projektbau' );
	update_option( 'blogdescription', 'Handwerkervermittlung in Niedersachsen' );
	if ( '/%postname%/' !== get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules( false );
}

/**
 * Meta-Description + Title-Tag (ohne SEO-Plugin).
 */
add_action(
	'wp_head',
	function () {
		$desc = '';
		if ( is_front_page() ) {
			$desc = 'Fliesen, Sanitär, Elektrik, Maler, Boden & Container: Wir vermitteln geprüfte Fachbetriebe in Niedersachsen. Für Sie kostenlos. Jetzt Angebot anfordern.';
		} elseif ( is_page() ) {
			$desc = (string) get_post_meta( get_queried_object_id(), '_bb_meta_description', true );
		}
		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
			echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
		echo '<meta property="og:title" content="' . esc_attr( wp_get_document_title() ) . '">' . "\n";
		echo '<meta property="og:type" content="website">' . "\n";
		echo '<meta property="og:locale" content="de_DE">' . "\n";
	},
	2
);

add_filter(
	'pre_get_document_title',
	function ( $title ) {
		if ( is_front_page() ) {
			return 'Handwerker in Niedersachsen vermittelt | B&B Projektbau';
		}
		return $title;
	}
);

/**
 * Strukturierte Daten (LocalBusiness) auf der Startseite.
 */
add_action(
	'wp_head',
	function () {
		if ( ! is_front_page() ) {
			return;
		}
		$data = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'HomeAndConstructionBusiness',
			'name'       => bb_opt( 'firma' ),
			'url'        => home_url( '/' ),
			'areaServed' => array( '@type' => 'State', 'name' => 'Niedersachsen' ),
		);
		if ( bb_opt( 'telefon' ) ) {
			$data['telephone'] = bb_opt( 'telefon' );
		}
		if ( bb_opt( 'strasse' ) ) {
			$data['address'] = array( '@type' => 'PostalAddress', 'streetAddress' => bb_opt( 'strasse' ), 'addressLocality' => bb_opt( 'ort' ), 'addressCountry' => 'DE' );
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	},
	3
);
