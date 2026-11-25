<?php
/**
 * Anfrageformular (Kunden und Handwerkspartner) + einfache Anfragen-Verwaltung.
 *
 * Shortcode: [bb_anfrage typ="kunde"] oder [bb_anfrage typ="partner"].
 * Anfragen werden als privater Beitragstyp gespeichert (Status, Partner, Übergabedatum –
 * Nachweis für die Provision) und per E-Mail gemeldet. Fotos liegen geschützt außerhalb
 * der öffentlichen Mediathek.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const BB_ANFRAGE_MAX_FOTOS = 5;
const BB_ANFRAGE_MAX_BYTES = 8 * 1024 * 1024;

function bb_anfrage_status(): array {
	return array(
		'neu'           => 'Neu',
		'kontaktiert'   => 'Kontaktiert',
		'uebergeben'    => 'An Partner übergeben',
		'angebot'       => 'Angebot erstellt',
		'beauftragt'    => 'Beauftragt',
		'abgeschlossen' => 'Abgeschlossen',
		'provision'     => 'Provision bezahlt',
		'verloren'      => 'Kein Auftrag',
	);
}

add_action(
	'init',
	function () {
		register_post_type(
			'bb_anfrage',
			array(
				'labels'          => array(
					'name'          => 'Anfragen',
					'singular_name' => 'Anfrage',
					'edit_item'     => 'Anfrage bearbeiten',
					'search_items'  => 'Anfragen durchsuchen',
					'not_found'     => 'Noch keine Anfragen',
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-clipboard',
				'menu_position'   => 3,
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}
);

/**
 * Formular ausgeben.
 */
