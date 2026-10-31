# User Authentication & Profile Management System

A full-stack web application where users can register, log in, and manage their personal profile. It uses three databases, each for the job it does best, and runs in Docker.

**Live demo:** https://internship-project-zmt8.onrender.com/

> Hosted on a free tier, so the first load after a period of inactivity can take up to a minute while the server wakes up.

---

## Features

- **User registration** with duplicate username and email checks
- **Secure login** with hashed passwords and token-based sessions
- **Profile management** for age, date of birth, and contact details
- **Input validation** on both the browser and the server (age range, valid date of birth, age and date of birth must match)
- **Session expiry:** login tokens expire automatically after 1 hour
- **Logout** that invalidates the session on the server
- **Responsive, modern UI** that works on desktop and mobile
- **Dockerized** setup that starts the whole stack with one command

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5, jQuery (AJAX) |
| Backend | PHP 8 (JSON endpoints) on Apache |
| Relational database | MySQL: user accounts |
| Document database | MongoDB: profile data |
| Cache / sessions | Redis: login tokens with TTL |
| DevOps | Docker, Docker Compose |

---

## Architecture

```
                 ┌──────────────────────────┐
   Browser ────► │  HTML + Bootstrap + jQuery │
                 └─────────────┬────────────┘
                               │ AJAX (JSON)
                      ┌────────▼────────┐
                      │   PHP (Apache)  │
                      └──┬─────┬─────┬──┘
                         │     │     │
              ┌──────────▼┐ ┌──▼─────┐ ┌▼────────┐
              │   MySQL   │ │ MongoDB │ │  Redis  │
              │ user      │ │ profile │ │ session │
              │ accounts  │ │ data    │ │ tokens  │
              └───────────┘ └─────────┘ └─────────┘
```

**Why three databases?**
- **MySQL** holds structured, unique, relational data (username, email, hashed password).
- **MongoDB** holds flexible, schema-light profile documents.
- **Redis** holds fast, auto-expiring session tokens.

### Authentication flow

1. The user registers. The password is hashed with `password_hash()` and saved in MySQL, and an empty profile document is created in MongoDB.
2. The user logs in. The password is checked with `password_verify()`, and a random 64-character token is generated with `random_bytes()`.
3. The token is stored in Redis for 1 hour (`SETEX token -> user id`) and returned to the browser.
4. Every profile request sends the token as `Authorization: Bearer <token>`. The server looks it up in Redis to find the user.
5. On logout, the token is deleted from Redis.

---

## Project Structure

```
.
├── index.html              # Entry page (redirects to login / profile)
├── login.html
├── register.html
├── profile.html
├── css/
│   └── style.css           # Design tokens + all styling
├── js/
│   ├── login.js
│   ├── register.js
│   └── profile.js
├── php/
│   ├── config.php          # DB connections (reads environment variables)
│   ├── register.php
│   ├── login.php
│   ├── profile.php         # Get / update profile
│   └── logout.php
├── schema.sql              # MySQL table definition
├── Dockerfile
├── docker-compose.yml
└── docker-entrypoint-fix.sh
```

---

## API Endpoints

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/php/register.php` | None | Create a new account |
| POST | `/php/login.php` | None | Log in and receive a token |
| GET | `/php/profile.php` | Bearer token | Fetch the profile |
| POST | `/php/profile.php` | Bearer token | Update the profile |
| POST | `/php/logout.php` | Bearer token | Invalidate the token |

---

## Run Locally (Docker)

**Prerequisites:** [Docker Desktop](https://www.docker.com/products/docker-desktop/)

```bash
# 1. Clone the repository
git clone <your-repo-url>
cd <your-repo-folder>

# 2. Build and start all services
docker compose up --build
```

Then open **http://localhost:8081/login.html**.

The MySQL table is created automatically from `schema.sql` on first start.

**Useful commands**

```bash
docker compose up -d          # run in the background
docker compose logs -f app    # view app logs
docker compose down           # stop everything
docker compose down -v        # stop and delete all database data
```

| Service | Local port |
|---|---|
| Website | 8081 |
| MySQL | 3307 |
| MongoDB | 27018 |
| Redis | 6380 |

---

## Configuration

`php/config.php` reads its settings from environment variables, so the same code runs locally and in the cloud.

| Variable | Purpose |
|---|---|
| `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_DATABASE` | MySQL connection |
| `MYSQL_SSL` | Set to `true` if the MySQL provider requires SSL |
| `MONGO_URL` | Full MongoDB connection string (for example MongoDB Atlas) |
| `REDIS_URL` | Full Redis connection string (`redis://` or `rediss://` for TLS) |

---

## Deployment

The live version runs on free cloud tiers:

- **App:** Render (Docker web service)
- **Redis:** Render Key Value
- **MongoDB:** MongoDB Atlas
- **MySQL:** a managed MySQL-compatible service

---

## Security Notes

- Passwords are hashed with PHP's `password_hash()` (bcrypt) and never stored in plain text.
- All SQL uses prepared statements to prevent SQL injection.
- Session tokens are cryptographically random and expire after 1 hour.
- Profile input is validated on both the client and the server.

### Possible improvements

- Store the session token in an `HttpOnly` cookie instead of `localStorage`
- Add rate limiting on login attempts
- Add email verification and password reset
- Add automated tests and a CI pipeline

---

## Author

**Sanjeev SB**
[LinkedIn](https://www.linkedin.com/in/sanjeev-sb-b49b4831a) · [GitHub](https://github.com/SanjeevSB78)

