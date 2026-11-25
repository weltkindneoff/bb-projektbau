<?php
/**
 * Einstellungen: Einstellungen → B&B Website.
 * Fehlende Angaben werden auf der Seite als sichtbarer Hinweis gezeigt, nie erfunden.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bb_settings_fields(): array {
	return array(
		'firma'      => array( 'Firmenname', 'B&B Projektbauvermittlung eGbR' ),
		'vertreten'  => array( 'Vertretungsberechtigte Gesellschafterinnen (für Impressum)', '' ),
		'strasse'    => array( 'Straße und Hausnummer', '' ),
		'ort'        => array( 'PLZ und Ort', '' ),
		'register'   => array( 'Gesellschaftsregister (Registergericht und Nummer, eGbR)', '' ),
		'ustid'      => array( 'USt-IdNr. (falls vorhanden)', '' ),
		'telefon'    => array( 'Telefon (wird als Button gezeigt)', '' ),
		'whatsapp'   => array( 'WhatsApp-Nummer, international ohne + (z. B. 4917612345678)', '' ),
		'email'      => array( 'E-Mail für Kontakt und Anfragen', '' ),
		'antwort'    => array( 'Versprochene Antwortzeit (Text)', 'innerhalb von 24 Stunden (werktags)' ),
	);
}

function bb_opt( string $key ): string {
	$opts   = get_option( 'bb_settings', array() );
	$fields = bb_settings_fields();
	if ( isset( $opts[ $key ] ) && '' !== trim( (string) $opts[ $key ] ) ) {
		return trim( (string) $opts[ $key ] );
	}
	return isset( $fields[ $key ] ) ? $fields[ $key ][1] : '';
}

/**
 * Sichtbare Platzhalter-Plakette für fehlende Pflichtangaben.
 */
function bb_missing( string $label ): string {
	return '<mark class="bb-missing">' . esc_html( 'Angabe fehlt: ' . $label ) . '</mark>';
}

function bb_preview_key(): string {
	$key = get_option( 'bb_preview_key' );
	if ( ! $key ) {
		$key = wp_generate_password( 12, false, false );
		update_option( 'bb_preview_key', $key, false );
	}
	return $key;
}

function bb_is_launched(): bool {
	return (bool) get_option( 'bb_launched', false );
}

add_action(
	'admin_menu',
	function () {
		add_options_page( 'B&B Website', 'B&B Website', 'manage_options', 'bb-settings', 'bb_settings_page' );
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'bb_settings_group',
			'bb_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => function ( $input ) {
					$out = array();
					foreach ( array_keys( bb_settings_fields() ) as $key ) {
						$value       = isset( $input[ $key ] ) ? wp_unslash( $input[ $key ] ) : '';
						$out[ $key ] = 'email' === $key ? sanitize_email( $value ) : sanitize_text_field( $value );
					}
					return $out;
				},
			)
		);
		register_setting(
			'bb_settings_group',
			'bb_launched',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => fn( $v ) => (bool) $v,
			)
		);
	}
);

function bb_settings_page(): void {
	$opts    = get_option( 'bb_settings', array() );
	$preview = add_query_arg( 'vorschau', bb_preview_key(), home_url( '/' ) );
	?>
	<div class="wrap">
		<h1>B&amp;B Website</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'bb_settings_group' ); ?>
			<h2>Firmendaten und Kontakt</h2>
			<p>Leere Pflichtangaben erscheinen auf der Website als gelber Hinweis „Angabe fehlt“.</p>
			<table class="form-table" role="presentation">
				<?php foreach ( bb_settings_fields() as $key => $field ) : ?>
					<tr>
						<th scope="row"><label for="bb_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field[0] ); ?></label></th>
						<td><input class="regular-text" type="<?php echo 'email' === $key ? 'email' : 'text'; ?>" id="bb_<?php echo esc_attr( $key ); ?>" name="bb_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $opts[ $key ] ?? '' ); ?>" placeholder="<?php echo esc_attr( $field[1] ); ?>"></td>
					</tr>
				<?php endforeach; ?>
			</table>
			<h2>Start der Website</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">Website ist online</th>
					<td>
						<label><input type="checkbox" name="bb_launched" value="1" <?php checked( bb_is_launched() ); ?>> Für alle Besucher freigeben (sonst sehen sie „Bald online“)</label>
					</td>
				</tr>
				<tr>
					<th scope="row">Vorschau-Link</th>
					<td><code><?php echo esc_html( $preview ); ?></code><p class="description">Mit diesem Link sehen Kunden die Seite schon vor dem Start (30 Tage gültig im Browser).</p></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
