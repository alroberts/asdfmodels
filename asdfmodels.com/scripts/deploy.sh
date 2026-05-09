#!/usr/bin/env bash

set -euo pipefail

usage() {
  cat <<'EOF'
Usage: ./scripts/deploy.sh [options]

Options:
  --dry-run            Show rsync changes and remote commands without applying them
  --migrate            Run php artisan migrate --force after sync
  --prune-remote       Allow rsync to delete remote files that do not exist locally
  --skip-build         Skip local npm run build
  --allow-dirty        Allow deployment from a dirty git working tree
  --skip-composer      Skip remote composer install
  --skip-cache-rebuild Skip remote Laravel cache clear/rebuild commands
  --help               Show this help
EOF
}

log() {
  printf '[deploy] %s\n' "$*"
}

fail() {
  printf '[deploy] ERROR: %s\n' "$*" >&2
  exit 1
}

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
KEY_PATH_DEFAULT="$ROOT_DIR/../../Helper Files/asdfmod1_asdfmodels_com"
KEY_PATH="${DEPLOY_KEY_PATH:-$KEY_PATH_DEFAULT}"
REMOTE_HOST="${DEPLOY_REMOTE_HOST:-asdfmod1@eu.alr-network.co.uk}"
REMOTE_PATH="${DEPLOY_REMOTE_PATH:-~/domains/asdfmodels.com}"
EXCLUDE_FILE="$ROOT_DIR/.rsync-exclude"

DRY_RUN=0
RUN_MIGRATIONS=0
SKIP_BUILD=0
ALLOW_DIRTY=0
SKIP_COMPOSER=0
SKIP_CACHE_REBUILD=0
PRUNE_REMOTE=0

while (($# > 0)); do
  case "$1" in
    --dry-run)
      DRY_RUN=1
      ;;
    --migrate)
      RUN_MIGRATIONS=1
      ;;
    --skip-build)
      SKIP_BUILD=1
      ;;
    --prune-remote)
      PRUNE_REMOTE=1
      ;;
    --allow-dirty)
      ALLOW_DIRTY=1
      ;;
    --skip-composer)
      SKIP_COMPOSER=1
      ;;
    --skip-cache-rebuild)
      SKIP_CACHE_REBUILD=1
      ;;
    --help)
      usage
      exit 0
      ;;
    *)
      fail "Unknown option: $1"
      ;;
  esac
  shift
done

command -v git >/dev/null 2>&1 || fail "git is required"
command -v ssh >/dev/null 2>&1 || fail "ssh is required"
command -v rsync >/dev/null 2>&1 || fail "rsync is required"
command -v php >/dev/null 2>&1 || fail "php is required locally"

[[ -f "$EXCLUDE_FILE" ]] || fail "Exclude file not found: $EXCLUDE_FILE"
[[ -f "$KEY_PATH" ]] || fail "SSH key not found: $KEY_PATH"

cd "$ROOT_DIR"

git rev-parse --is-inside-work-tree >/dev/null 2>&1 || fail "This script must be run from a git checkout"

if [[ $ALLOW_DIRTY -ne 1 ]] && [[ -n "$(git status --short)" ]]; then
  fail "Working tree is dirty. Commit or stash changes, or pass --allow-dirty."
fi

if [[ $SKIP_BUILD -ne 1 ]]; then
  command -v npm >/dev/null 2>&1 || fail "npm is required for local builds"
  [[ -f package.json ]] || fail "package.json not found"
  log "Building frontend assets locally"
  npm run build
fi

SSH_CMD=(ssh -i "$KEY_PATH" -o BatchMode=yes)

log "Checking remote access"
"${SSH_CMD[@]}" "$REMOTE_HOST" "test -d $REMOTE_PATH"

RSYNC_ARGS=(
  -az
  --human-readable
  --itemize-changes
  --exclude-from="$EXCLUDE_FILE"
  -e "ssh -i '$KEY_PATH' -o BatchMode=yes"
)

if [[ $PRUNE_REMOTE -eq 1 ]]; then
  RSYNC_ARGS+=(--delete)
fi

if [[ $DRY_RUN -eq 1 ]]; then
  RSYNC_ARGS+=(--dry-run)
fi

log "Syncing repository to $REMOTE_HOST:$REMOTE_PATH"
rsync "${RSYNC_ARGS[@]}" "$ROOT_DIR/" "$REMOTE_HOST:$REMOTE_PATH/"

PUBLIC_RSYNC_ARGS=(
  -az
  --human-readable
  --itemize-changes
  --exclude='.DS_Store'
  --exclude='storage'
  -e "ssh -i '$KEY_PATH' -o BatchMode=yes"
)

if [[ $PRUNE_REMOTE -eq 1 ]]; then
  PUBLIC_RSYNC_ARGS+=(--delete)
fi

if [[ $DRY_RUN -eq 1 ]]; then
  PUBLIC_RSYNC_ARGS+=(--dry-run)
fi

log "Syncing public webroot to $REMOTE_HOST:$REMOTE_PATH/public_html"
rsync \
  "${PUBLIC_RSYNC_ARGS[@]}" \
  "$ROOT_DIR/public/" \
  "$REMOTE_HOST:$REMOTE_PATH/public_html/"

declare -a REMOTE_STEPS=()

if [[ $SKIP_COMPOSER -ne 1 ]]; then
  REMOTE_STEPS+=("composer install --no-dev --optimize-autoloader --no-interaction")
fi

if [[ $SKIP_CACHE_REBUILD -ne 1 ]]; then
  REMOTE_STEPS+=("find public_html -type d -exec chmod 755 {} +")
  REMOTE_STEPS+=("find public_html -type f -exec chmod 644 {} +")
  REMOTE_STEPS+=("php artisan optimize:clear")
  REMOTE_STEPS+=("php artisan view:cache")
fi

if [[ $RUN_MIGRATIONS -eq 1 ]]; then
  REMOTE_STEPS+=("php artisan migrate --force")
fi

REMOTE_STEP_COUNT=${#REMOTE_STEPS[@]}

REMOTE_SCRIPT="cd $REMOTE_PATH"
if [[ $REMOTE_STEP_COUNT -gt 0 ]]; then
  for step in "${REMOTE_STEPS[@]}"; do
    REMOTE_SCRIPT="$REMOTE_SCRIPT && $step"
  done
fi

if [[ $REMOTE_STEP_COUNT -eq 0 ]]; then
  log "No remote post-deploy steps requested"
  exit 0
fi

if [[ $DRY_RUN -eq 1 ]]; then
  log "Dry run complete. Remote commands that would run:"
  printf '%s\n' "$REMOTE_SCRIPT"
  exit 0
fi

log "Running remote post-deploy commands"
"${SSH_CMD[@]}" "$REMOTE_HOST" "$REMOTE_SCRIPT"

log "Deploy complete"
