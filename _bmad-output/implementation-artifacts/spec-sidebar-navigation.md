---
title: 'Sidebar Navigation (App Shell)'
type: 'feature'
created: '2026-08-19'
status: 'in-review'
review_loop_iteration: 0
baseline_commit: '47a0b72d48ce26d9c005b24540affcd5a87ff96a'
context:
  - {project-root}/_bmad-output/implementation-artifacts/epic-1-context.md
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Navigation is a dead end — every page is reached by URL, and the left half of wide screens is unused whitespace.

**Approach:** Add a DLS-styled sidebar to the app shell below the header: role-aware nav links (Dashboard for all; Projects and Team for admins) with active-state highlighting. Composed entirely from existing DLS tokens (the DLS document defines no sidebar pattern), responsive down to phone where it collapses into a horizontal nav strip.

## Boundaries & Constraints

**Always:**
- Sidebar lives in `layouts/app.blade.php` between header and footer, beside page content; header/footer stay full-width.
- All styling from DLS tokens only (AD-6): surface-base background, subtle-turquoise border, Barlow Condensed uppercase labels, gold active accent. No raw hex.
- Nav items gate by role: `Team` and standalone `Projects` link render for admins only (`@can('admin')`) — absent, not disabled, for others.
- Active state derived from `request()->routeIs(...)` — server-rendered, no JS.
- Responsive: below 900px the sidebar becomes a horizontal scrollable nav strip (pure CSS media query, no JS framework code).
- Existing page containers (`x-iium-container` inside pages) unchanged — sidebar shrinks the content area, not the 1200px container.

**Ask First:**
- Adding nav items for routes that don't exist yet (Epic 2+).
- Any JS-driven collapsible behavior (v1 is CSS-only).

**Never:**
- No JavaScript framework frontend code (AD-1) — CSS + Blade only.
- No changes to header/footer components beyond including the sidebar wrapper.
- No new routes.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Admin on any page | GET /dashboard | Sidebar shows Dashboard, Projects, Team; current page highlighted | N/A |
| Non-admin | role != admin | Sidebar shows Dashboard only; no Projects/Team markup in HTML | N/A |
| Phone width | viewport < 900px | Sidebar renders as horizontal nav strip above content | N/A |
| Wide screen | viewport > 1400px | Content stays centered at 1200px max; sidebar fixed-width left | N/A |

</frozen-after-approval>

## Code Map

- `resources/views/components/iium-sidebar.blade.php` -- new: nav list, @can('admin') gating, routeIs active states, DLS tokens
- `resources/views/layouts/app.blade.php` -- wrap slot in flex row with sidebar (header/footer untouched, still full-width)
- `tests/Feature/SidebarNavigationTest.php` -- new: matrix rows (admin sees links, non-admin HTML has no Team link, active-state class on current route)

## Tasks & Acceptance

**Execution:**
- [x] `resources/views/components/iium-sidebar.blade.php` -- the deliverable: token-styled nav with role gating + active states
- [x] `resources/views/layouts/app.blade.php` -- flex wrapper: sidebar + main content, media-query collapse handled in sidebar component styles
- [x] `tests/Feature/SidebarNavigationTest.php` -- matrix coverage

**Acceptance Criteria:**
- Given an Admin, when on any app page, then the sidebar shows Dashboard, Projects, and Team with the current page visually marked active.
- Given a non-Admin, when on any app page, then the sidebar contains no Projects or Team links in the rendered HTML.
- Given a phone-width viewport, then the sidebar presents as a horizontal nav strip without JS.

## Spec Change Log

## Design Notes

- The DLS defines no sidebar pattern — this composes one from existing tokens so it stays on-system: `--color-iium-surface-base` panel, `--color-iium-border-subtle` divider, `--font-condensed` uppercase labels, gold left-border + turquoise text for active.
- Sidebar is a plain non-sticky column in v1 (pages are short); sticky-with-own-scroll can come when content grows.
- Non-admins get "Dashboard" only — their projects home IS the dashboard; `/projects` and `/users` are admin surfaces.

## Verification

**Commands:**
- `php artisan test --filter SidebarNavigationTest` -- expected: all matrix rows pass
- `php artisan test` -- expected: full suite green, no regressions
- `npm run build` -- expected: builds cleanly

**Manual checks (if no CLI):**
- Admin login: sidebar on dashboard/projects/users with active state; resize to phone width → horizontal strip; non-admin login → Dashboard link only.
