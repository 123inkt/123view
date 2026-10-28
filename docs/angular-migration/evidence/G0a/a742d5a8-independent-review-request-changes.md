# Independent review: REQUEST CHANGES

- Reviewed final SHA: `a742d5a8cdd793fe09af74fc0416b30d2130fa9b` (source commit `a525140f8fd3cc1985f8dec23b231a7c60771f49`).
- Reviewer: independent migration reviewer; findings and command results relayed by the orchestrator in the repair assignment on 2026-10-28. This artifact records that decision, not a review performed by the implementing worker.
- Review worktree: `/home/dr/projects/123view-g0-review`, clean, detached at the reviewed SHA as reported. Graph unavailable for that worktree; reviewer used source reads.
- Decision: **request-changes**, two P2 findings. G0a remains unmerged/unaccepted. Human acceptance is not yet applicable.

## Findings

1. **P2 — lifecycle gate validation missing** (`evidence/check-governance.cjs`, original lines 54–77). Existing structural validation accepted F1 implementing with incomplete G0, G0a merged without review/CI, and C1 accepted while its prerequisites remained unaccepted. Add required-check, exact-SHA independent review/required CI and merge/human gates; enforce ordinary dependency readiness, non-bypassable G0 foundation and C1 human acceptance. Explicitly authorized provisional stacking must be modeled without faking acceptance. Pending human review of integrated dependencies must not unnecessarily block ordinary development. Add positive valid scenarios as well as regression mutations.
2. **P2 — reviewer execution isolation not persisted** (reviewer and orchestrator agent instructions). Require a separate isolated exact-final-SHA review worktree, not the implementation checkout or main bind-mounted runtime. Verify actual checkout/mount/runtime identity rather than relying on prompts as tool-enforced permissions.

## Reported checks and limitations

The reviewer reported the existing validator's eight mutation cases passing, `node --check` passing and baseline-to-HEAD `git diff --check` passing. Additional in-memory mutations exposed the three invalid lifecycle cases above. Reported validator invocation was `NODE_PATH=/tmp/opencode/123view-g0a-validation/node_modules node evidence/check-governance.cjs`; command-specific cwd and full syntax/diff invocations were not included in the relay, so this artifact does not invent them or claim to rerun them. Passing the old eight cases did not establish lifecycle correctness.

The a525-source/a742-envelope convention was acceptable only if review and CI attest to the **final** SHA. Changed agent-definition runtime discovery remained `not_run`; restrictions were instruction-only. Pure-governance application tests were justified `not_applicable`, not baseline passes. CI was not established, and no approval or merge was authorized by this review.

Repair evidence must be new and append-only. This failed decision remains tied to a742 even after repairs; fresh independent review must name the new final candidate SHA.
