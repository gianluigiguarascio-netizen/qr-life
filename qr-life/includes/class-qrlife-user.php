<?php
defined( 'ABSPATH' ) || exit;

class QRLife_User {

    public function __construct() {
        add_action( 'wp_ajax_nopriv_qrlife_register', array( $this, 'handle_register' ) );
        add_action( 'wp_ajax_nopriv_qrlife_login',    array( $this, 'handle_login' ) );
        add_action( 'wp_ajax_qrlife_login',            array( $this, 'handle_login' ) );
        add_action( 'wp_ajax_qrlife_logout',           array( $this, 'handle_logout' ) );
    }

    public static function valida_cf( $cf ) {
        $cf = strtoupper( trim( $cf ) );
        return (bool) preg_match( '/^[A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST]{1}[0-9LMNPQRSTUV]{2}[A-Z]{1}[0-9LMNPQRSTUV]{3}[A-Z]{1}$/', $cf );
    }

    public function handle_register() {
        check_ajax_referer( 'qrlife_nonce', 'nonce' );

        $cf      = strtoupper( sanitize_text_field( $_POST['codice_fiscale'] ?? '' ) );
        $nome    = sanitize_text_field( $_POST['nome'] ?? '' );
        $cognome = sanitize_text_field( $_POST['cognome'] ?? '' );
        $email   = sanitize_email( $_POST['email'] ?? '' );
        $pwd     = $_POST['password'] ?? '';
        $pwd2    = $_POST['password2'] ?? '';

        if ( ! $cf || ! $nome || ! $cognome || ! $email || ! $pwd ) {
            wp_send_json_error( 'Tutti i campi sono obbligatori.' );
        }

        if ( ! self::valida_cf( $cf ) ) {
            wp_send_json_error( 'Codice fiscale non valido.' );
        }

        if ( ! is_email( $email ) ) {
            wp_send_json_error( 'Email non valida.' );
        }

        if ( strlen( $pwd ) < 8 ) {
            wp_send_json_error( 'La password deve contenere almeno 8 caratteri.' );
        }

        if ( $pwd !== $pwd2 ) {
            wp_send_json_error( 'Le password non coincidono.' );
        }

        if ( email_exists( $email ) ) {
            wp_send_json_error( 'Email già registrata.' );
        }

        global $wpdb;
        $cf_exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}qrlife_profili WHERE codice_fiscale = %s",
            $cf
        ) );
        if ( $cf_exists ) {
            wp_send_json_error( 'Codice fiscale già registrato.' );
        }

        $user_id = wp_create_user( $email, $pwd, $email );
        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( $user_id->get_error_message() );
        }

        $user = new WP_User( $user_id );
        $user->set_role( 'qrlife_citizen' );
        wp_update_user( array( 'ID' => $user_id, 'first_name' => $nome, 'last_name' => $cognome, 'display_name' => "$nome $cognome" ) );

        $token = wp_generate_password( 32, false );

        $wpdb->insert( "{$wpdb->prefix}qrlife_profili", array(
            'user_id'       => $user_id,
            'codice_fiscale'=> $cf,
            'nome'          => $nome,
            'cognome'       => $cognome,
            'token'         => $token,
        ) );

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        wp_send_json_success( array(
            'redirect' => home_url( '/qrlife-dashboard/' ),
        ) );
    }

    public function handle_login() {
        check_ajax_referer( 'qrlife_nonce', 'nonce' );

        $email = sanitize_email( $_POST['email'] ?? '' );
        $pwd   = $_POST['password'] ?? '';

        if ( ! $email || ! $pwd ) {
            wp_send_json_error( 'Inserisci email e password.' );
        }

        $user = wp_authenticate( $email, $pwd );
        if ( is_wp_error( $user ) ) {
            wp_send_json_error( 'Credenziali errate.' );
        }

        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID );

        wp_send_json_success( array(
            'redirect' => home_url( '/qrlife-dashboard/' ),
        ) );
    }

    public function handle_logout() {
        wp_logout();
        wp_send_json_success( array( 'redirect' => home_url( '/qrlife-login/' ) ) );
    }
}
