# DLS → Architecture Spine Reconciliation Review

**Reviewer:** Architecture Reconciliation Agent  
**Project:** iium_tester (Laravel test-reporting platform)  
**Review Date:** 2026-08-19  
**Documents Analyzed:**
- IIUM Digital Design Language System (HTML)
- Architecture Spine (AD-6 focus + Capability Map)

---

## Executive Summary

**Verdict: GAPS FOUND — Minor to Moderate Severity**

The architecture spine AD-6 captures the core DLS intent but drops several load-bearing implementation details that builders would need. The capability map partially acknowledges styling but omits critical dark theme mechanics and component vocabulary patterns.

---

## Detailed Findings

### 1. **MEDIUM — Theme Persistence Mechanism Not Documented**

**Issue:** AD-6 states "dark rides the `data-theme="dark"]` attribute as in the DLS" but does not specify the localStorage persistence mechanism (`localStorage.setItem('iium-theme', newTheme)`) shown in the DLS JavaScript.

**Impact:** Builders may implement theme switching without persistence, causing user preference to be lost on page refresh/reload. The DLS demonstrates a complete working system with localStorage integration, but AD-6 only captures the CSS attribute selector syntax.

**DLS Reference:** Lines 334-360 in HTML show complete `toggleTheme()` function with localStorage read/write on page load.

**Spine Coverage:** AD-6 mentions `data-theme="dark"]` attribute but no localStorage mechanism.

**Recommendation:** Extend AD-6 to specify: "Theme preference persists via localStorage key 'iium-theme'; Blade layouts must read this on initial render and apply `data-theme` attribute server-side for immediate paint (avoid FOUC)."

---

### 2. **MEDIUM — Dark Theme Surface Architecture Incomplete**

**Issue:** AD-6 references "surfaces" generically but does not enumerate the full dark theme surface architecture specified in the DLS:
- `--iium-deep: #030F0D` (deep turquoise-black base)
- `--iium-surface: #081D1A` (standard component background)  
- `--iium-surface-raised: #0F2B26` (hover/active surface)

**Impact:** Builders may inherit Tailwind defaults or implement incomplete dark mode, breaking visual hierarchy in dark theme. The DLS provides a complete 3-tier surface system; AD-6's generic reference risks inconsistent implementation.

**DLS Reference:** Lines 59-61 define complete dark surface architecture with hex values.

**Spine Coverage:** AD-6 mentions "surfaces" generically, no specific token mapping.

**Recommendation:** AD-6 should explicitly list the dark surface tokens or require Tailwind theme extension mapping for all 6 surface tokens (light: 3, dark: 3).

---

### 3. **LOW — Component Vocabulary Missing from Capability Map**

**Issue:** The DLS defines 5 core component patterns with specific class naming conventions:
- `.iium-card` (card container)
- `.btn` / `.btn-primary` / `.btn-gold` (buttons)
- `.form-control` (inputs)
- `.brand-label` (labels)
- `.theme-toggle` (switcher)

The capability map (Row 175: "NFR styling / responsive / themes") mentions "DLS Blade components" generically but does not enumerate this vocabulary.

**Impact:** Low but real — builders may create ad-hoc component classes instead of reusing DLS-defined patterns, leading to inconsistency and maintenance debt.

**DLS Reference:** Lines 110-221 define complete component CSS with class names and interaction states.

**Spine Coverage:** Capability map mentions "DLS Blade components" but no vocabulary list.

**Recommendation:** Add a component vocabulary section to AD-6 or companion artifact listing the 5 core DLS component classes and requiring their reuse.

---

### 4. **LOW — Motion Curve Underspecified**

**Issue:** AD-6 references "ease-spring motion" but does not specify the cubic-bezier parameters `cubic-bezier(.34, 1.56, .64, 1)` or duration (250ms) defined in the DLS.

**Impact:** Motion feel may be inconsistent across interactions — some components may use default Tailwind easings, breaking the IIUM brand motion character.

