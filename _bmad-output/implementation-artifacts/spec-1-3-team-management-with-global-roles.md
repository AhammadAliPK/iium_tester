---
title: '1.3 Team Management with Global Roles'
type: 'feature'
created: '2026-08-19'
status: 'in-progress'
review_loop_iteration: 0
baseline_commit: '47a0b72d48ce26d9c005b24540affcd5a87ff96a'
context:
  - {project-root}/_bmad-output/implementation-artifacts/epic-1-context.md
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Only a seeded admin exists — Admins can't onboard the team (FR-2), and project access is all-or-nothing because memberships don't exist yet.

**Approach:** Add the `memberships` pivot and an admin-gated team page (`/users`): create users (name, email, password, role), change roles, assign/remove users across active projects. Non-admin dashboards switch from "all projects" to "assigned projects only", with an empty state for zero assignments. Users are never deleted — removal from a project drops only the membership row.

## Boundaries & Constraints

**Always:**
- `memberships`: user_id, project_id, created_at; unique(user_id, project_id) (spine ERD). Portable migration (AD-8).
- Admin capability via Gate `admin` (AD-5): `/users` route middleware AND every ManageTeam action re-check it; failures return 403 with the explicit "not permitted for your role" message.
- Created users get `email_verified_at = now()` (admin-verified identity) — the `verified` middleware would otherwise redirect them to the verification notice and break the login AC.
- Membership revocation is immediate (query-time, no caching of access).
- Non-admins get NO team-management controls in rendered HTML — absent, not disabled.
- Admin may not change their own role (self-lockout guard); explicit error on attempt.
- UI in IIUM DLS components only; responsive. Route kebab-case plural (`/users`).

**Ask First:**
- Editing a user's name/email after creation.
- Any password-reset or invitation-email flow (v1: admin sets the initial password directly).
- Any deviation from the membership schema above.

**Never:**
- No user deletion or deactivation UI (users are never hard-deleted — data integrity NFR).
- No permission matrix beyond the `admin` gate (Story 1.4).
- No changes to project create/rename/archive behavior (Story 1.2 surface).
- Assignment to archived projects must not be offered.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Admin creates user | name/email/password + role + 2 projects | User row + memberships; appears in team list | N/A |
| Create with duplicate email | existing email | Validation error, no row | Inline DLS error |
| Create with missing fields | empty name/email/password | Validation errors, no row | Inline DLS errors |
| Added user logs in (assigned) | correct credentials | Dashboard lists ONLY their assigned active projects | N/A |
| Added user logs in (unassigned) | zero memberships | Dashboard empty state, no projects listed | N/A |
| Admin removes user from project | click remove | Membership row gone; user record intact; project gone from that user's dashboard | N/A |
| Admin changes role | Tester → Business User | Reflected in team list immediately | N/A |
| Admin changes own role | self-demotion attempt | Rejected, explicit error, role unchanged | N/A |
| Non-admin opens /users | role != admin | 403 "not permitted for your role" | N/A |
| Non-admin posts ManageTeam action | role != admin | 403, no row created | N/A |

</frozen-after-approval>

## Code Map

- `database/migrations/*_create_memberships_table.php` -- new: user_id, project_id, created_at, unique index
- `app/Models/Membership.php` -- new: pivot model (fillable user_id/project_id, created_at cast)
- `app/Models/User.php` -- add `memberships()` hasMany + `projects()` belongsToMany via Membership (reuse existing role cast)
- `app/Models/Project.php` -- add `memberships()` hasMany (inverse; existing scopes untouched)
- `app/Livewire/Team/ManageTeam.php` -- new: user list, create form, role change, project assignment sync; `$this->authorize('admin')` on every action (pattern: `app/Livewire/Projects/ManageProjects.php`)
- `resources/views/livewire/team/manage-team.blade.php` -- new: DLS create card + user cards (role select, active-project checkboxes, remove buttons)
- `app/Livewire/Projects/ManageProjects.php` -- modify `getActiveProjectsProperty()`: admin sees all active, non-admin sees assigned-only via membership join; adjust empty-state text
- `resources/views/dashboard.blade.php` -- admin-only link to `/users` beside the Projects heading
- `routes/web.php` -- `/users` GET (auth, verified, can:admin) → ManageTeam
- `tests/Feature/TeamManagementTest.php` -- new: one test per matrix row

## Tasks & Acceptance

**Execution:**
- [ ] `database/migrations/` + `app/Models/Membership.php` -- memberships table + pivot model -- spine ERD, unique guard against double-assign
- [ ] `app/Models/User.php` + `app/Models/Project.php` -- membership relations on both sides -- assignment queries live on the models
- [ ] `app/Livewire/Team/ManageTeam.php` + view -- the story's core deliverable: create user (validated, hashed via model cast), role change with self-lockout guard, project assignment via `sync()` on active projects only
- [ ] `app/Livewire/Projects/ManageProjects.php` -- assigned-only project list for non-admins + empty state -- FR-2 visibility rules
- [ ] `resources/views/dashboard.blade.php` + `routes/web.php` -- team entry point for admins, `/users` route with can:admin
- [ ] `tests/Feature/TeamManagementTest.php` -- matrix coverage, incl. Livewire-action 403s and assertDontSee on control markup (pattern: `tests/Feature/ProjectManagementTest.php`)

**Acceptance Criteria:**
- Given an Admin on `/users`, when creating a user with a role and projects, then the user appears in the team list with those assignments.
- Given an added user with assignments, when they log in, then the dashboard lists only their assigned active projects.
- Given an added user with no assignments, when they log in, then the dashboard shows an empty state and no projects.
- Given an Admin removing a user from a project, then the membership is revoked immediately and the user record is retained.
- Given a non-Admin, then `/users` and all ManageTeam actions return 403 with "not permitted for your role", and no team controls exist in their rendered HTML.

## Spec Change Log

## Design Notes

- `email_verified_at = now()` at creation: routes sit behind `['auth', 'verified']`; `MustVerifyEmail` is not implemented, so unverified users would be stranded on the verification-notice redirect. Internal tool → admin-verified identity is the honest model.
- Non-admin dashboard change is an intentional evolution of 1.2's "non-admin sees the active list" — memberships now exist, so the list becomes assigned-only. Admins still see all projects (they manage them).
- `sync()` on an array of active project IDs handles both assign and remove in one action; archived projects are filtered out of the checkbox list, and `sync` input is intersected with active-project IDs server-side so archived ones can't sneak in via a forged request.
- Self-lockout guard: compare `$user->id === auth()->id()` before a role change — one admin, single org, losing the only admin means editing the DB by hand.

## Verification

**Commands:**
- `php artisan migrate` -- expected: memberships table applies cleanly on MySQL and SQLite
- `php artisan test --filter TeamManagementTest` -- expected: all matrix rows pass
- `php artisan test` -- expected: full suite green, no 1.1/1.2 regressions (note: the 1.2 non-admin test sees all projects; update it to the assigned-only contract)
- `npm run build` -- expected: builds cleanly

**Manual checks (if no CLI):**
- As seeded admin: create a Tester with 2 projects, log in as that user, verify only those 2 appear; remove one assignment, reload, verify it disappears; verify the user still exists in the team list.
