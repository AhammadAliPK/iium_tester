# Input Reconciliation: brainstorm-intent.md

## Gaps Between Source Input and PRD

### Gap 1: Fix-progress visibility (SHOULD) → Missing
- **Input said**: "Fix-progress visibility" as a SHOULD item — developers' ability to mark which failures they're actively working on, making progress visible to business users
- **PRD says/omits**: Only mentions "Fixed" marking (FR-11) as a binary state; no in-progress visibility or workflow states (e.g., "In Progress", "Queued") for failures under active development
- **Severity**: Medium — impacts coordination between developers and business users during fix cycles, but not blocking for v1

### Gap 2: COULD items entirely dropped
- **Input said**: Three COULD items explicitly listed — "Mid-run edits with admin approval", "Disagreement flag", "Admin dashboard"
- **PRD says/omits**: Only "Mid-run edits" appears (as FR-5, moved to MUST via user decision); "Disagreement flag" and "Admin dashboard" appear only in out-of-scope list with no rationale for why they were demoted from COULD to out-of-scope
- **Severity**: Low — COULD items are optional, but the PRD should acknowledge they were considered and explain the demotion decision

### Gap 3: "Multiple runners" insight → Restricted implementation
- **Input said**: Under Key Insights: "Multiple runners: Same cases, separate runners (business user and developer)" — framed as a core insight about the system's value
- **PRD says/omits**: Implements this as a strictly sequential pipeline (FR-7) with enforced ordering (Tester → Business User → Developer private runs); the qualitative flexibility implied by "multiple runners" is lost in favor of rigid staging
- **Severity**: Medium — changes the philosophical tone from "multiple parallel perspectives" to "controlled sequential handoff"

### Gap 4: Simplified "Core Workflow" → Expanded complexity
- **Input said**: 9-step workflow presented simply: "Business user runs tests → Report auto-generated → Developer views failures → Developer runs to reproduce → Developer fixes → Business user re-runs"
- **PRD says/omits**: Expands into formal stages (Tester Run vs Business User Run), submission gates, and private runs — adds ceremony (submission step, read-only states) that wasn't hinted at in the source's straightforward cycle
- **Severity**: Low — the PRD elaborates for completeness, but loses the source's emphasis on directness and flow

### Gap 5: "Tick screen is the product" → Under-specified in NFRs
- **Input said**: "Tick screen is the product: The testing interface is the core value" — a qualitative emphasis calling out the execution surface as the product's center of gravity
- **PRD says/omits**: Mentions this in vision (§1) and NFRs (§5) but buries it; no explicit usability targets or UX principles for the tick screen beyond "one-tick-per-case with zero scrolling" — the emphasis feels diluted
- **Severity**: High — the source called out this surface as the product's reason for being; the PRD should treat it with corresponding specificity (e.g., interaction design principles, performance targets, error recovery)
