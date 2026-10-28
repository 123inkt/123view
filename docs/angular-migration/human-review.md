# Postmerge human acceptance

## Workflow

1. After an independently reviewed, CI-passed exact candidate is merged into angular, record merge SHA separately from candidate SHA. Set lifecycle `merged`, then `human-review-pending`; assign a named human owner and an isolated runtime pinned to merge SHA.
2. Give that human a concise scenario list, seeded account roles, expected results, relevant route/contract changes and immutable evidence links. Keep credential values outside the ledger and artifacts; use the approved secure handoff mechanism.
3. The human reviews the affected flows on the merged runtime, including error/permission/history paths and relevant theme/locale states. G0a review checks governance usability and gate completeness; G0b checks that real verification gates work. Feature review exercises actual journeys.
4. Record reviewer identity, timestamp, tested merge SHA, scenario results, feedback and explicit acceptance in a new evidence artifact. Agents may record the human's decision but may not invent it or self-certify human acceptance.
5. Set `accepted` only when required automated/independent gates are valid and the human has accepted the merged SHA. Rejection remains `human-review-pending` with blockers and a linked repair slice. Repairs require a new candidate, review, CI and postmerge human review.

Human status vocabulary: `unassigned`, `pending`, `changes_requested`, `accepted`. Check status vocabulary remains that in `ledger.yaml`; human acceptance is a separate field, not a CI result. Before merge use `unassigned` or an assigned `pending` review, never `accepted`.

## Review packet

- Slice ID and affected dependencies; candidate, reviewer, CI and merge SHAs.
- Human owner; runtime source SHA; safe fixture identity; known gaps.
- Commands/log links and browser scenarios with expected vs observed behavior.
- Permission roles including owner/nonowner and denied users; auth/SSO caveats.
- URLs/history/anchors, theme/locale, mail/integration impacts as applicable.
- Decision, date, feedback and follow-up IDs.

The orchestrator maintains the queue and keeps human-pending work visible while other eligible work proceeds. Stacked children must reconcile after their parent merges and cannot inherit the parent's human acceptance. C1 is blocked until **all** applicable parity and human-review records are accepted. No deployment or master modification is part of this process.
