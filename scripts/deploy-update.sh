#!/usr/bin/env bash
set -euo pipefail

# Update deploy helper: pulls latest, installs deps, migrates, rebuilds (if Node present), caches

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

echo "[deploy-update] Pulling latest from origin/main"
git fetch --all
git pull --ff-only origin main

echo "[deploy-update] Installing composer deps (no-dev)"
composer install --no-dev --optimize-autoloader

if command -v npm >/dev/null 2>&1; then
  echo "[deploy-update] Building frontend"
  npm ci && npm run build
else
  echo "[deploy-update] Skipping frontend build (npm not found)"
fi

echo "[deploy-update] Running migrations"
php artisan migrate --force

echo "[deploy-update] Refreshing caches"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[deploy-update] Done."

