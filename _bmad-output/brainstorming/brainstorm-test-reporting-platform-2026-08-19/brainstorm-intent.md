# Brainstorm Intent: Test Reporting Platform

## Product Intent

An internal web tool that replaces a paper-based test-execution process where test cases are printed, manually marked with pass/fail ticks and handwritten comments, then submitted to developers. The platform transforms the report from a manufactured artifact into a free byproduct of testing—capturing verdicts digitally at the moment of testing, eliminating transcription, and providing test cases with memory across fix/re-test cycles.

## Problem & Current Pain

The existing paper process requires testers to manually tick pass/fail on printed test cases and write comments by hand. These marked sheets are submitted directly to developers, who must extract failures, prioritize them, and fix them one by one. Re-testing requires a full reprint with only a handwritten date as version history. This creates transcription delays, lacks historical tracking, and forces developers to work from paper artifacts.

## Users & Roles

- **Admin**: Create projects, add/remove users
- **Business User**: Create/modify test cases AND run tests
- **Developer**: View reports, run cases to reproduce issues, fix bugs
- **Tester**: Update test status

## Core Workflow

1. Business user authors test cases per feature
2. Suite sent to project's developers
3. Business user runs tests (tick pass/fail + comment)
4. Report auto-generated and submitted to developers
5. Developer views failures first (prioritized)
6. Developer runs same case to reproduce
7. Developer fixes issues one by one
8. Business user re-runs (full suite or failed-only)
9. Repeat until feature declared done

## Key Insights

- **Report as free byproduct**: Zero transcription—testing directly creates the report
- **Test case memory**: Status history per case across fix/re-test cycles
- **Failures-first**: Developers see what needs fixing immediately
- **Multiple runners**: Same cases, separate runners (business user and developer)
- **Tick screen is the product**: The testing interface is the core value
- **Cycle as unit**: The real product unit is the cycle (run→report→reproduce→fix→re-run), not the report itself

## Scope (MoSCoW)

**MUST**:
- 4 roles/permissions
- Suites per feature
- Tick pass-fail+comment per runner
- Auto-generated report submitted to devs
- Failures-first view
- Re-run full-or-failed-only
- Status history per case

**SHOULD**:
- Reproduced status
- Fix-progress visibility
- Feature done-declaration (declared by business user)

**COULD**:
- Mid-run edits with admin approval
- Disagreement flag
- Admin dashboard

**WON'T**:
- CI hooks
- Mobile apps
- SaaS billing

## Resolved Decisions

- **Testing done is declared by the business user**
- **Mid-run test-case edits require admin approval** before taking effect