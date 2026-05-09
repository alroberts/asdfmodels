# Deployment

This project is deployed from the local repository to the server with `rsync` over SSH.

## Principles

- The local repository is the source of truth.
- Do not edit application code directly on the server.
- Keep server-only state on the server:
  - `.env`
  - `storage/`
  - uploaded files
  - runtime caches/logs
- Build frontend assets locally, then sync the result.
- Treat database migrations as an explicit action until the migration history is cleaned up.
- Do not route-cache this app until closure routes are removed from `routes/web.php`.
- Do not delete unknown remote files by default until the old server drift has been cleaned up.

## Server

- SSH host: `asdfmod1@eu.alr-network.co.uk`
- App path: `~/domains/asdfmodels.com`
- SSH key: `Helper Files/asdfmod1_asdfmodels_com`

## First-Time Checklist

1. Confirm `composer`, `php`, and `rsync` are installed locally and on the server.
2. Confirm the server already has a valid `.env`.
3. Confirm writable Laravel directories exist on the server:
   - `storage/`
   - `bootstrap/cache/`
4. Confirm user uploads live on the server and are not managed from git.

## Deploy

Dry run:

```bash
./scripts/deploy.sh --dry-run --allow-dirty
```

Normal deploy:

```bash
./scripts/deploy.sh
```

Deploy and run migrations explicitly:

```bash
./scripts/deploy.sh --migrate
```

## Flags

- `--dry-run`: show what would be synced and which remote commands would run
- `--migrate`: run `php artisan migrate --force` after sync
- `--prune-remote`: allow `rsync --delete` during sync
- `--skip-build`: skip `npm run build`
- `--allow-dirty`: allow deployment from a dirty working tree
- `--skip-composer`: skip remote `composer install`
- `--skip-cache-rebuild`: skip Laravel cache clear/rebuild commands

## What Gets Synced

The deploy script uses two sync passes:

- repository root to `~/domains/asdfmodels.com/`
- Laravel `public/` to `~/domains/asdfmodels.com/public_html/`

This avoids clobbering the server's `public -> public_html` symlink while still deploying the actual webroot files.

The root sync excludes entries in [.rsync-exclude](/Volumes/Photography%20Library/Development/ASDF%20Models/domains/asdfmodels.com/.rsync-exclude:1).

The important exclusions are:

- `.env`
- `storage/`
- `vendor/`
- `node_modules/`
- `public_html/uploads/`
- runtime logs and caches

## Notes

- Migrations are intentionally opt-in because the current server schema/migration history needs cleanup before auto-migrate is safe.
- The deploy script intentionally skips `php artisan route:cache` because the app still defines closure routes.
- Remote pruning is opt-in via `--prune-remote` because the current server still has manual drift outside the repo.
- The server currently has manual drift from the repo. This deploy flow is meant to stop that from getting worse; it does not itself reconcile old drift.
