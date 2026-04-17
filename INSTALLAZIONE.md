# QR Life — Istruzioni di Installazione

## Struttura del plugin
```
qr-life/
├── qr-life.php                          (file principale)
├── includes/
│   ├── class-qrlife-db.php              (database)
│   ├── class-qrlife-user.php            (registrazione/login)
│   ├── class-qrlife-health.php          (patologie e medicine)
│   ├── class-qrlife-qr.php              (generazione QR code)
│   ├── class-qrlife-admin.php           (pannello admin)
│   └── class-qrlife-frontend.php        (shortcode e pagine)
├── templates/
│   ├── page-registrazione.php
│   ├── page-login.php
│   ├── page-dashboard.php
│   └── page-profilo-pubblico.php
└── assets/
    ├── css/qrlife.css
    ├── css/qrlife-admin.css
    └── js/qrlife.js
```

## Installazione

1. Copia la cartella `qr-life/` in `wp-content/plugins/`
2. Vai su **Plugins → Plugin installati** nel pannello WordPress
3. Attiva il plugin **QR Life**
4. Il plugin crea automaticamente:
   - 4 pagine WordPress (registrazione, login, dashboard, profilo)
   - 3 tabelle nel database
   - Il ruolo utente `Cittadino QR Life`

## Pagine create automaticamente

| Slug | Contenuto | URL |
|------|-----------|-----|
| `/qrlife-registrazione/` | Form di registrazione | Pubblica |
| `/qrlife-login/` | Form di accesso | Pubblica |
| `/qrlife-dashboard/` | Area personale del cittadino | Solo loggati |
| `/qrlife-profilo/` | Profilo d'emergenza (QR) | Pubblica |

## Pannello Amministratore

Vai su **QR Life** nel menu laterale di WordPress Admin:
- **Cittadini**: elenco di tutti i cittadini registrati
- **Visualizza dati**: scheda completa con patologie e farmaci di ogni cittadino

## Shortcode disponibili

```
[qrlife_registrazione]   — form di registrazione
[qrlife_login]           — form di accesso
[qrlife_dashboard]       — area personale
[qrlife_profilo]         — profilo pubblico (via token QR)
```

## Funzionalità

### Per il cittadino
- Registrazione con Codice Fiscale, Nome, Cognome, Email
- Validazione del formato Codice Fiscale italiano
- Dashboard personale per gestire:
  - **Patologie**: nome, descrizione, data di diagnosi
  - **Farmaci**: nome, principio attivo, dosaggio (grammi/mg/ml/mcg/UI), quantità, frequenza, note
- QR Code univoco generato automaticamente
- Profilo d'emergenza pubblico accessibile via QR

### Per l'amministratore
- Visualizza tutti i cittadini registrati
- Accede alla scheda completa di ogni cittadino
- Vede patologie e terapia farmacologica nel dettaglio

## Note di sicurezza
- I dati sanitari sono protetti da login WordPress
- I QR code usano un token sicuro casuale a 32 caratteri
- Tutte le query usano prepared statements
- I nonce WordPress proteggono da CSRF
- I dati in input sono sempre sanitizzati

## Requisiti
- WordPress 5.8+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+
- Connessione internet (per la generazione QR via api.qrserver.com)
