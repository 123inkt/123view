# Immutable evidence convention

## Governance validator

G0a's replayable static check is `check-governance.cjs`, with synthetic positive/negative cases in `check-governance.test.cjs`. It parses YAML, validates approved dependency edges/tracking fields, lifecycle gates and agent frontmatter/pointers. Regression fixtures are not real slice evidence. It is not an application test or OpenCode runtime discovery test. Without adding project dependencies, prepare and run it from the repository root:

```sh
npm install --prefix /tmp/opencode/123view-g0a-validation --ignore-scripts --no-audit --no-fund js-yaml@4.1.1
NODE_PATH=/tmp/opencode/123view-g0a-validation/node_modules node docs/angular-migration/evidence/check-governance.cjs
node --check docs/angular-migration/evidence/check-governance.cjs
node --check docs/angular-migration/evidence/check-governance.test.cjs
```

## Ledger schema version 2: state gates

Version 2 preserves all slice IDs, dependencies and acceptance criteria. It adds `execution_mode` (default `implementation`), `provisional_stack` (default null), `independent_review.worktree`, and a `sha` on each passing check used to advance a gate. Existing append-only version 1 artifacts remain historical, not current approval. `acceptance_substeps` and discovery evidence describe work still needed; they do not create new scope or substitute for passing checks.

The validator checks state invariants, not a historical event log. Every state at or after a gate must satisfy that gate, so changing a label directly cannot bypass it. Human and CI decisions still require genuine linked evidence; static validation cannot prove a log's truth, runtime isolation or tool-enforced permissions.

| State / activity | Required invariant |
| --- | --- |
| `proposed`, `execution_mode: discovery` | Read-only discovery/capability measurement may precede dependency acceptance. Stay proposed with no candidate/merge identity or provisional implementation authorization. G0b measurements on unaccepted a742 fit here. |
| `ready` and later | Architecture approved. Ordinary dependencies integrated (`merged`, `human-review-pending`, or `accepted`), no blockers or human changes requested; or explicitly authorized provisional stack below. One implementation worker maximum. |
| Every feature `ready` and later | Hard foundation: `baseline_verified: true` AND G0/G0a/G0b integrated with valid checks, review and required CI. Stacking cannot bypass G0. Setting the boolean alone does not satisfy it. G0a/G0b can establish the foundation before this feature gate. |
| `candidate` | Exact source candidate SHA recorded; failing/incomplete checks may remain visible while awaiting repair. This state is not approval. |
| `independently-reviewed` and later | Independent review passed at candidate SHA, separate absolute review-worktree path, and entire check matrix complete. Every pass has evidence and matches candidate SHA; every NA has scope rationale. Governance must pass; G0b cannot waive any operational matrix check as NA. |
| `CI-passed` and later | Review gate plus passed required CI, verified required-gate enforcement, run URL and exact candidate SHA. CI may run before review, but the lifecycle label cannot skip the review gate. |
| `merged` and later | Above gates plus merge SHA; provisional stack reconciled/removed. Pending human acceptance does not block ordinary downstream work. |
| `human-review-pending` | Named human, tested merge SHA and pending/changes_requested outcome. Human rejection blocks dependents; keep repairs visible. |
| `accepted` | Passed checks/review/CI plus explicit human acceptance, owner/evidence and matching merge SHA. Human acceptance cannot appear on an unmerged candidate. |
| C1 `ready` and later | Every dependency lifecycle and human outcome accepted. Neither pending humans nor stacking can authorize cleanup. |

### Authorized provisional stacking

Record this only on `ready` through `CI-passed` implementation work. All nonintegrated dependencies must be explicitly listed exactly once; integrated/accepted dependencies must not be relabeled as provisional. Each listed parent is an unmerged candidate with completed exact-SHA checks, supplied prerequisites and evidence. This does not fake independent review, CI, merge or human acceptance of the parent. G0 aggregate and C1 cannot stack; features still require the complete foundation. G0b may be explicitly authorized on a checked G0a candidate to build the baseline, but discovery needs no such implementation authorization.

