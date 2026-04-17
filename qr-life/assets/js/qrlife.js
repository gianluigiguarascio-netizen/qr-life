/* QR Life — Frontend Script */
jQuery(function ($) {
    var ajax = qrlife.ajax_url;
    var nonce = qrlife.nonce;

    function showMsg(el, type, text) {
        el.removeClass('success error').addClass(type).text(text).show();
        if (type === 'success') setTimeout(function () { el.fadeOut(); }, 3500);
    }

    function setBtnLoading(btn, loading) {
        btn.find('.btn-text').toggle(!loading);
        btn.find('.btn-loader').toggle(loading);
        btn.prop('disabled', loading);
    }

    /* ---- Registrazione ---- */
    $('#qrlife-register-form').on('submit', function (e) {
        e.preventDefault();
        var msg = $('#qrlife-msg');
        var btn = $(this).find('button[type=submit]');
        setBtnLoading(btn, true);

        $.post(ajax, {
            action:         'qrlife_register',
            nonce:          nonce,
            nome:           $('#reg-nome').val(),
            cognome:        $('#reg-cognome').val(),
            codice_fiscale: $('#reg-cf').val().toUpperCase(),
            email:          $('#reg-email').val(),
            password:       $('#reg-pwd').val(),
            password2:      $('#reg-pwd2').val()
        }, function (res) {
            setBtnLoading(btn, false);
            if (res.success) {
                showMsg(msg, 'success', 'Registrazione completata! Reindirizzamento...');
                setTimeout(function () { window.location.href = res.data.redirect; }, 1200);
            } else {
                showMsg(msg, 'error', res.data);
            }
        });
    });

    /* ---- Login ---- */
    $('#qrlife-login-form').on('submit', function (e) {
        e.preventDefault();
        var msg = $('#qrlife-msg');
        var btn = $(this).find('button[type=submit]');
        setBtnLoading(btn, true);

        $.post(ajax, {
            action:   'qrlife_login',
            nonce:    nonce,
            email:    $('#login-email').val(),
            password: $('#login-pwd').val()
        }, function (res) {
            setBtnLoading(btn, false);
            if (res.success) {
                showMsg(msg, 'success', 'Accesso effettuato! Reindirizzamento...');
                setTimeout(function () { window.location.href = res.data.redirect; }, 1000);
            } else {
                showMsg(msg, 'error', res.data);
            }
        });
    });

    /* ---- Logout ---- */
    $('#qrlife-logout-btn').on('click', function () {
        $.post(ajax, { action: 'qrlife_logout', nonce: nonce }, function (res) {
            if (res.success) window.location.href = res.data.redirect;
        });
    });

    /* ---- Aggiorna profilo ---- */
    $('#qrlife-profilo-form').on('submit', function (e) {
        e.preventDefault();
        var msg = $('#qrlife-msg-dash');
        $.post(ajax, {
            action:       'qrlife_update_profilo',
            nonce:        nonce,
            data_nascita: $('[name=data_nascita]').val(),
            telefono:     $('[name=telefono]').val(),
            indirizzo:    $('[name=indirizzo]').val()
        }, function (res) {
            showMsg(msg, res.success ? 'success' : 'error',
                res.success ? 'Dati aggiornati.' : res.data);
        });
    });

    /* ---- Patologie ---- */
    $('#btn-open-patologia').on('click', function () {
        $('#form-patologia').slideToggle(200);
    });
    $('#btn-cancel-patologia').on('click', function () {
        $('#form-patologia').slideUp(200);
    });

    $('#btn-save-patologia').on('click', function () {
        var nome = $('#pat-nome').val().trim();
        if (!nome) { alert('Inserisci il nome della patologia.'); return; }
        var $btn = $(this).prop('disabled', true);

        $.post(ajax, {
            action:        'qrlife_add_patologia',
            nonce:         nonce,
            nome:          nome,
            descrizione:   $('#pat-desc').val(),
            data_diagnosi: $('#pat-data').val()
        }, function (res) {
            $btn.prop('disabled', false);
            if (!res.success) { alert(res.data); return; }
            $('#patologie-empty').hide();
            var d = res.data;
            var dataStr = d.data_diagnosi ? '<span class="qrlife-tag">' + formatDate(d.data_diagnosi) + '</span>' : '';
            var descStr = d.descrizione ? '<p class="qrlife-health-note">' + escHtml(d.descrizione) + '</p>' : '';
            var html = '<div class="qrlife-health-item" data-id="' + d.id + '">'
                + '<div class="qrlife-health-info"><strong>' + escHtml(d.nome) + '</strong>'
                + dataStr + descStr + '</div>'
                + '<button class="qrlife-btn-icon qrlife-delete-patologia" data-id="' + d.id + '" title="Elimina">&times;</button>'
                + '</div>';
            $('#list-patologie').append(html);
            $('#pat-nome').val(''); $('#pat-desc').val(''); $('#pat-data').val('');
            $('#form-patologia').slideUp(200);
        });
    });

    $(document).on('click', '.qrlife-delete-patologia', function () {
        if (!confirm('Eliminare questa patologia?')) return;
        var $item = $(this).closest('.qrlife-health-item');
        var id = $(this).data('id');
        $.post(ajax, { action: 'qrlife_delete_patologia', nonce: nonce, id: id }, function (res) {
            if (res.success) {
                $item.fadeOut(300, function () {
                    $(this).remove();
                    if (!$('#list-patologie .qrlife-health-item').length) $('#patologie-empty').show();
                });
            }
        });
    });

    /* ---- Medicine ---- */
    $('#btn-open-medicina').on('click', function () {
        $('#form-medicina').slideToggle(200);
    });
    $('#btn-cancel-medicina').on('click', function () {
        $('#form-medicina').slideUp(200);
    });

    $('#btn-save-medicina').on('click', function () {
        var nome = $('#med-nome').val().trim();
        if (!nome) { alert('Inserisci il nome del farmaco.'); return; }
        var $btn = $(this).prop('disabled', true);

        $.post(ajax, {
            action:    'qrlife_add_medicina',
            nonce:     nonce,
            nome:      nome,
            principio: $('#med-principio').val(),
            grammi:    $('#med-grammi').val(),
            unita:     $('#med-unita').val(),
            quantita:  $('#med-quantita').val(),
            frequenza: $('#med-frequenza').val(),
            note:      $('#med-note').val()
        }, function (res) {
            $btn.prop('disabled', false);
            if (!res.success) { alert(res.data); return; }
            $('#medicine-empty').hide();
            var d = res.data;
            var tags = '';
            if (d.grammi) tags += '<span class="qrlife-tag">' + d.grammi + ' ' + d.unita + '</span>';
            if (d.quantita) tags += '<span class="qrlife-tag">Qtà: ' + d.quantita + '</span>';
            if (d.principio) tags += '<span class="qrlife-tag qrlife-tag-light">' + escHtml(d.principio) + '</span>';
            var freqStr = d.frequenza ? '<p class="qrlife-health-note">' + escHtml(d.frequenza) + '</p>' : '';
            var noteStr = d.note ? '<p class="qrlife-health-note">' + escHtml(d.note) + '</p>' : '';
            var html = '<div class="qrlife-health-item" data-id="' + d.id + '">'
                + '<div class="qrlife-health-info"><strong>' + escHtml(d.nome) + '</strong>'
                + tags + freqStr + noteStr + '</div>'
                + '<button class="qrlife-btn-icon qrlife-delete-medicina" data-id="' + d.id + '" title="Elimina">&times;</button>'
                + '</div>';
            $('#list-medicine').append(html);
            $('#med-nome,#med-principio,#med-grammi,#med-quantita,#med-frequenza,#med-note').val('');
            $('#med-unita').val('mg');
            $('#form-medicina').slideUp(200);
        });
    });

    $(document).on('click', '.qrlife-delete-medicina', function () {
        if (!confirm('Eliminare questo farmaco?')) return;
        var $item = $(this).closest('.qrlife-health-item');
        var id = $(this).data('id');
        $.post(ajax, { action: 'qrlife_delete_medicina', nonce: nonce, id: id }, function (res) {
            if (res.success) {
                $item.fadeOut(300, function () {
                    $(this).remove();
                    if (!$('#list-medicine .qrlife-health-item').length) $('#medicine-empty').show();
                });
            }
        });
    });

    /* Utils */
    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function formatDate(str) {
        if (!str) return '';
        var p = str.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    }
});
