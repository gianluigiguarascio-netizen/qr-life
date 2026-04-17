<?php
defined( 'ABSPATH' ) || exit;

class QRLife_QR {

    /**
     * Restituisce l'URL immagine del QR Code per un dato token.
     * Usa il servizio gratuito qrserver.com
     */
    public static function get_qr_url( $token, $size = 200 ) {
        $profilo_url = home_url( '/qrlife-profilo/?token=' . urlencode( $token ) );
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode( $profilo_url );
    }

    /**
     * Restituisce il tag <img> del QR Code pronto per l'uso.
     */
    public static function render_qr( $token, $size = 200 ) {
        $url = self::get_qr_url( $token, $size );
        return sprintf(
            '<img src="%s" alt="QR Code Sanitario" width="%d" height="%d" class="qrlife-qr-img" />',
            esc_url( $url ),
            $size,
            $size
        );
    }
}
