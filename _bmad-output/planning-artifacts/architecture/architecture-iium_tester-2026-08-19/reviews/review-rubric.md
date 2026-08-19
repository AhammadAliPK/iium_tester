# Architecture Spine Rubric Review — iium_tester

**Review Date:** 2026-08-19  
**Reviewer:** Architecture Rubric Walker  
**Verdict:** PASS-WITH-FINDINGS

## Executive Summary

The iium_tester architecture spine demonstrates strong architectural discipline with clear decision enforcement and comprehensive FR coverage. The spine successfully establishes invariant constraints that prevent divergence at the epic/story level while maintaining appropriate deference for operational concerns. However, several findings require attention before finalization.

## Detailed Checklist Assessment

### 1. Fixes Real Divergence Points for Level Below ✅ PASS

The spine successfully identifies and constrains all major divergence points:

**STRENGTHS:**
- **AD-1** prevents SPA/API drift by mandating single Laravel 13 deployable with Blade+Livewire only
- **AD-2** eliminates inconsistent lifecycle implementation across controllers/Livewire components
- **AD-3** prevents snapshot mechanism divergence by establishing single versioning approach
- **AD-4** structurally guarantees append-only history through database design
- **AD-5** centralizes permission enforcement to prevent role-check drift

**MINOR FINDING:** Could be more explicit about Livewire component boundaries - what prevents business logic from creeping into Livewire handlers vs Domain services?

### 2. Every AD's Rule is Enforceable ⚠️ PASS-WITH-FINDINGS

**CRITICAL FINDING - AD-3 Enforcement Ambiguity:**
AD-3 states "A run's reference to the exact version per Test Case **is** the Run snapshot — no separate copy" but the Structural Seed shows both `run_cases` (snapshot pivot) AND `test_case_versions`. This creates ambiguity:
- Are `run_cases` rows the snapshot or just references?
- How does AD-3 prevent developers from creating denormalized snapshot copies?
- The rule needs explicit database-level enforcement (e.g., unique constraints)

**SEVERITY:** HIGH - This ambiguity could lead to divergent implementation patterns

**FINDING - AD-5 Enforcement Gap:**
"Route middleware applies gates" but there's no explicit rule about Livewire component authorization. Livewire can bypass route middleware. Need explicit rule: "All Livewire components authorize via gates in `render()` or component middleware."

**SEVERITY:** MEDIUM - Livewire components could become permission backdoors

### 3. Deferred Items Analysis ⚠️ PASS-WITH-FINDINGS

**CRITICAL FINDING - Production Hosting Silence:**
Deferred section states "Production hosting target — MAMP covers local dev; university shared hosting vs VPS vs container decides ops posture" but this defers the ENTIRE operational envelope:
- No deployment pipeline decisions
- No environment configuration strategy
- No infrastructure-as-code approach
- No containerization vs bare metal decision

This violates checklist requirement: "especially the operational/environmental envelope (deployment & environments, infra/provider, operations). A whole dimension left silent is a finding."

**SEVERITY:** HIGH - Operational silence will cause deployment divergence

**FINDING - Scale Assumptions Unclear:**
AD-9 assumes local disk adequate for v1 but doesn't define v1 scale parameters. What's the attachment size limit? User count? Concurrent runs? Without scale boundaries, "adequate for v1" is unenforceable.

**SEVERITY:** MEDIUM - Scale assumptions need explicit thresholds

**POSITIVE:** Other deferred items (email notifications, async PDF, cloud storage) properly acknowledge that deferring them won't cause unit divergence.

### 4. Tech Stack Version Pinning ✅ PASS

**STRENGTHS:**
- All named technologies pinned with specific versions (Laravel 13.x, Livewire 3.x, Tailwind 4.x, PHP ≥8.3)
- Clear rationale for version choices
- MySQL 8.x primary with SQLite test alternative explicitly stated

**MINOR FINDING:** Alpine.js version shown as 3.x but no constraint on major version - given Alpine's rapid evolution, consider pinning more precisely.

### 5. FR Coverage ✅ PASS

**COMPREHENSIVE COVERAGE:** All 16 FRs explicitly mapped in Capability → Architecture Map:
- FR-1–3: Projects/users/roles → app/Http + gates
- FR-4: Authoring → app/Livewire + TestCaseVersion  
- FR-5: Edit approval → EditApprovalService + edit_approvals
- FR-6–7: Tick screen/pipeline → Livewire + RunManager/CycleManager
- FR-8: Status History → verdicts + attachments
- FR-9–12: Reports/submission/PDF → ReportBuilder + Blade + dompdf
- FR-13–14: Re-run/Done → CycleManager
- FR-15: Private runs → RunManager (type=private)

