# Reconciliation Review: Epics vs Architecture Spine
**Project:** iium_tester  
**Date:** 2026-08-19  
**Reviewer:** Architecture Reconciliation Agent  
**Purpose:** Validate that every story's technical needs from the epics file have a home in the architecture spine, and identify any contradictions.

---

## Executive Summary

**VERDICT:** RECONCILED — All 21 stories find technical homes in the architecture spine. The spine successfully accommodates the mid-run stack change to Laravel Blade + session auth + Tailwind with IIUM DLS tokens. No direct contradictions found, but two minor clarification gaps identified.

---

## Detailed Analysis

### Story Coverage by Architecture Decisions

| Story | Key Technical Needs | Architecture Home | Status |
|-------|-------------------|------------------|---------|
| **Epic 1: Platform Foundation & Team Access** | | | |
| 1.1: Branded Laravel Application Shell | Tailwind theme extensions, DLS tokens, session auth, dark theme toggle, Roboto Slab/Inter/Barlow fonts | AD-1 (session auth), AD-6 (IIUM DLS tokens), AD-5 (gates) | ✅ Full coverage |
| 1.2: Project Creation & Management | Admin-only controls, DLS components, project archive | AD-1 (Blade + Livewire), AD-5 (named gates), AD-6 (styling) | ✅ Full coverage |
| 1.3: Team Management | Role assignment, multi-project membership, user preservation | AD-5 (gates), ERD (users, memberships, projects) | ✅ Full coverage |
| 1.4: Role-Based Permission Enforcement | Permission matrix, explicit denial messages | AD-5 (named gates per capability) | ✅ Full coverage |
| **Epic 2: Test Case Authoring** | | | |
| 2.1: Features and Test Suites | Feature-Suite 1:1 relationship, role-based visibility | ERD (features ||--|| test_suites), AD-5 (gates) | ✅ Full coverage |
| 2.2: Author Test Cases with Numbered Steps | Step validation, auto-renumbering, case detail view | AD-3 (test_case_versions), AD-1 (Livewire authoring) | ✅ Full coverage |
| 2.3: Manage the Suite | Edit/reorder/delete, suite overview with metadata | AD-3 (versioning), AD-1 (Livewire components) | ✅ Full coverage |
| **Epic 3: Test Execution & Instant Reports** | | | |
| 3.1: Start a Testing Cycle | Cycle creation, Tester Run generation, snapshot capture | AD-2 (CycleManager), AD-3 (run_cases snapshot), ERD | ✅ Full coverage |
| 3.2: The Tick Screen | 4-status verdicts, comment requirement, screenshot attachment, autosave, resume | AD-2 (RunManager), AD-9 (attachments), AD-1 (Livewire tick component), NFR-1 (autosave) | ✅ Full coverage |
| 3.3: Business User Stage | Sequential pipeline unlock, tester results visibility | AD-2 (CycleManager state machine), ERD (cycles ||--o{ runs) | ✅ Full coverage |
| 3.4: Run Abandonment and Reassignment | Abandon state, stage reassignment, snapshot preservation | AD-2 (RunManager), ERD (run type, status enums) | ✅ Full coverage |
| 3.5: Immutable Status History | Append-only verdicts, actor/timestamp/comment/attachments | AD-4 (verdicts append-only), AD-9 (attachment URLs), ERD | ✅ Full coverage |
| 3.6: Instant Run Report | Auto-generation on last tick, summary + failures + detail, IIUM DLS badges | AD-7 (Blade → dompdf), AD-6 (DLS tokens), AD-2 (RunManager) | ✅ Full coverage |
| **Epic 4: The Review Cycle** | | | |
| 4.1: Submit Report to Developers | Business User only, in-app notification, immutable submission record | AD-5 (gates), ERD (submissions ||--o| runs), AD-4 (immutable) | ✅ Full coverage |
| 4.2: Failures-First Developer View | Failures on top, open/fixed marking, binary state | AD-7 (Blade views), AD-2 (ReportBuilder), ERD (verdicts) | ✅ Full coverage |
| 4.3: PDF Export | PDF matches on-screen, includes screenshots, IIUM branding | AD-7 (single Blade → dompdf path), AD-6 (DLS tokens), AD-9 (signed URLs) | ✅ Full coverage |
| 4.4: Re-run | Failed-only/full, single Business User Run appended to Cycle | AD-2 (CycleManager), ERD (run type, cycle relationship) | ✅ Full coverage |
| 4.5: Done Declaration | Terminal state, suite read-only, Business User only | AD-2 (CycleManager), AD-5 (gates), ERD (CycleStatus enum) | ✅ Full coverage |
| **Epic 5: Developer Verification & Change Control** | | | |
| 5.1: Developer Private Runs | Private type, runner-only visibility, no submission | AD-2 (RunManager type=private), AD-5 (gates), ERD (run type) | ✅ Full coverage |
| 5.2: Reproduced Status (v1.1) | Reproduced verdict, private only | Deferred in spine (explicitly listed) | ✅ Deferred by design |
| 5.3: Admin-Approved Mid-Cycle Edits | Pending version, approve/reject, Status History recording | AD-3 (test_case_versions + edit_approvals), AD-2 (EditApprovalService), AD-4 (append-only) | ✅ Full coverage |

---

## Stack Change Validation

The spine fully accommodates the mid-run stack change recorded in the epics file:

| Stack Decision | Epics File Reference | Architecture Home | Validation |
|----------------|---------------------|-------------------|------------|
| **PHP Laravel (no full SPA)** | Additional Requirements §1 | AD-1 (monolith, no React/Vue) | ✅ Consistent |
| **Blade server-rendered + session auth (httpOnly cookie)** | Additional Requirements §2 | AD-1 (session auth exclusively), Stack table | ✅ Consistent |
| **Tailwind CSS with IIUM DLS tokens** | Additional Requirements §3 | AD-6 (--iium-* tokens as Tailwind extensions), Stack table | ✅ Consistent |
| **No JWT/API split** | Additional Requirements §2 | AD-1 (prevents API/SPA split) | ✅ Consistent |

---

## Requirement Inventory Coverage

All 15 functional requirements (FR-1 through FR-15) are explicitly bound in the spine:

| FR | Bound In ADs | Status |
|----|-------------|---------|
| FR-1 (Admin project controls) | AD-1, AD-5 | ✅ Bound |
| FR-2 (User management) | AD-5 | ✅ Bound |
| FR-3 (Permission matrix) | AD-5 | ✅ Bound |
| FR-4 (Authoring with Steps) | AD-3, AD-1 | ✅ Bound |
| FR-5 (Edit approval) | AD-3, AD-2 | ✅ Bound |
| FR-6 (Run execution) | AD-2, AD-9 | ✅ Bound |
| FR-7 (Sequential pipeline) | AD-2 | ✅ Bound |
| FR-8 (Status History) | AD-4, AD-9 | ✅ Bound |
| FR-9 (Auto-report) | AD-7, AD-2 | ✅ Bound |
| FR-10 (Submission) | AD-7, AD-5 | ✅ Bound |
| FR-11 (Failures-first) | AD-7, AD-2 | ✅ Bound |
| FR-12 (PDF export) | AD-7 | ✅ Bound |
| FR-13 (Re-run) | AD-2 | ✅ Bound |
| FR-14 (Done Declaration) | AD-2 | ✅ Bound |
| FR-15 (Private runs) | AD-2, AD-5 | ✅ Bound |

All NFRs addressed:
- **NFR-1 (Tick screen usability):** AD-1 (Livewire no page reload), AD-9 (autosave via component)
- **NFR-2 (PDF export):** AD-7 (Blade → dompdf)
- **NFR-3/4 (Immutability):** AD-3 (version rows immutable), AD-4 (verdicts append-only)
- **NFR-5 (Scale assumptions):** AD-8 (portable persistence), AD-9 (local disk v1)

---

## Gaps & Clarifications Needed

### 1. LOW: Tick Screen Autosave Implementation Detail
**Story:** 3.2 (The Tick Screen)  
**Requirement:** "Each verdict autosaves within 2 seconds without page reload"  
**Spine Coverage:** AD-1 states Livewire components (no page reload) and AD-2 references `RunManager` for state, but **no explicit AD or mechanism describes the 2-second autosave behavior**.  
**Impact:** Minor — Livewire wire:poll or Alpine.js debounce can achieve this, but the acceptance criteria is not explicitly codified in an invariant.  
**Recommendation:** Consider adding a convention note to AD-1 or AD-2 about "autosave within 2s" being a Livewire component pattern (wire:model.lazy + poll).

### 2. LOW: Status Badge Semantics for Fail State
**Story:** 3.6 (Instant Run Report) and Epic 2 Stories  
**Requirement:** "Report styling follows the IIUM DLS with status badges (Pass turquoise, Blocked gold; **Fail uses a danger addition to be confirmed during UI work**)"  
**Spine Coverage:** AD-6 defines IIUM DLS tokens (turquoise, gold, surfaces) but **does not explicitly permit or define a danger/red semantic** for the Fail status. The epics file explicitly flags this as "an open design decision."  
**Impact:** Minor — AD-6 states "Brand tokens ... are defined once as Tailwind theme extensions from the DLS and consumed via shared Blade components," which allows extending the token set for Fail status.  
**Recommendation:** No spine change needed, but track as UI implementation detail: the danger/red token for Fail will extend AD-6's token set without violating the "no raw hex" rule.

---

## Contradictions Analysis

**No contradictions found between the epics file and architecture spine.**

Key alignment checks:
- ✅ Session auth (no JWT) in epics ↔ AD-1 session auth exclusively
- ✅ Tailwind + DLS tokens in epics ↔ AD-6 brand tokens as Tailwind extensions
- ✅ Edit approval with versioning in epics ↔ AD-3 immutable version rows
- ✅ Status History append-only in epics ↔ AD-4 verdicts append-only structure
- ✅ Sequential pipeline in epics ↔ AD-2 CycleManager state machine
- ✅ Private runs in epics ↔ AD-2 RunManager type=private + runner-only visibility
- ✅ PDF matches on-screen in epics ↔ AD-7 single Blade → dompdf path

---

## Homeless Stories Check

**No homeless stories.** Every one of the 21 stories maps to one or more architecture decisions and/or ERD entities:

- **Epic 1 (4 stories):** All mapped to AD-1, AD-5, AD-6 + ERD users/projects/memberships
- **Epic 2 (3 stories):** All mapped to AD-1, AD-3 + ERD features/test_suites/test_cases/test_case_versions/steps
- **Epic 3 (6 stories):** All mapped to AD-1, AD-2, AD-4, AD-7, AD-9 + ERD cycles/runs/verdicts/attachments
- **Epic 4 (5 stories):** All mapped to AD-2, AD-5, AD-7 + ERD submissions
- **Epic 5 (3 stories):** All mapped to AD-2, AD-3, AD-4, AD-5 + ERD edit_approvals

---

## Deferred Items Alignment

The spine's "Deferred" section aligns with the epics file's scope:

| Spine Deferred | Epics Alignment |
|----------------|-----------------|
| FR-16 Reproduced status, disagreement flag, admin dashboard, multi-runner stages | ✅ FR-16 explicitly marked "(v1.1)" in epics; Story 5.2 is v1.1 |
| Production hosting target | ✅ Not in epics (ops concern) |
| Email notifications | ✅ Not in epics (PRD §7.2 states "in-app only") |
| Async PDF rendering | ✅ Not in epics (v1 scope is synchronous) |
| Cloud storage for screenshots | ✅ Not in epics (AD-9 local disk adequate for v1) |

---

## Conclusions

### Strengths
1. **Complete architectural traceability:** Every story's technical need has a clear home in the spine's ADs, components, or ERD.
2. **Stack change accommodated:** The Laravel Blade + session auth + Tailwind decision is fully integrated into AD-1, AD-6, and the Stack table.
3. **No contradictions:** The spine does not contradict any epic story or requirement inventory entry.
4. **Deferred scope一致:** Items deferred in the spine align with epics' v1.1 and out-of-scope decisions.

### Recommendations
1. **Track the 2-second autosave implementation** as a Livewire component pattern in implementation notes (no spine change needed).
2. **Confirm Fail status danger token** during UI implementation — it extends AD-6's token set without violation.
3. **Proceed with confidence:** The architecture spine is ratifying of the epics file and provides a solid foundation for story implementation.

---

**Review Status:** COMPLETE ✅  
**Recommendation:** APPROVE for sprint planning  
**Next Steps:** Begin Epic 1 (Story 1.1: Branded Laravel Application Shell) with confidence in architectural alignment.
