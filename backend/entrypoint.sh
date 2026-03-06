#!/bin/sh
set -e

echo "[entrypoint] Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT}..."
until pg_isready -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USERNAME}" -q; do
  echo "[entrypoint] PostgreSQL not ready yet — retrying in 2s..."
  sleep 2
done
echo "[entrypoint] PostgreSQL is ready."

echo "[entrypoint] Running database migrations..."
php artisan migrate --force

echo "[entrypoint] Running database seeders..."
php artisan db:seed --force

echo "[entrypoint] Starting Laravel development server on 0.0.0.0:8000..."
exec php artisan serve --host=0.0.0.0 --port=8000
