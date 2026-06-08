# Joey Maestro

A showcase application built with a custom PHP framework, Apache, and SQLite (local) / MySQL (production).

> Commands below expect **Git Bash** (Windows) or a Linux/macOS terminal.

---

## Quick Start

```bash
docker compose up --build -d
docker compose exec app php maestro migrate
```

Browse to **http://localhost**

---

## Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- Git Bash (Windows) or standard terminal (Linux/macOS)
- PHP 8.2+ and Composer (for local development outside Docker)

---

## Local Development

Uses SQLite locally. Three steps of increasing complexity.

### Step 1 — Single container (manual Docker)

Build the image:

```bash
docker build -t joey-maestro ./
```

Create a volume for SQLite:

```bash
docker volume create sqlite_data
```

Run the migration:

```bash
docker run --rm \
  -v ${PWD}:/var/www/html \
  -v sqlite_data:/var/www/html/database \
  joey-maestro \
  php maestro migrate
```

Start the container:

```bash
docker run -d \
  --name joey-maestro-app \
  -p 80:80 \
  -v ${PWD}:/var/www/html \
  -v sqlite_data:/var/www/html/database \
  joey-maestro
```

Browse to **http://localhost**

Stop and remove:

```bash
docker stop joey-maestro-app && docker rm joey-maestro-app
```

### Step 2 — Docker Compose

```bash
docker compose up --build -d
docker compose exec app php maestro migrate
```

Browse to **http://localhost**

```bash
docker compose down        # stop
```

### Step 3 — Separate MySQL container

The `docker-compose.yml` adds a MySQL container on `app-network` for production parity.

```bash
docker compose up --build -d
docker compose exec app php maestro migrate
```

Browse to **http://localhost**

Full reset:

```bash
docker compose down -v
docker compose up --build -d
docker compose exec app php maestro migrate
```

---

## Production Deployment (Railway)

Hosted on [Railway](https://railway.app) with a managed MySQL database over HTTPS.

### First-time setup

1. Go to [railway.app](https://railway.app) and sign in with GitHub
2. **New Project** → **Deploy from GitHub repo** → select `Joey-Maestro`
3. **New Service** → **Database** → **MySQL**
4. In the **Joey-Maestro** service → **Variables**, add:

```
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

5. In **Settings** → **Deploy**, set start command:

```
sh -c "a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork 2>/dev/null; php maestro migrate; apache2-foreground"
```

6. Railway builds the image and deploys automatically.

### Releasing a new version

1. Make changes locally and test
2. Commit and push:

```bash
git add .
git commit -m "description of changes"
git push origin main
```

3. GitHub Actions runs CI checks (PHPStan, PHPCS, Deptrac)
4. If checks pass, Railway redeploys automatically
5. Monitor in the Railway dashboard

---

## Continuous Integration

On every push to `main`, GitHub Actions runs:

| Check | Tool | Standard |
|---|---|---|
| Static analysis | PHPStan | Level 8 |
| Code style | PHPCS | PSR-12 |
| Architecture | Deptrac | See `deptrac.yaml` |

If any check fails the pipeline fails and notifies the developer.
Workflow: `.github/workflows/ci.yml`

---

## Quick Reference

| Task | Command |
|---|---|
| Start (Compose) | `docker compose up --build -d` |
| Stop (Compose) | `docker compose down` |
| Run migration | `docker compose exec app php maestro migrate` |
| View logs | `docker compose logs -f app` |
| Open shell | `docker compose exec app bash` |
| Full reset | `docker compose down -v` |
| Run PHPStan | `vendor/bin/phpstan analyse --level=8` |
| Run PHPCS | `vendor/bin/phpcs` |
| Run Deptrac | `vendor/bin/deptrac analyse --no-progress` |
