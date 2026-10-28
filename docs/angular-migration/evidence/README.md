# Immutable evidence convention

## Governance validator

G0a's replayable static check is `check-governance.cjs`. It parses YAML, validates all approved dependency edges/tracking fields and agent frontmatter/pointers, and rejects eight invalid gate/status mutations. It is not an application test or OpenCode runtime discovery test. Without adding project dependencies, prepare and run it from the repository root:

```sh
npm install --prefix /tmp/opencode/123view-g0a-validation --ignore-scripts --no-audit --no-fund js-yaml@4.1.1
NODE_PATH=/tmp/opencode/123view-g0a-validation/node_modules node docs/angular-migration/evidence/check-governance.cjs
node --check docs/angular-migration/evidence/check-governance.cjs
```

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
