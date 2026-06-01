# Joey Maestro

A task management application built with a custom PHP framework, Apache, and MySQL.

---

## Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- WSL or bash (not PowerShell or cmd.exe)
- PHP 8.2+ and Composer (for local development outside Docker)

---

## Local Development

### Step 1 — Single container with manual Docker commands

Build the image:

```bash
docker build -t joey-maestro ./
```

Create a volume for the SQLite database:

```bash
docker volume create sqlite_data
```

Run the database migration:

```bash
docker run --rm \
  -v $PWD:/var/www/html \
  -v sqlite_data:/var/www/html/database \
  joey-maestro \
  php maestro migrate
```

Start the container:

```bash
docker run -d \
  --name joey-maestro-app \
  -p 80:80 \
  -v $PWD:/var/www/html \
  -v sqlite_data:/var/www/html/database \
  joey-maestro
```

Browse to **http://localhost**

Stop and remove the container:

```bash
docker stop joey-maestro-app
docker rm joey-maestro-app
```

---

### Step 2 — Docker Compose

Start the container:

```bash
docker compose up --build -d
```

Run the database migration:

```bash
docker compose exec app php maestro migrate
```

Stop the container:

```bash
docker compose down
```

Browse to **http://localhost**

---

### Step 3 — Separate MySQL container

The `docker-compose.yml` includes a separate MySQL container that communicates
with the web server over a Docker network called `app-network`.

Start both containers:

```bash
docker compose up --build -d
```

Run the database migration:

```bash
docker compose exec app php maestro migrate
```

Stop everything:

```bash
docker compose down
```

To fully reset the database (deletes all data):

```bash
docker compose down -v
docker compose up --build -d
docker compose exec app php maestro migrate
```

Browse to **http://localhost**

---

## Production Deployment (Railway)

The application is hosted on [Railway](https://railway.app) with a managed MySQL database.
It is accessible at the domain provided by Railway over HTTPS.

### First-time setup

1. Go to [railway.app](https://railway.app) and sign in with GitHub
2. Click **New Project** → **Deploy from GitHub repo** → select `Joey-Maestro`
3. Add a MySQL database: click **New Service** → **Database** → **MySQL**
4. In the **Joey-Maestro** service → **Variables**, add the following using Railway variable references:

```
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

5. In the **Joey-Maestro** service → **Settings** → **Deploy**, set the start command to:

```
sh -c "a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork 2>/dev/null; php maestro migrate; apache2-foreground"
```

6. Railway will build the Docker image and deploy automatically.

---

### Releasing a new version

1. Make your changes locally and test them
2. Commit and push to the `main` branch:

```bash
git add .
git commit -m "description of changes"
git push origin main
```

3. GitHub Actions will run the CI checks automatically (PHPStan, PHPCS, Deptrac)
4. If all checks pass, Railway detects the push and redeploys automatically
5. Monitor the deployment in the Railway dashboard

---

## Continuous Integration

On every push to `main`, GitHub Actions runs three code quality checks:

| Check | Tool | Standard |
|---|---|---|
| Static analysis | PHPStan | Level 8 |
| Code style | PHPCS | PSR-12 |
| Architecture | Deptrac | See `deptrac.yaml` |

If any check fails the CI pipeline fails and GitHub notifies the developer.
The workflow is defined in `.github/workflows/ci.yml`.

---

## Quick reference

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




auth = /?admin=1 of /?admin=0