```yaml
provisional_stack:
  authorized_by: named orchestrator or authorizing user
  authorization_evidence: evidence/SLICE/authorization.md
  parents:
    - id: P1
      candidate_sha: <exact 40-character SHA of unmerged parent>
      evidence: evidence/P1/candidate.md
      supplied_prerequisites: [Explicit prerequisite supplied by this candidate]
parent_sha: <one declared parent SHA actually used as branch parent>
```

Once a parent merges, reconcile the child against the merged base, remove the provisional record, record its new parent/base/candidate identity and rerun applicable checks/review/CI. A stale parent SHA, incomplete parent checks, missing authorization or a provisional child merge is invalid. Integrated dependencies with pending human review allow ordinary ready work; C1 alone waits for all applicable human acceptances.

For executable independent review, the orchestrator assigns a separate clean detached exact-final-SHA review worktree, never main or implementation checkout. The reviewer records that worktree and verifies candidate-only mounts/build/runtime identity. Static path comparisons supplement this instruction but cannot prove actual isolation or runtime agent discovery.

## Record a run

Add a new Markdown artifact under `evidence/<slice>/` for each candidate/check/review event. Use `<UTC timestamp>-<short SHA>-<purpose>.md`. Once committed, append a new artifact to correct or supersede it; never rewrite old results. Link records from the ledger with explicit source/candidate SHA and, after commit, the artifact's Git commit SHA. Do not put credentials, auth headers, cookies, private fixture data or credential-bearing URLs in artifacts.

Every artifact contains:

```text
Slice / purpose / UTC timestamp / author or agent
Worktree / branch / base SHA / parent SHA / source SHA
Candidate SHA (or precommit source identity and later immutable attestation link)
Changed paths / scope / dependency and acceptance criteria
Graph project, root, generation, coverage, fallback and limitations
Environment/tool versions / fixture identity / runtime source SHA
For each check: exact command, cwd, start/result, exit code, status, log reference
CI run URL and SHA / independent reviewer identity and SHA (or not_run)
Merge SHA / human owner and status (or not yet merged/unassigned)
Gaps / blockers / follow-up / superseded evidence links
```

A commit cannot embed its own SHA. Precommit evidence must explicitly identify its base plus changed paths and mark candidate SHA pending. After committing, create a separate append-only attestation naming the checked source commit and checks. Report the final evidence-only commit SHA to the parent; review that exact final SHA. Do not silently treat checks on an earlier tree as checks on a changed candidate. For an evidence-only delta, record and validate its exact scope; any functional delta requires fresh applicable checks. The final commit identity can be linked by the parent's ledger/review artifact without a recursive self-hash.

## Status and scope

- `passed`: the check executed and met its acceptance criteria; include exact exit result and observations.
- `failed`: executed and failed; preserve failure evidence even if later repaired.
- `blocked`: could not execute/complete due to a stated prerequisite or capability.
- `not_run`: not attempted; explain who must run it and when.
- `not_applicable`: justify why the scope does not require it; never use for a missing tool or a failing check.

Exit zero alone does not prove meaningful tests or a real candidate runtime. Record test counts and behavior exercised; a creation-only spec, a mocked-only SSO flow or an unknown-SHA container is insufficient. Never turn missing/skipped checks into passes. Redact sensitive output before recording it, while preserving the failure and command identity.

## Evidence invalidation

Tie reviewer and CI decisions to exact candidate SHA. Rebase, fixes and integration invalidate stale results. Preserve old records, add reruns, and update current ledger pointers. Verify the postmerge runtime SHA separately. Scoped docs checks may replace runtime checks for G0a only with explicit not_applicable justifications; G0b must actually establish npm/build/lint/Karma/browser/backend/CI capability and isolated fixtures.
