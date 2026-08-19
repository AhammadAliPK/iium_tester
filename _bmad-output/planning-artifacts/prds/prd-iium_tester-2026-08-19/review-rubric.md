# PRD Quality Review — iium_tester (Test Reporting Platform)

## Overall verdict

This is an **adequate** internal-tool PRD that is decision-ready for its purpose. The document has a clear thesis — "the report is a free byproduct of testing" — and the FRs are universally testable with consequences, which is exceptional. The glossary is robust and traceability is strong, making this highly usable for downstream workflows. Three findings keep this from a higher grade: FR-16 (Reproduced status) is underspecified with a "v1.1 — SHOULD" callout that should be an Open Question; NFRs in §5 mix scale (assumption) with requirements (usability) without flagging which are aspirational; and §9's resolution note doesn't cross-reference an Open Question ID. All are fixable in minutes.

## Decision-readiness — adequate

The PRD makes decisions explicitly and names trade-offs. §0 documents that this "supersedes" the brainstorm MoSCoW list where items were upgraded. §7.2 calls out what's deferred (Reproduced status, email notifications, disagreement flag, admin dashboard, multiple runners per stage) with rationale. FR-5's `[NOTE FOR PM]` callout on edit approval workflow ("real build cost — could phase to v1.1 if timeline pressures demand") is exactly the kind of tension-surfacing that decision-readiness requires. The single Open Question in §9 on email notifications is genuinely open, with a deferral noted and a revisit trigger defined. The resolution note below it ("re-runs belong to the Business User alone") is useful but should cross-reference an OQ ID.

### Findings

- **[medium]** Missing OQ cross-reference in §9 (§9) — The resolved decision "re-runs belong to the Business User alone" appears without an OQ ID anchor, making auditability harder for downstream. *Fix:* Either give this an OQ ID before the resolution or move it to Assumptions Index with a `[RESOLVED 2026-08-19]` tag.

- **[low]** FR-16 status underspecified (§4.6) — "Reproduced status *(v1.1 — SHOULD)*" is a scope deferral without an Open Question or `[NOTE FOR PM]` on the decision criteria. *Fix:* Either promote to an OQ with acceptance criteria ("when do we fast-follow vs. defer?") or add a `[NOTE FOR PM]` on the trade-off.

## Substance over theater — strong

No furniture detected. Personas (Tester, Business User, Developer, Admin) are load-bearing: each appears in the permission matrix (FR-3), UJs, and success metrics. UJs are specific — Ravi ticks a suite, Sarah verifies and passes it on, Karim opens failures first — and each maps to FRs. The Vision (§1) is earned, not template: it names the exact problem ("test cases are printed on paper... report is manufactured after testing") and the thesis ("the report is a free byproduct of testing"). No innovation theater or NFR boilerplate. Even the "Working title — confirm" inline note is substance, not filler.

### Findings

None.

## Strategic coherence — strong

Clear thesis and consistent execution. The Vision's "free byproduct" thesis drives feature prioritization: FR-6 (Execute a Run) makes the tick screen the core surface; FR-9 (Auto-generated Report) eliminates the "manufacture" step; FR-11 (Failures-first developer view) serves the "developer opens straight to failures" promise. Success Metrics validate the thesis: SM-1 measures "100% of new test cycles executed in-platform" (validates the paper-to-platform shift), and SM-2 measures "time from last tick to developer-visible report" (validates the "free byproduct" claim). The counter-metric SM-C1 ("volume is not quality") is named to prevent perverse optimization. MVP scope (§7.1) is problem-solving, not platform-building, and the Non-Goals (§6) explicitly rule out "enterprise test management suite."

### Findings

None.

## Done-ness clarity — strong

Every FR has testable consequences, and they're explicit. FR-1's consequences: "Only Admins see project-create controls; non-admins get no such control (not merely disabled)." FR-6: "A Fail cannot be saved without a comment." FR-7: "Business User cannot start their stage before the Tester Run is completed." No "reasonable performance" or "user-friendly" hand-waving. Even non-functional requirements in §5 have bounds: "target one-tick-per-case with zero scrolling on a laptop" for usability, "business-hours availability" for availability. Success Metrics are measurable and time-bounded (SM-1: "within 4 weeks of rollout"; SM-2: "under 1 minute").

