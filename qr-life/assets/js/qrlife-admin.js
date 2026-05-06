/* QR Life v2 — Admin Script — Comune di Parenti */
jQuery(function ($) {
    var ajax = qrlifeAdmin.ajax_url;
    var nonce = qrlifeAdmin.nonce;

    /* ═══════ Crea medico ═══════ */
    $('#form-nuovo-medico').on('submit', function (e) {
        e.preventDefault();
        var msg = $('#qrlife-msg-medico');
        var btn = $(this).find('button[type=submit]').prop('disabled', true);
        var data = {
            action:           'qrlife_admin_crea_medico',
            nonce:            nonce,
            cognome:          $('[name=cognome]', this).val(),
            nome:             $('[name=nome]', this).val(),
            codice_medico:    $('[name=codice_medico]', this).val().toUpperCase(),
            pin:              $('[name=pin]', this).val(),
            specializzazione: $('[name=specializzazione]', this).val(),
            email:            $('[name=email]', this).val(),
            telefono:         $('[name=telefono]', this).val()
        };
        $.post(ajax, data, function (res) {
            btn.prop('disabled', false);
            if (res.success) {
                msg.removeClass('error').addClass('success').text('Medico creato con successo. Ricarico la pagina...').show();
                setTimeout(function () { location.reload(); }, 1500);
            } else {
                msg.removeClass('success').addClass('error').text(res.data).show();
            }
        });
    });

    /* ═══════ Toggle medico attivo/disattivato ═══════ */
    $(document).on('click', '.qrlife-toggle-medico', function () {
        var btn = $(this);
        var id = btn.data('id');
        var attivo = btn.data('attivo');
        btn.prop('disabled', true);
        $.post(ajax, {
            action: 'qrlife_admin_toggle_medico',
            nonce:  nonce,
            id:     id,
            attivo: attivo
        }, function (res) {
            if (res.success) location.reload();
            else btn.prop('disabled', false);
        });
    });

    /* ═══════ Cancella cittadino (diritto all'oblio) ═══════ */
    $(document).on('click', '.qrlife-cancella-cittadino', function () {
        var uid = $(this).data('uid');
        if (!confirm('ATTENZIONE: Stai per cancellare definitivamente tutti i dati di questo cittadino.\nQuesta operazione è IRREVERSIBILE.\n\nContinuare?')) return;
        if (!confirm('Confermi la cancellazione?')) return;

        $.post(ajax, {
            action:  'qrlife_admin_cancella_cittadino',
            nonce:   nonce,
            user_id: uid
        }, function (res) {
            if (res.success) {
                alert('Cittadino cancellato.');
                window.location.href = ajaxurl.replace('admin-ajax.php', 'admin.php?page=qr-life-cittadini');
            } else {
                alert(res.data || 'Errore.');
            }
        });
    });
});
