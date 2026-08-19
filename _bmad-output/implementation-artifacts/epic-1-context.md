# Epic 1 Context: Platform Foundation & Team Access

<!-- Compiled from planning artifacts. Edit freely. Regenerate with compile-epic-context if planning docs change. -->

## Goal

Establish the technical and access-control foundation for iium_tester. This epic delivers a branded Laravel application with session authentication, the IIUM Digital design language integrated via Tailwind, and the core user/project/role model with permission enforcement. It enables Admins to organize testing work into Projects, onboard users with Roles, assign them to Projects, and ensure every capability is gated by role-based permissions. All subsequent epics depend on this foundation—test authoring, execution, reporting, and verification all assume authenticated users, organized Projects, and enforced permissions.

## Stories

- Story 1.1: Branded Laravel Application Shell
- Story 1.2: Project Creation & Management
- Story 1.3: Team Management with Global Roles
- Story 1.4: Role-Based Permission Enforcement
- Story 1.5: Mobile Navigation Drawer

## Requirements & Constraints

### Functional Requirements (from PRD §4.1)

**FR-1: Project Creation**
- Only Admins see project-create/rename/archive controls; non-admins see no such controls (not merely disabled)
- Archived projects become read-only and disappear from active lists

**FR-2: User Management**
- Admin adds users with a Role and assigns them to any number of Projects
- Users with no Project assignment can log in but see no Projects
- Removing a user from a Project revokes access immediately; their historical Status History entries remain attributed to them (users are never hard-deleted)

**FR-3: Role-Based Permissions**
- Permission matrix enforced: Admin (projects, users, edit approvals), Business User (author/modify Test Cases, run Test Suites, submit Reports, Re-run, declare Done), Tester (run Test Suites, update Statuses), Developer (view submitted Reports, mark Fixed, Private Runs)
- Every capability has exactly one owning role list; permission failures return explicit "not permitted for your role" message

### Non-Functional Requirements (from PRD §5)

**Stack Constraints (user-locked)**
- PHP Laravel 13.x, Blade server-rendered monolith—no React/Vue SPA, no JavaScript framework frontend
- Standard Laravel session authentication (httpOnly cookie); JWT and API-first auth explicitly rejected
- Tailwind CSS for styling; IIUM Digital DLS brand tokens implemented as Tailwind theme extensions

**Data Integrity**
- Users are never hard-deleted—removal from a Project preserves attributed Status History

## Technical Decisions

### Architecture Paradigm (from ARCHITECTURE-SPINE §Design Paradigm)

**Server-rendered MVC monolith with Livewire 3 islands.** One Laravel 13 deployable; every page is a Blade view. Auth is Laravel session (httpOnly cookie) exclusively; role checks ride the session user. No JavaScript-framework frontend code enters the tree (AD-1).

**Permissions are named gates, applied at routes (AD-5).** One Gate (or Policy) per capability, defined centrally, named after the permission matrix. Route middleware applies gates; Livewire components enforce the same named gates on mount and every action. A failed check returns the explicit "not permitted for your role" message.

**Styling comes from IIUM DLS tokens only (AD-6).** Brand tokens (--iium-turquoise, --iium-gold, surfaces, typography, radii, motion) are defined once as Tailwind theme extensions from the DLS and consumed via shared Blade components (card, button, form control, brand label, theme toggle). No raw hex or font names inside views/Livewire templates. Light is default; dark rides the [data-theme="dark"] attribute with persistence via localStorage key iium-theme.

### Data Model (from ARCHITECTURE-SPINE §Structural Seed)

```
users ||--o{ memberships : "assigned via"
projects ||--o{ memberships : "staffed by"
```

**Eloquent Models (singular, per PRD glossary)**
- `User` — id, name, email, password, role (enum: admin, business_user, tester, developer)
- `Project` — id, name, archived_at (nullable), created_at, updated_at
- `Membership` — pivot: user_id, project_id, created_at

