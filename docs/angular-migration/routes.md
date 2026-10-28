# Browser route and ownership contract

## Before a route changes

1. Inventory Symfony route attributes/configuration and Angular routes at the candidate base. Record exact paths, methods, defaults, requirements, redirects, query/hash semantics, authentication and voters in slice evidence. This document is a required contract, **not an exhaustive verified route export**.
2. Assign one browser renderer and one DOM owner per route/region. Record the previous owner, intended owner, cutover gate and rollback behavior. Keep legacy ownership until the entire affected screen's parity gate passes.
3. Test direct loads, refresh, back/forward, redirects, denied/expired sessions, not-found and preserved links from mail/external consumers. Assert URL state restoration, not merely component creation.
4. Update this register with verified route details and evidence links before cutover. X1 reconciles the complete export against the register; unidentified routes block cleanup.

## Protected URLs and state

| Contract | Target owner / accountable slice | Required parity |
| --- | --- | --- |
| `/` and existing login routes/return behavior | core session/shell / F1 | Preserve root/login redirects, return URL, onboarding, expiry, SSO, auth flags and browser Git sync. Inventory exact login/SSO paths in F1. |
| `/app/projects` | projects / P1, shell F1 | Preserve project access, activity, theme/locale and navigation. |
| Existing project review-list routes, including observed `/app/projects/1/reviews` | projects / P2 | Preserve repository identity, filtering, order, pagination, denied/empty states. Concrete numeric example is not a new route definition. |
| `/app/{repositoryName}/review/cr-{reviewId}` | review-workspace / R1–R10 | Canonical review URL remains unchanged; preserve repository-name handling and all nested review behavior. Cutover waits for the complete screen. |
| `/app/reviews/{id}` | Symfony redirect, compatibility obligation R1/X1 | Retain deprecated redirect to canonical review URL, including supported URL state. |
| Existing repository branch/revision browsing and creation URLs | repository-browser / P3, P4 | Inventory exact paths; keep external/deep links, selection and permissions. |
| Existing search URLs | search / S1 | Preserve query semantics, filters, page and order; do not invent replacement paths. |
| Existing account/settings/token/Git-sync URLs | account / U1, U2 | Preserve owner authorization, browser notification and integration flows. |
| Existing rule/history/notification-viewer URLs | notifications / N1, N2 | Preserve rule ownership and read marking; separate Angular browser viewer from retained email renderer. |
| Existing admin users/repos/credentials/webhooks URLs | admin / A1–A3 | Preserve role gates, validation and external integration contracts. |
| Auxiliary and error routes | relevant owner / X1 | Audit complete Symfony/Angular inventory, redirects, 403/404/error handling and unmatched routes. |

All rows describe **target** ownership, not a claim that Angular already owns the route. The initial migration state is legacy browser ownership except the existing Angular scaffold; exact current ownership must be inventoried by G0b/F1 before changes. Cutover evidence must identify ownership explicitly.

Keep existing query keys `search`, `page`, `order`, `filePath`, `tab`, `action` and existing `#focus:comment`, reply and line anchor formats. Discover exact serialization from source and fixtures; do not reinterpret these shorthand anchor families as replacement syntax. URL state is authoritative for navigation, while Symfony remains authoritative for persisted preferences, seen and draft state. Test encoding, absent/default values and history restoration.

## Boundary rules

- External API URLs, response schemas/status codes, filters and pagination remain stable; additive browser DTO endpoints do not transfer domain authority to Angular.
- Angular and Stimulus may coexist only in disjoint ownership boundaries. No double event handlers, shared DOM ownership or accidental mixed partial replacement.
- A migrated review subfeature may be tested in isolation but cannot activate an incomplete review screen. R1–R10, P4 and applicable surrounding routes must demonstrate the affected end-to-end journeys before screen cutover.
- Permission-hidden controls supplement backend voters; they never replace server enforcement. Preserve legacy GET mutation semantics unless a separately approved security/compatibility decision changes them.
- Mail URLs continue to resolve after browser migration. Review `email-retention.md` before changing URL generation or notification rendering.
