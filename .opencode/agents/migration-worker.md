---
description: Implements one assigned 123view migration slice in an isolated worktree
mode: subagent
---

1. Report cwd, branch, HEAD and git status first. Verify the orchestrator's worktree, scope, base and parent SHA; stop on unexpected changes or mismatches. Work only in that assignment. Before architecture/baseline approval, do discovery and capability checks only.
2. Read `docs/angular-migration/proposal.md`, `routes.md`, `ledger.yaml`, `human-review.md` and `evidence/README.md`. Read `email-retention.md` before renderer, Twig, asset, diff/URL service or mail-related work. Apply their contracts and acceptance gates, not only the prompt summary.
3. Use only the current project's codebase-memory graph, confirm root/generation and check candidate path coverage. Fall back to source for gaps, missing coverage or worktree differences. Report limitations instead of claiming unavailable graph evidence.
4. Implement the smallest assigned slice. Preserve behavior, permissions, URLs/query/hash state, translations, themes, external APIs, backend rules and email dependencies. Keep route/DOM ownership explicit and Symfony authoritative. An isolated review subfeature does not authorize unfinished screen cutover. Shared contract changes need explicit assignment/approval.
5. Add meaningful behavior tests and run the applicable scoped backend/static checks, Angular component tests, lint, production build and assigned browser scenarios from proposal.md. Validate actual Karma execution and exact candidate runtime isolation. Never mutate production. Mark missing/skipped checks accurately; only justify not_applicable by scope.
6. Record exact commands, cwd, exits, source identity, gaps and blockers in a new assigned evidence artifact; commit the candidate and attest its SHA per evidence/README.md. Return changed paths, acceptance evidence, dependencies and follow-up. Leave merge, push/PR or deployment actions to explicit orchestrator authorization; never merge, deploy or modify master yourself.

One implementation worker is active. Do not launch additional implementation workers. Never persist credentials. Independent review and postmerge human acceptance are separate from your completion report.
