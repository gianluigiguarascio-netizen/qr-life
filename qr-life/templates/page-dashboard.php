<?php defined( 'ABSPATH' ) || exit;
/** @var object $profilo
 *  @var array  $patologie
 *  @var array  $medicine
 */
?>
<div class="qrlife-wrap qrlife-dashboard">

    <!-- Header -->
    <div class="qrlife-dash-header">
        <div class="qrlife-dash-welcome">
            <span class="qrlife-icon">&#10084;</span>
            <div>
                <h2>Benvenuto, <?php echo esc_html( $profilo->nome . ' ' . $profilo->cognome ); ?></h2>
                <p>CF: <code><?php echo esc_html( $profilo->codice_fiscale ); ?></code></p>
            </div>
        </div>
        <button id="qrlife-logout-btn" class="qrlife-btn qrlife-btn-outline">Esci</button>
    </div>

    <div id="qrlife-msg-dash" class="qrlife-msg" style="display:none;"></div>

    <div class="qrlife-dash-grid">

        <!-- Colonna sinistra: QR Code + dati personali -->
        <div class="qrlife-dash-sidebar">

            <div class="qrlife-card qrlife-qr-card">
                <h3>Il tuo QR Code Sanitario</h3>
                <?php echo QRLife_QR::render_qr( $profilo->token, 180 ); ?>
                <p class="qrlife-qr-hint">In caso di emergenza, mostra questo codice al personale sanitario</p>
                <a href="<?php echo esc_url( home_url( '/qrlife-profilo/?token=' . $profilo->token ) ); ?>"
                   target="_blank" class="qrlife-btn qrlife-btn-sm qrlife-btn-outline">Visualizza profilo pubblico</a>
            </div>

            <div class="qrlife-card">
                <h3>Dati Personali</h3>
                <form id="qrlife-profilo-form">
                    <div class="qrlife-field">
                        <label>Data di nascita</label>
                        <input type="date" name="data_nascita" value="<?php echo esc_attr( $profilo->data_nascita ); ?>">
                    </div>
                    <div class="qrlife-field">
                        <label>Telefono</label>
                        <input type="tel" name="telefono" value="<?php echo esc_attr( $profilo->telefono ); ?>" placeholder="+39 333 1234567">
                    </div>
                    <div class="qrlife-field">
                        <label>Indirizzo</label>
                        <textarea name="indirizzo" rows="2" placeholder="Via Roma 1, Milano"><?php echo esc_textarea( $profilo->indirizzo ); ?></textarea>
                    </div>
                    <button type="submit" class="qrlife-btn qrlife-btn-sm qrlife-btn-primary">Salva</button>
                </form>
            </div>
        </div>

        <!-- Colonna destra: salute -->
        <div class="qrlife-dash-main">

            <!-- Patologie -->
            <div class="qrlife-card" id="card-patologie">
                <div class="qrlife-card-head">
                    <h3>&#9763; Patologie</h3>
                    <button class="qrlife-btn qrlife-btn-sm qrlife-btn-primary" id="btn-open-patologia">+ Aggiungi</button>
                </div>

                <!-- Form aggiungi patologia -->
                <div id="form-patologia" class="qrlife-inline-form" style="display:none;">
                    <div class="qrlife-form-row qrlife-form-row-2">
                        <div class="qrlife-field">
                            <label>Nome patologia *</label>
                            <input type="text" id="pat-nome" placeholder="es. Diabete tipo 2">
                        </div>
                        <div class="qrlife-field">
                            <label>Data diagnosi</label>
                            <input type="date" id="pat-data">
                        </div>
                    </div>
                    <div class="qrlife-field">
                        <label>Note / descrizione</label>
                        <textarea id="pat-desc" rows="2" placeholder="Eventuali note..."></textarea>
                    </div>
                    <div class="qrlife-inline-form-actions">
                        <button class="qrlife-btn qrlife-btn-sm qrlife-btn-primary" id="btn-save-patologia">Salva patologia</button>
                        <button class="qrlife-btn qrlife-btn-sm qrlife-btn-ghost" id="btn-cancel-patologia">Annulla</button>
                    </div>
                </div>

                <div id="list-patologie">
                <?php if ( empty( $patologie ) ) : ?>
                    <p class="qrlife-empty" id="patologie-empty">Nessuna patologia inserita.</p>
                <?php else : ?>
                    <?php foreach ( $patologie as $pat ) : ?>
                    <div class="qrlife-health-item" data-id="<?php echo $pat->id; ?>">
                        <div class="qrlife-health-info">
                            <strong><?php echo esc_html( $pat->nome ); ?></strong>
                            <?php if ( $pat->data_diagnosi ) : ?>
                                <span class="qrlife-tag"><?php echo date_i18n( 'd/m/Y', strtotime( $pat->data_diagnosi ) ); ?></span>
                            <?php endif; ?>
                            <?php if ( $pat->descrizione ) : ?>
                                <p class="qrlife-health-note"><?php echo esc_html( $pat->descrizione ); ?></p>
                            <?php endif; ?>
                        </div>
                        <button class="qrlife-btn-icon qrlife-delete-patologia" data-id="<?php echo $pat->id; ?>" title="Elimina">&times;</button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
            </div>

            <!-- Medicine -->
            <div class="qrlife-card" id="card-medicine">
                <div class="qrlife-card-head">
                    <h3>&#128138; Terapia Farmacologica</h3>
                    <button class="qrlife-btn qrlife-btn-sm qrlife-btn-primary" id="btn-open-medicina">+ Aggiungi</button>
                </div>

                <!-- Form aggiungi medicina -->
                <div id="form-medicina" class="qrlife-inline-form" style="display:none;">
                    <div class="qrlife-form-row qrlife-form-row-2">
                        <div class="qrlife-field">
                            <label>Nome farmaco *</label>
                            <input type="text" id="med-nome" placeholder="es. Metformina">
                        </div>
                        <div class="qrlife-field">
                            <label>Principio attivo</label>
                            <input type="text" id="med-principio" placeholder="es. Metformina cloridrato">
                        </div>
                    </div>
                    <div class="qrlife-form-row qrlife-form-row-3">
                        <div class="qrlife-field">
                            <label>Dosaggio</label>
                            <input type="number" id="med-grammi" placeholder="es. 500" step="0.001" min="0">
                        </div>
                        <div class="qrlife-field">
                            <label>Unità</label>
                            <select id="med-unita">
                                <option value="mg">mg</option>
                                <option value="g">g</option>
                                <option value="ml">ml</option>
                                <option value="mcg">mcg</option>
                                <option value="UI">UI</option>
                                <option value="compresse">compresse</option>
                            </select>
                        </div>
                        <div class="qrlife-field">
                            <label>Quantità / dose</label>
                            <input type="number" id="med-quantita" placeholder="es. 1" step="0.5" min="0">
                        </div>
                    </div>
                    <div class="qrlife-field">
                        <label>Frequenza</label>
                        <input type="text" id="med-frequenza" placeholder="es. 2 volte al giorno, dopo i pasti">
                    </div>
                    <div class="qrlife-field">
                        <label>Note</label>
                        <textarea id="med-note" rows="2" placeholder="Eventuali note..."></textarea>
                    </div>
                    <div class="qrlife-inline-form-actions">
                        <button class="qrlife-btn qrlife-btn-sm qrlife-btn-primary" id="btn-save-medicina">Salva farmaco</button>
                        <button class="qrlife-btn qrlife-btn-sm qrlife-btn-ghost" id="btn-cancel-medicina">Annulla</button>
                    </div>
                </div>

                <div id="list-medicine">
                <?php if ( empty( $medicine ) ) : ?>
                    <p class="qrlife-empty" id="medicine-empty">Nessun farmaco inserito.</p>
                <?php else : ?>
                    <?php foreach ( $medicine as $med ) : ?>
                    <div class="qrlife-health-item" data-id="<?php echo $med->id; ?>">
                        <div class="qrlife-health-info">
                            <strong><?php echo esc_html( $med->nome ); ?></strong>
                            <?php if ( $med->grammi ) : ?>
                                <span class="qrlife-tag"><?php echo esc_html( $med->grammi . ' ' . $med->unita ); ?></span>
                            <?php endif; ?>
                            <?php if ( $med->quantita ) : ?>
                                <span class="qrlife-tag">Qtà: <?php echo esc_html( $med->quantita ); ?></span>
                            <?php endif; ?>
                            <?php if ( $med->principio ) : ?>
                                <span class="qrlife-tag qrlife-tag-light"><?php echo esc_html( $med->principio ); ?></span>
                            <?php endif; ?>
                            <?php if ( $med->frequenza ) : ?>
                                <p class="qrlife-health-note"><?php echo esc_html( $med->frequenza ); ?></p>
                            <?php endif; ?>
                            <?php if ( $med->note ) : ?>
                                <p class="qrlife-health-note"><?php echo esc_html( $med->note ); ?></p>
                            <?php endif; ?>
                        </div>
                        <button class="qrlife-btn-icon qrlife-delete-medicina" data-id="<?php echo $med->id; ?>" title="Elimina">&times;</button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
            </div>

        </div><!-- .qrlife-dash-main -->
    </div><!-- .qrlife-dash-grid -->
</div>
