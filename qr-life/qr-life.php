<?php
/**
 * Plugin Name: QR Life
 * Plugin URI:  https://qrlife.it
 * Description: Piattaforma sanitaria personale con QR Code per i cittadini.
 * Version:     1.0.0
 * Author:      QR Life
 * Text Domain: qr-life
 */

defined( 'ABSPATH' ) || exit;

define( 'QRLIFE_VERSION', '1.0.0' );
define( 'QRLIFE_PATH', plugin_dir_path( __FILE__ ) );
define( 'QRLIFE_URL', plugin_dir_url( __FILE__ ) );

require_once QRLIFE_PATH . 'includes/class-qrlife-db.php';
require_once QRLIFE_PATH . 'includes/class-qrlife-user.php';
require_once QRLIFE_PATH . 'includes/class-qrlife-health.php';
require_once QRLIFE_PATH . 'includes/class-qrlife-qr.php';
require_once QRLIFE_PATH . 'includes/class-qrlife-admin.php';
require_once QRLIFE_PATH . 'includes/class-qrlife-frontend.php';

register_activation_hook( __FILE__, array( 'QRLife_DB', 'install' ) );
register_deactivation_hook( __FILE__, array( 'QRLife_DB', 'deactivate' ) );

function qrlife_init() {
    new QRLife_User();
    new QRLife_Health();
    new QRLife_Admin();
    new QRLife_Frontend();
}
add_action( 'plugins_loaded', 'qrlife_init' );

// Aggiunge il ruolo cittadino all'attivazione
function qrlife_add_roles() {
    add_role( 'qrlife_citizen', 'Cittadino QR Life', array(
        'read' => true,
    ) );
}
register_activation_hook( __FILE__, 'qrlife_add_roles' );

function qrlife_remove_roles() {
    remove_role( 'qrlife_citizen' );
}
register_deactivation_hook( __FILE__, 'qrlife_remove_roles' );

// Shortcode pagine
add_shortcode( 'qrlife_registrazione', array( 'QRLife_Frontend', 'render_registrazione' ) );
add_shortcode( 'qrlife_login',         array( 'QRLife_Frontend', 'render_login' ) );
add_shortcode( 'qrlife_dashboard',     array( 'QRLife_Frontend', 'render_dashboard' ) );
add_shortcode( 'qrlife_profilo',       array( 'QRLife_Frontend', 'render_profilo_pubblico' ) );
