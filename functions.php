<?php
/**
 * B&B Projektbau – Theme-Bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BB_VERSION', '0.1.0' );
define( 'BB_DIR', get_template_directory() );
define( 'BB_URI', get_template_directory_uri() );

require BB_DIR . '/inc/i18n.php';
require BB_DIR . '/inc/settings.php';
require BB_DIR . '/inc/setup.php';
require BB_DIR . '/inc/components.php';
require BB_DIR . '/inc/anfrage.php';
require BB_DIR . '/inc/legal.php';
require BB_DIR . '/inc/launch.php';
require BB_DIR . '/inc/content.php';
