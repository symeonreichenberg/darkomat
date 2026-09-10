# Dárkomat — PostgreSQL / Render MVP

This is the second MVP iteration of Dárkomat.

## Stack

- PHP 8.3
- Apache
- PostgreSQL
- PDO_PGSQL
- Vanilla HTML/CSS
- Docker
- Render

There are no Composer dependencies yet. The code remains intentionally small and easy to learn.

## Deploy to Render

The repository contains `render.yaml`, which describes:

- one Free Docker Web Service
- one Free Render Postgres database
- `DATABASE_URL` wired automatically from the database to the web service

### Important Render limitation

Render's Free Postgres is intended for testing/hobby projects. As of September 2026, a Free Postgres database expires 30 days after creation and has 1 GB storage. It has no backups. Free web services also have usage limits and their local filesystem is ephemeral.

This MVP stores uploaded gift images as PostgreSQL `BYTEA` data specifically so the image survives web-service redeploys. That is acceptable for a prototype, but for a real production application images should move to object storage.

## Local Docker run

You can run the same image locally if you have Docker and a PostgreSQL database:

```bash
docker build -t darkomat .
docker run --rm -p 8080:80 \
  -e DATABASE_URL='postgresql://user:password@host:5432/darkomat' \
  darkomat
```

## Functional flow

1. Register account A.
2. Create a group.
3. Copy the invite code.
4. Register account B in another browser/private window.
5. Join the group.
6. Account A creates a gift.
7. Account B reserves it.
8. Account A still sees the gift as available.
9. Account B sees it as reserved.

The reservation hiding rule is enforced in the dashboard query by only exposing reservation state to members other than the gift owner.

## Before production

Add CSRF protection, secure cookies, password reset/email verification, rate limiting, better authorization tests, image processing, object storage, database migrations, structured logging, privacy/account deletion flows, and a proper application framework (Laravel would be a natural next step).
