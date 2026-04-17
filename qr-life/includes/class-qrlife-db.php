<?php
defined( 'ABSPATH' ) || exit;

class QRLife_DB {

    public static function install() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $sql_profili = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}qrlife_profili (
            id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id       BIGINT UNSIGNED NOT NULL,
            codice_fiscale VARCHAR(16) NOT NULL,
            nome          VARCHAR(100) NOT NULL,
            cognome       VARCHAR(100) NOT NULL,
            data_nascita  DATE NULL,
            telefono      VARCHAR(20) NULL,
            indirizzo     TEXT NULL,
            token         VARCHAR(64) NOT NULL,
            created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY codice_fiscale (codice_fiscale),
            UNIQUE KEY user_id (user_id),
            UNIQUE KEY token (token)
        ) $charset;";

        $sql_patologie = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}qrlife_patologie (
            id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id       BIGINT UNSIGNED NOT NULL,
            nome          VARCHAR(200) NOT NULL,
            descrizione   TEXT NULL,
            data_diagnosi DATE NULL,
            attiva        TINYINT(1) NOT NULL DEFAULT 1,
            created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset;";

        $sql_medicine = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}qrlife_medicine (
            id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id       BIGINT UNSIGNED NOT NULL,
            nome          VARCHAR(200) NOT NULL,
            principio     VARCHAR(200) NULL,
            grammi        DECIMAL(8,3) NULL,
            quantita      DECIMAL(8,2) NULL,
            unita         VARCHAR(20) NULL DEFAULT 'mg',
            frequenza     VARCHAR(100) NULL,
            note          TEXT NULL,
            attivo        TINYINT(1) NOT NULL DEFAULT 1,
            created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_profili );
        dbDelta( $sql_patologie );
        dbDelta( $sql_medicine );

        // Crea le pagine del plugin se non esistono
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

    public static function get_profilo_by_user( $user_id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qrlife_profili WHERE user_id = %d",
            $user_id
        ) );
    }

    public static function get_profilo_by_token( $token ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qrlife_profili WHERE token = %s",
            $token
        ) );
    }

    public static function get_patologie( $user_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qrlife_patologie WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ) );
    }

    public static function get_medicine( $user_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qrlife_medicine WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
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
}
