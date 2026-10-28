# Email retention and browser-viewer separation

## Retain through browser migration

The mail pipeline remains a backend responsibility. Angular browser parity is not authorization to remove its renderers or dependencies. Preserve:

- `templates/mail` and `assets/styles/mail`;
- the `@styles` Twig mapping;
- mail Twig filters/extensions, view models, images and inline/style dependencies;
- shared diff rendering and URL services consumed by mail;
- notification rules, filters, recipients, frequency, history/read behavior and delivery events;
- backend domain events, integrations and authorization used by notifications.

These are retention categories, not a claim of a complete source inventory. Before removing or moving any candidate dependency, enumerate inbound callers and template/asset references from the current project's graph, check coverage and read/grep source for Twig/config/dynamic references. Record exact paths and consumers in evidence; an empty graph result alone cannot justify deletion.

## N2 browser boundary

Parent source investigation identified `RuleNotificationController` as a browser route that renders mail Twig and marks the notification read. N2 must provide an Angular browser notification viewer while preserving the mail renderer and current read-marking authorization/side effects. Reuse domain services; do not make Angular the email renderer. Confirm the controller/route from source in N2 rather than treating this governance record as candidate verification.

## Required acceptance evidence

For any change touching these dependencies, compare seeded before/after HTML/text mail outputs, links, images, themes and diff content; verify rule recipients/filters/frequency and delivery behavior where affected. Include scoped unit/integration/functional tests, Twig lint, container checks and actual mail rendering. Use isolated delivery sinks and nonproduction fixture recipients. Record exact commands, fixture IDs and SHAs with no credentials or personal data.

C1 may remove browser-only Twig/Stimulus after all parity and human-review acceptance, but only after proving each deleted dependency has no retained mail consumer. Keep mail and shared dependencies whenever ownership is uncertain; record the blocker instead of guessing.
