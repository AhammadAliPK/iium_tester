---
title: '1.5 Mobile Navigation Drawer'
type: 'feature'
created: '2026-08-19'
status: 'draft'
review_loop_iteration: 0
context:
  - {project-root}/_bmad-output/implementation-artifacts/epic-1-context.md
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** On phones the sidebar degrades to a horizontal scroll strip — cramped, undiscoverable, and there's no toggle at all (NFR-1 demands real phone usability).

**Approach:** Replace the strip fallback with a proper off-canvas drawer: a hamburger toggle in the header (visible only below the 900px breakpoint) opens the sidebar as an overlay with backdrop. State is a tiny Alpine store shared by header and sidebar. Desktop (≥900px) is untouched.

## Boundaries & Constraints

**Always:**
- Alpine only for interactivity (on-stack via Livewire; AD-1 still bans JS-framework frontends). Registered via `alpine:init` inside the sidebar component's script.
- Drawer exists only below 900px (CSS media query); at/above the breakpoint the sidebar renders exactly as today and the toggle is hidden.
- Close paths: toggle tap, backdrop tap, Escape key, and nav-link tap.
- Toggle: `aria-expanded` reflects state, `aria-label="Toggle navigation"`, rendered only for authenticated users.
- Role gating unchanged — drawer shows the same role-appropriate links (Dashboard for all; Projects/Team admin-only, absent from HTML).
- DLS tokens only: surface panel, subtle border, DLS spring easing (250ms cubic-bezier(.34,1.56,.64,1)), backdrop matches the archive modal's rgba(0,0,0,.5).
- Body scroll locked while the drawer is open (class toggle on `<html>`).

**Ask First:**
- Swipe gestures, persisting drawer state across pages, or a desktop icon-collapse mode.

**Never:**
- No new npm dependencies, no React/Vue, no Livewire round-trips for open/close (pure client state).
- No route or nav-item changes.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Phone, drawer closed | viewport < 900px | Sidebar off-canvas; hamburger toggle visible in header | N/A |
| Toggle tap | drawer closed | Drawer slides in with backdrop; body scroll locked; aria-expanded=true | N/A |
| Backdrop / Escape / toggle | drawer open | Drawer closes; scroll unlocked; aria-expanded=false | N/A |
| Nav link tap | drawer open | Drawer closes and navigation proceeds | N/A |
| Desktop | viewport ≥ 900px | Static sidebar as today; no toggle visible | N/A |
| Non-admin phone | role != admin | Drawer contains Dashboard only | N/A |

</frozen-after-approval>

## Code Map

- `resources/views/components/iium-sidebar.blade.php` -- modify: replace the `@media (max-width: 900px)` horizontal-strip block with fixed off-canvas drawer styles + backdrop; register the Alpine store; add close handlers (`@click` on links, `@keydown.escape.window`, backdrop click); body-scroll lock class
- `resources/views/components/iium-header.blade.php` -- modify: hamburger button inside the existing `@auth` block, `$store.nav` toggle binding, hidden ≥900px via the same breakpoint media query
- `tests/Feature/SidebarNavigationTest.php` -- extend: toggle present + aria attrs for authed users, absent for guests, drawer + backdrop markup present, admin-only links still absent for non-admins

## Tasks & Acceptance

**Execution:**
- [ ] `resources/views/components/iium-sidebar.blade.php` -- drawer styles, Alpine store, backdrop, all four close paths, scroll lock -- the story's core deliverable
- [ ] `resources/views/components/iium-header.blade.php` -- hamburger toggle, @auth-gated, hidden on desktop
- [ ] `tests/Feature/SidebarNavigationTest.php` -- matrix coverage (markup-level; open/close animation is client-side, asserted via bindings present)

**Acceptance Criteria:**
- Given a phone-width viewport, when tapping the header toggle, then the sidebar opens as an overlay drawer with backdrop and locked body scroll.
- Given an open drawer, when tapping the toggle, backdrop, Escape, or a nav link, then the drawer closes.
- Given a desktop viewport, then the sidebar is static and no toggle is visible.
- Given a non-Admin on a phone, then the drawer offers Dashboard only.

## Spec Change Log

## Design Notes

- Alpine store over Livewire state: open/close must feel instant and never touch the server; header and sidebar are separate Blade components, so `$store.nav` is the lightest shared-state mechanism on the stack.
- The drawer reuses the existing `sidebar-nav-item` styles for links — only the container and positioning change per breakpoint, so active states and gating stay single-source.
- Store registration lives in the sidebar's script tag: the sidebar and toggle both render only `@auth`, so the store always exists before the toggle binds.

## Verification

**Commands:**
- `php artisan test --filter SidebarNavigationTest` -- expected: all rows pass
- `php artisan test` -- expected: full suite green
- `npm run build` -- expected: builds cleanly

**Manual checks (if no CLI):**
- Phone width: toggle opens/closes drawer via all four paths; backdrop visible; page behind doesn't scroll. Desktop: unchanged. Non-admin phone: Dashboard link only.
