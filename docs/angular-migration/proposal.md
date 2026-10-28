# Angular migration: approved architecture and gates

## Authority and scope

The user approved this architecture/baseline proposal and authorized implementation. Approval is permission to build, not evidence of baseline parity. `ledger.yaml` is the execution register; `routes.md` governs routing, `email-retention.md` governs retained mail dependencies, `human-review.md` governs acceptance, and `evidence/README.md` governs verification records. Read all five before assigning or implementing a slice. Read the email inventory before changing renderers, Twig, shared diff/URL services or assets.

Target branch: **angular**. Never deploy or modify master. A worker changes only its assigned scope/worktree and never merges. Only one implementation worker runs at a time. Independent review may run separately. Feature or CI work is outside G0a.

## Architecture

- Angular 20 standalone components with lazy feature routes: core session/shell, projects, repository-browser, search, review-workspace, account, notifications, admin, shared presentation UI.
- Review-workspace has separate summary, tree, diff, threads, composer, lifecycle, revisions and live subfeatures. Shared presentation components do not acquire domain orchestration responsibilities.
- Route-scoped facades combine signals and RxJS. Components own transient form state; only session, theme and locale are global. No additional store framework.
- URLs own existing query/hash state. Symfony remains authoritative for permissions, domain rules, persisted preferences, drafts and seen state. Preserve themes, translations and feature flags, including authentication and AI.
- Typed feature HttpClients preserve external API schemas, statuses, filtering and pagination. Additive UI read-model DTOs and command processors reuse backend domain services, events, mail and integrations. Use OpenAPI-derived types where reliable; otherwise explicit contract-tested view-model DTOs. Do not reshape external APIs to fit a component.
- New forms use typed reactive forms. Do not build a generalized Symfony FormView framework.
- Retain JWT/cookie authentication initially. Any departure requires explicit approval. Preserve browser GitLab synchronization despite `MandatoryGitlabSyncSubscriber` skipping `/api`; solve the browser requirement without breaking external API consumers.
- Preserve owner/nonowner voters for comments, replies, rules and tokens, draft visibility, upload/asset hash behavior and authorization. Legacy GET mutations require explicit compatibility/security decisions, not silent authorization redesign.
- Coexistence uses explicit route and DOM ownership. Angular and Stimulus must never own the same DOM. Individually verified review slices do not authorize cutover of an unfinished review screen.

## Execution gates

1. **Discovery and approval:** verify cwd, branch, HEAD and clean status; stop on unexpected changes. Confirm assigned scope/base/parent. Before approval, perform discovery and capability checks only. Current architecture is approved; operational baseline remains unproven.
2. **G0:** G0a delivers governance; G0b delivers seeded isolated fixtures and working verification/CI. G0 is complete only when both satisfy their criteria. F1 remains blocked until the complete G0 gate is met; splitting G0 does not waive any check.
3. **Implement:** select a ready ledger item, assign an isolated worktree and record base/parent SHA before editing. Consult this project's graph, check exact-path coverage and use source fallback for gaps/staleness. A graph for the canonical checkout is not proof of a changed worktree. Never read another project's graph.
4. **Candidate:** run the scoped matrix below, append immutable evidence, commit and record candidate SHA. Failures, unavailable tools and skipped checks remain explicit. No credential values may enter commits, logs, screenshots, fixtures or evidence.
5. **Review and CI:** independent reviewer examines the exact candidate SHA and all acceptance evidence. CI must be required on PRs targeting angular and attest to that same SHA. No masked failures. Any fix, rebase or integration invalidates earlier review/check claims until rerun against the new candidate.
6. **Merge and acceptance:** orchestrator serializes authorized merges into angular, records merge SHA and schedules the mandatory postmerge human review. Merged is not accepted. Follow `human-review.md`; C1 waits for all parity and human reviews to be accepted.

An unrelated ready slice may proceed while review is pending. A dependent stacked branch may proceed only when its parent's candidate supplies its prerequisites and its dependency is explicitly recorded as provisional; it cannot claim accepted dependencies. Record parent SHA, target every PR at angular, reconcile the child after parent merge and rerun checks/review on the reconciled candidate. One active implementation worker remains the limit. Stop if prerequisites or ownership are unclear.

## Verification matrix

For each candidate, record exact commands, cwd, exit results, tool/runtime versions, fixture identity, runtime source SHA, logs and coverage limitations using `evidence/README.md`.

| Area | Required evidence when applicable |
| --- | --- |
| Frontend | `npm ci`, production build, lint and meaningful Karma ChromeHeadless component tests. Validate that the runner really launches and executes tests; scaffold creation-only specs are insufficient. Rendered interaction, loading/error, permission and history behavior must be asserted. |
| Backend | Scoped unit, functional and integration checks for changed behaviors/contracts; scoped PHP static/style checks. Avoid full-suite fallback unless justified. Symfony remains the authorization boundary. |
| Render/mail | Twig lint, container checks and email rendering/regressions for changes affecting Twig, mail, shared services or assets. Browser viewer migration does not delete the mail renderer. |
| Browser | Approved Playwright dependency with isolated seeded fixtures; exercise meaningful journeys against the exact candidate runtime. Screenshots/smoke alone do not prove parity. No production mutation. |
| CI | Required angular-target PR gates with exact SHA; fail on failing commands. Existing migration `|| echo` masking is a known concern to eliminate in G0b. |

Critical journey coverage: auth/onboarding/session expiry/SSO; projects/search/create; diff/history/keyboard/theme; comments/drafts/owner/nonowner/uploads; lifecycle/revisions; live updates; account/rules/admin. Mocked SSO alone does not certify the real flow; record real-flow gaps and keep acceptance blocked until verified or an explicit approval changes the requirement.

Docs-only G0a may justify runtime tests as not_applicable, but must validate governance consistency, dependencies, references and agent frontmatter. This exception does not apply to G0b or feature work.

## Baseline observations, not passes

Parent-reported observations at base `0786d3a44f855bba5e4b364197c80c3f9f38603f`: five Angular login/root/project-list routes, unfinished create/review/header links and scaffold specs; CI targets master without Angular gates. Existing smoke at `https://10.221.14.56:8443` covered login return to root/projects and `/app/projects/1/reviews`, but container SHA was unknown. It is not candidate parity evidence.

Parent reported git/gh authentication, remote angular equal to base, no open angular PRs and angular unprotected. Main containers bind `/home/dr/projects/123view` to `/app`; they do not test this worker's worktree. Host Node 26, container Angular Node 22.23.4 and PHP 8.4.27 were observed; host PHP/browser were not discovered. G0b must establish isolated candidate execution and required CI. Recheck capabilities rather than treating observations as successful checks.

Earlier `Agent not found: migration-worker` was resolved by config reload and a successful capability session. It was not implementation completion. Project agent files added by G0a still require discovery validation in the intended runtime.
