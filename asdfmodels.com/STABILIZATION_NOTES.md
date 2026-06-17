# Stabilization Notes

## 2026-04-29

Recent stabilization work completed:

- Added a repeatable deploy flow with `scripts/deploy.sh`.
- Added `.rsync-exclude` and `DEPLOYMENT.md`.
- Fixed the photographer portfolio upload path to generate `medium_path` again.
- Tightened gallery ownership validation during upload.
- Improved the photographer dashboard/login landing experience.
- Changed gallery reordering to explicit toggle mode instead of always-on drag sorting.

Server cleanup completed:

- Backed up server-only drift files to:
  - `/home/asdfmod1/domains/asdfmodels.com/_cleanup_backups/20260429_221402`
- Removed loose duplicate code, view, and migration files from the deployed app tree.
- Removed mislocated PHP classes that were causing Composer PSR-4 warnings on deploy.
- Reconciled migration history for tables that already existed but were still marked pending.

Migration reconciliation inserted these migration records as already run in batch `22`:

- `2025_01_28_000001_create_photographer_specialties_table`
- `2025_01_28_000002_create_photographer_services_table`
- `2025_11_25_212237_create_portfolio_images_table`
- `2025_11_25_212310_create_portfolio_albums_table`

Current server state after cleanup:

- `php artisan migrate:status` is clean with no pending migrations from the current repo file set.
- Composer autoload rebuild completes without the earlier PSR-4 warning flood.
- The deployed app root now matches the local project structure much more closely.

Remaining stabilization work:

- Add meaningful automated tests.
- Validate admin flows end to end.
- Tighten profile completion logic.
