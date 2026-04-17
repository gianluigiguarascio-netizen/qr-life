<?php defined( 'ABSPATH' ) || exit; ?>
<div class="qrlife-wrap" id="qrlife-register-wrap">
    <div class="qrlife-card qrlife-form-card">
        <div class="qrlife-form-header">
            <span class="qrlife-icon">&#10084;</span>
            <h2>Registrati a QR Life</h2>
            <p>Crea il tuo profilo sanitario personale</p>
        </div>

        <div id="qrlife-msg" class="qrlife-msg" style="display:none;"></div>

        <form id="qrlife-register-form" novalidate>
            <div class="qrlife-form-row qrlife-form-row-2">
                <div class="qrlife-field">
                    <label for="reg-nome">Nome *</label>
                    <input type="text" id="reg-nome" name="nome" placeholder="Mario" required autocomplete="given-name">
                </div>
                <div class="qrlife-field">
                    <label for="reg-cognome">Cognome *</label>
                    <input type="text" id="reg-cognome" name="cognome" placeholder="Rossi" required autocomplete="family-name">
                </div>
            </div>

            <div class="qrlife-field">
                <label for="reg-cf">Codice Fiscale *</label>
                <input type="text" id="reg-cf" name="codice_fiscale" placeholder="RSSMRA80A01H501A"
                       maxlength="16" required autocomplete="off" style="text-transform:uppercase">
                <span class="qrlife-hint">16 caratteri alfanumerici</span>
            </div>

            <div class="qrlife-field">
                <label for="reg-email">Email *</label>
                <input type="email" id="reg-email" name="email" placeholder="mario.rossi@email.it" required autocomplete="email">
            </div>

            <div class="qrlife-form-row qrlife-form-row-2">
                <div class="qrlife-field">
                    <label for="reg-pwd">Password *</label>
                    <input type="password" id="reg-pwd" name="password" placeholder="Min. 8 caratteri" required autocomplete="new-password">
                </div>
                <div class="qrlife-field">
                    <label for="reg-pwd2">Conferma Password *</label>
                    <input type="password" id="reg-pwd2" name="password2" placeholder="Ripeti la password" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="qrlife-btn qrlife-btn-primary qrlife-btn-full">
                <span class="btn-text">Registrati</span>
                <span class="btn-loader" style="display:none;">Registrazione in corso...</span>
            </button>
        </form>

        <p class="qrlife-form-footer">
            Hai già un account? <a href="<?php echo esc_url( home_url( '/qrlife-login/' ) ); ?>">Accedi</a>
        </p>
    </div>
</div>
