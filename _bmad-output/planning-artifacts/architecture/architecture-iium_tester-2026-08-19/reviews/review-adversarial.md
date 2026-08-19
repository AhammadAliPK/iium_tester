# Adversarial Architecture Review — iium_tester

**Verdict:** HOLES FOUND — Multiple implementation incompatibilities exist despite AD compliance

## Executive Summary
The architecture spine establishes strong invariants (AD-1 through AD-9) but leaves critical gaps in shared data ownership, state transition boundaries, and authorization edge cases. Two teams following every AD to the letter could still build incompatible systems that fail at integration time.

## Critical Findings

### 1. CLASHING OWNERSHIP OF "TEST RESULTS" (Severity: HIGH)

**Two AD-Compliant Units:**
- **Team A (ReportBuilder):** Implements `ReportBuilder` service that queries `verdicts` table directly, treating it as the single source of truth for "test results." Believes `verdicts` owns all status history and builds reports via `SELECT * FROM verdicts WHERE run_id = ?`
- **Team B (RunManager):** Implements `RunManager` service that tracks "test results" via `run_cases` status field, believing the snapshot pivot is the authoritative execution state. Updates `run_cases.status` on each tick and uses it for stage transition logic.

**The Clash:**
When a Test Case status changes, both systems update differently:
- Team A's `ReportBuilder` reads from `verdicts` (append-only per AD-4)
- Team B's `RunManager` writes to `run_cases.status` for stage completion checks

**AD Gap:** No AD specifies which table owns the authoritative "current status" for a Run's Test Cases. AD-4 governs `verdicts` structure but doesn't declare it the source for current status. AD-2 mentions `RunManager` owns "Run state" but doesn't define whether "current Test Case status" is Run state or History state.

**Incompatibility:** Reports could show different current statuses than the Run completion logic uses. A Test Case might appear "Failed" in the Report (from latest `verdicts` entry) but "Pass" in stage completion checks (from `run_cases.status`), allowing invalid stage transitions.

---

### 2. CONFLICTING STATE-MUTATION PATHS: CYCLE MANAGER VS RUN MANAGER (Severity: HIGH)

**Two AD-Compliant Units:**
- **Team A (CycleManager):** Interprets AD-2's "Cycle state transitions" broadly: believes `CycleManager` owns the entire Cycle lifecycle including Run stage sequencing. Implements `CycleManager::advanceStage(Cycle $cycle)` that directly updates `runs.status` when moving from Tester → Business User stage.
- **Team B (RunManager):** Interprets AD-2's "Run state" literally: believes `RunManager` exclusively owns `runs.status` field. Implements `RunManager::completeStage(Run $run)` as the sole path to mark a Run Completed.

**The Clash:**
Both systems write to `runs.status` field through different code paths:
- Team A's `CycleManager::advanceStage()` updates `runs.status` = 'completed' when moving stages
- Team B's `RunManager::completeStage()` updates the same field for Run completion

**AD Gap:** AD-2 states "Every mutation of Run state, Cycle state goes through `app/Domain` services" but doesn't define ownership boundaries between `CycleManager` and `RunManager`. No AD specifies which service owns the `runs.status` field.

**Incompatibility:** Race conditions and lost updates. If `CycleManager` advances stages while `RunManager` completes a Run, they could overwrite each other. Stage progression could become inconsistent with Run completion status.

---

### 3. SNAPSHOT VERSIONING EDGE CASE: RE-RUN DURING PENDING APPROVAL (Severity: MEDIUM)

**Two AD-Compliant Units:**
- **Team A (EditApprovalService):** Implements AD-3 literally: creates new `test_case_versions` row with `approved_at = null` on edit. Believes "Runs always execute the currently-approved version captured at Run start" means Run's `run_cases` always references an approved `test_case_versions.id`.
- **Team B (ReRunLaunch):** Implements FR-13's "Failed-only Re-run" by querying the Cycle's latest Run, extracting failed Test Cases, and creating new `run_cases` entries referencing whatever `test_case_versions.id` the previous Run used (to maintain consistency).

**The Clash:**
When a Re-run launches while an edit approval is pending:
- Team A's system requires all new Runs to reference only approved versions (`approved_at != null`)
- Team B's Re-run logic copies the previous Run's version references, which could be pending approval

**AD Gap:** AD-3 states "Runs always execute the currently-approved version captured at Run start" but doesn't specify what happens when a Re-run references a version that becomes pending approval mid-Cycle. No AD covers whether Re-run version snapshots should ignore pending approvals or wait for them.

**Incompatibility:** Re-runs could execute pending-approval content, violating the approval workflow intent. Or Re-runs could fail to launch, blocking valid fix verification cycles.

---

### 4. AUTHORIZATION GAP: PRIVATE RUN REPORT VISIBILITY (Severity: MEDIUM)

