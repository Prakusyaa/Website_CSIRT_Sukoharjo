# CSIRT Incident Management System
## Production Deployment & Operations Guide

This document supplies production-ready deployment instructions, `.env` configuration strategies, execution sequences, architecture overview, and REST API documentation.

---

## 1. Installation (VPS Environment)

Deploying to a production Virtual Private Server (Ubuntu/Debian) requires standard Laravel / Node dependencies.

**Server Requirements:**
*   **OS:** Ubuntu 22.04 LTS / 24.04 LTS (Recommended)
*   **Web Server:** Nginx or Apache
*   **PHP:** >= 8.2 (extensions: `bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, tokenizer, xml`)
*   **Database:** MySQL 8.0+ / MariaDB 10.6+
*   **Node.js:** >= 20.0

### Step-by-Step Installation

1. **Clone the Repository**
   ```bash
   cd /var/www/
   git clone <repository_url> csirt
   cd csirt
   ```

2. **Install Composer Dependencies**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

3. **Install NPM Packages & Build Assets**
   ```bash
   npm ci
   npm run build
   ```

4. **Directory Permissions**
   Laravel requires write permissions for the web server user (typically `www-data` on NGINX/Ubuntu):
   ```bash
   chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

---

## 2. `.env` Configuration

Copy the example environment file and generate your application key:
```bash
cp .env.example .env
php artisan key:generate
```

### Production Variables Checklist

Ensure the following variables accurately reflect your production environment to prevent debug leaks or authentication failures:

```ini
APP_NAME="Corporate CSIRT"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://csirt.yourdomain.com

# Database Connection
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=csirt_production
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# Authentication (Sanctum Configuration)
# Required if your API is accessed cross-domain
SANCTUM_STATEFUL_DOMAINS="csirt.yourdomain.com"

# Session & Security
SESSION_DRIVER=database         # Strongly recommended over 'file' for production
SESSION_SECURE_COOKIE=true      # Enforce HTTPS-only cookies
```

---

## 3. Run & Maintenance Steps

Before taking the application live, configure storage symlinks and run performance caches.

1. **Link Public Storage (For Attachments)**
   ```bash
   php artisan storage:link
   ```

2. **Run Database Migrations**
   ```bash
   php artisan migrate --force
   ```

3. **Performance Optimization (Route, Config, & View Caching)**
   Run this command every time you deploy an update to cache all services for maximum execution speeds.
   ```bash
   php artisan optimize
   ```

### Nginx Virtual Host Example

Create a configuration file at `/etc/nginx/sites-available/csirt`:

```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name csirt.yourdomain.com;
    root /var/www/csirt/public;

    ssl_certificate /etc/letsencrypt/live/csirt.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/csirt.yourdomain.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    add_header Content-Security-Policy "default-src 'self' 'unsafe-inline' 'unsafe-eval'; img-src 'self' data:;";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 4. API Documentation

The REST API lives under the `/api` prefix and uses **Laravel Sanctum** personal access tokens. Integrations should send **`Accept: application/json`** on every request so validation and authentication failures return JSON (not HTML).

**Base URL:** `{APP_URL}/api` — e.g. `https://csirt.yourdomain.com/api` in production, or `http://localhost:8000/api` locally.

### 4.1 Authentication

#### Issue a token — `POST /login` (public)

| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `login` | string | yes | Username **or** email. |
| `password` | string | yes | Account password. |
| `remember` | boolean | no | Accepted by validation; token lifetime is fixed server-side. |

**Success (200)** — `ApiResponse::success` envelope:

```json
{
  "success": true,
  "message": "Authentication successful.",
  "data": {
    "token": "<plain-text-bearer-token>",
    "expires_at": "2026-04-05T12:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "…",
      "username": "…",
      "email": "…",
      "is_active": true,
      "role": { "id": 1, "name": "…", "level": 100 },
      "created_at": "…",
      "updated_at": "…"
    }
  }
}
```

**Rate limiting:** repeated failures for the same `login` + IP may return **422** with throttle messaging on the `login` field.

**Invalid credentials or deactivated account:** **422** with validation-style errors on `login`.

#### Use the token

Send the token on all routes behind `auth:sanctum`:

```http
Authorization: Bearer <plain-text-token>
```

**Token lifetime:** tokens issued at login expire after **7 days** (`AuthController@login`).

#### Revoke the current token — `POST /logout`

Requires `Authorization: Bearer …`. The response is **not** wrapped in the standard `success` / `data` envelope:

```json
{
  "message": "Token successfully revoked."
}
```

No JSON body is required; the current token is identified from the header.

### 4.2 Global response envelopes

**Success — single resource** (e.g. `ApiResponse::success`, resources using `BaseResource`):

```json
{
  "success": true,
  "message": "Optional localized success message.",
  "data": { }
}
```

`message` may be omitted when empty.

**Success — paginated collection:**

```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 45
  },
  "links": { ... }
}
```

`meta` and `links` follow Laravel’s default pagination (`current_page`, `last_page`, `per_page`, `total`, `from`, `to`, etc.).

**Error envelope** (typical for validation; other errors use `message` and optional `errors`):

```json
{
  "success": false,
  "message": "Detailed error context or validation message.",
  "errors": {
    "field_name": ["Specific validation rejection reasons."]
  }
}
```

**Common HTTP statuses** (see `bootstrap/app.php` for API JSON handling):

