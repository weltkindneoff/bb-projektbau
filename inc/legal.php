<?php
/**
 * Rechtliche Seiten aus dem Code, solange die Seite im Editor leer ist.
 * Fehlende Daten erscheinen als sichtbarer Hinweis – nichts wird erfunden.
 * Hinweis: Texte vor dem Start von einer Fachperson prüfen lassen.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bb_legal_slugs(): array {
	return array( 'impressum', 'datenschutz', 'vermittlungsbedingungen' );
}

add_filter(
	'the_content',
	function ( $content ) {
		if ( ! is_page() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$slug = get_post_field( 'post_name', get_the_ID() );
		if ( ! in_array( $slug, bb_legal_slugs(), true ) || '' !== trim( wp_strip_all_tags( $content ) ) ) {
			return $content;
		}
		$fn = 'bb_legal_' . $slug;
		return function_exists( $fn ) ? $fn() : $content;
	},
	20
);

function bb_val( string $key, string $label ): string {
	$v = bb_opt( $key );
	return $v ? esc_html( $v ) : bb_missing( $label );
}

function bb_legal_impressum(): string {
	$mail = bb_opt( 'email' );
	$h    = '<h2>Angaben gemäß § 5 DDG</h2>';
	$h   .= '<p>' . esc_html( bb_opt( 'firma' ) ) . '<br>' . bb_val( 'strasse', 'Straße und Hausnummer' ) . '<br>' . bb_val( 'ort', 'PLZ und Ort' ) . '</p>';
	$h   .= '<h3>Vertreten durch die Gesellschafterinnen</h3><p>' . bb_val( 'vertreten', 'Vertretungsberechtigte Gesellschafterinnen' ) . '</p>';
	$h   .= '<h3>Kontakt</h3><p>Telefon: ' . bb_val( 'telefon', 'Telefonnummer' ) . '<br>E-Mail: ' . ( $mail ? esc_html( antispambot( $mail ) ) : bb_missing( 'E-Mail-Adresse' ) ) . '</p>';
	$h   .= '<h3>Registereintrag</h3><p>' . bb_val( 'register', 'Registergericht und Registernummer (Gesellschaftsregister)' ) . '</p>';
	if ( bb_opt( 'ustid' ) ) {
		$h .= '<h3>Umsatzsteuer-ID</h3><p>Umsatzsteuer-Identifikationsnummer gemäß § 27a UStG: ' . esc_html( bb_opt( 'ustid' ) ) . '</p>';
	}
	$h .= '<h3>Tätigkeit</h3><p>Wir vermitteln Aufträge an selbstständige Handwerks- und Fachbetriebe. Wir führen selbst keine handwerklichen Arbeiten aus. Verträge über die Ausführung kommen ausschließlich zwischen dem Kunden und dem jeweiligen Fachbetrieb zustande.</p>';
	$h .= '<h3>Verbraucherstreitbeilegung</h3><p>Wir sind nicht bereit und nicht verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>';
	$h .= '<h3>Haftung für Links</h3><p>Unser Angebot enthält Links zu externen Websites Dritter, auf deren Inhalte wir keinen Einfluss haben. Für diese Inhalte ist stets der jeweilige Anbieter verantwortlich.</p>';
	return $h;
}

function bb_legal_datenschutz(): string {
	$firma = esc_html( bb_opt( 'firma' ) );
	$h     = '<h2>1. Verantwortlicher</h2><p>' . $firma . '<br>' . bb_val( 'strasse', 'Straße und Hausnummer' ) . '<br>' . bb_val( 'ort', 'PLZ und Ort' ) . '<br>E-Mail: ' . bb_val( 'email', 'E-Mail-Adresse' ) . '</p>';
	$h    .= '<h2>2. Hosting und Server-Logdateien</h2><p>Diese Website wird bei der Hostinger International Ltd. (61 Lordou Vironos Street, 6023 Larnaca, Zypern) betrieben. Beim Aufruf werden technisch notwendige Daten verarbeitet (IP-Adresse, Datum und Uhrzeit, aufgerufene Seite, Browser). Das ist für den sicheren Betrieb erforderlich (Art. 6 Abs. 1 lit. f DSGVO). Mit dem Hoster besteht ein Vertrag zur Auftragsverarbeitung.</p>';
	$h    .= '<h2>3. Anfrageformular</h2><p>Wenn Sie uns über das Formular eine Anfrage senden, verarbeiten wir Ihre Angaben (Name, Telefon, E-Mail, PLZ/Ort, Beschreibung, freiwillig hochgeladene Fotos), um Ihre Anfrage zu bearbeiten und einen passenden Fachbetrieb zu vermitteln. Dazu geben wir die nötigen Angaben an den ausgewählten Fachbetrieb weiter. Rechtsgrundlage ist Art. 6 Abs. 1 lit. b DSGVO (vorvertragliche Maßnahmen) und Ihre Einwilligung (Art. 6 Abs. 1 lit. a DSGVO), die Sie jederzeit für die Zukunft widerrufen können.</p><p>Wir speichern Anfragen, solange es für die Bearbeitung und die Abrechnung mit dem Fachbetrieb erforderlich ist, und löschen sie danach, soweit keine gesetzlichen Aufbewahrungspflichten bestehen. Fotos werden geschützt gespeichert und sind nicht öffentlich abrufbar.</p>';
	$h    .= '<h2>4. Kontakt per E-Mail, Telefon oder WhatsApp</h2><p>Wenn Sie uns direkt kontaktieren, verarbeiten wir Ihre Angaben zur Bearbeitung Ihres Anliegens (Art. 6 Abs. 1 lit. b bzw. f DSGVO). Bei Nutzung von WhatsApp gelten zusätzlich die Datenschutzbestimmungen der WhatsApp Ireland Ltd.</p>';
	$h    .= '<h2>5. Cookies, Schriften und Analyse</h2><p>Wir setzen keine Tracking- oder Marketing-Cookies und keine Analysewerkzeuge ein. Die Schriftart wird von unserem eigenen Server geladen, es besteht keine Verbindung zu Google. Ihre Wahl zwischen heller und dunkler Darstellung wird nur lokal in Ihrem Browser gespeichert. Vor dem offiziellen Start der Website kann ein technisch notwendiges Vorschau-Cookie gesetzt werden.</p>';
	$h    .= '<h2>6. Ihre Rechte</h2><p>Sie haben das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung, Datenübertragbarkeit sowie Widerspruch (Art. 15–21 DSGVO). Sie können sich zudem bei einer Datenschutz-Aufsichtsbehörde beschweren, zum Beispiel bei der Landesbeauftragten für den Datenschutz Niedersachsen.</p>';
	$h    .= '<p class="bb-legal-date">Stand: ' . esc_html( wp_date( 'F Y' ) ) . '</p>';
	return $h;
}

function bb_legal_vermittlungsbedingungen(): string {
	$firma = esc_html( bb_opt( 'firma' ) );
	$h     = '<h2>1. Leistung</h2><p>' . $firma . ' vermittelt Kunden an selbstständige Handwerks- und Fachbetriebe („Fachbetriebe“). Wir führen selbst keine handwerklichen Arbeiten aus.</p>';
	$h    .= '<h2>2. Kosten für Kunden</h2><p>Die Vermittlung ist für Kunden kostenlos. Unsere Vergütung erhalten wir vom Fachbetrieb.</p>';
	$h    .= '<h2>3. Vertragspartner</h2><p>Der Vertrag über die Ausführung der Arbeiten kommt ausschließlich zwischen dem Kunden und dem Fachbetrieb zustande. Angebot, Rechnung, Ausführung und Gewährleistung liegen beim Fachbetrieb.</p>';
	$h    .= '<h2>4. Unverbindlichkeit</h2><p>Anfrage und Beratung sind unverbindlich. Der Kunde entscheidet frei, ob er ein Angebot annimmt.</p>';
	$h    .= '<h2>5. Haftung</h2><p>Wir wählen Fachbetriebe sorgfältig aus. Für die Ausführung der Arbeiten haftet der jeweilige Fachbetrieb.</p>';
	if ( is_user_logged_in() ) {
		$h .= '<p>' . bb_missing( 'Bedingungen vor dem Start rechtlich prüfen lassen (nur für Eingeloggte sichtbar)' ) . '</p>';
	}
	return $h;
}
