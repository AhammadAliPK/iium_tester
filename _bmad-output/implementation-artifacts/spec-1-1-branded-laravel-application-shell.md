---
title: '1.1 Branded Laravel Application Shell'
type: 'feature'
created: '2026-08-19'
status: 'in-review'
review_loop_iteration: 0
baseline_commit: 'NO_VCS'
context:
  - {project-root}/_bmad-output/planning-artifacts/architecture/architecture-iium_tester-2026-08-19/ARCHITECTURE-SPINE.md
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** No application exists. We need the Laravel 13 foundation — session auth, IIUM-branded shell, Tailwind theme tokens — that every later story builds on.

**Approach:** Scaffold the official Laravel Livewire starter kit (Fortify session auth, Livewire 4, Tailwind 4, Blade), then re-skin it with the IIUM Digital DLS as Tailwind theme extensions emitting `--iium-*` custom properties, plus reusable Blade shell components (header, theme toggle, footer, container).

## Boundaries & Constraints

**Always:**
- IIUM DLS tokens defined once as Tailwind `@theme` extensions emitting `--iium-*` CSS variables (source: `ux-designs/iium_digital_dls.html`); no raw hex/fonts inside views.
- Light theme default; dark via `[data-theme="dark"]`, persisted in `localStorage` key `iium-theme`, restored on load (inline head script to avoid flash).
- Typography: Roboto Slab (headings), Inter (UI), Barlow Condensed (labels/buttons) via Google Fonts.
- Session auth only (httpOnly cookie; Fortify). Disable public registration, email verification, and 2FA (admin-created users only per FR-2).
- Laravel app lives at the product root (`app/`, `resources/`, `routes/` etc. beside `_bmad/` and `_bmad-output/`); git repo initialized covering code + planning artifacts.
- Portable migrations (AD-8): default `.env` uses SQLite for scaffold/tests; MAMP MySQL switch documented but not required this story.

**Ask First:**
- Any need to deviate from the DLS tokens or add a non-DLS color (except the status-semantic tokens reserved for later stories).
- Any dependency beyond the starter kit's defaults.

