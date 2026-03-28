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

The REST API is consumed directly via an Authorization bearer token. All API responses adhere to a consistent JSON envelope to simplify frontend error handling and typing.

### Global Response Envelopes

**Success Envelope:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Optional localized success message."
}
```

**Paginated Success:**
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

**Error Envelope (400 / 403 / 404 / 422 / 500):**
```json
{
  "success": false,
  "message": "Detailed error context or validation message.",
  "errors": {
      "field_name": ["Specific validation rejection reasons."]
  }
}
```

### Endpoints

Base URL: `https://csirt.yourdomain.com/api`

| Method | Endpoint | Description | Guard / Policy |
| :--- | :--- | :--- | :--- |
| `POST` | `/login` | Submit `email` and `password`. Returns plain-text Bearer token in the `token` string variable. | Public |
| `POST` | `/logout` | Invalidates current token. | `auth:sanctum` |
| `GET` | `/user` | Returns the currently authenticated user payload & loaded Role. | `auth:sanctum` |
| `GET` | `/incidents` | Returns paginated incident reports. | `auth:sanctum` + `ReportPolicy` |
| `GET` | `/incidents/{id}` | Returns single incident + attachments and severity relations. | `auth:sanctum` + `ReportPolicy` |
| `GET` | `/admin/users` | Returns paginated user array for administrative oversight. | `api.admin` |
| `GET` | `/admin/roles` | Returns list of roles, including custom provisioned ones. | `api.admin` |
| `GET` | `/admin/audit-logs`| Reverse chronologic JSON array of all database actions. | `api.admin` |

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
