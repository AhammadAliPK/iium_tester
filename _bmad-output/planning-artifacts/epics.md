---
stepsCompleted: ["step-01", "step-02", "step-03", "step-04"]
inputDocuments:
  - _bmad-output/planning-artifacts/prds/prd-iium_tester-2026-08-19/prd.md
  - _bmad-output/planning-artifacts/ux-designs/iium_digital_dls.html
---

# iium_tester - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for iium_tester, decomposing the requirements from the PRD, UX Design if it exists, and Architecture requirements into implementable stories.

## Requirements Inventory

### Functional Requirements

- FR-1: Admin can create, rename, and archive Projects; non-admins see no project controls; archived projects are read-only and hidden from active lists.
- FR-2: Admin can add users with a Role, assign them to any number of Projects, and remove them; unassigned users see no Projects; removal preserves attributed Status History.
- FR-3: Role-based permission matrix enforced (Admin, Business User, Tester, Developer); every capability has one owning role list; denials return explicit "not permitted for your role" messages.
- FR-4: Business User authors Test Cases (title, description, expected result, numbered Steps) organized into Feature Test Suites; a case cannot save with zero Steps; reordering renumbers automatically.
- FR-5: While a Cycle is Active, Test Case modifications are pending until Admin approval; each Run executes a read-only snapshot captured at Run start; approval creates a new Version; approve/reject recorded with reason.
- FR-6: Runners execute Runs marking each Test Case Pass/Fail/Blocked/Skipped; comment mandatory on Fail; screenshot attachable on Fail; interrupted Runs resume; Runs can be abandoned by Runner or Admin (recorded, stage restartable, reassignable).
- FR-7: Sequential pipeline per initial Cycle: Tester Run completes before Business User Run starts; tester results visible in the Business User's tick screen; one Runner per stage.
- FR-8: Immutable append-only Status History per Test Case: every Status change with actor, timestamp, comment, attachments; spans Versions; no edits or deletes.
- FR-9: Report auto-generated the moment a Run's last tick lands: summary (counts, pass rate), failures with evidence, per-case detail; zero manual fields.
- FR-10: Business User submits their Run's Report to the Project's Developers; in-app notification; submitted Report becomes read-only and appears in developers' reports list.
- FR-11: Developer report view is failures-first: failures on top with reporter, comment, screenshot, open/fixed status; passes collapsed to counts; failures markable Fixed, visible to the Business User (binary in v1).
- FR-12: Any Report exportable as PDF matching on-screen content including screenshots.
- FR-13: Business User launches Re-runs: Failed-only (exact failed/blocked set from latest Cycle) or Full; a Re-run is a single Business User Run appended to the same Cycle.
- FR-14: Business User declares testing done for a Feature: records who/when, Cycle becomes Done, suite read-only; Done is terminal — a new concern opens a new Cycle.
- FR-15: Developer Private Runs over any suite in their Project: reports visible only to the executing Developer; no submission control exists on them.
- FR-16: (v1.1) Reproduced status available in Private Runs; appears in private report only.

### NonFunctional Requirements

- NFR-1: Tick screen usability (MUST): at most 3 interactions per case verdict, no page reload between cases, every tick autosaved within 2 seconds; fully responsive on laptop, tablet, and phone browsers.
- NFR-2: PDF export of reports is a hard requirement (matches on-screen content).
- NFR-3: Auditability: Edit Approval decisions and Submissions are immutable records.
- NFR-4: Data integrity: Status History is append-only; Run snapshots are immutable.
- NFR-5 (working assumptions): ~10-50 users, single organization, dozens of concurrent Runs; business-hours availability; lifetime data retention.

### Additional Requirements

