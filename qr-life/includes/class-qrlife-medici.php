<?php
defined( 'ABSPATH' ) || exit;

/**
 * Gestione medici — credenziali create dal Comune.
 * I medici si autenticano con codice_medico + PIN per accedere ai profili QR.
 */
class QRLife_Medici {

    public function __construct() {
        add_action( 'wp_ajax_qrlife_medico_login',        array( $this, 'handle_login' ) );
        add_action( 'wp_ajax_nopriv_qrlife_medico_login', array( $this, 'handle_login' ) );
    }

    /**
     * Login medico via AJAX — chiamato dalla pagina profilo QR.
     */
    public function handle_login() {
        check_ajax_referer( 'qrlife_nonce', 'nonce' );

        $codice = strtoupper( sanitize_text_field( $_POST['codice_medico'] ?? '' ) );
        $pin    = sanitize_text_field( $_POST['pin'] ?? '' );
        $token  = sanitize_text_field( $_POST['token'] ?? '' );

        if ( ! $codice || ! $pin || ! $token ) {
            wp_send_json_error( 'Inserisci codice medico e PIN.' );
        }

        $medico = QRLife_DB::get_medico_by_codice( $codice );
        if ( ! $medico || ! wp_check_password( $pin, $medico->pin ) ) {
            wp_send_json_error( 'Credenziali non valide.' );
        }

        $profilo = QRLife_DB::get_profilo_by_token( $token );
        if ( ! $profilo ) {
            wp_send_json_error( 'Profilo non trovato.' );
        }

        // Registra accesso
        QRLife_DB::registra_accesso( $profilo->user_id, 'medico', $medico->id, 'completo' );

        // Carica dati completi
        $patologie = QRLife_DB::get_patologie( $profilo->user_id );
        $medicine  = QRLife_DB::get_medicine( $profilo->user_id );

        $pat_attive = array_values( array_filter( $patologie, fn($p) => $p->attiva ) );
        $med_attivi = array_values( array_filter( $medicine, fn($m) => $m->attivo ) );

        wp_send_json_success( array(
            'medico'     => $medico->nome . ' ' . $medico->cognome,
            'profilo'    => array(
                'nome'           => $profilo->nome,
                'cognome'        => $profilo->cognome,
                'codice_fiscale' => $profilo->codice_fiscale,
                'data_nascita'   => $profilo->data_nascita,
                'telefono'       => $profilo->telefono,
                'indirizzo'      => $profilo->indirizzo,
            ),
            'patologie'  => $pat_attive,
            'medicine'   => $med_attivi,
        ) );
    }

    /**
     * Crea un medico (usato dall'admin).
     */
    public static function crea_medico( $data ) {
        global $wpdb;

        $codice = strtoupper( sanitize_text_field( $data['codice_medico'] ) );
        $pin    = sanitize_text_field( $data['pin'] );

        if ( strlen( $pin ) < 6 ) {
            return new WP_Error( 'pin_corto', 'Il PIN deve avere almeno 6 caratteri.' );
        }

        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}qrlife_medici WHERE codice_medico = %s", $codice
        ) );
        if ( $existing ) {
            return new WP_Error( 'duplicato', 'Codice medico già esistente.' );
        }

        $wpdb->insert( "{$wpdb->prefix}qrlife_medici", array(
            'codice_medico'    => $codice,
            'pin'              => wp_hash_password( $pin ),
            'nome'             => sanitize_text_field( $data['nome'] ),
            'cognome'          => sanitize_text_field( $data['cognome'] ),
            'specializzazione' => sanitize_text_field( $data['specializzazione'] ?? '' ),
            'telefono'         => sanitize_text_field( $data['telefono'] ?? '' ),
            'email'            => sanitize_email( $data['email'] ?? '' ),
        ) );

        return $wpdb->insert_id;
    }

    /**
     * Aggiorna un medico.
     */
    public static function aggiorna_medico( $id, $data ) {
        global $wpdb;

        $update = array(
            'nome'             => sanitize_text_field( $data['nome'] ),
            'cognome'          => sanitize_text_field( $data['cognome'] ),
            'specializzazione' => sanitize_text_field( $data['specializzazione'] ?? '' ),
            'telefono'         => sanitize_text_field( $data['telefono'] ?? '' ),
            'email'            => sanitize_email( $data['email'] ?? '' ),
            'attivo'           => intval( $data['attivo'] ?? 1 ),
        );

        // Aggiorna PIN solo se fornito
        $pin = sanitize_text_field( $data['pin'] ?? '' );
        if ( $pin ) {
            if ( strlen( $pin ) < 6 ) {
                return new WP_Error( 'pin_corto', 'Il PIN deve avere almeno 6 caratteri.' );
            }
            $update['pin'] = wp_hash_password( $pin );
        }

        $wpdb->update( "{$wpdb->prefix}qrlife_medici", $update, array( 'id' => intval( $id ) ) );
        return true;
    }
}
