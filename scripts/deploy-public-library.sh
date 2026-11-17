#!/usr/bin/env bash
set -euo pipefail

# Deploy the reviewed correction through the project's existing Git workflow.
# No migrations, dependency changes, credentials or audio-file changes.
TARGET="${1:-}"
MODE="${2:-apply}"
ROOT="$(realpath "${PALMA_LARAVEL_ROOT:?Set the verified application root}")"
BACKUP_ROOT="$(realpath "${PALMA_DEPLOY_BACKUPS:?Set an existing private backup directory}")"
case "$BACKUP_ROOT/" in "$(dirname "$ROOT")/"*) echo 'Backups must be outside the web root'; exit 2;; esac
EXPECTED_BASE="${3:-}"
[[ "$TARGET" =~ ^[0-9a-f]{40}$ ]] || { echo 'Expected a full commit SHA'; exit 2; }
[[ "$EXPECTED_BASE" =~ ^[0-9a-f]{40}$ ]] || { echo 'Expected a full baseline SHA'; exit 2; }
[[ "$MODE" == 'apply' || "$MODE" == '--check' ]] || { echo 'Unexpected mode'; exit 2; }
cd "$ROOT"
[[ "$(pwd -P)" == "$ROOT" && -f artisan && -f .env ]] || { echo 'Unexpected application root'; exit 2; }
git diff --quiet
git diff --cached --quiet
GIT_TERMINAL_PROMPT=0 git -c credential.helper= -c http.extraHeader= -c http.https://github.com/.extraHeader= fetch https://github.com/carlosgilavilez/audios_app.git main
[[ "$(git rev-parse FETCH_HEAD)" == "$TARGET" ]] || { echo 'Remote main differs from the reviewed commit'; exit 2; }
OLD_SHA="$(git rev-parse HEAD)"
if [[ "$OLD_SHA" == "$TARGET" ]]; then
  echo 'STATUS=already_deployed'
  exit 0
fi
[[ "$OLD_SHA" == "$EXPECTED_BASE" ]] || { echo 'Unexpected production baseline'; exit 2; }
git merge-base --is-ancestor "$OLD_SHA" "$TARGET"

snapshot() {
  php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); if(!$app->environment("production")||($argv[1]==="before"&&$app->isDownForMaintenance()))exit(3); echo json_encode(["total"=>App\Models\Audio::count(),"states"=>App\Models\Audio::query()->selectRaw("estado, COUNT(*) as total")->groupBy("estado")->pluck("total","estado")->all()]),PHP_EOL;' "$1"
}
BEFORE="$(snapshot before)"
if [[ "$MODE" == '--check' ]]; then
  printf 'STATUS=validated\nBASE=%s\nTARGET=%s\nCOUNTS=%s\n' "$OLD_SHA" "$TARGET" "$BEFORE"
  exit 0
fi

umask 077
BACKUP="$BACKUP_ROOT/$(date -u +%Y%m%d_%H%M%S)-filters"
mkdir -p "$BACKUP"
git archive --format=tar HEAD | gzip > "$BACKUP/previous-code.tar.gz"
cp -a public/build "$BACKUP/previous-build"
sha256sum .env > "$BACKUP/environment.sha256"
printf '%s\n' "$OLD_SHA" > "$BACKUP/previous-commit.txt"
printf '%s\n' "$BEFORE" > "$BACKUP/before.json"
umask 022
MAINTENANCE=0
SWITCHED=0

finish() {
  local result=$?
  trap - EXIT
  if [[ "$result" -ne 0 && "$SWITCHED" -eq 1 ]]; then
    if git diff --quiet && git diff --cached --quiet; then
      git switch --detach "$OLD_SHA" || true
      php artisan view:clear || true
    else
      echo 'ROLLBACK_REQUIRES_REVIEW=working_tree_changed'
    fi
  fi
  if [[ "$MAINTENANCE" -eq 1 ]]; then
    php artisan up || result=1
  fi
  printf 'BACKUP=%s\nDEPLOY_EXIT=%s\n' "$BACKUP" "$result"
  exit "$result"
}
trap finish EXIT
MAINTENANCE=1
php artisan down
git merge --ff-only "$TARGET"
SWITCHED=1
# Keep previous fingerprinted assets for visitors with an older page cached.
php -r 'foreach(glob($argv[1]."/assets/*") as $file){$to="public/build/assets/".basename($file);if(is_file($file)&&!file_exists($to)&&!copy($file,$to))exit(1);}' "$BACKUP/previous-build"
php artisan view:clear
sha256sum -c "$BACKUP/environment.sha256"
AFTER="$(snapshot after)"
printf '%s\n' "$AFTER" > "$BACKUP/after.json"
[[ "$BEFORE" == "$AFTER" ]] || { echo 'Audio counts changed during deployment'; exit 1; }
[[ "$(git rev-parse HEAD)" == "$TARGET" ]]
printf 'STATUS=deployed\nCOMMIT=%s\nCOUNTS=%s\n' "$TARGET" "$AFTER"
