<?php
/**
 * Title: FAQ
 * Slug: bb-projektbau/faq
 * Categories: bb-projektbau
 * Inserter: no
 */
$faq = array(
	'Was kostet mich die Vermittlung?'          => 'Nichts. Sie zahlen nur die Arbeit des Fachbetriebs laut dessen Angebot. Unsere Vergütung trägt der Handwerksbetrieb.',
	'Mit wem schließe ich den Vertrag?'         => 'Direkt mit dem Fachbetrieb. Er führt die Arbeiten aus, stellt die Rechnung und übernimmt die Gewährleistung.',
	'Wie schnell meldet sich jemand?'           => 'Wir melden uns ' . bb_opt( 'antwort' ) . '. Den Besichtigungstermin stimmen wir dann gemeinsam ab.',
	'Kann ich mehrere Gewerke kombinieren?'     => 'Ja, genau dafür sind wir da. Wir stimmen Termine und Reihenfolge der Betriebe für Sie ab.',
	'Bin ich nach der Anfrage zu etwas verpflichtet?' => 'Nein. Anfrage, Beratung und Angebot sind unverbindlich.',
	'In welchen Regionen sind Sie tätig?'       => 'In ganz Niedersachsen, mit Schwerpunkt auf der Region Braunschweig.',
);
?>
<!-- wp:group {"tagName":"section","className":"bb-section","layout":{"type":"constrained","contentSize":"760px"}} -->
<section class="wp-block-group bb-section">
<!-- wp:paragraph {"className":"bb-eyebrow bb-eyebrow--num"} -->
<p class="bb-eyebrow bb-eyebrow--num">05 — Fragen</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Häufige Fragen</h2>
<!-- /wp:heading -->
<?php foreach ( $faq as $q => $a ) : ?>
<!-- wp:details {"className":"bb-faq"} -->
<details class="wp-block-details bb-faq"><summary><?php echo esc_html( $q ); ?></summary><!-- wp:paragraph -->
<p><?php echo esc_html( $a ); ?></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->
<?php endforeach; ?>
</section>
<!-- /wp:group -->