**DLS Reference:** Lines 52-53 define `--iium-ease-spring: cubic-bezier(.34, 1.56, .64, 1)` and `--iium-duration: 250ms`.

**Spine Coverage:** AD-6 mentions "ease-spring motion" but no cubic-bezier parameters or duration.

**Recommendation:** AD-6 should require Tailwind theme extension for `ease-spring` with the exact cubic-bezier value and 250ms default duration.

---

### 5. **MINOR — Font Stack Alias Not Acknowledged**

**Issue:** The DLS defines `--iium-teal` as an alias for `--iium-turquoise` (line 26: `--iium-teal: #00928F; /* Alias for --iium-turquoise */`) for legacy compatibility. AD-6 references "turquoise" and "gold" but does not mention the teal alias.

**Impact:** Very low — mostly relevant if migrating legacy systems. New builds should prefer `--iium-turquoise`, but builders may encounter `--iium-teal` in copy-pasted DLS code.

**DLS Reference:** Line 26 documents teal alias explicitly.

**Spine Coverage:** No mention of teal alias.

**Recommendation:** Consider documenting the teal alias in AD-6 for completeness, especially if integrating with legacy IIUM systems.

---

## Critical Gap: Status Color Semantics

**CRITICAL — Pass/Blocked/Fail Status Colors Missing from Both Documents**

**Issue:** The DLS defines brand colors (turquoise, gold) and general UI tokens but **does not define status color semantics** for test case verdicts (Pass = ?, Blocked = ?, Fail = ?). The architecture spine also does not define this in AD-6 or the capability map.

**Impact:** This is a critical gap for a test reporting platform. The PRD mentions "Pass," "Blocked," "Fail" (implied by testing workflow) but neither the DLS nor the architecture spine provides color semantics for these states. Builders will default to ad-hoc choices (likely red/green) that may conflict with brand guidelines or accessibility standards.

**DLS Coverage:** None — DLS defines brand colors but not test status semantics.

**Spine Coverage:** None — AD-6 and capability map are silent on status colors.

**Recommendation:** This requires a new architectural decision or DLS extension defining:
- Pass status color (likely turquoise variant for alignment with brand)
- Blocked status color (likely gold for warning/pending)
- Fail status color (needs definition — possibly a red from an extended palette)

**Severity:** HIGH — affects core functionality (test reporting) and brand consistency.

---

## Alignment Strengths

The following DLS elements are well-captured in AD-6:

✓ Brand color tokens (turquoise `#00928F`, gold `#D59F0F`) explicitly mentioned  
✓ Font stack documented (Roboto Slab / Inter / Barlow Condensed)  
✓ Radius tokens referenced (14px/8px)  
✓ Tailwind theme extension approach correctly specified  
✓ Light/dark theme concept acknowledged  
✓ No-raw-hex rule correctly prevents drift  

---

## Recommendations Summary

1. **Extend AD-6** to specify localStorage persistence mechanism (`iium-theme` key)
2. **Extend AD-6** to enumerate dark theme surface tokens (`--iium-surface`, `--iium-surface-raised`)
3. **Add component vocabulary** section to AD-6 or companion document
4. **Specify motion curve parameters** in AD-6 (cubic-bezier + duration)
5. **Define status color semantics** — new architectural decision required for Pass/Blocked/Fail colors
6. **Consider documenting teal alias** for legacy integration scenarios

---

## Conclusion

The architecture spine successfully captures the DLS's visual identity core but misses several implementation-critical details that would affect build quality, user experience (theme persistence), and brand consistency (status colors). The gaps are primarily in mechanics (localStorage, dark surfaces) and one critical semantic area (test status colors).

**Overall Assessment:** AD-6 is 70% reconciled with the DLS. The remaining 30% consists of implementation specifics and one critical semantic gap (status colors) that should be addressed before build commencement.

**Risk Level:** MEDIUM — gaps are fixable during build but may cause rework or inconsistencies if addressed ad-hoc by individual builders.
