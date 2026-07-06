# GAFCONL 24/7 Registration Portal — Full Documentation

**Version:** 1.9.5 "Phoenix"  
**Organisation:** Global Apex Farmers Cooperative Nigeria Limited  
**Last Updated:** July 6, 2025  

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Technology Stack](#2-technology-stack)
3. [Directory Structure](#3-directory-structure)
4. [Installation & Local Setup](#4-installation--local-setup)
5. [Environment Configuration](#5-environment-configuration)
6. [Architecture](#6-architecture)
7. [Authentication System](#7-authentication-system)
8. [User Roles & Permissions](#8-user-roles--permissions)
9. [Core Modules](#9-core-modules)
10. [Payment System](#10-payment-system)
11. [Security Controls](#11-security-controls)
12. [Rate Limiting](#12-rate-limiting)
13. [Logging & Monitoring](#13-logging--monitoring)
14. [Webhook Integration](#14-webhook-integration)
15. [API Reference](#15-api-reference)
16. [Database Schema](#16-database-schema)
17. [Deployment Guide](#17-deployment-guide)
18. [Legal Pages](#18-legal-pages)
19. [Known Limitations & Roadmap](#19-known-limitations--roadmap)

---

## 1. Project Overview

The GAFCONL 24/7 Registration Portal is a membership management system for **Global Apex Farmers Cooperative Nigeria Limited**. It provides:

- Self-service member registration with email verification
- Member portal for dues, shares, and thrift savings payments
- Admin dashboard for cooperative officers
- Community discussion forum
- Event calendar
- AI farming assistant ("Abinci Assistant")
- Immutable payment ledger with idempotency and webhook support
- Full audit trail for all authentication and financial events

**Two user types:**

| Type | Login credential | Table | Portal |
|---|---|---|---|
| Admin/Staff | Email address | `users` | `/dashboard` |
| Cooperative Member | Phone number (with country code) | `members` | `/member/dashboard` |

---

## 2. Technology Stack

| Layer | Technology |
|---|---|
| Language | PHP ≥ 7.4 |
| Architecture | Custom MVC (no framework) |
| Database | MySQL 5.7+ / 8.0 via PDO |
| Autoloading | Composer PSR-4 |
| Email | PHPMailer 6.x (SMTP) |
| PDF generation | mPDF 8.x, TCPDF 6.x |
| Spreadsheet export | PhpSpreadsheet 1.x |
| Environment | vlucas/phpdotenv 5.x |
| Payment gateways | Paystack, Monify (Monnify), OPay |
| AI services | OpenAI GPT-3.5-turbo, HuggingFace Inference API |
| Frontend CSS | Tailwind CSS 3.4 (CDN) |
| Frontend icons | Remix Icon 4.6 |
| Web server | Apache (with mod_rewrite) via Laragon locally |

---

## 3. Directory Structure

```
gafconl-app/
├── app/
│   ├── Config/          # Version.php
│   ├── Console/         # BackupScheduler.php
│   ├── Controllers/     # All HTTP controllers
│   ├── Helpers/         # Csrf, Mailer, Monitoring, PermissionHelper,
│   │                    # RateLimiter, SecurityLogger, Url
│   ├── Middleware/      # MonitoringMiddleware
│   ├── Models/          # PDO models (BaseModel, Database singleton, etc.)
│   ├── Services/        # EmailNotificationService
│   └── Views/           # PHP templates
│       ├── admin/       # Admin panel views
│       ├── auth/        # Login, reset password
│       ├── forum/       # Community forum views
│       ├── legal/       # Privacy, Terms, Compliance, IP pages
│       ├── layouts/     # admin.php, user.php shared layouts
│       ├── partials/    # Sidebar, header snippets
│       └── user/        # Member portal views
├── config/              # app.php, cache.php, monitoring.php, backup.php
├── database/
│   └── migrations/      # SQL migration files
├── logs/                # Security & application logs (excluded from git)
│   └── security/        # Daily security event logs
├── public/              # Web root
│   ├── .htaccess        # Security headers, routing, HTTPS redirect
│   ├── index.php        # Front controller / router
│   ├── css/             # Compiled stylesheets
│   ├── js/              # JavaScript (country-state-chapter.js etc.)
│   └── uploads/         # User-uploaded files (excluded from git)
├── vendor/              # Composer dependencies (excluded from git)
├── .env                 # Local secrets (excluded from git)
├── .env.example         # Template — safe to commit
├── .gitignore
├── composer.json
└── DOCUMENTATION.md     # This file
```

---

## 4. Installation & Local Setup

### Prerequisites
- PHP 7.4+ with extensions: PDO, PDO_MySQL, mbstring, openssl, fileinfo, curl, json, zip
- MySQL 5.7+ or MariaDB 10.4+
- Composer 2.x
- Apache with mod_rewrite (Laragon recommended on Windows)

### Steps

```bash
# 1. Clone the repository
git clone <repo-url> gafconl-app
cd gafconl-app

# 2. Install PHP dependencies
composer install

# 3. Copy environment template
cp .env.example .env
# Edit .env with your local values

# 4. Create the database
mysql -u root -e "CREATE DATABASE \`gafconl-app\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Run migrations in order
mysql -u root "gafconl-app" < database/migrations/001_initial.sql
mysql -u root "gafconl-app" < database/migrations/002_security_hardening.sql
mysql -u root "gafconl-app" < database/migrations/003_secure_deployment.sql
mysql -u root "gafconl-app" < database/migrations/004_payment_ledger.sql

# 6. Create required upload directories
mkdir -p public/uploads/{member_photos,nin_cards,signatures,logos,receipts}

# 7. Point your web server document root to: gafconl-app/public
```

**Local URL:** `http://localhost/gafconl-app/public`

---

## 5. Environment Configuration

Copy `.env.example` to `.env` and fill in all values. Never commit `.env`.

### Required variables

| Variable | Description |
|---|---|
| `APP_URL` | Full base URL including `/public` |
| `APP_ENV` | `local` or `production` |
| `APP_DEBUG` | `false` in production — never `true` |
| `DB_HOST` | Database host |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` | Database user |
| `DB_PASSWORD` | Database password |
| `SMTP_HOST` | SMTP server hostname |
| `SMTP_PORT` | 465 (SMTPS) or 587 (STARTTLS) |
| `SMTP_USER` | SMTP username |
| `SMTP_PASS` | SMTP password |
| `MAIL_FROM_ADDRESS` | From address for system emails |
| `PAYSTACK_SECRET_KEY` | Paystack secret key (sk_live_...) |
| `PAYSTACK_PUBLIC_KEY` | Paystack public key (pk_live_...) |
| `ADMIN_EMAIL` | Email for system alerts |

### Optional variables

| Variable | Default | Description |
|---|---|---|
| `MONIFY_SECRET_KEY` | *(empty)* | Activates Monify gateway |
| `OPAY_SECRET_KEY` | *(empty)* | Activates OPay gateway |
| `AI_ENABLED` | `true` | Enable AI farming assistant |
| `OPENAI_API_KEY` | *(empty)* | OpenAI key for Abinci Assistant |
| `HUGGINGFACE_API_KEY` | *(empty)* | HuggingFace fallback |
| `RATE_LIMIT_LOGIN` | `5:900` | Login limit: max:window_seconds |
| `RATE_LIMIT_AI_CHAT` | `20:60` | AI chat limit per user |

---

## 6. Architecture

### Request lifecycle

```
Browser → Apache → public/.htaccess (security headers, HTTPS)
       → public/index.php
           1. Load .env
           2. Configure PHP error display (off in production)
           3. Harden session cookie settings
           4. Start MonitoringMiddleware (error handlers, shutdown logging)
           5. Remember-me token check
           6. Unusual traffic pattern detection
           7. Strip base path from URI
           8. Route switch() → Controller::method()
```

### Front controller routing

All routing lives in `public/index.php` as a `switch ($uri)` block. There is no framework router. To add a new route:

```php
case '/my-new-route':
    (new \App\Controllers\MyController())->myMethod();
    break;
```

### MVC pattern

- **Models** — extend `BaseModel`, use the `Database` singleton for PDO connections. All queries use prepared statements.
- **Controllers** — extend `BaseController` which provides `render()`, `renderUserLayout()`, `requireAdmin()`, `requireUser()`, `requirePermission()`, and flash message helpers.
- **Views** — plain PHP files. Data is passed via `extract($data)` inside `render()`. No templating engine.

---

## 7. Authentication System

### Login flow

1. User submits email (admin) or phone number (member) + password
2. CSRF token validated
3. Rate limit checked (5 attempts / 15 min per IP)
4. Credential verified with `password_verify()` against bcrypt hash
5. Members: `email_verified` flag checked before allowing login
6. `session_regenerate_id(true)` — old session destroyed
7. Session timestamps written: `_last_activity`, `_created_at`
8. Redirect to appropriate dashboard

### Session timeouts (enforced on every protected request)

| Timeout | Duration |
|---|---|
| Idle timeout | 30 minutes |
| Absolute timeout | 8 hours |

### Password requirements

- Minimum 8 characters
- Must include: letters, numbers, and at least one special character (`!@#$%^&*`)
- Hashed with bcrypt, cost factor 12

### Email verification

New member registrations receive a 24-hour verification link. Login is blocked until the email is verified. The token is stored as a SHA-256 hash (never plaintext).

### Password reset

Reset tokens stored as SHA-256 hashes in `password_resets` table with a 1-hour TTL. The raw token only exists in the email link.

---

## 8. User Roles & Permissions

### Admin roles (users table)

| Role | Key permissions |
|---|---|
| Administrator | Full access including role management |
| Secretary | Member management, password resets |
| Financial Secretary | Shares, dues, thrift editing |
| Treasurer | Revenue reports, financial exports |

Permissions are stored in the `permissions` and `role_permissions` tables using dot notation:

```
members.view    members.create    members.edit    members.delete
members.export  members.password_reset
dues.view       dues.create       dues.edit       dues.delete
shares.view     shares.create     shares.edit     shares.delete
roles.view      roles.create      roles.edit      roles.delete
users.view      users.create      users.edit      users.delete
```

Checked via `PermissionHelper::hasPermission('module.action')` or `BaseController::requirePermission()`.

### Member portal

Authenticated members can only access their own data. All member-facing endpoints verify session `user_id` ownership before returning or modifying any record.

---

## 9. Core Modules

### Member Management (`/members`)
Admin creates and manages member records. Each member receives a unique `GAFCONL-XXXXXXX` membership number. Supports CSV, XLSX, and PDF export.

### Annual Dues (`/dues`, `/member/dues`)
Members pay annual cooperative dues. Admin can view, add, and approve manual payments. The immutable payment ledger tracks every state transition.

### Shares (`/shares`, `/member/shares`)
Members purchase cooperative shares at ₦100/share, minimum 100 shares per transaction.

### Thrift Savings (`/thrift`, `/member/thrift`)
Monthly thrift savings contributions tracked per member.

### Rice Project (`/rice-project`)
Members submit investment interest forms with payment proof. Admin approves or rejects each submission.

### Community Forum (`/forum`)
Category-based discussion board. Members can create topics and replies. Topic authors and admins can edit/delete. Rate-limited to prevent spam.

### Events (`/events`, `/member/events`)
Admin creates events; members view upcoming and past events in list and calendar views.

### AI Farming Assistant (`/ai-chat/chat`)
"Abinci Assistant" answers farming questions using a local knowledge base with optional OpenAI/HuggingFace fallback. Rate-limited: 20 requests/minute per user, 30/minute per IP.

---

## 10. Payment System

### Supported gateways

| Gateway | Mode | Config |
|---|---|---|
| Paystack | Live / Demo | `PAYSTACK_SECRET_KEY` |
| Monify | Live / Demo | `MONIFY_SECRET_KEY` |
| OPay | Live / Demo | `OPAY_SECRET_KEY` |
| Manual | Always available | No config needed |

If a gateway's secret key is empty in `.env`, the system falls into demo mode and redirects to the mock payment page for testing.

### Idempotency keys

Every payment form should generate a UUID v4 client-side and send it as:
- Hidden form field: `<input name="idempotency_key" value="<uuid>">`
- Or HTTP header: `X-Idempotency-Key: <uuid>`

The server checks the key before calling the gateway. If found with a matching payload, the cached response is returned immediately — no second charge.

```javascript
// Example: generate UUID v4 client-side
function generateUUID() {
    return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
        (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16));
}
document.getElementById('idempotency_key').value = generateUUID();
```

### Immutable payment ledger

The `payment_ledger` table is append-only. Every state transition creates a new row:

| State | Meaning |
|---|---|
| `intent` | Written before the gateway API call |
| `gateway_init` | Gateway accepted the request |
| `authorized` | Card/account authorised |
| `captured` | Money moved successfully |
| `failed` | Terminal failure |
| `cancelled` | User or system cancellation |
| `refunded` | Full refund processed |
| `webhook_received` | Raw webhook event appended |
| `admin_approved` | Manual admin override |
| `admin_rejected` | Admin rejection |

The `updatePaymentStatus()` method is kept for backward compatibility but now calls `PaymentLedger::append()` before the mutable UPDATE.

---

## 11. Security Controls

### Authentication
- Bcrypt password hashing (cost 12)
- CSRF token on every form (rotated after each use)
- Session fixation prevention (`session_regenerate_id(true)`)
- Session idle (30 min) and absolute (8 hr) timeouts
- Email verification required for new member accounts
- `email_verified = 0` blocks login

### Input validation
- All user input read via `trim()`, `filter_var()`, or explicit type casts
- All DB queries use PDO prepared statements — no string interpolation in SQL
- File uploads validated by extension AND `finfo` MIME type check
- Upload directories protected from PHP execution via `.htaccess`

### Headers (public/.htaccess)
```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()
Content-Security-Policy: default-src 'self'; [see .htaccess for full policy]
```
HSTS is commented in `.htaccess` — uncomment after confirming HTTPS works.

### IDOR protection
Every member-facing endpoint verifies that the resource being accessed belongs to the currently authenticated user before reading, modifying, or deleting it.

Payment callbacks verify `payment.membership_number === user.membership_number` before crediting any amount. IDOR attempts are logged to `SecurityLogger`.

---

## 12. Rate Limiting

Backed by the `rate_limit_attempts` DB table. All limits configurable via `.env`.

| Action | Default limit | Window | Key |
|---|---|---|---|
| Login | 5 attempts | 15 min | IP |
| Registration | 3 attempts | 1 hour | IP |
| Password reset | 3 attempts | 1 hour | IP |
| AI chat | 20 requests | 1 min | User |
| AI chat (no auth) | 30 requests | 1 min | IP |
| Forum new topic | 5 topics | 5 min | User |
| Forum reply | 10 replies | 1 min | User |
| Forum reaction | 30 reactions | 1 min | User |
| Payment initiation | 5 attempts | 5 min | User |
| Member export | 5 downloads | 5 min | User |
| Search | 30 queries | 1 min | User |

Override any limit in `.env`: `RATE_LIMIT_ACTION_NAME=max:window_seconds`  
Example: `RATE_LIMIT_AI_CHAT=10:60`

---

## 13. Logging & Monitoring

### Log files (under `/logs/`, excluded from git)

| File pattern | Contents |
|---|---|
| `logs/security/security_YYYY-MM-DD.log` | Auth events, IDOR attempts, CSRF failures, rate limits, API errors |
| `logs/php_errors.log` | PHP errors and exceptions |
| `logs/error_YYYY-MM-DD.log` | Application errors |
| `logs/info_YYYY-MM-DD.log` | Request start/info messages |

### Database tables

| Table | Contents |
|---|---|
| `audit_logs` | User actions: login, logout, page access |
| `security_event_logs` | Security events from SecurityLogger |
| `error_logs` | Application errors with stack traces |
| `payment_ledger` | Immutable payment timeline |
| `payment_webhooks` | Raw webhook bodies |

### SecurityLogger API

```php
use App\Helpers\SecurityLogger;

SecurityLogger::loginSuccess($identifier, $role);
SecurityLogger::loginFailure($identifier, $reason);
SecurityLogger::rateLimitExceeded($action, $key);
SecurityLogger::idorAttempt($resource, $ownerId, $requestedId);
SecurityLogger::csrfFailure($uri);
SecurityLogger::apiError($service, $httpCode, $detail);
SecurityLogger::unusualTraffic($reason, $context);
SecurityLogger::event($level, $event, $context);
```

---

## 14. Webhook Integration

### Paystack webhook

**Endpoint:** `POST /webhooks/paystack`  
**Authentication:** HMAC-SHA512 signature in `X-Paystack-Signature` header  
**Secret:** Your `PAYSTACK_SECRET_KEY` (same key used for API calls)

**Configure in Paystack Dashboard:**  
Dashboard → Settings → API Keys & Webhooks → Webhook URL: `https://yourdomain.com/public/webhooks/paystack`

### Processing flow

```
Request arrives
    → Validate HMAC-SHA512 signature (reject 401 if invalid)
    → Store raw body in payment_webhooks (before processing)
    → Check gateway_event_id uniqueness (return 200 if duplicate)
    → Append ledger row with state = webhook_received / captured / failed / refunded
    → Only fulfil benefits if payment.status = 'pending' (prevents double-credit)
    → Sync payment_transactions.status
    → Mark webhook processed
    → Return 200 OK
```

### Supported Paystack events

| Event | Ledger state | Action |
|---|---|---|
| `charge.success` | `captured` | Credit dues/shares/thrift |
| `charge.failed` | `failed` | Mark failed |
| `transfer.failed` | `failed` | Mark failed |
| `refund.processed` | `refunded` | Mark refunded |

---

## 15. API Reference

### Public endpoints (no auth)

| Method | Path | Description |
|---|---|---|
| GET/POST | `/login` | Member/admin login |
| POST | `/register` | New member registration |
| POST | `/request-reset` | Password reset request |
| GET | `/reset-password` | Show reset form |
| POST | `/reset-password` | Process password reset |
| GET | `/verify-email` | Email verification |
| GET | `/legal/privacy-policy` | Privacy policy page |
| GET | `/legal/terms-of-use` | Terms of use page |
| GET | `/legal/data-compliance` | Data & compliance page |
| GET | `/legal/ip-infringement` | IP infringement policy |
| POST | `/webhooks/paystack` | Paystack webhook receiver |

### Member portal (requires member session)

| Method | Path | Description |
|---|---|---|
| GET | `/member/dashboard` | Member dashboard |
| GET/POST | `/member/profile` | View/update profile |
| GET | `/member/dues` | Dues history |
| POST | `/member/dues/pay` | Initiate dues payment |
| GET | `/member/dues/payment-callback` | Payment gateway callback |
| GET | `/member/shares` | Shares history |
| POST | `/member/shares/pay` | Initiate share purchase |
| GET | `/member/thrift` | Thrift savings |
| POST | `/member/thrift/pay` | Initiate thrift payment |
| GET | `/member/events` | Events list |
| GET | `/member/calendar` | Event calendar |
| GET | `/member/forum` | Forum redirect |
| GET | `/member/id-card` | Download ID card |
| POST | `/member/change-password` | Change password |
| POST | `/ai-chat/chat` | AI farming assistant |

### Admin panel (requires admin session + permission)

| Method | Path | Permission |
|---|---|---|
| GET | `/dashboard` | Any admin |
| GET | `/members` | `members.view` |
| GET/POST | `/members/add` | `members.create` |
| GET/POST | `/members/edit` | `members.edit` |
| POST | `/members/delete` | `members.delete` |
| GET | `/members/export` | `members.view` |
| GET | `/dues` | Any admin |
| GET | `/shares` | Any admin |
| GET | `/thrift` | Any admin |
| GET | `/reports` | Any admin |
| GET | `/admin/payments` | Any admin |
| POST | `/admin/payments/approve` | Any admin |
| POST | `/admin/payments/reject` | Any admin |
| GET | `/roles` | `roles.view` |
| GET | `/users` | `users.view` |
| GET | `/settings` | Any admin |
| GET/POST | `/ai-chat/test` | Admin only |

---

## 16. Database Schema

### Key tables

**members** — cooperative members (login via phone number)  
**users** — admin/staff accounts (login via email)  
**roles** + **permissions** + **role_permissions** — RBAC  
**annual_dues** — dues payment records  
**shares** — share purchase records  
**thrift_savings** — thrift contribution records  
**payment_transactions** — mutable payment header  
**payment_ledger** — immutable append-only state transitions  
**payment_idempotency** — client UUID deduplication store  
**payment_webhooks** — raw signed webhook bodies  
**password_resets** — hashed reset tokens (1-hr TTL)  
**rate_limit_attempts** — sliding-window rate limiter  
**audit_logs** — authentication and action audit trail  
**security_event_logs** — security events (IDOR, CSRF, rate limits)  
**forum_topics** + **forum_posts** + **forum_reactions** — community forum  
**events** — cooperative events  
**notifications** — member notifications  

### Migrations

Run in order:

| File | Description |
|---|---|
| `001_initial.sql` | Base tables |
| `002_security_hardening.sql` | Rate limits, email_verified, password_resets.expires_at |
| `003_secure_deployment.sql` | security_event_logs, error_logs, php_error_logs |
| `004_payment_ledger.sql` | payment_ledger, payment_idempotency, payment_webhooks |

---

## 17. Deployment Guide

### Pre-deployment checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
- [ ] Set `APP_URL` to your production HTTPS URL
- [ ] Configure real payment gateway keys (not demo)
- [ ] Verify SMTP works with `MAIL_FROM_ADDRESS` set correctly
- [ ] Run all 4 migrations on production DB
- [ ] Confirm `composer install --no-dev --optimize-autoloader`
- [ ] Set file permissions: `public/uploads/` → 755, `.env` → 600
- [ ] Confirm `.env` is not web-accessible (test: `curl https://yourdomain.com/.env` should return 403)
- [ ] Uncomment HTTPS redirect in root `.htaccess`
- [ ] Uncomment HSTS header in `public/.htaccess` after confirming SSL
- [ ] Configure Paystack webhook URL in gateway dashboard
- [ ] Set `ADMIN_EMAIL` for security alerts
- [ ] Verify `logs/` directory exists and is writable by the web server

### Apache VirtualHost (minimal)

```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/gafconl-app/public

    SSLEngine on
    SSLCertificateFile    /etc/ssl/certs/yourdomain.com.crt
    SSLCertificateKeyFile /etc/ssl/private/yourdomain.com.key

    <Directory /var/www/gafconl-app/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Block access to app internals
    <Directory /var/www/gafconl-app/app>
        Require all denied
    </Directory>
    <Directory /var/www/gafconl-app/vendor>
        Require all denied
    </Directory>
</VirtualHost>
```

### Cron jobs

```bash
# Daily backup at 2am
0 2 * * * php /var/www/gafconl-app/app/Console/BackupScheduler.php >> /var/log/gafconl-backup.log 2>&1

# Clean expired idempotency keys (if MySQL event scheduler disabled)
0 3 * * * mysql -u app_user -p'pass' gafconl -e "DELETE FROM payment_idempotency WHERE expires_at < NOW();"
```

---

## 18. Legal Pages

All legal pages are publicly accessible (no login required) at:

| Page | URL |
|---|---|
| Privacy Policy | `/legal/privacy-policy` |
| Terms of Use | `/legal/terms-of-use` |
| Data & Compliance | `/legal/data-compliance` |
| IP Infringement | `/legal/ip-infringement` |

Pages are rendered by `LegalController` using a shared layout (`app/Views/legal/layout.php`) that matches the Portal's Tailwind design system. Links to all legal pages appear in:
- The login page footer
- The registration form "I agree to Terms" checkbox
- The legal page sidebar

---

## 19. Known Limitations & Roadmap

### Current limitations

- **CSRF protection** on admin write forms (event add/edit, dues add/edit, shares add/edit) is not yet implemented — tracked for next sprint
- **State-changing GETs** (delete endpoints) still accept GET requests — should be converted to POST with CSRF
- **Mock payment page** does not validate the `callback_url` against an allowlist — open redirect risk in demo mode
- **Monify and OPay** live integration stubs exist but are not fully implemented — Paystack is the production-ready gateway
- **SMS notifications** are stubbed in config but not wired to a real provider

### Roadmap

- [ ] Add CSRF tokens to all remaining admin forms
- [ ] Convert all delete actions to POST
- [ ] Validate `authorization_url` against known gateway domains before redirect
- [ ] Implement Monify live API flow
- [ ] Add SMS notifications via Termii or Twilio
- [ ] Add 2FA (TOTP) for admin accounts
- [ ] Build member-facing payment timeline UI using `payment_ledger` data
- [ ] Add idempotency UUID generation to all payment forms client-side
- [ ] Implement full-text search on forum topics
- [ ] Add email digest for forum activity

---

*Documentation maintained by the GAFCONL development team. For corrections or additions, open a pull request or email info@globalapexfarmers.org.ng.*
