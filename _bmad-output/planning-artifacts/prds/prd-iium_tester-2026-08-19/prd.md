---
title: PRD — iium_tester (Test Reporting Platform)
status: final
created: 2026-08-19
updated: 2026-08-19
---

# PRD: iium_tester — Test Reporting Platform

*Internal tool. Working title — confirm.*

## 0. Document Purpose

This PRD defines v1 of **iium_tester**, an internal web platform that replaces a paper-based test execution process. It is written for the PM (Ahammadali), the implementing developers, and downstream BMAD workflow owners (`bmad-ux`, `bmad-architecture`, `bmad-create-epics-and-stories`). Structure: Glossary-anchored vocabulary, features grouped with globally numbered FRs, assumptions tagged inline and indexed in §10. Source input: brainstorm intent doc (`_bmad-output/brainstorming/brainstorm-test-reporting-platform-2026-08-19/brainstorm-intent.md`); this PRD supersedes its MoSCoW list where later user decisions upgraded items.

## 1. Vision

Today, test cases are printed on paper, ticked pass/fail by hand with handwritten comments, and walked to a developer's desk. Re-testing means a full reprint whose only history is a hand-written date. Reports are manufactured after testing by transcription.

iium_tester makes the report a **free byproduct of testing**. Testers and business users tick verdicts on screen at the moment of testing; the report writes itself; the developer opens straight to the failures with comments and screenshots attached. Every Test Case carries a memory — who ticked what, when, and why — across fix and re-test Cycles. The product's real unit is not the report but the **Cycle**: Run → Report → reproduce → fix → Re-run, until the business user declares the Feature done.

## 2. Target User

### 2.1 Jobs To Be Done

- **Tester:** "When I execute a Test Suite, I want to record pass/fail with my comment and a screenshot the moment I see the result, so I never transcribe anything."
- **Business user:** "When I author Test Cases per Feature, I want them structured with numbered Steps, so any Runner executes them the same way."
- **Business user:** "When I test after the Testers, I want their results in front of me and my verdict passed to Developers in one click, so nothing is lost in a handoff."
- **Developer:** "When a Report lands, I want the failures first — prioritizable, with evidence — so I start fixing, not reading."
- **Developer:** "When I fix, I want to run Test Cases privately to verify, so I confirm the fix without creating noise for others."
- **Admin:** "When a Test Suite is mid-Run and a Test Case changes, I want to approve that edit, so in-flight results stay trustworthy."

### 2.2 Non-Users (v1)

External contractors without an account, automation/CI pipelines, and anyone outside the organization. Not a multi-tenant SaaS.

### 2.3 Key User Journeys

- **UJ-1. Ravi ticks a suite on his laptop.** Ravi (Tester), assigned a suite Run, opens the Run screen: each Test Case shows its numbered Steps; he ticks Pass/Fail/Blocked/Skipped, must comment on a fail, and attaches a screenshot. Finishing the last tick completes his stage; the business user sees his results immediately. *Edge:* a Test Case he cannot execute at all → Blocked, with reason.

- **UJ-2. Sarah verifies and passes it on.** Sarah (Business User) opens the Test Suite after Ravi's stage, sees his results beside her own ticking screen, runs her pass, and clicks **Submit report to developers**. The Report already exists — nothing to write. She later Re-runs failed-only after fixes land, and when satisfied, declares the Feature's testing done.

- **UJ-3. Dev opens failures first.** Karim (Developer) gets the Submission, opens the Report: failures listed first with comments and screenshots, passes counted quietly. He fixes one by one, marks each failure **Fixed**, and privately runs the affected Test Cases — a Private Run whose Report goes nowhere — before telling Sarah.

- **UJ-4. Maya approves a mid-run edit.** Maya (Admin) sees a pending edit request on a Test Case inside an active Run; she approves it, and only the next Run picks up the change — in-flight results keep the Version they started with.

## 3. Glossary

