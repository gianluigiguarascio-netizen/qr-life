<?php
defined( 'ABSPATH' ) || exit;

class QRLife_Frontend {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_qrlife_update_profilo', array( $this, 'update_profilo' ) );
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'qrlife-style', QRLIFE_URL . 'assets/css/qrlife.css', array(), QRLIFE_VERSION );
        wp_enqueue_script( 'qrlife-script', QRLIFE_URL . 'assets/js/qrlife.js', array( 'jquery' ), QRLIFE_VERSION, true );
        wp_localize_script( 'qrlife-script', 'qrlife', array(
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'qrlife_nonce' ),
            'logged_in' => is_user_logged_in(),
        ) );
    }

    public static function render_registrazione() {
        if ( is_user_logged_in() ) {
            $url = home_url( '/qrlife-dashboard/' );
            return '<p>Sei già registrato. <a href="' . esc_url( $url ) . '">Vai alla tua area personale &rarr;</a></p>';
        }
        ob_start();
        include QRLIFE_PATH . 'templates/page-registrazione.php';
        return ob_get_clean();
    }

    public static function render_login() {
        if ( is_user_logged_in() ) {
            $url = home_url( '/qrlife-dashboard/' );
            return '<p>Sei già connesso. <a href="' . esc_url( $url ) . '">Vai alla tua area personale &rarr;</a></p>';
        }
        ob_start();
        include QRLIFE_PATH . 'templates/page-login.php';
        return ob_get_clean();
    }

    public static function render_dashboard() {
        if ( ! is_user_logged_in() ) {
            $url = home_url( '/qrlife-login/' );
            return '<p>Devi accedere per vedere questa pagina. <a href="' . esc_url( $url ) . '">Accedi &rarr;</a></p>';
        }
        $user    = wp_get_current_user();
        if ( ! in_array( 'qrlife_citizen', (array) $user->roles ) ) {
            return '<p>Accesso non consentito.</p>';
        }
        $profilo   = QRLife_DB::get_profilo_by_user( $user->ID );
        $patologie = QRLife_DB::get_patologie( $user->ID );
        $medicine  = QRLife_DB::get_medicine( $user->ID );
        ob_start();
        include QRLIFE_PATH . 'templates/page-dashboard.php';
        return ob_get_clean();
    }

    public static function render_profilo_pubblico() {
        $token = sanitize_text_field( $_GET['token'] ?? '' );
        if ( ! $token ) return '<p>Profilo non trovato.</p>';

        $profilo = QRLife_DB::get_profilo_by_token( $token );
        if ( ! $profilo ) return '<p>Profilo non trovato.</p>';

        $patologie = QRLife_DB::get_patologie( $profilo->user_id );
        $medicine  = QRLife_DB::get_medicine( $profilo->user_id );
        ob_start();
        include QRLIFE_PATH . 'templates/page-profilo-pubblico.php';
        return ob_get_clean();
    }

    public function update_profilo() {
        check_ajax_referer( 'qrlife_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error( 'Non autorizzato.' );

        $user_id      = get_current_user_id();
        $data_nascita = sanitize_text_field( $_POST['data_nascita'] ?? '' );
        $telefono     = sanitize_text_field( $_POST['telefono'] ?? '' );
        $indirizzo    = sanitize_textarea_field( $_POST['indirizzo'] ?? '' );

        global $wpdb;
        $wpdb->update( "{$wpdb->prefix}qrlife_profili", array(
            'data_nascita' => $data_nascita ?: null,
            'telefono'     => $telefono,
            'indirizzo'    => $indirizzo,
        ), array( 'user_id' => $user_id ) );

        wp_send_json_success();
    }
}
