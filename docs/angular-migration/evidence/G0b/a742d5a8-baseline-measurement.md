# G0b discovery measurement preserved from a742

Source: `/tmp/opencode/g0b-baseline/REPORT.md`, read in full by the governance worker on 2026-10-28. Measurements were performed by the separate baseline worker, not rerun here. Temporary raw logs remain under `/tmp/opencode/g0b-baseline`; they are not durable repository artifacts. This bounded summary preserves failures, capabilities and follow-up without copying large logs or credentials.

Report SHA-256 at preservation: `4c5870d70d5e79f1d26a0e468a9f77fdeea47e215fd5d73cf25f7d7e2668bf56` (`sha256sum /tmp/opencode/g0b-baseline/REPORT.md`, exit 0). This is document identity, not candidate verification.

## Identity and boundary

- Measured source/base/stack parent: `a742d5a8cdd793fe09af74fc0416b30d2130fa9b`; worktree `/home/dr/projects/123view-g0-verification`, branch `migration/g0-verification`, clean before/after; no tracked changes.
- G0a was unmerged/unaccepted. This was **discovery only**, not authorized feature implementation or advancement of G0b lifecycle. No CI, review, parity or human acceptance claim.
- Host Node 26.3.0/npm 11.16.0; isolated image Node 22.23.4/npm 10.9.9. PHP image 8.4.27/Composer 2.10.3; installed PHPUnit 13.4.3. Graph unavailable for the measured worktree; source fallback used.
- Temporary command containers mounted only the measured checkout, used isolated entrypoints and host UID/GID. PHP checks used network isolation, an intentionally unusable database and null mail. No main services/database or live sessions were used. No complete fixture/runtime environment was established.
- These results apply **only to a742**, not the repaired governance candidate. They are historical discovery evidence, not current ledger check passes.

## Measured results (reported exits)

| Scope / exact command where supplied | Result | Original log |
| --- | --- | --- |
| `frontend`: `npm ci --cache /tmp/opencode/g0b-baseline/npm-cache`; `npm run build`; `npm run lint` | Each 0 on host Node 26; Node 22 cross-check also each 0. Install reported 58 vulnerabilities; build emitted bundle-size and NG8102 warnings. | angular-ci.log, angular-build.log, angular-lint.log, angular-node22.log |
| `frontend`: initial `npm test -- --watch=false --browsers=ChromeHeadless` | 1; initially missing Chrome, no execution. Superseded in capability only by actual browser measurement below. | angular-test.log |
| `frontend`: `CHROME_BIN=/tmp/opencode/g0b-baseline/browsers-node22/chromium-1194/chrome-linux/chrome npm test -- --watch=false --browsers=ChromeHeadless` | **1; Chrome Headless 141 connected, 5 executed, 4 failed, 1 success.** Login/Header missing HttpClient; App missing TranslateStore. Passing guard wrapper-existence case has no meaningful guard assertion. Component gate FAILED. | angular-karma-browser.log |
| Repository root: `npm ci --cache /tmp/opencode/g0b-baseline/npm-cache`; `npm run build`; `npm run eslint`; `npm run stylelint` | Each 0 for legacy assets; 3 reported audit findings. Mail assets retained. | legacy-ci.log, legacy-build.log, legacy-eslint.log, legacy-stylelint.log |
| Isolated PHP, repository root: `php vendor/bin/phpunit --testsuite unit --filter "HtmlMailExtensionTest\|CommitMailServiceTest\|CommentMailServiceTest\|MailRecipientServiceTest\|MailSubjectFormatterTest"` | 0; 14 tests, 92 assertions | backend-mail-unit-registered.log |
| Same PHP context: `php vendor/bin/phpunit --testsuite integration --filter MailNotificationHandlerProviderTest` | 0; 1 test, 6 assertions; source-confirmed no DB/fixture operation | backend-integration.log |
| Same PHP context: `php bin/console lint:twig templates` | 0; 81 templates | backend-twig-after-autoload.log |
| Same PHP context: `php bin/console lint:container` | **1 in test environment:** RemoteEventHandler definition replaced, constructor argument missing. Dev environment exit 0 does not repair the test failure. | backend-container-after-autoload.log, backend-container-dev.log |
| Same PHP context: `php vendor/bin/phpcs src/Twig/HtmlMailExtension.php tests/Unit/Twig/HtmlMailExtensionTest.php`; `php vendor/bin/phpstan analyse src/Twig/HtmlMailExtension.php --no-progress`; `php vendor/bin/phpmd analyze src/Twig/HtmlMailExtension.php --no-progress --format text --ruleset phpmd.xml` | Each 0 after bootstrap/plugin registration. Narrow static feasibility only, not full-suite acceptance. | backend-phpcs-registered.log, backend-phpstan-registered.log, backend-phpmd-registered.log |
| Temporary Playwright synthetic Chromium launch/setContent/button click/close | 0; synthetic about:blank only, **not an application journey**. Full inline command was not retained in the report; do not invent a replay command. | playwright-capability.log |

The report retains earlier bootstrap failures (missing runtime/extensions) and timed-out browser downloads as resolved capability attempts, not application fixes. Browser tooling remained outside repository; no project Playwright dependency or CI change was made. No audit fixes were attempted.

## G0b substeps and blockers (not new feature scope)

1. Fix deterministic Karma providers and replace creation/scaffold-only assertions with meaningful rendered interactions, error/permission/history cases; retain failure evidence.
2. Repair test-only RemoteEventHandler registration without changing production rules; require test and dev container checks. Parent report identified `set(...)->public()` in `config/services_test.php` replacing the existing definition; implementation must verify source before repair.
3. Build a separately isolated seeded MySQL/local Git/mail/live harness, candidate-only mounts and serving, source/build identity, fixture roles including owner/nonowner/denied/admin, and safe ports/network boundaries. No DB or functional fixture tests ran in measurement.
4. Run fixture-backed functional/integration/mail rendering and actual Playwright candidate journeys. Synthetic capability and narrow mail units do not certify rendered email parity. Real SSO requires safe real-flow verification beyond mocks.
5. Establish angular-target exact-SHA CI with unmasked failures and separately verify required-gate enforcement. Existing master-only workflows are not migration gates.

G0b/G0/F1 remain blocked. The ledger retains these as discovery blockers/substeps, not passes on the repair SHA. No worker from this measurement remains active; report says temporary containers terminated, no main service was reconfigured, and no tracked change was produced.