- **Project** — the top-level container owned by an Admin; users are assigned to it with a Role. One organization, many Projects.
- **Role** — one of Admin, Business User, Tester, Developer. A user's Role is global to the organization; users may belong to multiple Projects (Testers test multiple apps; Developers work across Projects).
- **Feature** — a unit of delivered functionality within a Project; the anchor for organizing testing work.
- **Test Case** — a scripted procedure belonging to exactly one Feature: title, description, expected result, and one or more numbered **Steps**.
- **Step** — a numbered action within a Test Case. Steps are MUST (user decision).
- **Test Suite** — the set of Test Cases for one Feature, handed to Runners as a unit.
- **Run** — one execution stage of a Test Suite by one Runner. Lifecycle: **Not Started → In Progress → Completed / Abandoned**. The initial pipeline per Cycle: **Tester Run → Business User Run** (sequential, same Test Cases). A **Private Run** is a Developer Run whose Report is never submitted.
- **Cycle** — the testing lineage of one Feature's Test Suite: the initial staged pipeline, the Submission, any Re-runs, ending in a Done Declaration. States: **Not Started → Active → Done**. A Cycle is Active from its first Run's start until Done.
- **Version** — the content state of a Test Case. An approved edit creates a new Version. Each Run executes a read-only snapshot of one Version per Test Case, captured at Run start.
- **Runner** — the person executing a Run (Tester or Business User; Developer for Private Runs).
- **Status** — per Test Case per Run: **Pass, Fail, Blocked, Skipped**; plus **Reproduced** (v1.1, Developer Private Runs) and **Fixed** (Developer's mark on a reported failure).
- **Status History** — the append-only record per Test Case: every Status change, actor, timestamp, comment, attachment. "A Test Case with a memory."
- **Report** — the auto-generated result of a Run: pass/fail breakdown, failures with evidence, per-Test Case detail. Exists the moment the last tick lands; exportable as PDF.
- **Submission** — the Business User's act of sending their Run's Report to the Project's Developers. The gate of the Cycle.
- **Re-run** — a fresh Run over a Test Suite: **Failed-only** (targeted retest) or **Full** (regression).
- **Done Declaration** — the Business User's explicit "testing done" for a Feature.
- **Edit Approval** — Admin approval required before a Test Case modification takes effect on a Test Suite that has an active Run.

## 4. Features

### 4.1 Projects & User Administration

**Description:** Admins create projects and populate them. Access is enforced by Role. Realizes UJ-4 context.

**Functional Requirements:**

#### FR-1: Project creation
Admin can create, rename, and archive Projects.
**Consequences (testable):**
- Only Admins see project-create controls; non-admins get no such control (not merely disabled).
- Archived projects become read-only and disappear from active lists.

#### FR-2: User management
Admin can add users to the organization with a Role, assign them to any number of Projects, and remove them.
**Consequences:**
- A user with no Project assignment can log in but sees no Projects.
- Removing a user from a Project revokes access immediately; their historical Status History entries remain attributed to them.

#### FR-3: Role-based permissions
The system enforces the permission matrix: Admin (Projects, users, Edit Approvals); Business User (author/modify Test Cases, run Test Suites, submit Reports, Re-run, declare Done); Tester (run Test Suites, update Statuses); Developer (view submitted Reports, mark Fixed, Private Runs).

**Consequences:**
- Every capability has exactly one owning role list; no orphan permissions.
- A permission check failing returns an explicit "not permitted for your role" message.

### 4.2 Test Case Authoring & Change Control

**Description:** Business users write Test Cases as scripted procedures with numbered steps, grouped into suites per feature. Mid-run changes are gated by admin approval. Realizes UJ-2, UJ-4.

**Functional Requirements:**

#### FR-4: Authored Test Cases with numbered Steps
Business User can create and edit Test Cases (title, description, expected result, numbered Steps) and organize them into a Feature's Test Suite.
**Consequences:**
- A Test Case cannot be saved with zero Steps.
- Steps render numbered in execution order; reordering renumbers automatically.

#### FR-5: Edit approval for active Test Suites
While a Test Suite's Cycle is Active, Test Case modifications enter a **pending** state and take effect only after Admin approval.
**Consequences:**
- Each Run executes a read-only snapshot of its Test Cases captured at Run start; pending or approved edits affect only Runs started afterwards — never in-flight Runs.
- Admin can approve or reject with a reason; the decision is recorded in Status History.
- Approval creates a new Version of the Test Case; Status History spans Versions.
- `[NOTE FOR PM]` approval workflow is real build cost — could phase to v1.1 if timeline pressures demand; the user explicitly designed it, so v1 default.

### 4.3 Test Execution — the Tick Screen

**Description:** The core surface. A Runner works through a Test Suite Test Case by Test Case, ticking status per Test Case with evidence. The Tester stage precedes the Business User stage. Realizes UJ-1, UJ-2.

**Functional Requirements:**

#### FR-6: Execute a Run
An assigned Runner can open their Run and set each Test Case to Pass, Fail, Blocked, or Skipped, with an optional comment and — on Fail — a screenshot attachment (mandatory comment on Fail).
**Consequences:**
- A Fail cannot be saved without a comment.
- Screenshots (PNG/JPG) attach to the Test Case's Status History entry and appear in the Report.
- Partial completion is preserved: a Run interrupted mid-suite resumes exactly where left.
- A Run in progress can be abandoned by its Runner or an Admin; Abandoned Runs are recorded in Status History, do not complete their stage, and the stage may be restarted (Admin may reassign the Runner).

#### FR-7: Sequential pipeline per suite Cycle
Runs are staged: Tester Run first; the Business User Run becomes available with the Tester's results visible alongside.
**Consequences:**
- Business User cannot start their stage before the Tester Run is completed.
- Tester results are shown in the Business User's tick screen for the same Test Cases.
- One Runner per stage per Test Suite Cycle.

#### FR-8: Status History
Every Status change is recorded immutably per Test Case: actor, timestamp, Status, comment, attachments.
**Consequences:**
- The per-Test Case history view shows all entries across Runs in chronological order.
- No entry can be edited or deleted; corrections are new entries.

### 4.4 Reports & Submission

**Description:** Reports generate themselves from Run data. The Business User's Submission is the handoff to Developers; the Developer's landing view is failures-first. Realizes UJ-2, UJ-3.

**Functional Requirements:**

#### FR-9: Auto-generated Report
When a Run's last tick lands, its Report exists: summary (counts, pass rate), failures with comments and screenshots, per-Test Case detail with Status History references.
**Consequences:**
- Zero manual fields exist in Report generation.
- Report reflects Run state within one refresh; no "finalize report" step.

#### FR-10: Submission to developers
Business User can submit their Run's Report to the Project's Developers from the Report view.
**Consequences:**
- Submission notifies assigned Developers in-app (email deferrable — §9).
- The submitted Report becomes read-only and appears in the developers' Project Reports list.
- The Submission carries the Business User's Run Report; the Tester's Run remains viewable in-platform but is not itself submitted.

#### FR-11: Failures-first developer view
Developers open a submitted Report to a failures-first list: each failure with reporter, comment, screenshot, and status (open / fixed).
**Consequences:**
- Failures sort to the top above summary statistics; passes are collapsed into counts.
- Each failure is markable **Fixed** by a Developer, visible to the Business User. Fix progress is binary in v1 (open → Fixed); richer states (in progress, verified) are fast-follow.

#### FR-12: PDF export
Any Report (submitted or private) can be exported as PDF.
**Consequences:**
- PDF matches on-screen content, including screenshots.

### 4.5 Re-run & Cycle Closure

**Description:** After fixes, the Business User Re-runs failed-only or full; the loop repeats until the Feature is declared done. Realizes UJ-2, UJ-3.

**Functional Requirements:**

#### FR-13: Re-run launch
Business User can launch a Re-run of a Feature's Test Suite in one action: **Failed-only** (Test Cases failed in the latest Cycle) or **Full** (all Test Cases).
**Consequences:**
- Failed-only pre-populates exactly the Test Cases failed or blocked in the latest Cycle.
- Each Re-run is a single new Business User Run appended to the same Cycle; Status History spans all Runs in the Cycle.

#### FR-14: Done Declaration
Business User can declare "testing done" for a Feature.
**Consequences:**
- Declaration records who and when; the Cycle becomes Done and the Feature's Test Suite is read-only.
- Done is terminal: a new testing concern for the Feature opens a new Cycle (starting the staged pipeline over).

### 4.6 Developer Private Runs

**Description:** Developers verify fixes themselves without creating noise. A Private Run's Report is never submitted and visible only to its Runner. Realizes UJ-3.

**Functional Requirements:**

#### FR-15: Private Runs
Developer can execute a Private Run over any Test Suite in their Project.
**Consequences:**
- Private Run Reports are visible to the executing Developer only — not to Testers, Business Users, or other Developers.
- No submission control exists on Private Run Reports.

#### FR-16: Reproduced status *(v1.1 — SHOULD)*
In a Private Run, Developer can mark a Test Case **Reproduced** ("I see it too") alongside Pass/Fail.
**Consequences:**
- Reproduced appears in the private Report only; it never appears in submitted Reports.

*Deferred to v1.1: private verification lands with Pass/Fail alone; Reproduced adds cross-Run signal once multi-developer workflows emerge.*

## 5. Cross-Cutting NFRs

**Requirements**
- **Usability (MUST):** the tick screen (FR-6) is the product — at most 3 interactions per Test Case verdict, no page reload between Test Cases, every tick autosaved within 2 seconds; fully responsive on laptop, tablet, and phone browsers; PDF export of Reports is a hard requirement (FR-12).
- **Auditability:** Edit Approval decisions and Submissions are immutable records.
- **Data integrity:** Status History is append-only; Run snapshots are immutable.

**Working assumptions (unconfirmed)**
- **Scale:** ~10–50 users, single organization, dozens of concurrent active Runs at most.
- **Availability:** business-hours availability; no 24/7 SLA.
- **Retention:** screenshots and history retained for the life of the project; no retention policy in v1.

## 6. Non-Goals (Explicit)

- Not building: CI/automation hooks, native mobile apps, multi-tenant SaaS or billing, requirement traceability, Jira-style integrations, AI test generation.
- Parallel multi-Runner execution per stage — deferred by design; the sequential pipeline is the confirmed model.
- Not becoming: an enterprise test management suite. If a need points there, buy — don't build.

## 7. MVP Scope

### 7.1 In Scope
FR-1 through FR-15 (with FR-16 as fast-follow SHOULD). Roles, Projects, authored Test Cases with numbered Steps, staged pipeline (Tester → Business User), tick screen with comments + screenshots, auto-Reports, Submission, failures-first, Fixed marking, Re-runs (failed-only / full), Done Declaration, Private Runs, Edit Approval, PDF export.

### 7.2 Out of Scope for MVP
- **Reproduced status** (FR-16) — fast-follow.
- **Email notifications** — in-app only in v1.
- **Disagreement flag** (Tester fail vs Business User pass) — COULD, deferred.
- **Admin dashboard** (Project status at a glance) — COULD, deferred.
- **Multiple Runners per stage** — deferred; one Runner per stage.

## 8. Success Metrics

**Primary**
- **SM-1:** 100% of new test Cycles executed in-platform within 4 weeks of rollout — zero paper packs printed. Validates FR-6, FR-9.
- **SM-2:** Time from last tick to developer-visible Report under 1 minute (vs. days today). Validates FR-9, FR-10.

**Secondary**
- **SM-3:** Every failed Test Case in submitted Reports carries a comment + screenshot ≥ 90% of the time. Validates FR-6.

**Counter-metrics (do not optimize)**
- **SM-C1:** Number of Test Cases or Re-runs executed — volume is not quality; more Re-runs may mean worse fixes. Counterbalances SM-1.

## 9. Open Questions

1. Notification needs beyond in-app — is email truly deferrable for the Submission event? *(Deferred: owner PM; revisit if Developers miss Submissions in practice.)*

*Resolved in review 2026-08-19: Re-runs (failed-only and full) belong to the Business User alone; the staged Tester → Business User pipeline applies to a Feature's initial Cycle only.*

## 10. Assumptions Index

*Confirmed by user 2026-08-19 (retained for audit): global Role per user with multi-Project membership · Statuses Pass/Fail/Blocked/Skipped · strict stage sequencing, one Runner per stage · Submission carries the Business User's Report, in-app notifications · responsive web + PDF export as MUST.*

- §5 — Small-team scale (~10–50 users); business-hours availability; lifetime data retention.
