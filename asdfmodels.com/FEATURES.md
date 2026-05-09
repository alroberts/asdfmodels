# Features

This file is the working feature inventory for `asdfmodels.com`.

## Built

- Authentication: login, logout, registration, password reset, email verification, and two-factor login flow.
- Account types: separate model and photographer user flows.
- Model profiles: profile editing, public profile pages, and portfolio image management.
- Photographer profiles: profile editing, public profile pages, profile media, and portfolio image management.
- Photographer galleries: gallery creation, gallery settings, image upload, image editing, image deletion, and explicit image reordering.
- Model albums: album creation, editing, public display, and age-verification flow.
- Messaging: user-to-user threads and individual message views.
- Discovery: public browse pages for models and photographers.
- Verification: model verification submission plus admin review flow.
- Admin area: dashboard, verification review, settings, user management, and photographer options management.
- Platform support: legal pages, cookie consent, Turnstile protection, dynamic mail configuration, and GeoNames-backed location lookup.
- Member dashboard: role-aware post-login dashboard with quick links and summary stats.

## Partially Built

- Photographer gallery UX: now stable enough for normal use, but still deserves broader regression coverage.
- Profile completion enforcement: currently checks for profile existence more than true completeness.
- Admin-managed photographer specialties and services: feature exists, but was previously affected by migration drift and still needs follow-up validation.
- Messaging polish: core flow exists, but there is not yet a refined inbox experience or notification layer.
- Dashboard experience: now useful, but still a summary page rather than the richer feed-style home the product likely wants later.

## Planned Or Implied

These appear in project notes or product intent, but are not yet established as complete platform features:

- Rich member activity feed
- Following, favorites, or saved members
- Articles, guides, or resource center
- Support or helpdesk workflow
- Feedback collection workflow
- Stronger notifications center
- Deeper discovery and filtering

## Current Priorities

1. Keep stabilizing the existing platform surface before adding broad new features.
2. Add tests around auth, profiles, galleries, and messaging.
3. Finish the remaining gallery and profile-completion polish.
4. Convert product intent into a more deliberate roadmap.
