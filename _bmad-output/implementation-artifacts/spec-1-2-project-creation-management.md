---
title: '1.2 Project Creation & Management'
type: 'feature'
created: '2026-08-19'
status: 'in-review'
review_loop_iteration: 0
baseline_commit: 'b9873b34258dbe4f360ea58bec355691c5fbfd1c'
context:
  - {project-root}/_bmad-output/planning-artifacts/architecture/architecture-iium_tester-2026-08-19/ARCHITECTURE-SPINE.md
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Admins have no way to organize testing work — Projects (FR-1) don't exist yet.

**Approach:** Add the `projects` table and an admin-gated management surface on the dashboard: create, rename, archive. Active and archived projects are separate lists; archived is read-only. Includes the minimal role skeleton (users.role enum + `admin` gate) so admin-only enforcement is real from day one — Story 1.3 builds user management on it, 1.4 the full matrix.

## Boundaries & Constraints

**Always:**
- Admin capability via a named Gate `admin` (AD-5): route middleware AND Livewire mount/actions both check it; failures return the explicit "not permitted for your role" message.
- `UserRole` string-backed enum (`admin`, `business_user`, `tester`, `developer`), snake_case stored values (spine conventions); migration defaults the existing seeded user to `admin`, new column NOT NULL with safe default `tester` for any other rows.
- `projects`: bigint id, `name`, `archived_at` nullable, timestamps (spine ERD). Portable migration (AD-8).
- Non-admins get NO create/rename/archive controls in rendered HTML — absent, not disabled (FR-1).
- Rename shows everywhere immediately (single source of truth; no cached copies).
- Archive is one-way in this story; unarchive is out of scope.
- UI in IIUM DLS components only (cards, brand labels, buttons, form controls — AD-6); responsive.
- Routes kebab-case plural (`/projects`) per conventions.

**Ask First:**
- Anything that requires a second role to exist meaningfully (team UI is 1.3's).
- Any deviation from the `projects` schema above.

**Never:**
- No user management UI, no role editing screen (Story 1.3).
- No permission matrix beyond the `admin` gate (Story 1.4).
- No Features/TestCases/Cycles — Epics 2+.
- Archiving must not delete or mutate project data — read-only tombstone semantics.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Guest opens /projects | Unauthenticated | Redirect to /login | N/A |
| Admin creates project | Name "Student Portal" | Row created, appears in active list | N/A |
| Create with empty name | Missing name | Validation error, no row | Inline DLS error, old input kept |
| Create with duplicate name | Same name twice | Allowed (names not unique in v1) | N/A |
| Admin renames | New valid name | Updated everywhere on next render | Validation on empty |
| Non-admin views dashboard | role != admin | Active list visible, zero management controls in HTML | N/A |
| Non-admin POSTs create | role != admin | 403 with "not permitted for your role" | N/A |
| Admin archives | Click archive + confirm | Moves to archived list, gone from active | N/A |
| Mutation of archived project | Rename/archive on archived | Rejected 422/403, row unchanged | Explicit error |

</frozen-after-approval>

## Code Map

- `database/migrations/*_add_role_to_users_table.php` -- new: enum column, backfill seeded admin
- `database/migrations/*_create_projects_table.php` -- new: name, archived_at, timestamps
- `app/Enums/UserRole.php` -- new: string-backed enum
- `app/Models/Project.php` -- new: fills, casts (archived_at datetime), `isActive` scope
- `app/Models/User.php` -- add role cast + UserRole enum
- `app/Providers/AppServiceProvider.php` -- Gate::define('admin', ...) (AD-5 central definition)
- `app/Livewire/Projects/ManageProjects.php` -- new: list/create/rename/archive actions, gate on mount + actions
- `resources/views/livewire/projects/manage-projects.blade.php` -- new: DLS cards (active), archived section, create form, inline rename, archive confirm (Alpine dialog)
- `resources/views/dashboard.blade.php` -- embed ManageProjects (welcome placeholder becomes project home)
- `routes/web.php` -- `/projects` GET route (auth + can:admin) mirroring the Livewire mount
- `tests/Feature/ProjectManagementTest.php` -- new: matrix coverage

## Tasks & Acceptance

**Execution:**
- [x] `database/migrations/` -- role column (default `tester`, seeded user → `admin`) + projects table -- schema per spine
- [x] `app/Enums/UserRole.php` + `app/Models/User.php` -- UserRole enum with cast -- snake_case stored values
- [x] `app/Models/Project.php` -- model with `isActive` scope and `isArchived` helper -- active-list filtering in one place
- [x] `app/Providers/AppServiceProvider.php` -- `Gate::define('admin')` -- single central definition (AD-5)
- [x] `app/Livewire/Projects/ManageProjects.php` + view -- DLS management surface: active cards, create form, inline rename, archive confirm, archived read-only section; `authorize` on mount and every action -- the story's core deliverable
- [x] `resources/views/dashboard.blade.php` -- replace placeholder with ManageProjects -- dashboard becomes project home
- [x] `routes/web.php` -- `/projects` route (auth, can:admin) -- deep-link entry beside the dashboard embed
- [x] `tests/Feature/ProjectManagementTest.php` -- one test per matrix row -- AC evidence

**Acceptance Criteria:**
- Given an Admin on the dashboard, when creating a Project with a name, then it appears in the active project list.
- Given an existing Project, when renamed, then the new name shows everywhere immediately.
- Given an active Project, when archived, then it disappears from active lists, appears in the archived section, and rejects further mutation.
- Given a non-Admin, when viewing the dashboard, then no create/rename/archive controls exist in the rendered HTML; direct POST attempts return 403 with "not permitted for your role".

## Spec Change Log

## Design Notes

- Sequencing: the role column is pulled into this story (not 1.3) because AC "non-admin sees no controls" is untestable without it. 1.3 adds the user-management UI over the same enum; 1.4 expands gates to the full matrix. No rework.
- Livewire over plain forms: inline rename + archive confirm need component state; AD-5 requires Livewire actions to re-check gates anyway.
- Archived read-only enforcement lives in the component actions (reject with explicit error) — belt-and-braces with UI hiding the controls on archived cards.

## Verification

**Commands:**
- `php artisan migrate` -- expected: role + projects migrations apply cleanly on MySQL and SQLite
- `php artisan test --filter ProjectManagementTest` -- expected: all matrix rows pass
- `php artisan test` -- expected: full suite green, no regressions
- `npm run build` -- expected: builds (new view classes picked up)

**Manual checks (if no CLI):**
- Login as seeded admin: create/rename/archive flow on dashboard; verify archived section; verify a second (non-admin) user sees a control-free list.