**Never:**
- No React/Vue/Inertia code, no JWT/API-token auth (AD-1).
- No Projects/Users/Roles models or migrations — Stories 1.2–1.4 own them (only the starter's `users` table ships here).
- No Flux UI re-branding effort — starter Flux internals may remain where invisible, but all visible chrome uses our DLS Blade components.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Guest hits `/dashboard` | Unauthenticated | Redirect to `/login` | N/A |
| Valid login | Correct email + password | Session set (httpOnly cookie), redirect to branded placeholder dashboard | N/A |
| Invalid login | Wrong password | Re-render login with error, branded styling | Laravel validation errors |
| Theme toggle | Click header toggle | `data-theme` swaps, `iium-theme` key saved, icons swap moon/sun | N/A |
| Theme restore | Return visit with saved theme | Saved theme applied before first paint | Missing key → light |
| No registration | GET `/register` | 404 (feature removed from Fortify config) | N/A |

</frozen-after-approval>

## Code Map

- `composer.json`, `package.json` -- starter kit manifest (Livewire 4, Tailwind 4, Fortify; do not add deps)
- `resources/css/app.css` -- Tailwind entry: add `@theme` block defining `--iium-*` tokens (colors, surfaces, borders, text states, fonts, radii, motion) + `[data-theme="dark"]` overrides
- `resources/views/layouts/` -- starter layouts exist; brand via DLS
- `resources/views/components/` -- add `iium-header.blade.php`, `iium-footer.blade.php`, `iium-theme-toggle.blade.php`, `iium-container.blade.php`, `iium-card.blade.php`, `iium-brand-label.blade.php`
- `resources/views/layouts/app.blade.php` -- rebuild shell: sticky blurred header (IIUM wordmark, Roboto Slab + gold accent span, theme toggle), 1200px container, branded footer
- `resources/views/layouts/auth.blade.php` (or starter's `auth` layout) -- login card in DLS
- `resources/views/dashboard.blade.php` -- placeholder dashboard ("Welcome to iium_tester") in shell
- `resources/views/pages/auth/login.blade.php` (starter path) -- re-skin only
- `config/fortify.php` -- remove `Features::registration()`, `emailVerification()`, `twoFactorAuthentication()`
- `app/View/Components/AppLayout.php` (or starter equivalent) -- shared layout data
- `routes/web.php` -- dashboard route already present; verify auth middleware
- `database/seeders/DatabaseSeeder.php` -- seed first admin user (role column does not exist yet — plain user; roles land in Story 1.3)
- `app/Http/Middleware/` -- none new this story
- `layout.blade.php` head -- inline theme-restore script before CSS to prevent flash
- `.gitignore` -- ensure `vendor/`, `node_modules/`, `.env` ignored; keep `_bmad-output/` tracked

## Tasks & Acceptance

**Execution:**
- [x] `.scaffold-tmp/` (temp) -- `composer global require laravel/installer`; `laravel new` selecting the Livewire starter kit, no teams; move contents into product root; `git init` + initial commit -- installers refuse non-empty dirs, root is the app home
- [x] `config/fortify.php` -- remove registration/email-verification/2FA features -- FR-2: admin-created users only
- [x] `database/seeders/DatabaseSeeder.php` -- seed one login-capable user -- enables manual auth verification
- [x] `resources/css/app.css` -- `@theme` block emitting all `--iium-*` tokens (light) + `[data-theme="dark"]` surface overrides; font imports -- AC: tokens as theme extensions
- [x] `resources/views/components/iium-*.blade.php` -- header, theme-toggle (moon/sun SVGs, localStorage `iium-theme`), container (1200px/40px), footer (11px uppercase, 2.5px tracking), card, brand-label -- reusable DLS components
- [x] `resources/views/layouts/app.blade.php` + `auth` layout -- assemble shell from components; inline theme-restore script in `<head>` -- sticky blurred header, branded auth + app pages
- [x] `resources/views/dashboard.blade.php` + login page -- re-skin placeholder dashboard and login in DLS -- AC: reachable, branded, no framework code
- [x] `README.md` (root) -- one paragraph: serve via `composer run dev` + `npm run dev`, DB default SQLite, MAMP MySQL switch env vars -- dev onboarding

**Acceptance Criteria:**
- Given a fresh install, when any page loads, then `--iium-*` custom properties are present (turquoise #00928F, gold #D59F0F, surfaces, borders, text states) with light theme default.
- Given the header toggle, when clicked, then `data-theme` swaps, persists to `localStorage['iium-theme']`, and restores on reload before first paint.
- Given valid credentials on `/login`, when submitted, then the user reaches the branded placeholder dashboard via httpOnly session cookie; `/register` returns 404; no React/Vue/Inertia packages in `composer.json`/`package.json` beyond starter Livewire stack.
- Given phone/tablet/laptop widths, when browsing, then the shell remains usable (responsive container/header).

## Spec Change Log

## Design Notes

- Scaffold command (interactive prompts: choose Livewire kit, decline teams): `laravel new .scaffold-tmp` then move. Alternative if installer unavailable: `composer create-project laravel/laravel` + `php artisan install:livewire` equivalent is NOT used — prefer the installer for the authentic kit.
- Tailwind 4 `@theme` maps token names to CSS custom properties natively (e.g. `--color-iium-turquoise: #00928F` emits `--color-iium-turquoise`); DLS variables keep their `--iium-*` names via a small `:root`/`[data-theme]` block alongside `@theme` for the non-color tokens (fonts, radii, motion).
- Flux UI ships in the starter; treat it as plumbing only — visible components are ours.
- Login uses Fortify's `/login` route with our re-skinned Blade views (Fortify renders views via `fortify()` view hooks in the starter).

## Verification

**Commands:**
- `npm run build` -- expected: Vite build succeeds, CSS contains `--iium-turquoise` and `[data-theme="dark"]`
- `php artisan test` -- expected: starter test suite green (login tests may need the registration removal reflected)
- `php artisan migrate --seed && php artisan serve` -- expected: boots; manual login with seeded user
- `composer show | grep -E "react|inertia|vue"` -- expected: no matches

**Manual checks (if no CLI):**
- Visit `/login`, `/dashboard`: sticky blurred header, IIUM wordmark (Roboto Slab + gold span), toggle flips theme and survives reload; footer centered uppercase.