add_shortcode(
	'bb_anfrage',
	function ( $atts ) {
		$atts   = shortcode_atts( array( 'typ' => 'kunde', 'gewerk' => '' ), $atts );
		$typ    = 'partner' === $atts['typ'] ? 'partner' : 'kunde';
		$status = isset( $_GET['anfrage'] ) ? sanitize_key( wp_unslash( $_GET['anfrage'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		ob_start();
		echo '<div class="bb-form-wrap" id="anfrage">';
		if ( 'danke' === $status ) {
			echo '<div class="bb-form-done" role="status"><span class="bb-ico bb-ico--check" aria-hidden="true"></span><h3>' . esc_html( bb_t( 'Vielen Dank! Ihre Anfrage ist bei uns angekommen.' ) ) . '</h3>';
			echo '<p>' . esc_html( bb_t( 'Wir melden uns' ) . ' ' . bb_opt( 'antwort' ) . '. ' . bb_t( 'Eine Bestätigung haben wir Ihnen per E-Mail geschickt.' ) ) . '</p></div></div>';
			return ob_get_clean();
		}
		if ( 'fehler' === $status ) {
			echo '<p class="bb-form-error" role="alert">' . esc_html( bb_t( 'Das hat leider nicht geklappt. Bitte prüfen Sie Ihre Angaben und senden Sie das Formular erneut – oder rufen Sie uns an.' ) ) . '</p>';
		}
		$action = esc_url( admin_url( 'admin-post.php' ) );
		$pre    = isset( $_GET['gewerk'] ) ? sanitize_key( wp_unslash( $_GET['gewerk'] ) ) : sanitize_key( $atts['gewerk'] ); // phpcs:ignore WordPress.Security.NonceVerification
		?>
		<form class="bb-form" data-bb-steps method="post" action="<?php echo $action; // phpcs:ignore ?>" enctype="multipart/form-data" novalidate>
			<input type="hidden" name="action" value="bb_anfrage">
			<input type="hidden" name="typ" value="<?php echo esc_attr( $typ ); ?>">
			<input type="hidden" name="quelle" value="<?php echo esc_url( get_permalink() ?: home_url( '/' ) ); ?>">
			<input type="hidden" name="t" value="<?php echo esc_attr( (string) time() ); ?>">
			<?php wp_nonce_field( 'bb_anfrage', 'bb_nonce' ); ?>
			<div class="bb-hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

			<?php if ( 'kunde' === $typ ) : ?>
				<div class="bb-progress" aria-hidden="true"><span></span></div>

				<fieldset class="bb-step" data-step="1">
					<legend><span class="bb-step__no">1/4</span> <?php bb_e( 'Welche Arbeiten sollen gemacht werden?' ); ?></legend>
					<div class="bb-options">
						<?php foreach ( bb_gewerke() as $slug => $g ) : ?>
							<label class="bb-option"><input type="checkbox" name="gewerke[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $pre, $slug ); ?>><span><span class="bb-ico bb-ico--<?php echo esc_attr( $g[2] ); ?>" aria-hidden="true"></span><?php echo esc_html( bb_t( $g[0] ) ); ?></span></label>
						<?php endforeach; ?>
					</div>
					<label class="bb-field"><span><?php bb_e( 'Kurz beschreiben (optional)' ); ?></span><textarea name="beschreibung" rows="3" maxlength="2000" placeholder="<?php echo esc_attr( bb_t( 'z. B. Badewanne raus, bodengleiche Dusche rein, ca. 8 m²' ) ); ?>"></textarea></label>
				</fieldset>

				<fieldset class="bb-step" data-step="2">
					<legend><span class="bb-step__no">2/4</span> <?php bb_e( 'Wo und wann?' ); ?></legend>
					<div class="bb-row">
						<label class="bb-field bb-field--sm"><span><?php bb_e( 'PLZ' ); ?> *</span><input type="text" name="plz" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" required autocomplete="postal-code"></label>
						<label class="bb-field"><span><?php bb_e( 'Ort' ); ?></span><input type="text" name="ort" maxlength="80" autocomplete="address-level2"></label>
					</div>
					<label class="bb-field"><span><?php bb_e( 'Größe (optional)' ); ?></span><input type="text" name="umfang" maxlength="120" placeholder="<?php echo esc_attr( bb_t( 'z. B. 8 m² Bad oder 3 Zimmer' ) ); ?>"></label>
					<div class="bb-field"><span><?php bb_e( 'Wann soll es losgehen?' ); ?></span>
						<div class="bb-options bb-options--row">
							<?php foreach ( array( 'Sofort / so schnell wie möglich', 'In 1–3 Monaten', 'Später / nur Preis erfragen' ) as $i => $z ) : ?>
								<label class="bb-option"><input type="radio" name="zeitraum" value="<?php echo esc_attr( $z ); ?>" <?php checked( 0, $i ); ?>><span><?php echo esc_html( bb_t( $z ) ); ?></span></label>
							<?php endforeach; ?>
						</div>
					</div>
				</fieldset>

				<fieldset class="bb-step" data-step="3">
					<legend><span class="bb-step__no">3/4</span> <?php bb_e( 'Fotos (optional)' ); ?></legend>
					<p class="bb-hint"><?php echo esc_html( sprintf( bb_t( 'Fotos helfen dem Fachbetrieb, Ihr Vorhaben schneller einzuschätzen. Bis zu %d Bilder, je max. 8 MB.' ), BB_ANFRAGE_MAX_FOTOS ) ); ?></p>
					<label class="bb-upload"><input type="file" name="fotos[]" accept="image/jpeg,image/png,image/webp,image/heic" multiple><span><?php bb_e( 'Fotos auswählen' ); ?></span></label>
					<ul class="bb-upload__list" data-bb-files></ul>
				</fieldset>

				<fieldset class="bb-step" data-step="4">
					<legend><span class="bb-step__no">4/4</span> <?php bb_e( 'Wie erreichen wir Sie?' ); ?></legend>
					<label class="bb-field"><span><?php bb_e( 'Name' ); ?> *</span><input type="text" name="name" required maxlength="120" autocomplete="name"></label>
					<div class="bb-row">
						<label class="bb-field"><span><?php bb_e( 'Telefon' ); ?> *</span><input type="tel" name="telefon" required maxlength="40" autocomplete="tel"></label>
						<label class="bb-field"><span><?php bb_e( 'E-Mail' ); ?> *</span><input type="email" name="email" required maxlength="120" autocomplete="email"></label>
					</div>
					<label class="bb-field"><span><?php bb_e( 'Wann können wir Sie am besten anrufen?' ); ?></span><input type="text" name="rueckruf" maxlength="120" placeholder="<?php echo esc_attr( bb_t( 'z. B. werktags ab 17 Uhr' ) ); ?>"></label>
					<?php bb_anfrage_consent(); ?>
				</fieldset>
			<?php else : ?>
				<fieldset class="bb-step bb-step--single">
					<legend><?php bb_e( 'Partner werden' ); ?></legend>
					<div class="bb-row">
						<label class="bb-field"><span><?php bb_e( 'Firma' ); ?> *</span><input type="text" name="firma" required maxlength="120" autocomplete="organization"></label>
						<label class="bb-field"><span><?php bb_e( 'Ansprechpartner' ); ?> *</span><input type="text" name="name" required maxlength="120" autocomplete="name"></label>
					</div>
					<div class="bb-field"><span><?php bb_e( 'Gewerke' ); ?> *</span>
						<div class="bb-options">
							<?php foreach ( bb_gewerke() as $slug => $g ) : ?>
								<?php
								if ( in_array( $slug, array( 'bad', 'barriere' ), true ) ) {
									continue;
								}
								?>
								<label class="bb-option"><input type="checkbox" name="gewerke[]" value="<?php echo esc_attr( $slug ); ?>"><span><?php echo esc_html( bb_t( $g[0] ) ); ?></span></label>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="bb-row">
						<label class="bb-field"><span><?php bb_e( 'Einsatzgebiet (PLZ/Region)' ); ?> *</span><input type="text" name="ort" required maxlength="120"></label>
						<label class="bb-field"><span><?php bb_e( 'Eintrag in der Handwerksrolle?' ); ?></span>
							<select name="umfang"><option value="Ja"><?php bb_e( 'Ja' ); ?></option><option value="Nein / zulassungsfrei"><?php bb_e( 'Nein / zulassungsfreies Gewerk' ); ?></option></select></label>
					</div>
					<div class="bb-row">
						<label class="bb-field"><span><?php bb_e( 'Telefon' ); ?> *</span><input type="tel" name="telefon" required maxlength="40" autocomplete="tel"></label>
						<label class="bb-field"><span><?php bb_e( 'E-Mail' ); ?> *</span><input type="email" name="email" required maxlength="120" autocomplete="email"></label>
					</div>
					<label class="bb-field"><span><?php bb_e( 'Nachricht (optional)' ); ?></span><textarea name="beschreibung" rows="3" maxlength="2000"></textarea></label>
					<?php bb_anfrage_consent(); ?>
				</fieldset>
			<?php endif; ?>

			<div class="bb-form__nav">
				<button type="button" class="bb-btn bb-btn--ghost" data-bb-prev hidden><?php bb_e( 'Zurück' ); ?></button>
				<button type="button" class="bb-btn bb-btn--primary" data-bb-next hidden><?php bb_e( 'Weiter' ); ?></button>
				<button type="submit" class="bb-btn bb-btn--primary" data-bb-submit><?php echo esc_html( bb_t( 'partner' === $typ ? 'Anfrage senden' : 'Kostenlos anfragen' ) ); ?></button>
			</div>
			<p class="bb-form__trust"><?php echo esc_html( bb_t( 'partner' === $typ ? 'Wir melden uns persönlich bei Ihnen.' : 'Kostenlos und unverbindlich. Wir melden uns' ) . ( 'partner' === $typ ? '' : ' ' . bb_opt( 'antwort' ) . '.' ) ); ?></p>
		</form>
		<?php
		echo '</div>';
		return ob_get_clean();
	}
);

function bb_anfrage_consent(): void {
	$privacy = home_url( '/datenschutz/' );
	echo '<label class="bb-consent"><input type="checkbox" name="datenschutz" value="1" required><span>';
	echo wp_kses(
		sprintf(
			/* translators: %s: link */
			bb_t( 'Ich bin einverstanden, dass meine Angaben zur Bearbeitung der Anfrage gespeichert und – soweit nötig – an einen passenden Fachbetrieb weitergegeben werden. Details in der %s.' ),
			'<a href="' . esc_url( $privacy ) . '" target="_blank">' . esc_html( bb_t( 'Datenschutzerklärung' ) ) . '</a>'
		),
		array( 'a' => array( 'href' => array(), 'target' => array() ) )
	);
	echo ' *</span></label>';
}

/**
 * Absenden verarbeiten.
 */
add_action( 'admin_post_nopriv_bb_anfrage', 'bb_anfrage_handle' );
add_action( 'admin_post_bb_anfrage', 'bb_anfrage_handle' );

function bb_anfrage_handle(): void {
	$back = isset( $_POST['quelle'] ) ? esc_url_raw( wp_unslash( $_POST['quelle'] ) ) : home_url( '/' );
	if ( ! wp_validate_redirect( $back, false ) ) {
		$back = home_url( '/' );
	}
	$fail = fn() => wp_safe_redirect( add_query_arg( 'anfrage', 'fehler', $back ) . '#anfrage' ) && exit;

	if ( ! isset( $_POST['bb_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bb_nonce'] ) ), 'bb_anfrage' ) ) {
		$fail();
	}
	// Spamschutz: Honeypot + Mindestzeit 3 Sekunden.
	$t = isset( $_POST['t'] ) ? (int) $_POST['t'] : 0;
	if ( ! empty( $_POST['website'] ) || ( time() - $t ) < 3 ) {
		wp_safe_redirect( add_query_arg( 'anfrage', 'danke', $back ) . '#anfrage' );
		exit;
	}
	// Einfaches Limit: max. 5 Anfragen pro IP und Stunde.
	$ip_key = 'bb_rl_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 5 ) {
		$fail();
	}
	set_transient( $ip_key, $count + 1, HOUR_IN_SECONDS );

	$typ  = ( isset( $_POST['typ'] ) && 'partner' === $_POST['typ'] ) ? 'partner' : 'kunde';
	$text = function ( string $key, int $max = 200 ): string {
		return isset( $_POST[ $key ] ) ? mb_substr( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ), 0, $max ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	};
	$data = array(
		'typ'          => $typ,
		'name'         => $text( 'name', 120 ),
		'firma'        => $text( 'firma', 120 ),
		'telefon'      => $text( 'telefon', 40 ),
		'email'        => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
		'plz'          => preg_replace( '/\D/', '', $text( 'plz', 5 ) ),
		'ort'          => $text( 'ort', 120 ),
		'umfang'       => $text( 'umfang', 120 ),
		'zeitraum'     => $text( 'zeitraum', 80 ),
		'rueckruf'     => $text( 'rueckruf', 120 ),
		'beschreibung' => isset( $_POST['beschreibung'] ) ? mb_substr( sanitize_textarea_field( wp_unslash( $_POST['beschreibung'] ) ), 0, 2000 ) : '',
		'quelle'       => $back,
	);
	$valid_g         = array_keys( bb_gewerke() );
	$gewerke         = isset( $_POST['gewerke'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['gewerke'] ) ) : array();
	$data['gewerke'] = array_values( array_intersect( $gewerke, $valid_g ) );

	$ok = $data['name'] && $data['telefon'] && is_email( $data['email'] ) && ! empty( $_POST['datenschutz'] );
	if ( 'kunde' === $typ ) {
		$ok = $ok && 5 === strlen( $data['plz'] );
	} else {
		$ok = $ok && $data['firma'] && $data['ort'];
	}
	if ( ! $ok ) {
		$fail();
	}

	$labels = array_map( fn( $g ) => bb_gewerke()[ $g ][0], $data['gewerke'] );
	$title  = ( 'partner' === $typ ? 'Partner: ' . $data['firma'] : $data['name'] . ' – ' . ( $labels ? implode( ', ', $labels ) : 'Anfrage' ) ) . ( $data['plz'] ? ' (' . $data['plz'] . ')' : '' );

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'bb_anfrage',
			'post_status' => 'private',
			'post_title'  => $title,
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		$fail();
	}
	foreach ( $data as $k => $v ) {
		update_post_meta( $post_id, '_bb_' . $k, $v );
	}
	update_post_meta( $post_id, '_bb_status', 'neu' );
	update_post_meta( $post_id, '_bb_einwilligung', current_time( 'mysql' ) );

	$fotos = 'kunde' === $typ ? bb_anfrage_store_photos( $post_id ) : array();
	update_post_meta( $post_id, '_bb_fotos', $fotos );

	bb_anfrage_mail( $post_id, $data, $labels, count( $fotos ) );

	wp_safe_redirect( add_query_arg( 'anfrage', 'danke', $back ) . '#anfrage' );
	exit;
}

