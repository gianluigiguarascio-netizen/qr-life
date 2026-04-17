<?php
defined( 'ABSPATH' ) || exit;

class QRLife_Health {

    public function __construct() {
        add_action( 'wp_ajax_qrlife_add_patologia',    array( $this, 'add_patologia' ) );
        add_action( 'wp_ajax_qrlife_delete_patologia', array( $this, 'delete_patologia' ) );
        add_action( 'wp_ajax_qrlife_add_medicina',     array( $this, 'add_medicina' ) );
        add_action( 'wp_ajax_qrlife_delete_medicina',  array( $this, 'delete_medicina' ) );
    }

    private function check_citizen() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Non autorizzato.' );
        }
        $user = wp_get_current_user();
        if ( ! in_array( 'qrlife_citizen', (array) $user->roles ) && ! current_user_can( 'administrator' ) ) {
            wp_send_json_error( 'Accesso non consentito.' );
        }
        return $user->ID;
    }

    public function add_patologia() {
        check_ajax_referer( 'qrlife_nonce', 'nonce' );
        $user_id = $this->check_citizen();

        $nome         = sanitize_text_field( $_POST['nome'] ?? '' );
        $descrizione  = sanitize_textarea_field( $_POST['descrizione'] ?? '' );
        $data_diagnosi = sanitize_text_field( $_POST['data_diagnosi'] ?? '' );

        if ( ! $nome ) {
            wp_send_json_error( 'Il nome della patologia è obbligatorio.' );
        }

        global $wpdb;
        $wpdb->insert( "{$wpdb->prefix}qrlife_patologie", array(
            'user_id'      => $user_id,
            'nome'         => $nome,
            'descrizione'  => $descrizione,
            'data_diagnosi'=> $data_diagnosi ?: null,
        ) );

        wp_send_json_success( array( 'id' => $wpdb->insert_id, 'nome' => $nome, 'descrizione' => $descrizione, 'data_diagnosi' => $data_diagnosi ) );
    }

    public function delete_patologia() {
        check_ajax_referer( 'qrlife_nonce', 'nonce' );
        $user_id = $this->check_citizen();
        $id = intval( $_POST['id'] ?? 0 );

        global $wpdb;
        $wpdb->delete( "{$wpdb->prefix}qrlife_patologie", array( 'id' => $id, 'user_id' => $user_id ) );
        wp_send_json_success();
    }

    public function add_medicina() {
        check_ajax_referer( 'qrlife_nonce', 'nonce' );
        $user_id = $this->check_citizen();

        $nome      = sanitize_text_field( $_POST['nome'] ?? '' );
        $principio = sanitize_text_field( $_POST['principio'] ?? '' );
        $grammi    = floatval( $_POST['grammi'] ?? 0 );
        $quantita  = floatval( $_POST['quantita'] ?? 0 );
        $unita     = sanitize_text_field( $_POST['unita'] ?? 'mg' );
        $frequenza = sanitize_text_field( $_POST['frequenza'] ?? '' );
        $note      = sanitize_textarea_field( $_POST['note'] ?? '' );

        if ( ! $nome ) {
            wp_send_json_error( 'Il nome del farmaco è obbligatorio.' );
        }

        $unita_valide = array( 'mg', 'g', 'ml', 'mcg', 'UI', 'compresse' );
        if ( ! in_array( $unita, $unita_valide ) ) {
            $unita = 'mg';
        }

        global $wpdb;
        $wpdb->insert( "{$wpdb->prefix}qrlife_medicine", array(
            'user_id'  => $user_id,
            'nome'     => $nome,
            'principio'=> $principio,
            'grammi'   => $grammi ?: null,
            'quantita' => $quantita ?: null,
            'unita'    => $unita,
            'frequenza'=> $frequenza,
            'note'     => $note,
        ) );

        wp_send_json_success( array(
            'id'        => $wpdb->insert_id,
            'nome'      => $nome,
            'principio' => $principio,
            'grammi'    => $grammi,
            'quantita'  => $quantita,
            'unita'     => $unita,
            'frequenza' => $frequenza,
            'note'      => $note,
        ) );
    }

    public function delete_medicina() {
        check_ajax_referer( 'qrlife_nonce', 'nonce' );
        $user_id = $this->check_citizen();
        $id = intval( $_POST['id'] ?? 0 );

        global $wpdb;
        $wpdb->delete( "{$wpdb->prefix}qrlife_medicine", array( 'id' => $id, 'user_id' => $user_id ) );
        wp_send_json_success();
    }
}
