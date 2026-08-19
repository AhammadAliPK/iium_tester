# Buildability Review: iium_tester PRD

**Reviewer:** Senior Developer (implementing team perspective)  
**Date:** 2026-08-19  
**PRD Version:** draft  
**Verdict:** BUILDABLE-WITH-FIXES

## Executive Summary

The PRD demonstrates strong domain understanding and clear user journeys. However, it contains **14 findings** (3 critical, 5 high, 4 medium, 2 low) that would significantly impact implementation complexity and timeline. The critical issues center on undefined state machine behaviors, conflicting lifecycle rules, and underspecified edge cases in the core run/test case evolution system.

## Critical Findings

### 1. [CRITICAL] Test Case Versioning Undefined During Active Runs
**Reference:** FR-5 (Edit Approval for Active Suites), Glossary "Status History"  
**Issue:** PRD states "In-flight Runs continue against the version they started with" but provides no version semantics. What happens when:
- A step is renumbered during a pending edit approval?
- Expected result text changes mid-run?
- Steps are deleted/added?

A developer cannot implement "continue against the version they started with" without defining what "version" means for a test case. Is it the entire test case snapshot? Individual field versions? This is a data modeling decision forced on developers.

**Fix Suggestion:** Define test case versioning model: "Test case version = snapshot of entire case (title, description, expected result, step set) at run start. Each run records test_case_version_id."

---

### 2. [CRITICAL] Run Lifecycle State Machine Gaps on Done Declaration + Re-opening
**Reference:** FR-14 (Done Declaration), FR-13 (Re-run launch)  
**Issue:** PRD states Done Declaration "locks the Feature's suite to read-only unless reopened" but never defines:
- What does "reopened" mean procedurally?
- Who can reopen? (Admin only? Business User?)
- What happens to in-progress runs when suite is reopened?
- Does reopening create a new cycle or resume the old one?
- Can a developer's private run execute on a "done" suite?

The phrase "unless reopened" introduces a capability without specifying the workflow, making it untestable and potentially conflicting with FR-15 (private runs).

**Fix Suggestion:** Either: (a) Remove "unless reopened" and make done irreversible, OR (b) Add FR defining "Reopen Feature" with full workflow: role, permissions, state machine transitions, and effect on active runs.

---

### 3. [CRITICAL] Conflicting Rules on Who Can Execute Re-runs
**Reference:** §9 Open Questions, FR-13 (Re-run launch), FR-7 (Sequential pipeline)  
**Issue:** The resolved open question states "re-runs belong to the Business User alone" but FR-7 establishes "One Runner per stage per suite cycle" and FR-13 says "Each Re-run is a new Run pair in the same cycle lineage." This creates a contradiction:
- If re-runs are "new Run pair" (FR-13), they imply Tester + Business User stages
- But re-runs "belong to Business User alone" (§9)
- FR-7 says "One Runner per stage" — which doesn't fit single-person re-runs

A developer cannot determine whether to build the pipeline infrastructure for re-runs or build a simplified single-user flow.

**Fix Suggestion:** Clarify in FR-13: "Re-runs are Business User-only (single stage, no Tester stage)" OR update §9 resolution to: "Re-runs follow the same two-stage pipeline as initial cycles."

## High Findings

### 4. [HIGH] Undefined "Active Run" Detection for Edit Approval Triggering
**Reference:** FR-5 (Edit approval for active suites)  
**Issue:** FR-5 triggers edit approval "when a suite has an active Run" but "active Run" is undefined:
- Does a completed-but-unsubmitted Tester Run count as active?
- What about a Business User Run in progress?
- Private Developer Runs — do they trigger approval?
- What if a suite has multiple runs in different states?

Without precise "active" definition, developers must guess, leading to inconsistent behavior.

**Fix Suggestion:** Define "active run" = "any run with status IN_PROGRESS or COMPLETED but not yet FINALIZED (submitted/expired/abandoned)."

---

### 5. [HIGH] Missing Edge Case: Run Interruption/Abandonment Behavior
**Reference:** FR-6 (Execute a Run), "Partial completion is preserved"  
**Issue:** PRD specifies interruption resumption but not:
- What happens if a user never returns to an interrupted run? Timeout?
- Can a user explicitly abandon a run? UI for it?
- Does abandonment trigger notifications?
- What if a Tester abandons — does Business User stage become available?
- Are abandoned runs visible in Status History?