**Naming Conventions (from ARCHITECTURE-SPINE §Consistency Conventions)**
- bigint surrogate ids
- string-backed PHP enums for states (UserRole enum with values: admin, business_user, tester, developer)
- timestamps via Eloquent defaults (UTC)
- Routes kebab-case plural resources (/projects/{project})

**Persistence Portable (AD-8)**
- Migrations use portable column types; MySQL 8.x is primary target, SQLite remains viable for tests and local runs

### Tech Stack (from ARCHITECTURE-SPINE §Stack)

- PHP ≥ 8.3
- Laravel 13.x
- Livewire 4.x (official Laravel 13 starter kit — spine amended 2026-08-19)
- Tailwind CSS 4.x (DLS tokens as theme extensions)
- MySQL 8.x (MAMP local)

## UX & Interaction Patterns

### IIUM Digital Design Language System (from ux-designs/iium_digital_dls.html)

**Brand Tokens (Light Theme — Default)**
- Colors: turquoise #00928F (PANTONE 7716 C), gold #D59F0F (PANTONE 7555 C)
- Surfaces: deep #FFFFFF, surface #F8FAFC, raised #FFFFFF
- Borders: subtle-turquoise (rgba(0, 146, 143, 0.15)), active-gold (rgba(213, 159, 15, 0.3))
- Text: primary #1A202C, secondary #4A5568, muted #718096, accent gold

**Dark Theme (Opt-in)**
- Surfaces: deep #030F0D, surface #081D1A, raised #0F2B26
- Adjusted borders and text states
- Applied via data-theme="dark" attribute

**Typography**
- Roboto Slab (headings) — substitute for Rockwell
- Inter (UI/body)
- Barlow Condensed (uppercase labels, buttons, small caps)

**Motion & Radius**
- Radius: lg 14px, sm 8px
- Ease: cubic-bezier(.34, 1.56, .64, 1)
- Duration: 250ms

**Core Components (from DLS)**
- `iium-card`: raised background, subtle border, radius-lg, 32px padding, spring hover (translateY -4px, gold border, turquoise shadow); dark variant lifts -8px with deep shadow
- Buttons: Barlow Condensed uppercase, 0.05em tracking, radius-sm; primary (turquoise, hover #007575 with translateY -2px), gold (gold background, black text, hover #b88a0a), outline (subtle border variant)
- Form controls: glass background, subtle border, radius-sm; focus = teal border with 3px rgba(0,146,143,.1) ring
- Brand labels: 12px gold uppercase condensed

**Application Shell (Story 1.1)**
- Sticky header: backdrop blur, IIUM wordmark in heading font with gold accent span, theme toggle on right
- Theme toggle: persists choice to localStorage key iium-theme, swaps moon/sun icons, applies on load
- Container: max-width 1200px with 40px side padding
- Footer: centered, 11px uppercase, 2.5px letter-spacing, muted color

**Responsive (NFR-1)**
- Fully responsive on laptop, tablet, and phone browsers
- Tick screen usability (applies in Epic 3): at most 3 interactions per case verdict, no page reload between cases, every tick autosaved within 2 seconds

## Cross-Story Dependencies

**Within Epic 1**
- Story 1.1 (Branded Laravel Application Shell) must complete before Story 1.2 (Project Creation & Management) can build Admin controls
- Story 1.3 (Team Management) depends on Story 1.2 (Projects exist) and Story 1.4 (Roles defined)
- Story 1.4 (Role-Based Permission Enforcement) depends on Story 1.3 (Users and Roles exist) to define gates

**Future Epic Dependencies**
- Epic 1 completion (Projects, Users, Roles, Permissions) is a hard prerequisite for Epic 2 (Test Case Authoring—Business Users author within assigned Projects)
- Epic 1 permissions gates all subsequent epics: Epic 3 (Test Execution), Epic 4 (Review Cycle), Epic 5 (Developer Verification) all assume role-based access control
- IIUM DLS integration from Story 1.1 is used across all epics for consistent styling

**No External Dependencies**
- No external integrations, CI hooks, or data migrations in scope (PRD Non-Goals)
- Single organization, no multi-tenant SaaS concerns