| Status | Typical case |
| :--- | :--- |
| **401** | Missing or invalid Bearer token. |
| **403** | Policy denial or admin gate (`Administrator access required.`). |
| **404** | Unknown route or model. |
| **405** | HTTP method not allowed. |
| **422** | Validation failure. |
| **500** | Server error (`debug` may appear when `APP_DEBUG=true`). |

### 4.3 Current user — `GET /user`

Requires Bearer token. Returns the authenticated user with `role` loaded (same user shape as in the login response `data.user`).

### 4.4 Incidents (reports)

Incidents use the `reports` model. **`ReportPolicy`** applies: staff / CSIRT / admin can **read**; **this API only exposes read routes** (writes use the web app unless you extend `routes/api.php`).

| Method | Path | Description |
| :--- | :--- | :--- |
| `GET` | `/incidents` | Paginated list. `viewAny` on `Report`. Query: e.g. `?page=2`. |
| `GET` | `/incidents/{incident}` | Single incident; `view` on that report. Relationships loaded (including `attachments` on show). |

**Incident resource fields** (list/show may omit some relations if not loaded):

| Field | Notes |
| :--- | :--- |
| `id`, `subject`, `description`, `status`, `reporter_email` | Core |
| `category` | `{ id, name }` when loaded |
| `severity` | `{ id, name, level }` when loaded |
| `reporter` | `{ id, name, username }` when loaded |
| `assignee` | `{ id, name }` (assigned user) when loaded |
| `creator` | `{ id, name }` when loaded |
| `attachments` | `[{ id, file_name, file_type, file_size }, …]` when loaded |
| `created_at`, `updated_at` | ISO 8601 strings |

### 4.5 Admin (Administrator only)

Middleware requires **`$user->isAdmin()`** (role level ≥ 100). Others receive **403** with `Administrator access required.`

| Method | Path | Description |
| :--- | :--- | :--- |
| `GET` | `/admin/users` | Paginated users with `role` loaded. |
| `GET` | `/admin/users/{user}` | Single user with `role`. |
| `GET` | `/admin/roles` | All roles ordered by level; includes `users_count`. |
| `GET` | `/admin/roles/{role}` | One role with `users_count`. |
| `GET` | `/admin/audit-logs` | Paginated audit logs; actor loaded as `actor` (`id`, `name`, `email`). |

**Role resource:** `id`, `name`, `level`, `users_count` (when counted), `created_at`.

**Audit log resource:** `id`, `action`, `table_name`, `record_id`, `changes`, `actor` (when loaded), `created_at`.

### 4.6 Quick reference

| Method | Endpoint | Auth |
| :--- | :--- | :--- |
| `POST` | `/login` | Public |
| `POST` | `/logout` | Bearer |
| `GET` | `/user` | Bearer |
| `GET` | `/incidents` | Bearer + `ReportPolicy` |
| `GET` | `/incidents/{incident}` | Bearer + `ReportPolicy` |
| `GET` | `/admin/users` | Bearer + admin |
| `GET` | `/admin/users/{user}` | Bearer + admin |
| `GET` | `/admin/roles` | Bearer + admin |
| `GET` | `/admin/roles/{role}` | Bearer + admin |
| `GET` | `/admin/audit-logs` | Bearer + admin |

**Note:** Create/update/delete incidents are not exposed under `routes/api.php` today; use the web application or add routes if required.

---

## 5. Clean Architecture Folder Structure

A high-level overview of the MVC+S (Model-View-Controller-Service) structure scaling this application:

```text
📁 csirt/
├── 📁 app/
│   ├── 📁 Http/
│   │   ├── 📁 Controllers/   # Lean routing controllers
│   │   │   ├── 📁 Admin/     # Nested admin-only controllers (Role, User)
│   │   │   ├── 📁 Api/       # Stateless API controllers (AuthController)
│   │   │   └── IncidentController.php
│   │   ├── 📁 Middleware/    # EnsureUserIsActive, EnsureUserHasRole, etc.
│   │   ├── 📁 Requests/      # Strict validated FormRequests (StoreIncident, etc.)
│   │   └── 📁 Resources/     # Standardized JSON API wrapping (IncidentResource)
│   ├── 📁 Models/            # Eloquent Models & Scopes (User, Report, Role)
│   ├── 📁 Observers/         # Background listeners for Cache Validation / Audit Logs
│   ├── 📁 Policies/          # Gate/Authorization logic (ReportPolicy)
│   └── 📁 Services/          # Clean Architecture: Heavy DB lifting isolated here
│       ├── IncidentService.php      # Extracted Incident business logic
│       └── ReferenceDataService.php # Aggressive caching for Categories/Roles
├── 📁 bootstrap/
│   └── app.php               # Global exceptions, middlewares & routing
├── 📁 config/                # Environment-driven core config (.env tied)
├── 📁 database/
│   └── 📁 migrations/        # Sequential DB schema modifications
├── 📁 resources/
│   └── 📁 js/
│       ├── 📁 Components/    # Reusable Vue components (Modals, Tables, Forms)
│       ├── 📁 Layouts/       # Persistent UI layout shells (AdminLayout, AppLayout)
│       └── 📁 Pages/         # Page-level Vue mapping exactly to Controller routes
│           ├── 📁 Admin/
│           ├── 📁 AuditLogs/
│           └── 📁 Incidents/
└── 📁 routes/
    ├── api.php               # Token-guarded JSON REST endpoints
    └── web.php               # Session-guarded Inertia.js (Vue) page endpoints
```
