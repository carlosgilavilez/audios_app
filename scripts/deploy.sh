#!/usr/bin/env bash
set -euo pipefail

# Simple deploy helper for production via SSH.
# Usage:
#   chmod +x scripts/deploy.sh
#   ./scripts/deploy.sh

echo "-- Deploy: $(date -Is) --"
echo "Dir : $(pwd)"

branch=${1:-main}

echo "1) Git pull (branch: ${branch})"
git fetch --all
if ! git rev-parse --verify "origin/${branch}" >/dev/null 2>&1; then
  echo "Error: remote branch origin/${branch} not found" >&2
  exit 1
fi

if ! git diff --quiet || ! git diff --cached --quiet; then
  echo "Working tree not clean. Stashing local changes..."
  git stash -u || true
fi

git pull --rebase origin "${branch}"

echo "1b) Ensure broadcasting uses Pusher (env)"
if [ -f .env ]; then
  if grep -q '^BROADCAST_CONNECTION=' .env; then
    sed -i.bak 's/^BROADCAST_CONNECTION=.*/BROADCAST_CONNECTION=pusher/' .env || true
  else
    printf '\nBROADCAST_CONNECTION=pusher\n' >> .env
  fi
  if grep -q '^BROADCAST_DRIVER=' .env; then
    sed -i.bak 's/^BROADCAST_DRIVER=.*/BROADCAST_DRIVER=null/' .env || true
  fi
else
  echo "Warning: .env not found; skipping broadcast env adjustments" >&2
fi

echo "2) Composer install (no-dev, optimized)"
if command -v composer >/dev/null 2>&1; then
  composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
else
  echo "Warning: composer not found in PATH" >&2
fi

echo "3) Laravel migrations"
php artisan migrate --force

echo "4) Clear + cache config/routes/views"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true

echo "5) Vite build (if Node is available)"
if command -v node >/dev/null 2>&1; then
  if [ -f package-lock.json ]; then
    npm ci
  else
    npm install
  fi
  npm run build
else
  echo "Node not found. Skipping asset build. Ensure public/build is present."
fi

echo "6) Permissions for storage + cache (if needed)"
if [ -d storage ] && [ -d bootstrap/cache ]; then
  chmod -R ug+rwx storage bootstrap/cache || true
  # Uncomment and adjust the user/group for your web server if needed:
  # chown -R www-data:www-data storage bootstrap/cache || true
fi

echo "OK ✅ Deploy complete"
