# Kogi State Ministry of Culture and Tourism (KSMCT) — Internal Back-Office System

An internal-only back-office management platform built with **Laravel 12** and **MySQL 8.0+** for officers of the Kogi State Ministry of Culture and Tourism (Nigeria).

- **Data Sovereignty**: 100% on-premise — zero external SaaS or CDN dependencies.
- **Localization**: Nigerian English (`en_NG`), West Africa Time (`Africa/Lagos`), `DD/MM/YYYY` dates, and Naira (`₦` NGN) currency formatting.
- **Permission Matrix**: See [docs/PERMISSION_MATRIX.md](docs/PERMISSION_MATRIX.md).

---

## 1. Quick Start (Docker Compose — Recommended On-Premise Deployment)

### Prerequisites

- Docker Engine 24+ & Docker Compose v2

### Setup Steps

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install --no-interaction
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app npm install && docker compose exec app npm run build
```

Access the internal back-office at `http://<server-ip>:8080`.

---

## 2. Non-Docker Fallback (XAMPP / cPanel / Shared Hosting)

For deployment on a cPanel or Apache/XAMPP server without Docker:

1. **Requirements**: PHP 8.2+ (8.3 recommended) with `pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`, `bcmath` extensions; MySQL 8.0+ (`utf8mb4_unicode_ci`, InnoDB).
2. **Upload & Point Document Root**:
    - Place the repository outside `public_html` (e.g., `/home/<user>/ksmct_app`).
    - Point the domain/subdomain document root to `/home/<user>/ksmct_app/public` (or symlink `public_html` to `public`).
3. **Configure `.env`**:
    - Set `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_CHARSET=utf8mb4`, `DB_COLLATION=utf8mb4_unicode_ci`.
    - Set `SESSION_DRIVER=database`, `FILESYSTEM_DISK=local`, `APP_ENV=production`, `APP_DEBUG=false`.
4. **Install & Migrate**:
    ```bash
    composer install --no-dev --optimize-autoloader
    npm ci && npm run build
    php artisan key:generate
    php artisan migrate --force --seed
    php artisan config:cache && php artisan route:cache && php artisan view:cache
    ```
5. **Cron Job (cPanel)**:
    ```cron
    * * * * * cd /home/<user>/ksmct_app && php artisan schedule:run >> /dev/null 2>&1
    ```

---

## 3. Seeded Demo Accounts (Phase 1)

- **Super Admin**: `admin` / `admin`
- **All other demo accounts**: `Password@2026!`

| Role                | Username / Email                      | Password         | Department                         |
| :------------------ | :------------------------------------ | :--------------- | :--------------------------------- |
| `SUPER_ADMIN`       | `admin`                               | `admin`          | Administration & Executive         |
| `DIRECTOR`          | `director@ksmct.kogistate.gov.ng`     | `Password@2026!` | Administration & Executive         |
| `DEPT_HEAD_CULTURE` | `culture.head@ksmct.kogistate.gov.ng` | `Password@2026!` | Department of Culture              |
| `DEPT_HEAD_TOURISM` | `tourism.head@ksmct.kogistate.gov.ng` | `Password@2026!` | Department of Tourism              |
| `DEPT_HEAD_HOTELS`  | `hotels.head@ksmct.kogistate.gov.ng`  | `Password@2026!` | Department of Hotels & Hospitality |
| `LICENSING_OFFICER` | `licensing@ksmct.kogistate.gov.ng`    | `Password@2026!` | Department of Hotels & Hospitality |
| `DATA_ENTRY`        | `dataentry@ksmct.kogistate.gov.ng`    | `Password@2026!` | Planning, Research & Finance       |
| `VIEWER`            | `viewer@ksmct.kogistate.gov.ng`       | `Password@2026!` | Planning, Research & Finance       |

---

## 4. Backup & Restore Procedure

### Creating a Backup

Run the built-in Artisan command (can be scheduled daily via cron):

```bash
php artisan ksmct:backup
```

This dumps the MySQL database (`--single-transaction --default-character-set=utf8mb4`) and packages it together with `storage/app/private` uploads into `storage/app/backups/ksmct_backup_YYYYMMDD_HHMMSS.zip`.

### Restoring from a Backup

```bash
# 1. Extract archive to a temporary directory
unzip storage/app/backups/ksmct_backup_YYYYMMDD_HHMMSS.zip -d /tmp/ksmct_restore

# 2. Restore MySQL database
mysql -h 127.0.0.1 -u ksmct_user -p ksmct_db < /tmp/ksmct_restore/database/ksmct_db_*.sql

# 3. Restore uploaded files
cp -R /tmp/ksmct_restore/uploads/* storage/app/private/

# 4. Clear caches and verify
php artisan optimize:clear
```

---

## 5. Phase 1 Demo Script

1. **Sign In & Audit Verification**:
    - Sign in at `/login` as `admin` / `admin`.
    - Open **Audit Trail** (`/admin/audit-logs`) and verify the `Login` event with timestamp (WAT) and IP address.
2. **User Lifecycle & Forced Password Change**:
    - Go to **Staff & Roles** (`/admin/users`) and click **+ Add Staff Account**.
    - Create a new officer with `DATA_ENTRY` role and "Require password change on first login" checked.
    - Click **Reset Pwd** on any non-admin user to generate a one-time temporary password, or **Force Logout** to immediately terminate their database sessions.
    - Click **Deactivate** on the newly created officer and verify they are blocked from signing in.
3. **System Configuration (Zero Hardcoded Dropdowns)**:
    - Visit **Kogi State LGAs (21)** (`/config/lgas`) to verify all 21 Kogi LGAs with senatorial districts and GPS centroids.
    - Visit **Asset Categories**, **License Types**, **Fee Schedules (₦)**, and **Inspection Checklists**; add a new Asset Category and confirm the `Create` event appears immediately in **Audit Trail** with JSON `after_value`.
4. **RBAC Enforcement**:
    - Sign out and sign in as `viewer@ksmct.kogistate.gov.ng`.
    - Confirm **Staff & Roles** and **Audit Trail** are hidden from the sidebar and return `403 Forbidden` if accessed directly, while System Configuration tables are visible in read-only mode.

---

## 6. Offline Map Tiles (Optional Local Tile Server)

Leaflet.js CSS and JavaScript are bundled locally via Vite (no CDN). By default, map tiles load from OpenStreetMap (`https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png`). For air-gapped or fully offline on-premise deployments, run a local tile server (e.g., `maptiler/tileserver-gl` with a Nigeria `.mbtiles` extract) on the Ministry server and set `MAP_TILE_URL` in `.env`:

```env
MAP_TILE_URL=http://<ministry-server-ip>:8085/styles/basic-preview/{z}/{x}/{y}.png
```

---

## 7. Running Automated Tests

```bash
php artisan test
```