**STRENGTH:** Coverage map traces each FR to specific architectural components and governing ADs.

### 6. Dimension Completeness ⚠️ PASS-WITH-FINDINGS

**CRITICAL OPERATIONAL DIMENSION GAPS:**

**DEPLOYMENT & ENVIRONMENTS - UNADDRESSED:**
- No environment strategy (dev/staging/prod)
- No configuration management approach (env files vs config services)
- No asset compilation/pipeline strategy
- No migration deployment strategy

**INFRA/PROVIDER - UNADDRESSED:**
- No hosting target decision (shared hosting vs VPS vs containers)
- No database backup/retention strategy
- No logging/monitoring approach
- No CDN/asset delivery strategy

**OPERATIONS - UNADDRESSED:**
- No deployment automation approach
- No health check/monitoring strategy  
- No incident response procedures
- No data backup/recovery procedures

**SEVERITY:** HIGH - Operational dimension is entirely silent, violating checklist requirement that "every dimension the altitude owns is decided, deferred, or an open question."

**POSITIVE:** Application architecture dimensions are thoroughly covered (presentation, HTTP, domain, persistence, styling, state management).

### 7. Diagram Validity ✅ PASS

**MERMAID VALIDATION:** Both diagrams are syntactically valid Mermaid:
- Dependency direction graph (graph TD) is valid and clearly shows architectural layering
- ER diagram (erDiagram) is valid with proper relationship notation

**STRUCTURAL CLARITY:**
- Dependency graph successfully encodes the rule that `app/Domain` imports Models only
- ER diagram comprehensively shows all entities and their relationships
- Entity relationships align with AD-3 (versioning) and AD-4 (append-only verdicts)

**STRENGTH:** Diagrams actually convey architectural structure, not just visual decoration.

### 8. Terse Style ✅ PASS

**STRENGTHS:**
- Decisions stated without extensive rationale
- No template comments or placeholder text visible
- Rules are clear, prescriptive statements
- No "why" explanations cluttering the architecture

**MINOR FINDING:** Some AD rules combine multiple concerns (e.g., AD-1 covers monolith, rendering model, AND auth). Consider splitting atomic rules for easier enforcement checking.

## Summary of Findings by Severity

### HIGH SEVERITY (Must Fix Before Finalization):

1. **AD-3 Enforcement Ambiguity:** The "single versioning mechanism" rule creates confusion between `run_cases` as snapshot vs reference. Need explicit enforcement mechanism.
   
2. **Operational Dimension Silence:** Deployment, environments, infra/provider, and operations are completely unaddressed - violating checklist requirement for altitude-owned dimensions.

### MEDIUM SEVERITY (Should Address):

3. **AD-5 Livewire Authorization Gap:** Route middleware doesn't cover Livewire components - explicit authorization rule needed for components.

4. **AD-9 Scale Assumptions Undefined:** "Adequate for v1" is unenforceable without explicit scale parameters.

### LOW SEVERITY (Consider Addressing):

5. **Alpine.js Version Pinning:** Consider more precise version constraint than 3.x.

6. **AD Rule Atomicity:** Some ADs combine multiple concerns - consider splitting for clearer enforcement.

## Coverage Verification

**PRD Requirements:** All 16 FRs mapped in Capability → Architecture table  
**Epics Coverage:** All 5 epics (E1-E5) referenced in binds and coverage map  
**NFRs:** Addressed through specific ADs and architectural choices  
**UX Requirements:** IIUM DLS tokens comprehensively integrated via AD-6

## Conclusion

This architecture spine demonstrates strong architectural thinking with clear invariant establishment and comprehensive requirement coverage. The Dependency Direction graph and ER diagram successfully convey structural relationships. The spine effectively prevents most divergence points at the epic/story level.

However, the HIGH severity findings around AD-3 enforcement ambiguity and operational dimension silence prevent a clean PASS. Addressing the operational envelope and clarifying the versioning enforcement mechanism would elevate this to a solid PASS.

**RECOMMENDATION:** Address the HIGH severity findings before finalizing the spine for build agents. The MEDIUM severity findings should be resolved to prevent potential implementation drift.
