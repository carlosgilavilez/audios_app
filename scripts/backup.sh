#!/usr/bin/env bash
set -euo pipefail

# Simple backup helper: copies .env and dumps MySQL database to storage/backups

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

TS="$(date +%Y%m%d-%H%M%S)"
BACKUP_DIR="$ROOT_DIR/storage/backups"
mkdir -p "$BACKUP_DIR"

echo "[backup] Starting backup at $TS"

# 1) Backup .env
if [ -f .env ]; then
  cp .env "$BACKUP_DIR/.env.$TS"
  echo "[backup] .env copied to $BACKUP_DIR/.env.$TS"
else
  echo "[backup] WARNING: .env not found in $ROOT_DIR"
fi

# 2) Try to dump MySQL DB using mysqldump (reads creds from .env)
if [ -f .env ]; then
  DB_HOST=$(grep -E '^DB_HOST=' .env | sed -E 's/DB_HOST=\"?([^\"]*)\"?/\1/')
  DB_PORT=$(grep -E '^DB_PORT=' .env | sed -E 's/DB_PORT=\"?([^\"]*)\"?/\1/')
  DB_NAME=$(grep -E '^DB_DATABASE=' .env | sed -E 's/DB_DATABASE=\"?([^\"]*)\"?/\1/')
  DB_USER=$(grep -E '^DB_USERNAME=' .env | sed -E 's/DB_USERNAME=\"?([^\"]*)\"?/\1/')
  DB_PASS=$(grep -E '^DB_PASSWORD=' .env | sed -E 's/DB_PASSWORD=\"?([^\"]*)\"?/\1/')

  if command -v mysqldump >/dev/null 2>&1; then
    OUT_SQL="$BACKUP_DIR/db-$DB_NAME-$TS.sql"
    echo "[backup] Dumping DB $DB_NAME to $OUT_SQL"
    mysqldump -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$OUT_SQL"
    if command -v gzip >/dev/null 2>&1; then
      gzip "$OUT_SQL"
      echo "[backup] Compressed to $OUT_SQL.gz"
    fi
  else
    echo "[backup] mysqldump not found. Skipping DB dump."
  fi
fi

# 3) (Optional) Backup public storage files
PUB_DIR="$ROOT_DIR/storage/app/public"
if [ -d "$PUB_DIR" ]; then
  TAR_OUT="$BACKUP_DIR/storage-public-$TS.tar.gz"
  echo "[backup] Archiving $PUB_DIR to $TAR_OUT"
  tar -czf "$TAR_OUT" -C "$ROOT_DIR/storage/app" public || echo "[backup] tar failed or not available"
fi

echo "[backup] Done. Files in: $BACKUP_DIR"