**Two AD-Compliant Units:**
- **Team A (ReportAuthorization):** Implements AD-5's named gates literally: creates `view-private-reports` gate that checks `Auth::user()->id === $run->runner_id`. Believes AD-9's "signed URLs tied to authorization" means PDF URLs must validate the user on each request.
- **Team B (PDFExport):** Implements AD-7's "Blade views → dompdf" path and AD-9's "signed URLs" as time-limited tokens that bypass per-request user checks. Believes once a signed URL is generated, it's valid until expiry without re-checking user permissions.

**The Clash:**
For a Private Run PDF:
- Team A's system requires user re-validation on every PDF access
- Team B's signed URLs work for anyone with the token until expiry

**AD Gap:** AD-5 requires gate checks at routes but doesn't specify whether signed URLs (AD-9) require ongoing permission validation or are self-validating. No AD clarifies whether Private Run PDFs can be shared via URL or require continuous authentication.

**Incompatibility:** Private Run PDFs could leak via shared URLs, violating FR-15's "visible to executing Developer only." Or PDF generation could fail for legitimate private Run owners.

---

### 5. ENUM VALUE CONVENTION AMBIGUITY (Severity: LOW)

**Two AD-Compliant Units:**
- **Team A (StatusEnum):** Implements `RunStatus` enum per AD's "string-backed PHP enums" with values: `'not-started'`, `'in-progress'`, `'completed'`, `'abandoned'` (kebab-case matching PRD glossary)
- **Team B (StatusEnum):** Implements same enum but with values: `'NOT_STARTED'`, `'IN_PROGRESS'`, `'COMPLETED'`, `'ABANDONED'` (upper-case snake-case matching Laravel conventions)

**The Clash:**
Both systems comply with AD's requirement for "string-backed PHP enums" but use different string values.

**AD Gap:** AD states "string-backed PHP enums for every state" but doesn't specify the convention for enum values themselves (kebab vs snake case, lowercase vs uppercase).

**Incompatibility:** Database queries and form validation would fail across the two implementations. Status comparisons would break, and Run state transitions would fail silently or throw type errors.

---

### 6. QUERY SEMANTICS GAP: "FAILED IN LATEST CYCLE" (Severity: MEDIUM)

**Two AD-Compliant Units:**
- **Team A (ReRunScope):** Implements "Failed-only Re-run" (FR-13) as: `SELECT test_cases WHERE id IN (SELECT test_case_id FROM verdicts WHERE run_id IN (SELECT id FROM runs WHERE cycle_id = ? AND created_at = MAX(created_at)) AND status IN ('fail', 'blocked'))` — finds failures from the single most recent Run by timestamp
- **Team B (ReRunScope):** Implements same feature as: `SELECT test_cases WHERE id IN (SELECT test_case_id FROM verdicts WHERE cycle_id = ? AND status IN ('fail', 'blocked') AND run_order = (SELECT MAX(run_order) FROM runs WHERE cycle_id = ? AND type = 'business-user'))` — finds failures from the most recent Business User stage by `run_order` sequencing

**The Clash:**
When Run types overlap (e.g., Private Run runs mid-Cycle):
- Team A's query might return Private Run failures (if it's the latest timestamp)
- Team B's query ignores Private Runs, targeting only Business User stages

**AD Gap:** No AD specifies how "latest Cycle" should account for different Run types when determining failure scope. FR-13 mentions "Failed-only" but doesn't define whether Private Runs count toward "latest."

**Incompatibility:** Re-run scope could include unintended Test Cases (from Private Runs) or miss intended ones (if ordering semantics differ), causing inconsistent re-test coverage.

---

## Summary of AD Gaps

| AD | Gap | Severity |
|----|-----|----------|
| AD-2 | Missing boundary specification between CycleManager and RunManager ownership | HIGH |
| AD-2 | No definition of which table owns authoritative "current status" | HIGH |
| AD-3 | No coverage of Re-run version selection during pending approvals | MEDIUM |
| AD-5 + AD-9 | Unclear if signed URLs require continuous permission validation | MEDIUM |
| AD conventions | Enum value format not specified (case/style) | LOW |
| FR-13 | "Latest Cycle" semantics ambiguous with mixed Run types | MEDIUM |

---

## Recommendations

1. **Add AD-10: Data Ownership Boundaries** — Specify exact ownership of `runs.status`, `run_cases.status`, and `verdicts` for current state queries
2. **Tighten AD-2** — Explicitly state `CycleManager` orchestrates but `RunManager` owns all `runs` table mutations
3. **Extend AD-3** — Cover Re-run version selection semantics during pending approvals
4. **Clarify AD-5 + AD-9 intersection** — Specify whether Private Run PDFs require continuous auth validation
5. **Add enum convention to Consistency Conventions** — Fix string format for all enum values
6. **Add AD covering Run type isolation** — Specify how Private Runs interact with Cycle-level queries like "latest failures"

---

**Reviewed:** 2026-08-19  
**Reviewer:** Adversarial Architecture Review  
**Mandate:** Attack the spine — find holes where AD compliance still allows incompatible implementations  
**Status:** 6 critical/medium gaps found — spine requires tightening before safe build initiation