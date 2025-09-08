#!/usr/bin/env bash
set -euo pipefail

# First-time deploy helper for Hostinger (or similar shared hosting)
# Assumes repo already cloned and this script run from repo root.

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

echo "[deploy-first] Working dir: $ROOT_DIR"

if [ ! -f .env ]; then
  echo "[deploy-first] No .env found. Creating from .env.production.example (edit it with real credentials)."
  cp .env.production.example .env
fi

echo "[deploy-first] Installing composer dependencies (no-dev)"
composer install --no-dev --optimize-autoloader

echo "[deploy-first] Generating APP_KEY"
php artisan key:generate --force || true

echo "[deploy-first] Running migrations"
php artisan migrate --force

echo "[deploy-first] Storage link"
php artisan storage:link || true

# Build frontend if Node is available on the server
if command -v npm >/dev/null 2>&1; then
  echo "[deploy-first] Building frontend"
  npm ci && npm run build
else
  echo "[deploy-first] Node not found. Upload public/build from local machine after running 'npm run build'."
fi

echo "[deploy-first] Caching config/routes/views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[deploy-first] Ensuring write permissions on storage and cache"
chmod -R 775 storage bootstrap/cache || true

# PHP limits for uploads - write a .user.ini in the public docroot (subdomain root)
DOCROOT_DIR="$ROOT_DIR/public"
echo "[deploy-first] Writing PHP limits to $DOCROOT_DIR/.user.ini"
cat > "$DOCROOT_DIR/.user.ini" <<'INI'
upload_max_filesize=100M
post_max_size=100M
memory_limit=256M
max_execution_time=120
INI

echo "[deploy-first] Done. Point the subdomain docroot to: $DOCROOT_DIR"