### Findings

None.

## Scope honesty — adequate

Omissions are explicit in §7.2 with rationale: "Reproduced status — fast-follow," "Email notifications — in-app only in v1," "Disagreement flag — COULD, deferred." Assumptions are tagged inline (`[ASSUMPTION]` in §5, confirmed by user 2026-08-19) and indexed in §10. The single `[NOTE FOR PM]` in FR-5 calls out edit approval workflow as a build-cost risk. Two findings: §5 mixes scale assumptions ( "~10–50 users") with hard requirements ("responsive web + PDF export as MUST") without distinguishing which are thresholds vs. observations; and FR-16's "v1.1 — SHOULD" scope callout should be an OQ or a more explicit deferral note.

### Findings

- **[medium]** NFR section conflates assumptions with requirements (§5) — The paragraph begins with scale ("~10–50 users") and availability ("business-hours availability") as observations, then shifts to usability ("responsive web + PDF export as MUST") as a requirement, without flagging which are `[ASSUMPTION]` vs. `[REQUIREMENT]`. *Fix:* Tag scale and availability as `[ASSUMPTION]` (already indexed) and keep usability/hard requirements untagged, or add a clarifying sentence.

- **[low]** FR-16 deferral lacks explicit rationale (§4.6) — "Reproduced status *(v1.1 — SHOULD)*" is noted but not explained as a scope callout with rationale, unlike §7.2 entries. *Fix:* Either inline a `[NOTE FOR PM]` on the decision criteria or cross-reference §7.2.

## Downstream usability — strong

Glossary (§3) is comprehensive and used consistently across FRs, UJs, and SMs. Domain nouns (Project, Role, Feature, Test Case, Step, Test Suite, Run, Runner, Status, Status History, Report, Submission, Re-run, Done Declaration, Edit Approval) appear identically throughout. FRs are globally numbered (FR-1 through FR-16) and cross-referenced in UJs and MVP scope. UJs each have a named protagonist (Ravi, Sarah, Karim, Maya). The document is designed to feed UX/architecture/epics generation, and the traceability is excellent. SM-1 and SM-2 explicitly reference the FRs they validate ("Validates FR-6, FR-9").

### Findings

None.

## Shape fit — strong

Internal tool, mid stakes — the PRD matches the right shape. UJ density is appropriate (four journeys across roles). Success metrics are operational (time-to-report, platform adoption), not user-facing KPIs. FRs are capability-spec style with testable consequences. No over-formalization (consumer-grade UJ theater) or under-formalization (missing acceptance criteria). The note "Internal tool. Working title — confirm" signals the right informality level. Downstream usability (§4) is explicitly called out as the purpose, so traceability matters — and it's there.

### Findings

None.

## Mechanical notes

- **Glossary drift:** None detected. Domain nouns are used consistently (Run, Runner, Status all stable).
- **ID continuity:** FRs are contiguous (FR-1 through FR-16). No gaps or duplicates. UJs are numbered (UJ-1 through UJ-4). SMs are numbered (SM-1 through SM-3, plus SM-C1).
- **Assumptions Index roundtrip:** Inline `[ASSUMPTION]` in §5 appears in §10 index. Confirmed user assumptions are listed in §10 with confirmation date. All roundtrips resolve.
- **UJ protagonist naming:** Each UJ has a named protagonist (Ravi, Sarah, Karim, Maya) and context inline.
- **Required sections present:** For an internal-tool PRD at mid stakes, all required sections are present: Vision, Target User (JTBDs, Non-Users, UJs), Glossary, Features with FRs and consequences, Cross-Cutting NFRs, Non-Goals, MVP Scope, Success Metrics, Open Questions, Assumptions Index. Document Purpose (§0) is a bonus.
