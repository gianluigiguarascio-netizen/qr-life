<?php defined( 'ABSPATH' ) || exit; ?>
<div class="qrlife-wrap" id="qrlife-login-wrap">
    <div class="qrlife-card qrlife-form-card">
        <div class="qrlife-form-header">
            <span class="qrlife-icon">&#10084;</span>
            <h2>Accedi a QR Life</h2>
            <p>Inserisci le tue credenziali</p>
        </div>

        <div id="qrlife-msg" class="qrlife-msg" style="display:none;"></div>

        <form id="qrlife-login-form" novalidate>
            <div class="qrlife-field">
                <label for="login-email">Email</label>
                <input type="email" id="login-email" name="email" placeholder="mario.rossi@email.it" required autocomplete="email">
            </div>
            <div class="qrlife-field">
                <label for="login-pwd">Password</label>
                <input type="password" id="login-pwd" name="password" placeholder="La tua password" required autocomplete="current-password">
            </div>

            <button type="submit" class="qrlife-btn qrlife-btn-primary qrlife-btn-full">
                <span class="btn-text">Accedi</span>
                <span class="btn-loader" style="display:none;">Accesso in corso...</span>
            </button>
        </form>

        <p class="qrlife-form-footer">
            Non hai un account? <a href="<?php echo esc_url( home_url( '/qrlife-registrazione/' ) ); ?>">Registrati</a>
        </p>
    </div>
</div>