This is a real-world scenario (user leaves, gets sick, reassigns) that the PRD treats as trivial.

**Fix Suggestion:** Add FR for "Run Abandonment" covering timeout rules, explicit abandon action, role permissions, and effect on pipeline sequencing.

---

### 6. [HIGH] Undefined "Project Deletion" and Data Retention Conflict
**Reference:** FR-1 (Project creation - "create, rename, archive"), §5 ("data retained for life of project")  
**Issue:** FR-1 only mentions archive, not deletion. But §5 says "data retained for life of project." This creates ambiguity:
- Can projects be permanently deleted? If yes, how does that reconcile with "lifetime retention"?
- Archive = read-only — but can users still view/edit their own data in archived projects?
- What happens to active runs in archived projects?
- Does archive trigger any data export/compliance actions?

Developers cannot implement archival without knowing the full implications.

**Fix Suggestion:** Clarify: (a) Delete is not supported (archive-only), OR (b) Add FR-1.1 for "Project Deletion" with data retention/disposal rules. For archive, specify whether runs continue and if users retain read/write access.

---

### 7. [HIGH] Screenshot Attachment Undefined — Size Limits, Storage, and Formats
**Reference:** FR-6 (Execute a Run), "Screenshots (PNG/JPG) attach"  
**Issue:** The PRD treats screenshots as trivial but this is non-trivial build cost:
- Max file size? Resolution limits? File count per case?
- Storage backend? CDN?
- What happens when upload fails? Network timeout?
- Are screenshots stored per Status History entry or deduplicated?
- Can screenshots be deleted/edited after attachment?
- What about GIF/WEBP? Only PNG/JPG mentioned.

Developers cannot implement attachment logic without these specs.

**Fix Suggestion:** Specify attachment limits (e.g., "max 5MB per screenshot, 3 per case, PNG/JPG only, stored in S3 with CDN"), upload failure handling, and whether screenshots are immutable once attached.

---

### 8. [HIGH] "Assign" Operation Undefined Across Runs
**Reference:** UJ-1 ("assigned a suite run"), FR-2 (assign users to projects), FR-6 ("assigned Runner can open their Run")  
**Issue:** PRD uses "assigned" but never specifies:
- Who assigns runs to specific runners? Admin? Self-assignment?
- Can a run be reassigned mid-execution? If yes, what happens to in-progress ticks?
- Can a runner decline assignment?
- Does assignment happen at run creation or ad hoc?
- Are there notification hooks on assignment?

"Assigned" appears in critical user journeys but is not specified as a feature.

**Fix Suggestion:** Add FR-1.1 "Run Assignment" specifying: (a) who assigns (admin/self-assignment), (b) reassignment rules, (c) effect on in-progress runs, (d) any notification behavior.

## Medium Findings

### 9. [MEDIUM] Ambiguous "Run Pair" Lineage in Re-runs
**Reference:** FR-13 ("Each Re-run is a new Run pair in the same cycle lineage")  
**Issue:** "Run pair" and "cycle lineage" are not defined in the glossary:
- Does lineage mean a linked list? Graph? Tags?
- How do developers query "all runs in this cycle"?
- What's the parent/child relationship?
- Do earlier cycles' reports reference later re-runs?

This is underspecified data modeling that developers will need to guess.

**Fix Suggestion:** Add to glossary: "Cycle Lineage — all runs (initial + re-runs) for a feature, tracked as parent/child relationships. Queryable by feature_id and lineage_id."

---

### 10. [MEDIUM] PDF Export Scope Undefined
**Reference:** FR-12 (PDF export), "matches on-screen content"  
**Issue:** "Matches on-screen content" is ambiguous for PDF generation:
- Does it include Status History? Just the summary?
- What about screenshots in PDF? All of them?
- Does PDF export include the Business User's notes or just the Run data?
- What's the PDF page size/orientation? Header/footer?
- Is PDF generation real-time or pre-rendered?