/**
 * Fotos geschützt speichern: uploads/bb-anfragen/<zufall>/, Zugriff nur über Admin.
 */
function bb_anfrage_dir(): string {
	$up  = wp_upload_dir();
	$dir = trailingslashit( $up['basedir'] ) . 'bb-anfragen';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore
		file_put_contents( $dir . '/index.php', "<?php // Stille.\n" ); // phpcs:ignore
	}
	return $dir;
}

function bb_anfrage_store_photos( int $post_id ): array {
	if ( empty( $_FILES['fotos'] ) || ! is_array( $_FILES['fotos']['name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return array();
	}
	$files  = $_FILES['fotos']; // phpcs:ignore
	$saved  = array();
	$folder = bb_anfrage_dir() . '/' . $post_id . '-' . wp_generate_password( 10, false, false );
	$allow  = array( 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'heic' => 'image/heic' );
	$count  = min( count( $files['name'] ), BB_ANFRAGE_MAX_FOTOS );
	for ( $i = 0; $i < $count; $i++ ) {
		if ( UPLOAD_ERR_OK !== $files['error'][ $i ] || $files['size'][ $i ] > BB_ANFRAGE_MAX_BYTES || ! is_uploaded_file( $files['tmp_name'][ $i ] ) ) {
			continue;
		}
		$check = wp_check_filetype_and_ext( $files['tmp_name'][ $i ], $files['name'][ $i ], $allow );
		if ( empty( $check['ext'] ) ) {
			continue;
		}
		if ( ! is_dir( $folder ) ) {
			wp_mkdir_p( $folder );
		}
		$name = 'foto-' . ( $i + 1 ) . '.' . $check['ext'];
		if ( move_uploaded_file( $files['tmp_name'][ $i ], $folder . '/' . $name ) ) {
			$saved[] = str_replace( bb_anfrage_dir() . '/', '', $folder . '/' . $name );
		}
	}
	return $saved;
}

function bb_anfrage_mail( int $post_id, array $data, array $labels, int $fotos ): void {
	$to    = bb_opt( 'email' ) ?: get_option( 'admin_email' );
	$lines = array(
		'Typ'          => 'partner' === $data['typ'] ? 'Partner-Bewerbung' : 'Kundenanfrage',
		'Name'         => $data['name'],
		'Firma'        => $data['firma'],
		'Telefon'      => $data['telefon'],
		'E-Mail'       => $data['email'],
		'Gewerke'      => implode( ', ', $labels ),
		'PLZ / Ort'    => trim( $data['plz'] . ' ' . $data['ort'] ),
		'Größe'        => $data['umfang'],
		'Zeitraum'     => $data['zeitraum'],
		'Rückruf'      => $data['rueckruf'],
		'Beschreibung' => $data['beschreibung'],
		'Fotos'        => $fotos ? $fotos . ' (im Adminbereich ansehen)' : '',
		'Seite'        => $data['quelle'],
	);
	$body = '';
	foreach ( $lines as $k => $v ) {
		if ( '' !== trim( (string) $v ) ) {
			$body .= $k . ': ' . $v . "\n";
		}
	}
	$body .= "\nIm Adminbereich: " . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . "\n";
	wp_mail( $to, '[Neue Anfrage] ' . get_the_title( $post_id ), $body, array( 'Reply-To: ' . $data['name'] . ' <' . $data['email'] . '>' ) );

	if ( 'kunde' === $data['typ'] ) {
		$msg  = 'Guten Tag ' . $data['name'] . ",\n\n";
		$msg .= 'vielen Dank für Ihre Anfrage bei ' . bb_opt( 'firma' ) . '. Wir melden uns ' . bb_opt( 'antwort' ) . " bei Ihnen.\n\n";
		$msg .= 'Ihre Angaben: ' . ( $labels ? implode( ', ', $labels ) : 'Anfrage' ) . ( $data['plz'] ? ', PLZ ' . $data['plz'] : '' ) . "\n\n";
		$msg .= "Die Vermittlung ist für Sie kostenlos. Den Auftrag erteilen Sie direkt dem Fachbetrieb.\n\nFreundliche Grüße\n" . bb_opt( 'firma' ) . "\n";
		wp_mail( $data['email'], 'Ihre Anfrage bei ' . bb_opt( 'firma' ), $msg );
	}
}

/**
 * Admin: Spalten, Status-Box, Fotos ansehen.
 */
add_filter(
	'manage_bb_anfrage_posts_columns',
	fn() => array(
		'cb'        => '<input type="checkbox">',
		'title'     => 'Anfrage',
		'bb_status' => 'Status',
		'bb_tel'    => 'Telefon',
		'bb_partner' => 'Partner',
		'date'      => 'Eingang',
	)
);

add_action(
	'manage_bb_anfrage_posts_custom_column',
	function ( $col, $post_id ) {
		if ( 'bb_status' === $col ) {
			$s = get_post_meta( $post_id, '_bb_status', true ) ?: 'neu';
			echo '<span class="bb-badge bb-badge--' . esc_attr( $s ) . '">' . esc_html( bb_anfrage_status()[ $s ] ?? $s ) . '</span>';
		} elseif ( 'bb_tel' === $col ) {
			$tel = get_post_meta( $post_id, '_bb_telefon', true );
			echo $tel ? '<a href="' . esc_attr( bb_tel_href( $tel ) ) . '">' . esc_html( $tel ) . '</a>' : '–';
		} elseif ( 'bb_partner' === $col ) {
			echo esc_html( get_post_meta( $post_id, '_bb_partner', true ) ?: '–' );
		}
	},
	10,
	2
);

add_action(
	'add_meta_boxes_bb_anfrage',
	function () {
		add_meta_box( 'bb_anfrage_daten', 'Angaben', 'bb_anfrage_box_daten', 'bb_anfrage', 'normal', 'high' );
		add_meta_box( 'bb_anfrage_status', 'Status & Vermittlung', 'bb_anfrage_box_status', 'bb_anfrage', 'side', 'high' );
	}
);

function bb_anfrage_box_daten( WP_Post $post ): void {
	$keys = array( 'typ' => 'Typ', 'name' => 'Name', 'firma' => 'Firma', 'telefon' => 'Telefon', 'email' => 'E-Mail', 'plz' => 'PLZ', 'ort' => 'Ort / Region', 'umfang' => 'Größe / Handwerksrolle', 'zeitraum' => 'Zeitraum', 'rueckruf' => 'Rückruf', 'beschreibung' => 'Beschreibung', 'quelle' => 'Seite', 'einwilligung' => 'Einwilligung am' );
	echo '<table class="widefat striped"><tbody>';
	$g = get_post_meta( $post->ID, '_bb_gewerke', true );
	echo '<tr><th>Gewerke</th><td>' . esc_html( implode( ', ', array_map( fn( $s ) => bb_gewerke()[ $s ][0] ?? $s, (array) $g ) ) ) . '</td></tr>';
	foreach ( $keys as $k => $label ) {
		$v = get_post_meta( $post->ID, '_bb_' . $k, true );
		if ( '' !== (string) $v ) {
			echo '<tr><th style="width:180px">' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( (string) $v ) ) . '</td></tr>';
		}
	}
	echo '</tbody></table>';
	$fotos = (array) get_post_meta( $post->ID, '_bb_fotos', true );
	$fotos = array_filter( $fotos );
	if ( $fotos ) {
		echo '<h4>Fotos</h4><p>';
		foreach ( $fotos as $i => $f ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=bb_foto&post=' . $post->ID . '&i=' . $i ), 'bb_foto' );
			echo '<a href="' . esc_url( $url ) . '" target="_blank"><img src="' . esc_url( $url ) . '" alt="" style="width:140px;height:100px;object-fit:cover;margin:0 8px 8px 0;border-radius:6px"></a>';
		}
		echo '</p>';
	}
}

function bb_anfrage_box_status( WP_Post $post ): void {
	wp_nonce_field( 'bb_anfrage_save', 'bb_anfrage_save' );
	$s = get_post_meta( $post->ID, '_bb_status', true ) ?: 'neu';
	echo '<p><label><strong>Status</strong><br><select name="bb_status" style="width:100%">';
	foreach ( bb_anfrage_status() as $k => $label ) {
		echo '<option value="' . esc_attr( $k ) . '" ' . selected( $s, $k, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select></label></p>';
	$fields = array( 'partner' => 'Übergeben an (Fachbetrieb)', 'uebergeben_am' => 'Übergeben am (Datum)', 'auftragswert' => 'Auftragswert netto (€)', 'provision' => 'Provision (€ oder %)', 'notiz' => 'Interne Notiz' );
	foreach ( $fields as $k => $label ) {
		$type = 'uebergeben_am' === $k ? 'date' : 'text';
		echo '<p><label><strong>' . esc_html( $label ) . '</strong><br><input type="' . esc_attr( $type ) . '" name="bb_' . esc_attr( $k ) . '" value="' . esc_attr( get_post_meta( $post->ID, '_bb_' . $k, true ) ) . '" style="width:100%"></label></p>';
	}
	echo '<p class="description">Partner + Übergabedatum dienen als Nachweis für die Provision.</p>';
}

add_action(
	'save_post_bb_anfrage',
	function ( $post_id ) {
		if ( ! isset( $_POST['bb_anfrage_save'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bb_anfrage_save'] ) ), 'bb_anfrage_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$s = isset( $_POST['bb_status'] ) ? sanitize_key( wp_unslash( $_POST['bb_status'] ) ) : 'neu';
		if ( isset( bb_anfrage_status()[ $s ] ) ) {
			update_post_meta( $post_id, '_bb_status', $s );
		}
		foreach ( array( 'partner', 'uebergeben_am', 'auftragswert', 'provision', 'notiz' ) as $k ) {
			if ( isset( $_POST[ 'bb_' . $k ] ) ) {
				update_post_meta( $post_id, '_bb_' . $k, sanitize_text_field( wp_unslash( $_POST[ 'bb_' . $k ] ) ) );
			}
		}
	}
);

/**
 * Foto für eingeloggte Bearbeiter ausliefern.
 */
add_action(
	'admin_post_bb_foto',
	function () {
		check_admin_referer( 'bb_foto' );
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		$i       = isset( $_GET['i'] ) ? absint( $_GET['i'] ) : 0;
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( 'Kein Zugriff', 403 );
		}
		$fotos = (array) get_post_meta( $post_id, '_bb_fotos', true );
		if ( empty( $fotos[ $i ] ) ) {
			wp_die( 'Nicht gefunden', 404 );
		}
		$path = realpath( bb_anfrage_dir() . '/' . $fotos[ $i ] );
		if ( ! $path || 0 !== strpos( $path, realpath( bb_anfrage_dir() ) ) || ! is_file( $path ) ) {
			wp_die( 'Nicht gefunden', 404 );
		}
		$type = wp_check_filetype( $path );
		header( 'Content-Type: ' . ( $type['type'] ?: 'application/octet-stream' ) );
		header( 'Content-Length: ' . filesize( $path ) );
		header( 'Cache-Control: private, max-age=600' );
		readfile( $path ); // phpcs:ignore
		exit;
	}
);

/**
 * Zähler „Neu“ im Menü.
 */
add_action(
	'admin_menu',
	function () {
		global $menu;
		$new = get_posts(
			array(
				'post_type'   => 'bb_anfrage',
				'post_status' => 'private',
				'numberposts' => 50,
				'fields'      => 'ids',
				'meta_key'    => '_bb_status', // phpcs:ignore
				'meta_value'  => 'neu', // phpcs:ignore
			)
		);
		if ( ! $new ) {
			return;
		}
		foreach ( $menu as $k => $item ) {
			if ( 'edit.php?post_type=bb_anfrage' === $item[2] ) {
				$menu[ $k ][0] .= ' <span class="awaiting-mod">' . count( $new ) . '</span>'; // phpcs:ignore
			}
		}
	},
	99
);

add_action(
	'admin_head',
	function () {
		echo '<style>.bb-badge{display:inline-block;padding:2px 8px;border-radius:99px;background:#eef1f5;font-size:12px}.bb-badge--neu{background:#fde7b0}.bb-badge--uebergeben,.bb-badge--angebot{background:#d6ecf7}.bb-badge--beauftragt,.bb-badge--abgeschlossen{background:#d8f0e3}.bb-badge--provision{background:#1e7f74;color:#fff}.bb-badge--verloren{background:#f3d6d6}</style>';
	}
);
