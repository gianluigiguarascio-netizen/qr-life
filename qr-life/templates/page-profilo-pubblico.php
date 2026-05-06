<?php defined( 'ABSPATH' ) || exit;
/** @var object $profilo
 *  @var array  $patologie
 *  @var array  $medicine
 */

// Dati vitali (solo patologie critiche + farmaci salvavita)
$pat_critiche    = array_filter( $patologie, fn($p) => $p->attiva && $p->critica );
$med_salvavita   = array_filter( $medicine,  fn($m) => $m->attivo && $m->salvavita );
$has_vitali      = ! empty( $pat_critiche ) || ! empty( $med_salvavita );

// Registra accesso emergenza (scansione QR)
QRLife_DB::registra_accesso( $profilo->user_id, 'emergenza', null, 'vitali' );
?>

<div class="qrlife-wrap qrlife-profilo-pubblico">

    <!-- Banner Comune di Parenti -->
    <div class="qrlife-comune-banner">
        <strong>Comune di Parenti</strong> — Provincia di Cosenza
    </div>

    <!-- Banner emergenza -->
    <div class="qrlife-emergency-banner">
        <span>&#9888;</span> Profilo d'emergenza sanitaria — Dati vitali
    </div>

    <!-- Dati anagrafici minimi -->
    <div class="qrlife-card">
        <div class="qrlife-profilo-header">
            <span class="qrlife-icon-lg">&#10084;</span>
            <div>
                <h2><?php echo esc_html( strtoupper( $profilo->cognome ) . ' ' . $profilo->nome ); ?></h2>
                <p class="qrlife-cf">CF: <code><?php echo esc_html( $profilo->codice_fiscale ); ?></code></p>
                <?php if ( $profilo->data_nascita ) : ?>
                    <p>Nato/a il: <?php echo date_i18n( 'd/m/Y', strtotime( $profilo->data_nascita ) ); ?></p>
                <?php endif; ?>
                <?php if ( $profilo->telefono ) : ?>
                    <p>Tel: <a href="tel:<?php echo esc_attr( $profilo->telefono ); ?>"><?php echo esc_html( $profilo->telefono ); ?></a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SOLO DATI VITALI -->
    <?php if ( $has_vitali ) : ?>

        <?php if ( ! empty( $pat_critiche ) ) : ?>
        <div class="qrlife-card qrlife-card-critical">
            <h3>&#9888; Patologie critiche</h3>
            <ul class="qrlife-pub-list">
            <?php foreach ( $pat_critiche as $pat ) : ?>
                <li>
                    <strong><?php echo esc_html( $pat->nome ); ?></strong>
                    <?php if ( $pat->descrizione ) : ?>
                        — <?php echo esc_html( $pat->descrizione ); ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if ( ! empty( $med_salvavita ) ) : ?>
        <div class="qrlife-card qrlife-card-critical">
            <h3>&#128138; Farmaci salvavita</h3>
            <ul class="qrlife-pub-list">
            <?php foreach ( $med_salvavita as $med ) : ?>
                <li>
                    <strong><?php echo esc_html( $med->nome ); ?></strong>
                    <?php if ( $med->grammi ) : ?>
                        <span class="qrlife-tag"><?php echo esc_html( $med->grammi . ' ' . $med->unita ); ?></span>
                    <?php endif; ?>
                    <?php if ( $med->quantita ) : ?>
                        <span class="qrlife-tag">&times; <?php echo esc_html( $med->quantita ); ?></span>
                    <?php endif; ?>
                    <?php if ( $med->frequenza ) : ?>
                        — <?php echo esc_html( $med->frequenza ); ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

    <?php else : ?>
        <div class="qrlife-card">
            <p class="qrlife-empty">Nessun dato vitale d'emergenza inserito dal cittadino.</p>
        </div>
    <?php endif; ?>

    <div class="qrlife-card qrlife-notice-card">
        <p><strong>&#128274; Dati completi protetti.</strong> La scansione QR mostra solo patologie critiche e farmaci salvavita.
        Per accedere alla scheda completa, il medico deve autenticarsi con le credenziali rilasciate dal Comune di Parenti.</p>
    </div>

    <!-- LOGIN MEDICO -->
    <div class="qrlife-card" id="qrlife-medico-login-card">
        <h3>&#129658; Accesso Medico</h3>
        <p class="qrlife-hint">Inserisci le credenziali rilasciate dal Comune di Parenti per visualizzare la scheda completa.</p>
        <div id="qrlife-msg-medico-login" class="qrlife-msg" style="display:none;"></div>
        <form id="qrlife-medico-login-form">
            <input type="hidden" name="token" value="<?php echo esc_attr( $profilo->token ); ?>">
            <div class="qrlife-form-row qrlife-form-row-2">
                <div class="qrlife-field">
                    <label>Codice Medico</label>
                    <input type="text" name="codice_medico" required placeholder="es. MED001" style="text-transform:uppercase;" autocomplete="off">
                </div>
                <div class="qrlife-field">
                    <label>PIN</label>
                    <input type="password" name="pin" required placeholder="PIN di accesso" autocomplete="off">
                </div>
            </div>
            <button type="submit" class="qrlife-btn qrlife-btn-primary">Accedi alla scheda completa</button>
        </form>
    </div>

    <!-- SCHEDA COMPLETA (nascosta, mostrata dopo login medico) -->
    <div id="qrlife-scheda-completa" style="display:none;">
        <div class="qrlife-card qrlife-card-doctor">
            <p>&#9989; <strong>Accesso autorizzato</strong> — Dr. <span id="qrlife-medico-nome"></span></p>
        </div>

        <div class="qrlife-card">
            <h3>Dati personali</h3>
            <table class="qrlife-info-table" id="qrlife-dati-personali"></table>
        </div>

        <div class="qrlife-card">
            <h3>&#9763; Tutte le patologie attive</h3>
            <div id="qrlife-all-patologie"></div>
        </div>

        <div class="qrlife-card">
            <h3>&#128138; Terapia farmacologica completa</h3>
            <div id="qrlife-all-medicine"></div>
        </div>
    </div>

    <p class="qrlife-timestamp">
        Accesso registrato il <?php echo date_i18n( 'd/m/Y H:i' ); ?><br>
        <small>Ogni accesso a questo profilo viene registrato e notificato al cittadino.</small>
    </p>
</div>