Developers cannot test "matches on-screen content" without specification.

**Fix Suggestion:** Specify PDF template: "PDF includes Report summary, failure list with screenshots, per-case details. Status History excluded. A4 portrait, generated on-demand."

---

### 11. [MEDIUM] "Partial Completion is Preserved" — But How?
**Reference:** FR-6, "Partial completion is preserved: a Run interrupted mid-suite resumes exactly where left"  
**Issue:** This is stated as a consequence but the mechanism is undefined:
- Is there a "save draft" button? Auto-save?
- How frequently is state persisted? After each tick?
- What if the browser crashes mid-tick?
- What if the user switches devices during a run?
- Is there a conflict if the same user opens the run on two devices?

"Preserved" hides significant complexity around state synchronization.

**Fix Suggestion:** Specify: "Auto-save after each tick (state persisted immediately). Conflict resolution: last-write-wins per case. Device switching supported."

---

### 12. [MEDIUM] Undefined "In-App Notification" Behavior
**Reference:** FR-10, "in-app notifications"  
**Issue:** PRD relies on "in-app notifications" for submission but never specifies:
- What do notifications look like? Where do they appear?
- Do they persist? Are they dismissible?
- What's the notification retention policy?
- Are they real-time (WebSocket) or poll-based?
- What if a user is offline when notification is sent?

This is a significant feature area treated as trivial.

**Fix Suggestion:** Add NFR or FR for notification system specifying: "Real-time notifications via WebSocket, persist for 30 days, dismissible, appear in global notification center."

## Low Findings

### 13. [LOW] "Zero Manual Fields" Contradicted by "Mark Fixed"
**Reference:** FR-9, FR-11  
**Issue:** FR-9 claims "Zero manual fields exist in report generation" but FR-11 introduces "Each failure is markable Fixed by a Developer." "Mark Fixed" is a manual field that affects the report. This is a minor contradiction but demonstrates imprecision.

**Fix Suggestion:** Clarify FR-9: "Zero manual fields exist in report generation at the time of Run completion" OR explicitly exclude Developer "Fixed" marks from the "zero manual fields" claim.

---

### 14. [LOW] Glossary Term "Runner" Doesn't Include Developer for Private Runs
**Reference:** Glossary "Runner" definition, FR-15 (Private Runs)  
**Issue:** Glossary defines Runner as "Tester or Business User; Developer for Private Runs" but the parenthetical creates ambiguity about whether Developers are "Runners" in the permission model. A minor definitional gap.

**Fix Suggestion:** Update glossary: "Runner — the person executing a Run (Tester, Business User, or Developer for Private Runs)."

## Summary Table

| Severity | Count | References |
|----------|-------|------------|
| Critical | 3 | FR-5, FR-13/FR-7 conflict, FR-14/FR-13 state machine |
| High | 5 | FR-5 (active run definition), FR-6 (abandonment), FR-1/§5 (deletion), FR-6 (screenshots), Assignment workflow |
| Medium | 4 | FR-13 (lineage), FR-12 (PDF), FR-6 (partial save), FR-10 (notifications) |
| Low | 2 | FR-9/FR-11 contradiction, Glossary "Runner" |

## Recommendations

1. **Address all critical findings before epic/story generation** — they represent fundamental architecture decisions that will be expensive to change later.
2. **High findings should be resolved in PRD revision** — they add significant scope not accounted for in MVP timeline.
3. **Medium findings can be resolved during sprint planning** but may spike complexity.
4. **Consider staging FR-5 (Edit Approval) to v1.1** — as the PRD notes, this is real build cost that could defer launch.

## Testability Assessment

**Testable as written:** ~65%  
**Untestable without clarification:** ~35%

The untestable portions cluster around:
- State machine transitions (Done Declaration, Re-opening, Re-run staging)
- Test case versioning during active runs
- Edge cases in assignment, abandonment, and archival

## Final Verdict

**BUILDABLE-WITH-FIXES** — The PRD contains a solid core but requires clarification of state machine behaviors, versioning semantics, and edge case handling before developers can proceed with confidence estimation and architecture design. The critical and high findings represent significant hidden complexity that should be surfaced in planning, not discovered during implementation.