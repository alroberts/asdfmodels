# Roadmap

This roadmap is intentionally practical. It reflects the current state of the codebase and the recent stabilization work.

## Phase 1: Stabilize The Platform

- Keep the local repository as the only source of truth.
- Continue using the deploy script for all changes.
- Add feature tests for:
  - login and dashboard redirect
  - model profile completion
  - photographer profile completion
  - photographer gallery upload and reorder
  - image edit and delete
  - basic messaging flow
- Review the admin area now that migration drift has been cleaned up.

## Phase 2: Finish Existing Product Surface

- Tighten profile completion rules so incomplete profiles cannot silently pass through.
- Improve the photographer portfolio and gallery management UX further.
- Review public profile visibility, nudity flags, and gallery privacy rules.
- Improve the messaging experience so it feels more like a real inbox than a bare thread list.
- Decide what the dashboard should become long term:
  - operational home page
  - feed
  - summary plus notifications

## Phase 3: Build A Real Product Backlog

- Define the actual member journey for:
  - photographers
  - models
  - admins
- Decide which of these becomes the next major feature area:
  - content/resources
  - following/favorites
  - notifications
  - improved discovery/search
  - support and feedback flows

## Suggested Immediate Sequence

1. Add core tests.
2. Validate admin flows on the live site.
3. Tighten profile completion behavior.
4. Build the next backlog slice from the planned features list.
