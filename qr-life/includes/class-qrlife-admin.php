<?php
defined( 'ABSPATH' ) || exit;

class QRLife_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_qrlife_admin_get_cittadino',     array( $this, 'ajax_get_cittadino' ) );
        add_action( 'wp_ajax_qrlife_admin_toggle_patologia',  array( $this, 'ajax_toggle_patologia' ) );
        add_action( 'wp_ajax_qrlife_admin_toggle_medicina',   array( $this, 'ajax_toggle_medicina' ) );
        add_action( 'wp_ajax_qrlife_admin_crea_medico',       array( $this, 'ajax_crea_medico' ) );
        add_action( 'wp_ajax_qrlife_admin_aggiorna_medico',   array( $this, 'ajax_aggiorna_medico' ) );
        add_action( 'wp_ajax_qrlife_admin_toggle_medico',     array( $this, 'ajax_toggle_medico' ) );
        add_action( 'wp_ajax_qrlife_admin_cancella_cittadino', array( $this, 'ajax_cancella_cittadino' ) );
    }

    public function add_menu() {
        add_menu_page(
            'QR Life — Comune di Parenti',
            'QR Life',
            'manage_options',
            'qr-life',
            array( $this, 'page_dashboard' ),
            'dashicons-heart',
            30
        );
        add_submenu_page( 'qr-life', 'Dashboard',   'Dashboard',   'manage_options', 'qr-life',             array( $this, 'page_dashboard' ) );
        add_submenu_page( 'qr-life', 'Cittadini',    'Cittadini',   'manage_options', 'qr-life-cittadini',   array( $this, 'page_cittadini' ) );
        add_submenu_page( 'qr-life', 'Medici',       'Medici',      'manage_options', 'qr-life-medici',     array( $this, 'page_medici' ) );
        add_submenu_page( 'qr-life', 'Report Accessi','Report Accessi','manage_options','qr-life-report',   array( $this, 'page_report' ) );
        // Pagina nascosta dettaglio cittadino
        add_submenu_page( 'qr-life', 'Dettaglio', '', 'manage_options', 'qr-life-cittadino', array( $this, 'page_cittadino_dettaglio' ) );
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

    // ═══════════════════════════════════════════
    // DASHBOARD
    // ═══════════════════════════════════════════
    public function page_dashboard() {
        $stats = QRLife_DB::get_stats();
        $log_recenti = QRLife_DB::get_log_accessi( 10 );
        ?>
        <div class="wrap qrlife-admin-wrap">
            <div class="qrlife-admin-header">
                <div class="qrlife-logo-area">
                    <span class="qrlife-heart">&#10084;</span>
                    <div>
                        <h1>QR Life — Comune di Parenti</h1>
                        <p class="qrlife-subtitle">Pannello di gestione sanitaria</p>
                    </div>
                </div>
            </div>

            <div class="qrlife-stats-bar">
                <div class="qrlife-stat">
                    <span class="qrlife-stat-number"><?php echo $stats->cittadini; ?></span>
                    <span class="qrlife-stat-label">Cittadini</span>
                </div>
                <div class="qrlife-stat">
                    <span class="qrlife-stat-number"><?php echo $stats->medici; ?></span>
                    <span class="qrlife-stat-label">Medici attivi</span>
                </div>
                <div class="qrlife-stat">
                    <span class="qrlife-stat-number"><?php echo $stats->accessi; ?></span>
                    <span class="qrlife-stat-label">Accessi totali</span>
                </div>
                <div class="qrlife-stat">
                    <span class="qrlife-stat-number"><?php echo $stats->accessi_oggi; ?></span>
                    <span class="qrlife-stat-label">Accessi oggi</span>
                </div>
                <div class="qrlife-stat">
                    <span class="qrlife-stat-number"><?php echo $stats->patologie; ?></span>
                    <span class="qrlife-stat-label">Patologie attive</span>
                </div>
                <div class="qrlife-stat">
                    <span class="qrlife-stat-number"><?php echo $stats->farmaci; ?></span>
                    <span class="qrlife-stat-label">Farmaci attivi</span>
                </div>
            </div>

            <div class="qrlife-card">
                <h2>Ultimi accessi ai profili</h2>
                <?php $this->render_log_table( $log_recenti ); ?>
                <p style="margin-top:12px;"><a href="<?php echo admin_url('admin.php?page=qr-life-report'); ?>">Vedi tutti i report &rarr;</a></p>
            </div>
        </div>
        <?php
    }

    // ═══════════════════════════════════════════
    // CITTADINI
    // ═══════════════════════════════════════════
    public function page_cittadini() {
        $profili = QRLife_DB::get_tutti_profili();
        ?>
        <div class="wrap qrlife-admin-wrap">
            <div class="qrlife-admin-header">
                <h1>Cittadini registrati</h1>
                <p class="qrlife-subtitle"><?php echo count($profili); ?> cittadini nel sistema</p>
            </div>

            <div class="qrlife-card">
                <?php if ( empty( $profili ) ) : ?>
                    <p class="qrlife-empty">Nessun cittadino registrato.</p>
                <?php else : ?>
                <div class="qrlife-table-wrap">
                    <table class="wp-list-table widefat fixed striped qrlife-table">
                        <thead>
                            <tr>
                                <th>Cognome</th><th>Nome</th><th>Codice Fiscale</th>
                                <th>Email</th><th>GDPR</th><th>Registrato il</th><th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $profili as $p ) : ?>
                            <tr>
                                <td><strong><?php echo esc_html( $p->cognome ); ?></strong></td>
                                <td><?php echo esc_html( $p->nome ); ?></td>
                                <td><code><?php echo esc_html( $p->codice_fiscale ); ?></code></td>
                                <td><?php echo esc_html( $p->user_email ); ?></td>
                                <td><?php echo $p->consenso_gdpr ? '<span class="qrlife-badge active">Si</span>' : '<span class="qrlife-badge inactive">No</span>'; ?></td>
                                <td><?php echo date_i18n( 'd/m/Y', strtotime( $p->created_at ) ); ?></td>
                                <td>
                                    <a href="<?php echo admin_url( 'admin.php?page=qr-life-cittadino&uid=' . $p->user_id ); ?>"
                                       class="button button-primary button-small">Scheda</a>
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

    // ═══════════════════════════════════════════
    // DETTAGLIO CITTADINO
    // ═══════════════════════════════════════════
    public function page_cittadino_dettaglio() {
        $user_id = intval( $_GET['uid'] ?? 0 );
        if ( ! $user_id ) { echo '<div class="wrap"><p>Cittadino non trovato.</p></div>'; return; }

        $profilo   = QRLife_DB::get_profilo_by_user( $user_id );
        $patologie = QRLife_DB::get_patologie( $user_id );
        $medicine  = QRLife_DB::get_medicine( $user_id );
        $user      = get_userdata( $user_id );
        $log       = QRLife_DB::get_log_by_profilo( $user_id, 20 );

        if ( ! $profilo ) { echo '<div class="wrap"><p>Profilo non trovato.</p></div>'; return; }
        ?>
        <div class="wrap qrlife-admin-wrap">
            <div class="qrlife-admin-header">
                <a href="<?php echo admin_url( 'admin.php?page=qr-life-cittadini' ); ?>" class="qrlife-back-btn">&larr; Torna all'elenco</a>
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
                        <tr><th>Consenso GDPR</th><td><?php echo $profilo->consenso_gdpr ? 'Si (' . date_i18n('d/m/Y H:i', strtotime($profilo->consenso_data)) . ')' : 'No'; ?></td></tr>
                        <tr><th>Registrato il</th><td><?php echo date_i18n( 'd/m/Y H:i', strtotime( $profilo->created_at ) ); ?></td></tr>
                    </table>
                    <div style="margin-top:16px;">
                        <button class="button button-link-delete qrlife-cancella-cittadino" data-uid="<?php echo $user_id; ?>">Cancella cittadino (diritto all'oblio)</button>
                    </div>
                </div>

                <div class="qrlife-card qrlife-qr-card">
                    <h2>QR Code Sanitario</h2>
                    <?php echo QRLife_QR::render_qr( $profilo->token, 180 ); ?>
                    <p class="qrlife-qr-hint">Token: <code><?php echo esc_html( substr($profilo->token, 0, 8) . '...' ); ?></code></p>
                </div>
            </div>

            <div class="qrlife-card">
                <h2>Patologie (<?php echo count( $patologie ); ?>)</h2>
                <?php if ( empty( $patologie ) ) : ?>
                    <p class="qrlife-empty">Nessuna patologia inserita.</p>
                <?php else : ?>
                <table class="wp-list-table widefat fixed striped qrlife-table">
                    <thead><tr><th>Patologia</th><th>Descrizione</th><th>Data diagnosi</th><th>Critica</th><th>Stato</th></tr></thead>
                    <tbody>
                    <?php foreach ( $patologie as $pat ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $pat->nome ); ?></strong></td>
                            <td><?php echo esc_html( $pat->descrizione ?: '—' ); ?></td>
                            <td><?php echo $pat->data_diagnosi ? date_i18n( 'd/m/Y', strtotime( $pat->data_diagnosi ) ) : '—'; ?></td>
                            <td><?php echo $pat->critica ? '<span class="qrlife-badge critical">Critica</span>' : '—'; ?></td>
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
                    <thead><tr><th>Farmaco</th><th>Principio</th><th>Dosaggio</th><th>Frequenza</th><th>Salvavita</th><th>Stato</th></tr></thead>
                    <tbody>
                    <?php foreach ( $medicine as $med ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $med->nome ); ?></strong></td>
                            <td><?php echo esc_html( $med->principio ?: '—' ); ?></td>
                            <td><?php echo $med->grammi ? esc_html( $med->grammi . ' ' . $med->unita ) : '—'; ?></td>
                            <td><?php echo esc_html( $med->frequenza ?: '—' ); ?></td>
                            <td><?php echo $med->salvavita ? '<span class="qrlife-badge critical">Salvavita</span>' : '—'; ?></td>
                            <td><span class="qrlife-badge <?php echo $med->attivo ? 'active' : 'inactive'; ?>"><?php echo $med->attivo ? 'Attivo' : 'Sospeso'; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $log ) ) : ?>
            <div class="qrlife-card">
                <h2>Ultimi accessi al profilo</h2>
                <?php $this->render_log_table( $log ); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    // ═══════════════════════════════════════════
    // MEDICI
    // ═══════════════════════════════════════════
    public function page_medici() {
        $medici = QRLife_DB::get_tutti_medici();
        ?>
        <div class="wrap qrlife-admin-wrap">
            <div class="qrlife-admin-header">
                <h1>Gestione Medici</h1>
                <p class="qrlife-subtitle">Credenziali create e gestite dal Comune di Parenti</p>
            </div>

            <!-- Form nuovo medico -->
            <div class="qrlife-card" id="card-nuovo-medico">
                <h2>Nuovo Medico</h2>
                <div id="qrlife-msg-medico" class="qrlife-msg" style="display:none;"></div>
                <form id="form-nuovo-medico" class="qrlife-form-grid">
                    <div class="qrlife-field">
                        <label>Cognome *</label>
                        <input type="text" name="cognome" required>
                    </div>
                    <div class="qrlife-field">
                        <label>Nome *</label>
                        <input type="text" name="nome" required>
                    </div>
                    <div class="qrlife-field">
                        <label>Codice Medico * <small>(identificativo univoco)</small></label>
                        <input type="text" name="codice_medico" required placeholder="es. MED001" style="text-transform:uppercase;">
                    </div>
                    <div class="qrlife-field">
                        <label>PIN * <small>(min 6 caratteri)</small></label>
                        <input type="text" name="pin" required minlength="6" placeholder="PIN di accesso">
                    </div>
                    <div class="qrlife-field">
                        <label>Specializzazione</label>
                        <input type="text" name="specializzazione" placeholder="es. Medicina Generale">
                    </div>
                    <div class="qrlife-field">
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>
                    <div class="qrlife-field">
                        <label>Telefono</label>
                        <input type="tel" name="telefono">
                    </div>
                    <div class="qrlife-field" style="align-self:end;">
                        <button type="submit" class="button button-primary">Crea Medico</button>
                    </div>
                </form>
            </div>

            <!-- Elenco medici -->
            <div class="qrlife-card">
                <h2>Medici registrati (<?php echo count($medici); ?>)</h2>
                <?php if ( empty( $medici ) ) : ?>
                    <p class="qrlife-empty">Nessun medico registrato.</p>
                <?php else : ?>
                <div class="qrlife-table-wrap">
                    <table class="wp-list-table widefat fixed striped qrlife-table">
                        <thead>
                            <tr>
                                <th>Cognome</th><th>Nome</th><th>Codice</th>
                                <th>Specializzazione</th><th>Email</th><th>Stato</th><th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $medici as $m ) : ?>
                            <tr id="medico-row-<?php echo $m->id; ?>">
                                <td><strong><?php echo esc_html( $m->cognome ); ?></strong></td>
                                <td><?php echo esc_html( $m->nome ); ?></td>
                                <td><code><?php echo esc_html( $m->codice_medico ); ?></code></td>
                                <td><?php echo esc_html( $m->specializzazione ?: '—' ); ?></td>
                                <td><?php echo esc_html( $m->email ?: '—' ); ?></td>
                                <td>
                                    <span class="qrlife-badge <?php echo $m->attivo ? 'active' : 'inactive'; ?>">
                                        <?php echo $m->attivo ? 'Attivo' : 'Disattivato'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="button button-small qrlife-toggle-medico"
                                            data-id="<?php echo $m->id; ?>" data-attivo="<?php echo $m->attivo; ?>">
                                        <?php echo $m->attivo ? 'Disattiva' : 'Riattiva'; ?>
                                    </button>
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

    // ═══════════════════════════════════════════
    // REPORT ACCESSI
    // ═══════════════════════════════════════════
    public function page_report() {
        $page_num = max( 1, intval( $_GET['paged'] ?? 1 ) );
        $per_page = 50;
        $offset   = ( $page_num - 1 ) * $per_page;
        $total    = QRLife_DB::get_log_count();
        $log      = QRLife_DB::get_log_accessi( $per_page, $offset );
        $pages    = ceil( $total / $per_page );
        ?>
        <div class="wrap qrlife-admin-wrap">
            <div class="qrlife-admin-header">
                <h1>Report Accessi</h1>
                <p class="qrlife-subtitle"><?php echo $total; ?> accessi registrati — Pagina <?php echo $page_num; ?> di <?php echo max(1, $pages); ?></p>
            </div>

            <div class="qrlife-card">
                <?php $this->render_log_table( $log ); ?>

                <?php if ( $pages > 1 ) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php for ( $i = 1; $i <= $pages; $i++ ) : ?>
                            <?php if ( $i == $page_num ) : ?>
                                <span class="tablenav-pages-navspan button disabled"><?php echo $i; ?></span>
                            <?php else : ?>
                                <a class="button" href="<?php echo admin_url('admin.php?page=qr-life-report&paged=' . $i); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    // ═══════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════
    private function render_log_table( $log ) {
        if ( empty( $log ) ) {
            echo '<p class="qrlife-empty">Nessun accesso registrato.</p>';
            return;
        }
        ?>
        <div class="qrlife-table-wrap">
            <table class="wp-list-table widefat fixed striped qrlife-table">
                <thead>
                    <tr>
                        <th>Data/Ora</th><th>Cittadino</th><th>Tipo</th>
                        <th>Medico</th><th>Dati mostrati</th><th>IP</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $log as $l ) : ?>
                    <tr>
                        <td><?php echo date_i18n( 'd/m/Y H:i:s', strtotime( $l->created_at ) ); ?></td>
                        <td>
                            <?php if ( ! empty( $l->citt_cognome ) ) : ?>
                                <?php echo esc_html( $l->citt_cognome . ' ' . $l->citt_nome ); ?>
                            <?php elseif ( ! empty( $l->cognome ) ) : ?>
                                <?php echo esc_html( $l->cognome . ' ' . $l->nome ); ?>
                            <?php else : ?>
                                <em>Rimosso</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="qrlife-badge <?php echo $l->tipo_accesso === 'medico' ? 'active' : ($l->tipo_accesso === 'emergenza' ? 'critical' : 'inactive'); ?>">
                                <?php echo esc_html( ucfirst( $l->tipo_accesso ) ); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ( $l->medico_id && ! empty( $l->med_cognome ) ) : ?>
                                <?php echo esc_html( $l->med_cognome . ' ' . $l->med_nome ); ?>
                                <br><small><code><?php echo esc_html( $l->codice_medico ); ?></code></small>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( ucfirst( $l->dati_mostrati ) ); ?></td>
                        <td><small><?php echo esc_html( $l->ip_address ); ?></small></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // ═══════════════════════════════════════════
    // AJAX HANDLERS
    // ═══════════════════════════════════════════
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
        $id     = intval( $_POST['id'] );
        $attiva = intval( $_POST['attiva'] );
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

    public function ajax_crea_medico() {
        check_ajax_referer( 'qrlife_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Non autorizzato.' );

        $result = QRLife_Medici::crea_medico( $_POST );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }
        wp_send_json_success( array( 'id' => $result ) );
    }

    public function ajax_aggiorna_medico() {
        check_ajax_referer( 'qrlife_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Non autorizzato.' );

        $result = QRLife_Medici::aggiorna_medico( intval( $_POST['id'] ), $_POST );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }
        wp_send_json_success();
    }

    public function ajax_toggle_medico() {
        check_ajax_referer( 'qrlife_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        global $wpdb;
        $id     = intval( $_POST['id'] );
        $attivo = intval( $_POST['attivo'] );
        $wpdb->update( "{$wpdb->prefix}qrlife_medici", array( 'attivo' => $attivo ? 0 : 1 ), array( 'id' => $id ) );
        wp_send_json_success();
    }

    public function ajax_cancella_cittadino() {
        check_ajax_referer( 'qrlife_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Non autorizzato.' );
        $user_id = intval( $_POST['user_id'] ?? 0 );
        if ( ! $user_id ) wp_send_json_error( 'ID non valido.' );
        QRLife_DB::cancella_cittadino( $user_id );
        wp_send_json_success();
    }
}
