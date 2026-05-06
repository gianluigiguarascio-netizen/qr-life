<?php
defined( 'ABSPATH' ) || exit;

class QRLife_DB {

    public static function install() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $sql_profili = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}qrlife_profili (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id         BIGINT UNSIGNED NOT NULL,
            codice_fiscale  VARCHAR(16) NOT NULL,
            nome            VARCHAR(100) NOT NULL,
            cognome         VARCHAR(100) NOT NULL,
            data_nascita    DATE NULL,
            telefono        VARCHAR(20) NULL,
            indirizzo       TEXT NULL,
            token           VARCHAR(64) NOT NULL,
            consenso_gdpr   TINYINT(1) NOT NULL DEFAULT 0,
            consenso_data   DATETIME NULL,
            created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY codice_fiscale (codice_fiscale),
            UNIQUE KEY user_id (user_id),
            UNIQUE KEY token (token)
        ) $charset;";

        $sql_patologie = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}qrlife_patologie (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id         BIGINT UNSIGNED NOT NULL,
            nome            VARCHAR(200) NOT NULL,
            descrizione     TEXT NULL,
            data_diagnosi   DATE NULL,
            attiva          TINYINT(1) NOT NULL DEFAULT 1,
            critica         TINYINT(1) NOT NULL DEFAULT 0,
            created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset;";

        $sql_medicine = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}qrlife_medicine (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id         BIGINT UNSIGNED NOT NULL,
            nome            VARCHAR(200) NOT NULL,
            principio       VARCHAR(200) NULL,
            grammi          DECIMAL(8,3) NULL,
            quantita        DECIMAL(8,2) NULL,
            unita           VARCHAR(20) NULL DEFAULT 'mg',
            frequenza       VARCHAR(100) NULL,
            note            TEXT NULL,
            attivo          TINYINT(1) NOT NULL DEFAULT 1,
            salvavita       TINYINT(1) NOT NULL DEFAULT 0,
            created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset;";

        $sql_medici = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}qrlife_medici (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            codice_medico   VARCHAR(32) NOT NULL,
            pin             VARCHAR(255) NOT NULL,
            nome            VARCHAR(100) NOT NULL,
            cognome         VARCHAR(100) NOT NULL,
            specializzazione VARCHAR(200) NULL,
            telefono        VARCHAR(20) NULL,
            email           VARCHAR(200) NULL,
            attivo          TINYINT(1) NOT NULL DEFAULT 1,
            created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY codice_medico (codice_medico)
        ) $charset;";

        $sql_log = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}qrlife_log_accessi (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            profilo_user_id BIGINT UNSIGNED NOT NULL,
            tipo_accesso    VARCHAR(20) NOT NULL,
            medico_id       BIGINT UNSIGNED NULL,
            ip_address      VARCHAR(45) NULL,
            user_agent      VARCHAR(500) NULL,
            dati_mostrati   VARCHAR(20) NOT NULL DEFAULT 'vitali',
            created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY profilo_user_id (profilo_user_id),
            KEY medico_id (medico_id),
            KEY created_at (created_at)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_profili );
        dbDelta( $sql_patologie );
        dbDelta( $sql_medicine );
        dbDelta( $sql_medici );
        dbDelta( $sql_log );

        self::crea_pagine();
    }

    private static function crea_pagine() {
        $pagine = array(
            'qrlife-registrazione' => array(
                'title'   => 'Registrazione QR Life',
                'content' => '[qrlife_registrazione]',
            ),
            'qrlife-login' => array(
                'title'   => 'Accesso QR Life',
                'content' => '[qrlife_login]',
            ),
            'qrlife-dashboard' => array(
                'title'   => 'La Mia Salute',
                'content' => '[qrlife_dashboard]',
            ),
            'qrlife-profilo' => array(
                'title'   => 'Profilo Sanitario',
                'content' => '[qrlife_profilo]',
            ),
        );

        foreach ( $pagine as $slug => $data ) {
            $exists = get_page_by_path( $slug );
            if ( ! $exists ) {
                wp_insert_post( array(
                    'post_title'   => $data['title'],
                    'post_content' => $data['content'],
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_name'    => $slug,
                ) );
            }
        }
    }

    public static function deactivate() {}

    // ── Profili ──
    public static function get_profilo_by_user( $user_id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qrlife_profili WHERE user_id = %d", $user_id
        ) );
    }

    public static function get_profilo_by_token( $token ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qrlife_profili WHERE token = %s", $token
        ) );
    }

    public static function get_tutti_profili() {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT p.*, u.user_email
             FROM {$wpdb->prefix}qrlife_profili p
             LEFT JOIN {$wpdb->users} u ON u.ID = p.user_id
             ORDER BY p.cognome, p.nome"
        );
    }

    // ── Patologie ──
    public static function get_patologie( $user_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qrlife_patologie WHERE user_id = %d ORDER BY critica DESC, created_at DESC",
            $user_id
        ) );
    }

    public static function get_patologie_critiche( $user_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qrlife_patologie WHERE user_id = %d AND attiva = 1 AND critica = 1 ORDER BY created_at DESC",
            $user_id
        ) );
    }

    // ── Medicine ──
    public static function get_medicine( $user_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qrlife_medicine WHERE user_id = %d ORDER BY salvavita DESC, created_at DESC",
            $user_id
        ) );
    }

    public static function get_medicine_salvavita( $user_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qrlife_medicine WHERE user_id = %d AND attivo = 1 AND salvavita = 1 ORDER BY created_at DESC",
            $user_id
        ) );
    }

    // ── Medici ──
    public static function get_medico_by_codice( $codice ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qrlife_medici WHERE codice_medico = %s AND attivo = 1",
            $codice
        ) );
    }

    public static function get_medico( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qrlife_medici WHERE id = %d", $id
        ) );
    }

    public static function get_tutti_medici() {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}qrlife_medici ORDER BY cognome, nome"
        );
    }

    // ── Log accessi ──
    public static function registra_accesso( $profilo_user_id, $tipo, $medico_id = null, $dati_mostrati = 'vitali' ) {
        global $wpdb;
        $wpdb->insert( "{$wpdb->prefix}qrlife_log_accessi", array(
            'profilo_user_id' => $profilo_user_id,
            'tipo_accesso'    => $tipo,
            'medico_id'       => $medico_id,
            'ip_address'      => self::get_ip(),
            'user_agent'      => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ), 0, 500 ) : '',
            'dati_mostrati'   => $dati_mostrati,
        ) );
        return $wpdb->insert_id;
    }

    public static function get_log_accessi( $limit = 100, $offset = 0 ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT l.*, p.nome AS citt_nome, p.cognome AS citt_cognome, p.codice_fiscale,
                    m.nome AS med_nome, m.cognome AS med_cognome, m.codice_medico
             FROM {$wpdb->prefix}qrlife_log_accessi l
             LEFT JOIN {$wpdb->prefix}qrlife_profili p ON p.user_id = l.profilo_user_id
             LEFT JOIN {$wpdb->prefix}qrlife_medici m ON m.id = l.medico_id
             ORDER BY l.created_at DESC
             LIMIT %d OFFSET %d",
            $limit, $offset
        ) );
    }

    public static function get_log_count() {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}qrlife_log_accessi" );
    }

    public static function get_log_by_profilo( $user_id, $limit = 50 ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT l.*, m.nome AS med_nome, m.cognome AS med_cognome, m.codice_medico
             FROM {$wpdb->prefix}qrlife_log_accessi l
             LEFT JOIN {$wpdb->prefix}qrlife_medici m ON m.id = l.medico_id
             WHERE l.profilo_user_id = %d
             ORDER BY l.created_at DESC
             LIMIT %d",
            $user_id, $limit
        ) );
    }

    // ── Stats per admin ──
    public static function get_stats() {
        global $wpdb;
        $stats = new stdClass();
        $stats->cittadini   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}qrlife_profili" );
        $stats->medici      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}qrlife_medici WHERE attivo = 1" );
        $stats->accessi     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}qrlife_log_accessi" );
        $stats->accessi_oggi = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}qrlife_log_accessi WHERE DATE(created_at) = CURDATE()"
        );
        $stats->patologie   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}qrlife_patologie WHERE attiva = 1" );
        $stats->farmaci     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}qrlife_medicine WHERE attivo = 1" );
        return $stats;
    }

    private static function get_ip() {
        $keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
        foreach ( $keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = explode( ',', sanitize_text_field( $_SERVER[ $key ] ) );
                return trim( $ip[0] );
            }
        }
        return '0.0.0.0';
    }

    // ── Cancellazione account (diritto all'oblio) ──
    public static function cancella_cittadino( $user_id ) {
        global $wpdb;
        $wpdb->delete( "{$wpdb->prefix}qrlife_patologie", array( 'user_id' => $user_id ) );
        $wpdb->delete( "{$wpdb->prefix}qrlife_medicine",  array( 'user_id' => $user_id ) );
        // Anonimizza log (non cancellare per audit)
        $wpdb->update(
            "{$wpdb->prefix}qrlife_log_accessi",
            array( 'ip_address' => '0.0.0.0', 'user_agent' => '' ),
            array( 'profilo_user_id' => $user_id )
        );
        $wpdb->delete( "{$wpdb->prefix}qrlife_profili", array( 'user_id' => $user_id ) );
        // Rimuovi utente WordPress
        require_once ABSPATH . 'wp-admin/includes/user.php';
        wp_delete_user( $user_id );
    }
}
