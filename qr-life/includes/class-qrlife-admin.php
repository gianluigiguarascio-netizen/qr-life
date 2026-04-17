<?php
defined( 'ABSPATH' ) || exit;

class QRLife_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_qrlife_admin_get_cittadino', array( $this, 'ajax_get_cittadino' ) );
        add_action( 'wp_ajax_qrlife_admin_toggle_patologia', array( $this, 'ajax_toggle_patologia' ) );
        add_action( 'wp_ajax_qrlife_admin_toggle_medicina', array( $this, 'ajax_toggle_medicina' ) );
    }

    public function add_menu() {
        add_menu_page(
            'QR Life',
            'QR Life',
            'manage_options',
            'qr-life',
            array( $this, 'page_overview' ),
            'dashicons-heart',
            30
        );
        add_submenu_page(
            'qr-life',
            'Tutti i Cittadini',
            'Cittadini',
            'manage_options',
            'qr-life',
            array( $this, 'page_overview' )
        );
        add_submenu_page(
            'qr-life',
            'Dettaglio Cittadino',
            '',
            'manage_options',
            'qr-life-cittadino',
            array( $this, 'page_cittadino' )
        );
    }

    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'qr-life' ) === false ) return;
        wp_enqueue_style( 'qrlife-admin', QRLIFE_URL . 'assets/css/qrlife-admin.css', array(), QRLIFE_VERSION );
        wp_enqueue_script( 'qrlife-admin', QRLIFE_URL . 'assets/js/qrlife-admin.js', array( 'jquery' ), QRLIFE_VERSION, true );
        wp_localize_script( 'qrlife-admin', 'qrlifeAdmin', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'qrlife_admin_nonce' ),
        ) );
    }

    public function page_overview() {
        $profili = QRLife_DB::get_tutti_profili();
        ?>
        <div class="wrap qrlife-admin-wrap">
            <div class="qrlife-admin-header">
                <div class="qrlife-logo-area">
                    <span class="qrlife-heart">&#10084;</span>
                    <h1>QR Life — Pannello Amministratore</h1>
                </div>
                <p class="qrlife-subtitle">Gestione cittadini e dati sanitari</p>
            </div>

            <div class="qrlife-stats-bar">
                <div class="qrlife-stat">
                    <span class="qrlife-stat-number"><?php echo count( $profili ); ?></span>
                    <span class="qrlife-stat-label">Cittadini registrati</span>
                </div>
            </div>

            <div class="qrlife-card">
                <h2>Elenco Cittadini</h2>
                <?php if ( empty( $profili ) ) : ?>
                    <p class="qrlife-empty">Nessun cittadino registrato.</p>
                <?php else : ?>
                <div class="qrlife-table-wrap">
                    <table class="wp-list-table widefat fixed striped qrlife-table">
                        <thead>
                            <tr>
                                <th>Cognome</th>
                                <th>Nome</th>
                                <th>Codice Fiscale</th>
                                <th>Email</th>
                                <th>Registrato il</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $profili as $p ) : ?>
                            <tr>
                                <td><strong><?php echo esc_html( $p->cognome ); ?></strong></td>
                                <td><?php echo esc_html( $p->nome ); ?></td>
                                <td><code><?php echo esc_html( $p->codice_fiscale ); ?></code></td>
                                <td><?php echo esc_html( $p->user_email ); ?></td>
                                <td><?php echo date_i18n( 'd/m/Y', strtotime( $p->created_at ) ); ?></td>
                                <td>
                                    <a href="<?php echo admin_url( 'admin.php?page=qr-life-cittadino&uid=' . $p->user_id ); ?>"
                                       class="button button-primary button-small">Visualizza dati</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function page_cittadino() {
        $user_id = intval( $_GET['uid'] ?? 0 );
        if ( ! $user_id ) {
            echo '<div class="wrap"><p>Cittadino non trovato.</p></div>';
            return;
        }

        $profilo    = QRLife_DB::get_profilo_by_user( $user_id );
        $patologie  = QRLife_DB::get_patologie( $user_id );
        $medicine   = QRLife_DB::get_medicine( $user_id );
        $user       = get_userdata( $user_id );

        if ( ! $profilo ) {
            echo '<div class="wrap"><p>Profilo non trovato.</p></div>';
            return;
        }
        ?>
        <div class="wrap qrlife-admin-wrap">
            <div class="qrlife-admin-header">
                <a href="<?php echo admin_url( 'admin.php?page=qr-life' ); ?>" class="qrlife-back-btn">&larr; Torna all'elenco</a>
                <h1><?php echo esc_html( $profilo->cognome . ' ' . $profilo->nome ); ?></h1>
                <p class="qrlife-subtitle">Scheda sanitaria completa</p>
            </div>

            <div class="qrlife-grid-2">
                <div class="qrlife-card">
                    <h2>Dati Personali</h2>
                    <table class="qrlife-info-table">
                        <tr><th>Codice Fiscale</th><td><code><?php echo esc_html( $profilo->codice_fiscale ); ?></code></td></tr>
                        <tr><th>Nome</th><td><?php echo esc_html( $profilo->nome ); ?></td></tr>
                        <tr><th>Cognome</th><td><?php echo esc_html( $profilo->cognome ); ?></td></tr>
                        <tr><th>Email</th><td><?php echo esc_html( $user->user_email ); ?></td></tr>
                        <?php if ( $profilo->data_nascita ) : ?>
                        <tr><th>Data di nascita</th><td><?php echo date_i18n( 'd/m/Y', strtotime( $profilo->data_nascita ) ); ?></td></tr>
                        <?php endif; ?>
                        <?php if ( $profilo->telefono ) : ?>
                        <tr><th>Telefono</th><td><?php echo esc_html( $profilo->telefono ); ?></td></tr>
                        <?php endif; ?>
                        <tr><th>Registrato il</th><td><?php echo date_i18n( 'd/m/Y H:i', strtotime( $profilo->created_at ) ); ?></td></tr>
                    </table>
                </div>

                <div class="qrlife-card qrlife-qr-card">
                    <h2>QR Code Sanitario</h2>
                    <?php echo QRLife_QR::render_qr( $profilo->token, 180 ); ?>
                    <p class="qrlife-qr-hint">Scansiona per accedere al profilo d'emergenza</p>
                </div>
            </div>

            <div class="qrlife-card">
                <h2>Patologie (<?php echo count( $patologie ); ?>)</h2>
                <?php if ( empty( $patologie ) ) : ?>
                    <p class="qrlife-empty">Nessuna patologia inserita.</p>
                <?php else : ?>
                <table class="wp-list-table widefat fixed striped qrlife-table">
                    <thead><tr><th>Patologia</th><th>Descrizione</th><th>Data diagnosi</th><th>Stato</th></tr></thead>
                    <tbody>
                    <?php foreach ( $patologie as $pat ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $pat->nome ); ?></strong></td>
                            <td><?php echo esc_html( $pat->descrizione ?: '—' ); ?></td>
                            <td><?php echo $pat->data_diagnosi ? date_i18n( 'd/m/Y', strtotime( $pat->data_diagnosi ) ) : '—'; ?></td>
                            <td><span class="qrlife-badge <?php echo $pat->attiva ? 'active' : 'inactive'; ?>"><?php echo $pat->attiva ? 'Attiva' : 'Inattiva'; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <div class="qrlife-card">
                <h2>Terapia Farmacologica (<?php echo count( $medicine ); ?>)</h2>
                <?php if ( empty( $medicine ) ) : ?>
                    <p class="qrlife-empty">Nessun farmaco inserito.</p>
                <?php else : ?>
                <table class="wp-list-table widefat fixed striped qrlife-table">
                    <thead><tr><th>Farmaco</th><th>Principio attivo</th><th>Dosaggio</th><th>Quantità</th><th>Frequenza</th><th>Note</th><th>Stato</th></tr></thead>
                    <tbody>
                    <?php foreach ( $medicine as $med ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $med->nome ); ?></strong></td>
                            <td><?php echo esc_html( $med->principio ?: '—' ); ?></td>
                            <td><?php echo $med->grammi ? esc_html( $med->grammi . ' ' . $med->unita ) : '—'; ?></td>
                            <td><?php echo $med->quantita ? esc_html( $med->quantita ) : '—'; ?></td>
                            <td><?php echo esc_html( $med->frequenza ?: '—' ); ?></td>
                            <td><?php echo esc_html( $med->note ?: '—' ); ?></td>
                            <td><span class="qrlife-badge <?php echo $med->attivo ? 'active' : 'inactive'; ?>"><?php echo $med->attivo ? 'Attivo' : 'Sospeso'; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function ajax_get_cittadino() {
        check_ajax_referer( 'qrlife_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        $user_id   = intval( $_POST['user_id'] ?? 0 );
        $patologie = QRLife_DB::get_patologie( $user_id );
        $medicine  = QRLife_DB::get_medicine( $user_id );
        wp_send_json_success( array( 'patologie' => $patologie, 'medicine' => $medicine ) );
    }

    public function ajax_toggle_patologia() {
        check_ajax_referer( 'qrlife_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        global $wpdb;
        $id      = intval( $_POST['id'] );
        $attiva  = intval( $_POST['attiva'] );
        $wpdb->update( "{$wpdb->prefix}qrlife_patologie", array( 'attiva' => $attiva ? 0 : 1 ), array( 'id' => $id ) );
        wp_send_json_success();
    }

    public function ajax_toggle_medicina() {
        check_ajax_referer( 'qrlife_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        global $wpdb;
        $id     = intval( $_POST['id'] );
        $attivo = intval( $_POST['attivo'] );
        $wpdb->update( "{$wpdb->prefix}qrlife_medicine", array( 'attivo' => $attivo ? 0 : 1 ), array( 'id' => $id ) );
        wp_send_json_success();
    }
}
