# TyphonCath CRM — Local Setup Guide

## Prerequisites

Install **Docker Desktop** for your OS:

- **Windows**: https://docs.docker.com/desktop/install/windows-install/
- **Mac**: https://docs.docker.com/desktop/install/mac-install/
- **Linux**: https://docs.docker.com/desktop/install/linux-install/

That's the only thing you need to install. No PHP, no MySQL, no XAMPP.

---

## Setup Steps

### 1. Clone the repository

```bash
git clone <repo-url>
cd typhoncath
```

### 2. Start the project

```bash
docker compose up
```

The first run will take a minute or two — Docker is downloading images and building the PHP container. Subsequent starts are fast.

When you see a line like `app-1 | AH00558: apache2: Could not reliably determine...` the server is ready.

### 3. Open the app

Go to **http://localhost:8080** in your browser.

### 4. Log in

Use the demo credentials seeded into the database:

| Field | Value |
|-------|-------|
| Email | `admin@typhoncath.test` |
| Password | *(ask Trevor — the hash in seed.sql needs replacing with a real one)* |

---

## Daily Use

| Task | Command |
|------|---------|
| Start the app | `docker compose up` |
| Start in background | `docker compose up -d` |
| Stop the app | `docker compose down` |
| View logs | `docker compose logs -f` |
| Reset the database | `docker compose down -v` then `docker compose up` |

> **Reset warning**: `docker compose down -v` deletes the database volume. All data is wiped and re-seeded from scratch. Use this if the DB gets into a bad state.

---

## Troubleshooting

**Port 8080 already in use**
Something else on your machine is using port 8080. Either stop that process, or change `"8080:80"` to `"8081:80"` in `docker-compose.yml` and visit http://localhost:8081.

**Port 3306 already in use**
You have a local MySQL running. Change `"3306:3306"` to `"3307:3306"` in `docker-compose.yml`. The app will still work — that port is only for connecting with a DB client like TablePlus or DBeaver.

**Database connection error on first boot**
The app container sometimes starts before MySQL is fully ready. Run `docker compose restart app` to fix it.

**Schema changes / new migrations**
If someone adds a migration file, run `docker compose down -v && docker compose up` to rebuild the database from scratch.

---

## Connecting a Database Client (optional)

If you want to browse the database directly with TablePlus, DBeaver, or MySQL Workbench:

| Setting | Value |
|---------|-------|
| Host | `127.0.0.1` |
| Port | `3306` |
| Database | `typhon_cath_crm` |
| Username | `crm_user` |
| Password | `secret` |
