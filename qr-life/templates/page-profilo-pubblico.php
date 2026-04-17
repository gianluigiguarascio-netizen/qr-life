<?php defined( 'ABSPATH' ) || exit;
/** @var object $profilo
 *  @var array  $patologie
 *  @var array  $medicine
 */
?>
<div class="qrlife-wrap qrlife-profilo-pubblico">
    <div class="qrlife-emergency-banner">
        <span>&#9888;</span> Profilo d'emergenza sanitaria — Solo per uso medico
    </div>

    <div class="qrlife-card">
        <div class="qrlife-profilo-header">
            <span class="qrlife-icon-lg">&#10084;</span>
            <div>
                <h2><?php echo esc_html( strtoupper( $profilo->cognome ) . ' ' . $profilo->nome ); ?></h2>
                <p class="qrlife-cf">CF: <code><?php echo esc_html( $profilo->codice_fiscale ); ?></code></p>
                <?php if ( $profilo->data_nascita ) : ?>
                    <p>Nato il: <?php echo date_i18n( 'd/m/Y', strtotime( $profilo->data_nascita ) ); ?></p>
                <?php endif; ?>
                <?php if ( $profilo->telefono ) : ?>
                    <p>Tel: <?php echo esc_html( $profilo->telefono ); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Patologie -->
    <div class="qrlife-card">
        <h3>&#9763; Patologie</h3>
        <?php
        $patologie_attive = array_filter( $patologie, fn($p) => $p->attiva );
        if ( empty( $patologie_attive ) ) :
        ?>
            <p class="qrlife-empty">Nessuna patologia registrata.</p>
        <?php else : ?>
            <ul class="qrlife-pub-list">
            <?php foreach ( $patologie_attive as $pat ) : ?>
                <li>
                    <strong><?php echo esc_html( $pat->nome ); ?></strong>
                    <?php if ( $pat->descrizione ) : ?>
                        — <?php echo esc_html( $pat->descrizione ); ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Terapia -->
    <div class="qrlife-card">
        <h3>&#128138; Terapia Farmacologica</h3>
        <?php
        $medicine_attive = array_filter( $medicine, fn($m) => $m->attivo );
        if ( empty( $medicine_attive ) ) :
        ?>
            <p class="qrlife-empty">Nessuna terapia registrata.</p>
        <?php else : ?>
            <ul class="qrlife-pub-list">
            <?php foreach ( $medicine_attive as $med ) : ?>
                <li>
                    <strong><?php echo esc_html( $med->nome ); ?></strong>
                    <?php if ( $med->grammi ) : ?>
                        <span class="qrlife-tag"><?php echo esc_html( $med->grammi . ' ' . $med->unita ); ?></span>
                    <?php endif; ?>
                    <?php if ( $med->quantita ) : ?>
                        <span class="qrlife-tag">× <?php echo esc_html( $med->quantita ); ?></span>
                    <?php endif; ?>
                    <?php if ( $med->frequenza ) : ?>
                        — <?php echo esc_html( $med->frequenza ); ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <p class="qrlife-timestamp">Dati aggiornati al <?php echo date_i18n( 'd/m/Y H:i' ); ?></p>
</div>