- Stack decision (user, 2026-08-19): **PHP Laravel** — no full architecture document exists yet; stories carry tech-decision notes where needed.
- Auth decision (user, 2026-08-19): **Blade server-rendered monolith with standard Laravel session authentication** (httpOnly cookie; no JWT — API/SPA split explicitly not chosen).
- UI decision (user, 2026-08-19): **Tailwind CSS** — PHP ecosystem only, no React/Vue SPA. The IIUM DLS tokens (colors, fonts, radii, motion) are implemented as Tailwind theme extensions (`--iium-*` mapped into the Tailwind config) and applied through Blade components.
- Epic 1 Story 1 is a fresh Laravel application setup (framework install, auth baseline, DLS integration), not a template scaffold.
- No external integrations, CI hooks, or data migrations in scope (PRD Non-Goals).

### UX Design Requirements

Source: IIUM Digital Design Language System (`iium_digital_dls.html`) — official IIUM Visual Identity 2021.

- UX-DR1: Brand token system (light theme, default): turquoise #00928F (PANTONE 7716 C), gold #D59F0F (PANTONE 7555 C); surfaces deep #FFFFFF / surface #F8FAFC / raised #FFFFFF; borders subtle-turquoise / active-gold / glass; text primary #1A202C, secondary #4A5568, muted #718096, accent gold.
- UX-DR2: Dark theme opt-in via `[data-theme="dark"]`: deep #030F0D, surface #081D1A, raised #0F2B26, adjusted borders and text states; same brand hues.
- UX-DR3: Theme toggle component: persists choice to localStorage key `iium-theme`, swaps moon/sun icons and label, applies on load.
- UX-DR4: Typography system: Roboto Slab for headings (Rockwell substitute), Inter for UI/body, Barlow Condensed for uppercase labels, buttons, and small caps.
- UX-DR5: Radius and motion tokens: radius-lg 14px, radius-sm 8px; ease-spring cubic-bezier(.34,1.56,.64,1); duration 250ms.
- UX-DR6: `iium-card` component: raised background, subtle border, radius-lg, 32px padding, spring hover (translateY -4px, gold border, turquoise shadow); dark variant lifts -8px with deep shadow.
- UX-DR7: Button system: base button in Barlow Condensed uppercase, 0.05em tracking, radius-sm; primary (turquoise, hover #007575 with translateY -2px); gold (gold background, black text, hover #b88a0a); outline (subtle border variant).
- UX-DR8: Form controls: glass background, subtle border, radius-sm; focus = teal border with 3px rgba(0,146,143,.1) ring; field labels 11px muted; brand-label eyebrow 12px gold uppercase condensed.
- UX-DR9: App header: sticky, backdrop blur, brand wordmark in heading font with gold accent span, theme toggle on the right.
- UX-DR10: Layout system: container max-width 1200px with 40px side padding; auto-fit grid minmax(280px, 1fr) with 24px gap.
- UX-DR11: Footer pattern: centered, 11px uppercase, 2.5px letter-spacing, muted color.
- NOTE: The DLS defines no status semantics for Pass/Fail/Blocked/Skipped. Status badge stories must map statuses onto the brand system (e.g., turquoise = Pass, gold = Blocked); a danger/red addition for Fail is an open design decision to settle during UI stories.

### FR Coverage Map

- FR-1: Epic 1 — Project creation/renaming/archiving
- FR-2: Epic 1 — User management, Role assignment, multi-project membership
- FR-3: Epic 1 — Role-based permission matrix
- FR-4: Epic 2 — Test Case authoring with numbered Steps, suite organization
- FR-5: Epic 5 — Edit approval with snapshot versioning (needs Runs, hence Epic 5)
- FR-6: Epic 3 — Run execution: statuses, comments, screenshots, resume, abandon
- FR-7: Epic 3 — Sequential pipeline: Tester Run then Business User Run
- FR-8: Epic 3 — Immutable Status History
- FR-9: Epic 3 — Auto-generated Run Report at last tick
- FR-10: Epic 4 — Report submission to Developers
- FR-11: Epic 4 — Failures-first developer view, Fixed marking
- FR-12: Epic 4 — PDF export
- FR-13: Epic 4 — Re-runs: failed-only or full
- FR-14: Epic 4 — Done Declaration (terminal)
- FR-15: Epic 5 — Developer Private Runs
- FR-16: Epic 5 — Reproduced status (v1.1)
- UX-DR1–11: Epic 1 — IIUM DLS bootstrap (applied across all epics' stories)
- NFR-1: Epic 3 — tick-screen usability; NFR-2: Epic 4 — PDF; NFR-3/4: Epics 3–5 — immutability; NFR-5: global assumption

## Epic List

### Epic 1: Platform Foundation & Team Access
Laravel application bootstrapped with the IIUM Digital design language; Admin creates Projects, adds users with Roles, and permissions are enforced across the platform.
**FRs covered:** FR-1, FR-2, FR-3, UX-DR1 through UX-DR11

### Epic 2: Test Case Authoring
Business Users author the testing script book: Features with Test Suites of Test Cases structured into numbered Steps — the digital replacement for the printed pack.
**FRs covered:** FR-4

### Epic 3: Test Execution & Instant Reports
Testers and Business Users execute staged Runs on the tick screen (Tester Run, then Business User Run), recording statuses with comments and screenshots into immutable Status History; the Run Report exists the moment the last tick lands.
**FRs covered:** FR-6, FR-7, FR-8, FR-9, NFR-1

### Epic 4: The Review Cycle
Business Users submit Reports to Developers; Developers work failures-first and mark Fixed; Business Users re-run failed-only or full and declare testing Done; Reports export to PDF.
**FRs covered:** FR-10, FR-11, FR-12, FR-13, FR-14, NFR-2, NFR-3

### Epic 5: Developer Verification & Change Control
Developers verify fixes with Private Runs (reports that are never submitted); Admins approve mid-cycle Test Case edits with snapshot versioning so in-flight results stay trustworthy.
**FRs covered:** FR-15, FR-16, FR-5


## Epic 1: Platform Foundation & Team Access

Laravel application bootstrapped with the IIUM Digital design language; Admin creates Projects, adds users with Roles, and permissions are enforced across the platform.

### Story 1.1: Branded Laravel Application Shell

As a user,
I want to log into a Laravel application styled in the official IIUM design language,
So that the platform feels official and familiar from first contact.

**Acceptance Criteria:**

**Given** a fresh Laravel installation (Blade + session auth starter, Tailwind CSS configured)
**When** any page loads
**Then** the IIUM DLS tokens are implemented as Tailwind theme extensions (colors, fonts, radii, motion) that emit the `--iium-*` CSS custom properties (turquoise #00928F, gold #D59F0F, surfaces, borders, text states) with light theme as default
**And** the application shell is built as reusable Blade components: sticky blurred header with IIUM wordmark, theme toggle, branded footer, 1200px container layout
**And** the dark theme is available via the header toggle (data-theme attribute), persists across sessions in localStorage (`iium-theme`), and restores on load
**And** typography uses Roboto Slab (headings), Inter (UI), Barlow Condensed (labels/buttons)

**Given** valid credentials on the session-based login page (standard Laravel auth, httpOnly cookie — no JWT, no API tokens)
**When** I submit
**Then** I reach a placeholder dashboard page in the same design language, and no React/Vue SPA or JavaScript framework is present in the stack

### Story 1.2: Project Creation & Management

As an Admin,
I want to create, rename, and archive Projects,
So that testing work is organized per application.

**Acceptance Criteria:**

**Given** I am an Admin on the dashboard built with DLS components (cards, primary/gold/outline buttons, form controls with teal focus rings, brand labels)
**When** I create a Project with a name
**Then** it appears in my active project list

**Given** an existing Project
**When** I rename it
**Then** the new name shows everywhere immediately

**When** I archive a Project
**Then** it becomes read-only and disappears from active lists

**Given** I am not an Admin
**When** I view the dashboard
**Then** no project-create/rename/archive controls exist anywhere (not merely disabled)

### Story 1.3: Team Management with Global Roles

As an Admin,
I want to add users with a Role and assign them to any number of Projects,
So that the right people have the right access.

**Acceptance Criteria:**

**When** I add a user (name, email, password) with a Role (Admin, Business User, Tester, Developer)
**Then** they can log in and see only their assigned Projects

**Given** a user with no Project assignment
**When** they log in
**Then** they see an empty state and no projects

**When** I remove a user from a Project
**Then** their access to it is revoked immediately, and their user record is retained (never hard-deleted) so future attributed history stays intact

### Story 1.4: Role-Based Permission Enforcement

As any user,
I want the platform to enforce what my Role permits,
So that I only see and do what applies to me.

**Acceptance Criteria:**

**Given** the permission matrix (Admin: projects/users/approvals · Business User: author/run/submit/re-run/done · Tester: run/update status · Developer: view reports/mark fixed/private runs)
**When** any route is accessed
**Then** only roles on its owning list may proceed

**Given** a Tester attempting a Business User action
**When** access is denied
**Then** an explicit "not permitted for your role" message is shown

**And** every route in the application is mapped to exactly one owning role list (no unprotected or orphan routes)

### Story 1.5: Mobile Navigation Drawer

As a mobile user,
I want a slide-in navigation drawer,
So that I can navigate the platform comfortably on a phone.

**Acceptance Criteria:**

**Given** a phone-width viewport (below the sidebar breakpoint)
**When** I tap the menu toggle in the header
**Then** the sidebar opens as an overlay drawer with my role-appropriate links

**Given** the drawer is open
**When** I tap a link, the backdrop, or the toggle again
**Then** the drawer closes and navigation proceeds cleanly

**And** the desktop sidebar remains unchanged (no toggle visible at or above the breakpoint)

**Notes:** Desktop sidebar and its CSS-only horizontal-strip fallback shipped with the app shell (spec-sidebar-navigation.md). This story upgrades the mobile experience to a proper drawer (Alpine allowed — on-stack via Livewire; no JS-framework frontend per AD-1).

## Epic 2: Test Case Authoring

Business Users author the testing script book: Features with Test Suites of Test Cases structured into numbered Steps — the digital replacement for the printed pack.

### Story 2.1: Features and Test Suites

As a Business User,
I want to create a Feature with its Test Suite inside a Project,
So that testing work anchors to delivered functionality.

**Acceptance Criteria:**

**Given** I am a Business User in a Project
**When** I create a Feature (name, description)
**Then** its Test Suite is created with it and both appear in the project's feature list
**And** each Feature has exactly one Test Suite shown with its case count

**Given** I am a Tester or Developer
**When** I view a Project
**Then** I can see Features and Suites but no create controls exist

### Story 2.2: Author Test Cases with Numbered Steps

As a Business User,
I want to write a Test Case with title, description, expected result, and numbered Steps,
So that any Runner executes it exactly the same way.

**Acceptance Criteria:**

**When** I add a Test Case to a Suite with title, description, expected result, and one or more Steps
**Then** it is saved and listed in the suite

**Given** a Test Case with no Steps
**When** I attempt to save
**Then** validation rejects it with a clear error

**And** Steps display numbered in execution order; adding, removing, or reordering Steps renumbers automatically
**And** the case detail view shows all fields with steps clearly numbered for execution

### Story 2.3: Manage the Suite

As a Business User,
I want to edit, reorder, and remove Test Cases in a Suite,
So that the script book stays current and well-ordered.

**Acceptance Criteria:**

**When** I edit a Test Case's fields or Steps
**Then** changes save and reflect immediately in all views of the suite

**When** I reorder Cases within the Suite
**Then** the execution order updates suite-wide

**When** I remove a Test Case that has never been run
**Then** it is deleted from the suite with confirmation

**And** the Suite overview lists all Cases with title, step count, and last-modified indicator

## Epic 3: Test Execution & Instant Reports

Testers and Business Users execute staged Runs on the tick screen (Tester Run, then Business User Run), recording statuses with comments and screenshots into immutable Status History; the Run Report exists the moment the last tick lands.

### Story 3.1: Start a Testing Cycle with Tester Assignment

As a Business User,
I want to initiate testing for a Feature's suite by assigning a Tester,
So the cycle begins digitally and nothing gets printed.

**Acceptance Criteria:**

**Given** a Feature with a Suite containing at least one Test Case
**When** I start a Cycle and assign a Tester
**Then** the Cycle becomes Active and a Tester Run is created in Not Started state
**And** the Run captures a read-only snapshot of each Test Case's current content at Run creation

**Given** a Suite with zero Test Cases
**When** I attempt to start a Cycle
**Then** it is refused with a clear message

### Story 3.2: The Tick Screen — Execute a Run

As a Runner,
I want to work through my Run case by case marking Pass/Fail/Blocked/Skipped with a comment and screenshot on failures,
So my verdict is captured the moment I see the result.

**Acceptance Criteria:**

**Given** my assigned Run
**When** I open it
**Then** each Test Case shows its numbered Steps and status controls (Pass, Fail, Blocked, Skipped)

**Given** I mark a case Fail
**When** I save the verdict
**Then** a comment is required and a screenshot (PNG/JPG) can be attached

**And** each verdict autosaves within 2 seconds without page reload; at most 3 interactions per verdict; fully responsive on laptop, tablet, and phone

**Given** I close the Run mid-suite
**When** I reopen it
**Then** it resumes exactly where I left off

### Story 3.3: Business User Stage with Tester Results Visible

As a Business User,
I want my Run stage to unlock once the Tester finishes, with their results beside my tick screen,
So I verify with full context.

**Acceptance Criteria:**

**Given** the Tester Run is In Progress
**When** I attempt to start my stage
**Then** it is locked with an explanation

**When** the Tester Run completes
**Then** my Business User Run unlocks against the same case snapshot

**And** the tester's verdicts and comments display alongside each case in my tick screen

### Story 3.4: Run Abandonment and Reassignment

As an Admin,
I want to abandon a stuck Run and reassign its stage,
So a missing runner never blocks the cycle.

**Acceptance Criteria:**

**Given** a Run in Not Started or In Progress
**When** its Runner or an Admin abandons it
**Then** the Run is marked Abandoned and recorded

**And** an Abandoned Run never counts as completing its stage

**When** I reassign and restart the stage
**Then** the new Runner executes the same case snapshot

### Story 3.5: Immutable Status History

As any project member,
I want every verdict recorded permanently per Test Case,
So each case carries its full memory across Runs.

**Acceptance Criteria:**

**When** any status change occurs
**Then** it is recorded with actor, timestamp, status, comment, and attachments

**And** entries are append-only — no edit or delete exists anywhere in the system

**And** the per-case history view lists all entries chronologically across all Runs

### Story 3.6: Instant Run Report

As a Runner,
I want the Run Report to exist the moment my last tick lands,
So nobody ever writes or transcribes a report.

**Acceptance Criteria:**

**When** the final verdict of a Run is saved
**Then** the Report is generated with zero manual fields: summary (counts, pass rate), failures with comments and screenshots, per-case detail

**And** the Report reflects Run state immediately with no finalize step

**And** report styling follows the IIUM DLS with status badges (Pass turquoise, Blocked gold; Fail uses a danger addition to be confirmed during UI work)

## Epic 4: The Review Cycle

Business Users submit Reports to Developers; Developers work failures-first and mark Fixed; Business Users re-run failed-only or full and declare testing Done; Reports export to PDF.

### Story 4.1: Submit Report to Developers

As a Business User,
I want to submit my Run's Report to the Project's Developers,
So the handoff happens in one click with nothing lost.

**Acceptance Criteria:**

**Given** my completed Business User Run Report
**When** I submit it
**Then** the Project's Developers are notified in-app
**And** the submitted Report becomes read-only and appears in the developers' project reports list
**And** the Submission itself is an immutable record (who, when)

**Given** a Tester Run report
**When** I view submission controls
**Then** none exist — only Business User Runs can be submitted

### Story 4.2: Failures-First Developer View with Fixed Marking

As a Developer,
I want to open a submitted Report straight to its failures with evidence,
So I start fixing instead of reading.

**Acceptance Criteria:**

**When** I open a submitted Report
**Then** failures appear above summary statistics, each with reporter, comment, screenshot, and open/fixed state; passes are collapsed into counts

**When** I mark a failure Fixed
**Then** the change is visible to the Business User immediately

**And** fix state is binary in v1 (open → Fixed) with the marking recorded in Status History

### Story 4.3: PDF Export

As any report viewer,
I want to export a Report as PDF,
So results can be archived or shared outside the tool.

**Acceptance Criteria:**

**Given** any Report (submitted or private)
**When** I export
**Then** a PDF downloads matching on-screen content including screenshots

**And** the PDF carries IIUM branding consistent with the DLS

### Story 4.4: Re-run — Failed-only or Full

As a Business User,
I want to re-run a Feature's suite after fixes land, choosing failed-only or full,
So I verify precisely at the right speed.

**Acceptance Criteria:**

**Given** an Active Cycle with a submitted Report
**When** I launch a failed-only Re-run
**Then** it contains exactly the cases failed or blocked in the latest Cycle state

**When** I launch a full Re-run
**Then** it contains every case in the suite snapshot

**And** each Re-run is a single new Business User Run appended to the same Cycle; Status History spans all of them

**And** only the Business User can launch Re-runs

### Story 4.5: Done Declaration

As a Business User,
I want to declare testing done for a Feature,
So the cycle closes formally and everyone knows.

**Acceptance Criteria:**

**When** I declare Done
**Then** the declaration records who and when, the Cycle becomes Done, and the Feature's suite becomes read-only

**And** Done is terminal — no reopen exists; a new testing concern for the Feature starts a new Cycle with the staged pipeline

**And** declaring Done is restricted to the Business User role

## Epic 5: Developer Verification & Change Control

Developers verify fixes with Private Runs (reports that are never submitted); Admins approve mid-cycle Test Case edits with snapshot versioning so in-flight results stay trustworthy.

### Story 5.1: Developer Private Runs

As a Developer,
I want to execute Private Runs over my Project's suites,
So I can verify fixes myself without creating noise for anyone.

**Acceptance Criteria:**

**Given** any Test Suite in my Project
**When** I start a Private Run
**Then** I execute it on the same tick screen with Pass/Fail/Blocked/Skipped

**And** the Private Run's Report is visible only to me — not to Testers, Business Users, or other Developers

**And** no submission control exists on Private Run reports anywhere in the UI

**And** Private Runs never advance or block a Cycle's staged pipeline

### Story 5.2: Reproduced Status in Private Runs (v1.1)

As a Developer,
I want to mark a case Reproduced in my Private Run,
So my private record shows "I see it too" distinct from a clean pass.

**Acceptance Criteria:**

**Given** a Private Run
**When** I mark a case
**Then** Reproduced is available alongside Pass/Fail/Blocked/Skipped

**And** Reproduced appears only in the private report — never in any submitted Report

**And** the Reproduced verdict is recorded in Status History attributed to me

### Story 5.3: Admin-Approved Mid-Cycle Edits with Versioning

As an Admin,
I want to approve Test Case edits that arrive while a Cycle is Active,
So in-flight results stay trustworthy and changes land deliberately.

**Acceptance Criteria:**

**Given** a Cycle is Active
**When** a Business User edits a Test Case
**Then** the edit is saved as pending and does not affect any existing Run's snapshot

**When** I approve the pending edit
**Then** a new Version of the Test Case is created that applies only to Runs started afterwards

**When** I reject it
**Then** the Test Case stays on its current Version and my reason is recorded

**And** every approve/reject decision lands in Status History with actor, timestamp, and reason
