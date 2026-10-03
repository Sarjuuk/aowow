# Security regressions

## Continuous integration

[Security tests](../.github/workflows/security-tests.yml) runs the complete suite
on pushes to any branch and pull requests affecting PHP/CLI sources, endpoints,
localization, templates, static assets, setup/schema files, Composer dependencies,
HTTP/legacy policies, tests or workflows. Root README/review-only changes do not
start CI. The Actions page also offers **Run workflow** for a complete manual run.
The [GitHub workflow syntax](https://docs.github.com/en/actions/reference/workflows-and-actions/workflow-syntax)
describes these path filters and manual triggers.

Each PHP 8.4/8.5 matrix job uses Node 24 and its own disposable MySQL 8.4 service.
It installs `composer.lock` without plugins/scripts, verifies platform requirements,
and runs PHP/JavaScript/Python/shell syntax, every standalone PHP/HTTP and Node
suite, all three generated browser fixtures and all six SQL suites. Recovery,
activation and password-policy tests run sequentially because they rebuild the
same fixture tables. Screenshots, migrations and contribution/retention tests
use their separately guarded database names. A one-sample synthetic password
benchmark is a timing diagnostic, with no performance threshold. A separate
Apache 2.4 job tests upload denials, public assets and deny-policy delivery.

The cache fixture supplies a global `Memcached` stand-in. The workflow explicitly
disables the native extension with `:memcached`, using
[setup-php's extension controls](https://github.com/shivammathur/setup-php#extensions-optional).
Required extension names do not disable unrelated preinstalled extensions. The
PHP runner checks for a preloaded `Memcached` class before executing suites and
fails with a configuration message instead of the fixture's class-redeclaration
fatal. For local checks, use an isolated PHP test configuration with native
`memcached` disabled and the documented required extensions enabled.

Browser checks use Chrome from the
[Ubuntu 24.04 runner image](https://github.com/actions/runner-images/blob/main/images/ubuntu/Ubuntu2404-Readme.md).
They execute generated HTML and require a completed, nonzero `PASS` marker in
the resulting DOM; missing/incomplete/failed results fail the job. Fixture helper
files, intentional fatal/error workers and included assertion files are driven
by their parent suites, never executed independently. CI has read-only repository
permissions, does not persist checkout credentials and needs no repository
secrets or deployment configuration. No site setup, production migration, asset
build or deployment is performed.

The workflow calls [tests/ci/run.sh](ci/run.sh). With the documented runtimes
installed, its groups can also run locally from the checkout root:

```sh
bash tests/ci/run.sh lint
bash tests/ci/run.sh php
bash tests/ci/run.sh javascript
bash tests/ci/run.sh browser
bash tests/ci/run.sh apache
```

`browser` needs `google-chrome` on PATH, or an explicit executable through
`AOWOW_TEST_CHROME`. `apache` needs Docker, Python and curl; it creates a temporary
fixture, binds only loopback, waits for startup, runs assertions once, and removes
the container/tree on success or failure. `sql` needs installed Composer
packages and a **dedicated disposable** MySQL service on `127.0.0.1`; it creates
four exact fixture databases and destructively rebuilds their tables. Never run
it against a shared/deployment server. For example, after starting the isolated
MySQL container described below:

```sh
AOWOW_TEST_DB_HOST=127.0.0.1 AOWOW_TEST_DB_PORT=33060 bash tests/ci/run.sh sql
```

Add new suite entrypoints to the appropriate runner group; do not glob-execute
all PHP files, since several are deliberate fixture workers. All checks use
synthetic data. Successful CI does not establish full-site browser, FPM/proxy,
mail delivery, historical storage, production grants or deployment acceptance.

These tests exercise the A01 fix without an application database, credentials,
Composer packages, or a web server. Use PHP ≥ 8.4 and Node.js ≥ 18 from the
repository root:

```sh
php tests/security-json.php
php tests/security-json.php --fixtures | node tests/security-json.mjs
```

The PHP checks use the real JSON/JavaScript serializers, community readers,
response serializer, and page template with synthetic DB rows. They verify
that dollar-prefixed text and HTML script delimiters remain data, while explicit
callbacks, locale references, tabs, numeric-looking strings, and profiler values
retain their expected behavior. The JavaScript runner independently executes the
generated scripts with small frontend fixtures and checks their values and an
execution marker.

To check HTML parsing and script execution in a browser, generate a standalone
fixture and open it in a browser. Its result must report `PASS`:

```sh
php tests/security-json.php --fixtures | node tests/security-json.mjs --browser > /tmp/aowow-security-json.html
```

The fixtures test serialization boundaries. They do not boot the complete site,
exercise real account submissions, rebuild production assets, or validate the
full frontend. Complete those checks in restricted staging before deployment.

For A02 guide output checks, run:

```sh
php tests/security-guide-editor.php
php tests/security-guide-editor.php --browser > /tmp/aowow-security-guide-editor.html
```

Open the generated HTML in a browser; its result must report `PASS`. These checks
render the real editor, guide, and changelog templates with synthetic response
properties and database fixtures. They cover author/staff rendering branches,
error redisplay, quoted attributes, closing textarea tags, entities, Unicode,
leading newlines, and adjacent headings/changelog messages. Guide and changelog
responses use their real generation paths and English localization; surrounding
page bricks and unrelated metadata assembly are omitted. The actual preview
function receives the textarea value, with a fixture markup renderer. This does
not verify authentication, persistence, or the complete frontend; test real
author/staff submissions and previews in restricted staging.

For A03 request protection and compatibility checks, run:

```sh
php tests/security-csrf.php
php tests/security-csrf.php --http
php tests/security-csrf.php --browser > /tmp/aowow-security-csrf.html
```

The HTTP checks start a temporary PHP server on loopback and exercise real
configuration, favorites, confirmation, and email-change handlers with fixture
users/database reads and session-backed mutation counters. They check rejected
requests, protected requests, password reauthentication, and token rotation.
The browser fixture executes the real jQuery, legacy Ajax, and CSRF transport
against recording network/form implementations. It checks query selectors,
POST bodies, native forms, upload transports, cancelled confirmations, and
cross-origin token isolation. The generated page must report `PASS`. Complete
real account, database, mail, upload, and frontend checks in restricted staging.

Deploy `static/js/csrf.js` with the PHP changes. Existing command selectors stay
in the query string; mutation requests now use POST and carry `csrfToken` in the
form body or `X-CSRF-Token` in the header. Old pages and old-session tokens must
be reloaded. Set `HOST_URL` to the application's browser origin, including the
correct scheme and nondefault port, for Origin/Referer checks.

For A04 token generation checks, run:

```sh
php tests/security-tokens.php
```

The checks exercise real `Util::createHash` calls, existing account/upload
formats, independence from `mt_srand`, and an injected CSPRNG failure. Existing
pending tokens retain their previous security characteristics; expire or
reissue tokens issued before this fix before public rollout. Token storage,
atomic consumption, expiry, and session revocation need separate acceptance.


For A06 error logging checks, run:

```sh
php tests/security-error-log.php
```

The suite starts isolated PHP subprocesses with synthetic signup/change/reset/
email request shapes. It exercises the real warning, exception and fatal
handlers, config, DB error/profiler callbacks, staff notes and CLI/fallback output.
The DB transport and error response are fixtures. It verifies that request data
is unchanged and secrets do not enter diagnostics, even with exception arguments
enabled, DEBUG zero, missing DB, failed log writes and attempted INI overrides.
Do not run `security-error-log-fixture.php` or `security-error-log-fatal.php`
directly; they intentionally raise errors and are driven by the suite.

Error records now retain metadata only. SQL profiling retains operations and
timings; arbitrary error text, request values and SQL literals are intentionally
omitted. Enforce `display_errors=Off`, `display_startup_errors=Off` and
`log_errors=Off` in effective PHP-FPM settings to cover failures before bootstrap;
explicit safe fallback logging still works. Verify web-server/proxy logs do not
record token-bearing query strings, bodies or credentials. Keep production
`DEBUG=0`, since debug mail previews still contain full messages/tokens. This
suite does not purge old errors/session notes/logs/backups or rotate previously
exposed credentials; complete those steps and real account-flow acceptance in
restricted staging before deployment.


For A07 password recovery/session checks, use PHP ≥ 8.4 with mysqli/mbstring and
the dependencies installed from `composer.lock`. This suite rebuilds account
fixture tables in the dedicated database `aowow_security_test_passwords`. It
requires that exact database name and a disposable MySQL instance. A loopback
fixture can be started with:

```sh
docker run --detach --rm --name aowow-password-test-db --publish 127.0.0.1:33060:3306 --tmpfs /var/lib/mysql --env MYSQL_ALLOW_EMPTY_PASSWORD=yes --env MYSQL_ROOT_HOST=% --env MYSQL_DATABASE=aowow_security_test_passwords mysql:8.4
docker exec aowow-password-test-db mysqladmin ping --silent
```

Once the fixture reports ready, run from the repository root, then remove it:

```sh
AOWOW_TEST_DATABASE=aowow_security_test_passwords AOWOW_TEST_DB_HOST=127.0.0.1 AOWOW_TEST_DB_PORT=33060 php tests/security-password-recovery.php
docker stop aowow-password-test-db
```

`AOWOW_TEST_DB_USER`/`AOWOW_TEST_DB_PASSWORD` optionally select fixture credentials.
The test uses the installed Composer autoloader by default; `AOWOW_TEST_DIBI_DIR`
can point to the pinned Dibi source checkout instead. Custom PHP builds can pass
extra child-process extension paths through `AOWOW_TEST_PHP_EXTENSIONS`
(comma-separated paths). No production credentials or data are needed.

The suite uses the real shared policy, bcrypt, recovery transaction, Dibi SQL,
shipped InnoDB schema, account handler methods and PHP session/authentication
methods. Config and request properties are fixtures. Independent child DB
connections verify competing resets/confirmations, expiry after a lock wait,
rollback after a real SQL-triggered revocation failure, and a signin crossing
confirmation. Both previous browser sessions fail restoration. It also verifies
legacy local-session rejection and unchanged provider-session restoration.
Complete full HTTP/browser/mail acceptance in restricted staging.

Successful reset and confirmed password change always revoke every session,
including the current one. The account form explains this; the optional checkbox
still requests early logout of other sessions before confirmation. Deploy the
localized template and PHP changes together. Local sessions issued before
revision 58 require one fresh signin, because they lack the verified password
fingerprint. No schema migration is needed; account/session tables must be
InnoDB. Revision 61 adds the A09 policy/budget migration described below;
the no-migration statement here applies only to the original A07 fix.


For A05 client-IP checks, run:

```sh
php tests/security-client-ip.php
```

The suite exercises real User initialization and local login-attempt/ban methods
with a synthetic database transport. It tests IPv4/IPv6 and malformed peers,
conflicting environment variables, and forged client/forwarding headers. Local
HTTP servers verify that IPv4/IPv6 connection peers remain the abuse-limit keys;
IPv6 HTTP reports a skip when that loopback transport is unavailable. No real
accounts or persisted bans are used.

Revision 59 obtains client IPs only from validated `$_SERVER['REMOTE_ADDR']`.
Forwarding headers and `getenv` cannot override it. For proxy/CDN deployments,
configure explicit trusted proxy rewriting at the web server and verify the
resulting `REMOTE_ADDR`; otherwise limits/attribution intentionally use the proxy
peer. Check direct-origin access and trusted-hop handling in staging. These
checks do not validate Apache/FPM or a live proxy configuration.


For A08 activation/resend checks, use the same disposable MySQL fixture and PHP/
Dibi setup described for A07, and run from the repository root:

```sh
AOWOW_TEST_DATABASE=aowow_security_test_passwords AOWOW_TEST_DB_HOST=127.0.0.1 AOWOW_TEST_DB_PORT=33060 php tests/security-activation.php
```

This suite shares the guarded SQL/schema/session bootstrap with the recovery
suite; run the two suites sequentially because both rebuild the fixture tables.
It exercises real activation/resend/signin/signup methods, InnoDB transactions,
mail rendering and controlled lock races. The mail transport is intercepted;
no email is sent. Checks include expiry/replay, group preservation, signup
remember-me metadata, session-local prefill, new-key mail, failure rollback,
concurrent activation/resend, and stale signup reclamation.

Revision 60 clears activation authority on success. The signin link uses a
one-use, five-minute prefill in the confirming browser's session. Resend rotates
the key, renews `ACC_CREATE_SAVE_DECAY` and commits its attempt budget before
mail; mail failure leaves the new pending key/budget committed and never restores
the old key. Only the latest link remains valid. The original A08 fix needs no
schema migration; revision 61 adds the A09 budget table described below.
Verify real mailbox delivery, GET scanner visits, protected POST confirmation,
failed mail/cooldowns, InnoDB tables and full browser flows in restricted staging.


For A09 password policy and work-budget checks, use the same disposable MySQL
fixture and PHP/Dibi setup as A07/A08. Run SQL suites sequentially because each
rebuilds the guarded fixture schema. From the repository root:

```sh
AOWOW_TEST_DATABASE=aowow_security_test_passwords AOWOW_TEST_DB_HOST=127.0.0.1 AOWOW_TEST_DB_PORT=33060 php tests/security-password-policy.php
node tests/security-password-policy.mjs
php tests/benchmark-password.php 3
```

The policy suite uses real bcrypt, Dibi transactions, handler methods and shipped
InnoDB schema, with synthetic account/config/request data and intercepted mail.
It verifies boundary/Unicode/space handling, raw input callbacks, legacy short
credentials and cost-15 hashes, upward-only rehash and its session marker,
compare-and-swap races, shared peer/account limits, concurrent first reservations,
fixed windows, rollback, failed mail and migration replay/missing-table behavior.
The JavaScript suite uses the same Unicode/byte vectors with real form scripts
and UI/transport fixtures; it is not a full browser/site test. The standalone CLI
benchmark hashes/verifies a fixed synthetic password sequentially at costs 12
and 15, with 1–10 samples per cost. It reads no accounts, credentials or database
and reports only timing metadata. Run it on deployment hardware; container
results do not establish production FPM capacity. Do not load-test production.

Revision 61 requires `setup/sql/updates/1790899200_01.sql` before PHP deployment.
Apply with the deployment account and verify the exact InnoDB table and indexes;
Revision 66 adds A14 migration accounting; still verify deployment schema and grants. Runtime needs DML on
`aowow_account_password_budget`, never DDL. Fresh setup includes the table.
Deploy the PHP, templates, locale files and `static/js/password-policy.js`
together, including any separate static host/CDN. No generated asset rebuild
or production-password rewrite is needed.

New local passwords require 15 Unicode code points and at most 72 UTF-8 bytes;
all password input callbacks preserve significant spaces. Existing credentials
remain verifiable up to 4,096 bytes. New hashes use bcrypt cost 12; existing
cost-15 hashes remain unchanged at signin. Eligible weaker hashes upgrade with
a guarded write; other existing sessions require fresh signin after rehash.
Peer/account work reservations count admitted successes and failures, using
`ACC_FAILED_AUTH_COUNT` (clamped 1–100) and `ACC_FAILED_AUTH_BLOCK` (clamped
1–86,400 seconds). Known-account aliases share a budget, and peers behind NAT
share the peer budget. Slots are not refunded on success or mail failure; blocked
requests do not extend fixed windows. Provider hashing is unchanged.

Verify real HTTP/browser/mail flows, providers, legacy credentials, session
restoration, deployment grants and bounded FPM behavior in restricted staging.
Shared budgets do not replace hosting concurrency/request controls.

## Private screenshot and upload preview (A10)

For A10 private-upload checks, run from the repository root with PHP ≥ 8.4,
GD with JPEG support, and Node:

```sh
php tests/security-private-uploads.php
node tests/security-private-uploads.mjs
```

The PHP suite creates synthetic JPEGs and temporary directories, uses actual
image writers, crop/approval and private-preview methods, and starts a loopback
PHP HTTP fixture. Account/session/request data and database operations are
fixtures; no database, email or production configuration is accessed. It tests
owner/staff authorization, browser-session crop ownership, expiry/cap limits,
path/symlink confinement, JPEG headers, missing/malformed requests, HEAD and
conditional HTTP behavior. The Node suite renders the real moderation list
using a small DOM fixture and verifies pending/deleted versus approved/sticky
image links. These do not establish full browser or application acceptance.

For actual Apache denial checks, use Python ≥ 3.10 and a disposable Apache 2.4
container. This fixture contains only synthetic sentinel files and serves no
PHP code or account data. The temporary directory must be empty and use the
shown prefix. Choose an unused loopback port if 18080 is occupied:

```sh
fixture_root="$(mktemp -d /tmp/aowow-private-uploads-apache-XXXXXX)"
python3 tests/security-private-uploads-apache.py --prepare "$fixture_root"
docker run --detach --rm --name aowow-upload-apache-test --publish 127.0.0.1:18080:8080 --read-only --cap-drop ALL --security-opt no-new-privileges --user 65534:65534 --tmpfs /tmp:rw,nosuid,nodev --volume "$fixture_root:/fixture:ro" --entrypoint httpd httpd:2.4 -f /fixture/httpd.conf -DFOREGROUND
python3 tests/security-private-uploads-apache.py --check http://127.0.0.1:18080
docker stop aowow-upload-apache-test
rm -r -- "$fixture_root"
```

Run the check after Apache has started. It covers direct pending/temp denial,
encoded paths, PATH_INFO, GET/HEAD and public assets in root, subdirectory and
separate-static-host layouts. It also checks delivery of A16's explicit deny
`crossdomain.xml` in those layouts; the policy must be served at the origin root
to act as a master policy. The PHP stream test and Apache policy test are
separate fixtures; neither runs the complete deployed Apache/PHP application.

Revision 62 needs no schema migration or file move. Deploy the application and
moderator JavaScript plus both `.htaccess` files on every relevant asset host;
verify effective override/rewrite settings or equivalent server rules. Purge
historically cached pending/temp responses and verify real cropper, session,
moderator and approval flows in restricted staging. Crop stages created before
deployment, after logout, expired after one day or evicted beyond 100 session
entries require reupload. Existing pending database records remain accessible
to their owner/staff. A11 addresses completion replay below; storage retention
remains A15.

For A11 screenshot completion checks, use PHP ≥ 8.4 with mysqli and GD/JPEG plus
the pinned Composer dependencies. This suite rebuilds only account, screenshot
and completion-claim tables in the dedicated disposable database
`aowow_security_test_screenshots`; it requires that exact name. A loopback MySQL
fixture can be started with:

```sh
docker run --detach --rm --name aowow-screenshot-test-db --publish 127.0.0.1:33061:3306 --tmpfs /var/lib/mysql --env MYSQL_ALLOW_EMPTY_PASSWORD=yes --env MYSQL_ROOT_HOST=% --env MYSQL_DATABASE=aowow_security_test_screenshots mysql:8.4
docker exec aowow-screenshot-test-db mysqladmin ping --silent
```

After MySQL reports ready, run from the repository root, then remove the fixture:

```sh
AOWOW_TEST_DATABASE=aowow_security_test_screenshots AOWOW_TEST_DB_HOST=127.0.0.1 AOWOW_TEST_DB_PORT=33061 php tests/security-screenshot-completion.php
docker stop aowow-screenshot-test-db
```

The optional Dibi directory, fixture credentials and child-process extension
variables described for A07 also apply. No production credentials, configuration
or files are needed. The suite creates synthetic JPEGs and temporary files,
uses the shipped InnoDB schema and migration, runs real completion/crop response
methods, and starts independent SQL workers plus a loopback PHP HTTP fixture.
It verifies replay/concurrency, permanent-deletion replay, lock-wait expiry,
post-staging bans, session/target ownership, coordinates, transactional failures,
partial-image cleanup, uncertain commits and bounded expired-claim cleanup.
HTTP checks exercise real filtering, method/CSRF checks and response redirects.
Config/localization, target validation and identity/session transport are
fixtures; no full site boot, browser crop UI or actual account restoration is
exercised. Temporary images, workers and the HTTP server are cleaned up.

Revision 63 requires `setup/sql/updates/1790985600_01.sql` before PHP deployment.
Verify the exact claim-table primary/expiry indexes and InnoDB on both screenshot
tables with the deployment account; updater exit status alone is insufficient.
Runtime needs DML on `aowow_screenshot_uploads`, never DDL. Fresh setup includes
the table; existing screenshots/files remain compatible. Deploy completion,
crop and private-upload PHP together, retaining A10's staging access denials.
Unexpired claims must be preserved alongside session/stage state. Successful
completion consumes both staging files/session authority; confirmed pre-commit
failures permit retry. An uncertain commit conservatively retains pending bytes;
its claim prevents a duplicate if the database committed. Orphan reconciliation
and broader retention remain A15 work. Verify real upload, account restrictions,
crop/completion/retry, moderator and filesystem/grant behavior in restricted
staging.

For A12 guide upload checks, run from the repository root with PHP ≥ 8.4,
fileinfo, mbstring and GD with JPEG/PNG support, plus Node ≥ 18:

```sh
php tests/security-guide-uploads.php
node tests/security-guide-uploads.mjs
```

The PHP suite uses actual GD decoders/encoders, upload streams, request filters/
CSRF, guide response and publication methods. It creates only synthetic files in
temporary directories, starts a loopback PHP HTTP fixture, and launches two
independent publishers forced to collide on one numeric filename. No database,
mail, production configuration or real accounts are used. It covers raw-body and
multipart requests (including the legacy form without a query filename), byte/
axis/pixel boundaries, fake/corrupt images, metadata/trailing-content stripping,
transparency, actual/declared size mismatch, existing-file/symlink collisions,
partial-output/staging cleanup, configuration errors and guide restrictions.
An encoder-only noise fixture tests the encoded output cap without bypassing
the HTTP input cap. Custom PHP builds can pass child-process extensions through
`AOWOW_TEST_PHP_EXTENSIONS` as described above. The Node suite exercises actual
uploader validators, guide completion callbacks and XHR/iframe JSON parsers with
small DOM fixtures; it verifies literal names/errors and unchanged numeric
image markup links. These are not full browser or application acceptance tests.

Revision 64 requires no schema migration, generated-asset rebuild or existing
image rewrite. Deploy `includes/components/guidemgr.class.php`, the vendored
`includes/libs/qqFileUploader.class.php`, `endpoints/edit/image.php`,
`static/js/guide-editing.js` and `static/js/fileuploader.js` together, including
every static host/CDN; reload open guide editors. Retain A10's staging denials
and upload script-execution restrictions. Images are bounded to 10 MiB before
and after encoding, 4096 per axis and 12,000,000 pixels. PHP
`upload_max_filesize` must be at least `10M`, and `post_max_size` greater than
`10M` for multipart overhead (the fixture uses `12M`); verify effective FPM/
proxy/web-server limits and GD capabilities. Verify writable temporary/image
directories, exclusive file creation and resource behavior in restricted staging.
New numeric IDs are random and exactly representable by JavaScript; previous
URLs remain valid. New uploads lose original metadata/trailing containers.
Existing images are not re-encoded automatically. Historical files, interrupted
request reconciliation, aggregate quotas and broad retention remain A15 work.

For A13 expression, authenticated-cache and build/permission checks, run from the
repository root with PHP ≥ 8.4, mbstring, SimpleXML, zlib, process/stream functions
and an unprivileged OS account:

```sh
php tests/security-expressions.php
php tests/security-cache.php
php tests/security-builds.php
```

No Composer, MySQL, production configuration/accounts, real Memcached service,
mail or actual generated assets are needed. Each suite cleans its temporary
files/processes. The native `memcached` extension must be disabled in the test
PHP configuration because the cache suite supplies its own writer class.
The cache suite binds a synthetic raw-protocol worker to
loopback port 11211 inside its isolated test environment; do not run it beside
a production cache server. It exercises real TrCache/envelopes and PageTemplate/
Tabs/Listview/LocString/markup serialization and hooks. PECL writes are stubbed;
reads use real local sockets. The expression suite uses actual config reset,
spell evaluation/modifiers and Filter comparison methods with synthetic DB/
localization; it parses all 48 numeric defaults from shipped initial data. The
build suite verifies real PHP argv/cwd/probing, output/timeout handling and actual
config/weight callers while replacing only the dataset entrypoint with a small
synthetic script. Request/identity preconditions and DB persistence are fixtures.
Filesystem checks cover unchanged existing modes, private config/cache modes,
new public modes, destination symlinks and updating an approved file beneath an
immutable parent. Root bypasses the parent-writability checks; use an ordinary
OS identity to exercise them. Full site boot, world-tooltip corpus, real asset
generation, PECL/server integration, FPM and Windows remain staging acceptance.

Revision 65 needs no SQL migration or static JavaScript deployment. Deploy the
PHP changes together, including the three new components and setup file helpers.
Provision `config/security.php` from `setup/security.php.example` with a unique
32-byte random key as 64 hex characters; protect the file and its parent from
PHP-FPM/other-site writes and HTTP access. Missing/invalid keys disable response
caching while pages still generate. Keep keys per-installation and out of Git.
Older unsigned response caches are rejected; key rotation/revision changes cause
cache misses without changing sessions. Private cache files/directories use
0600/0700; existing directory permissions are preserved and require inspection.

Verify the absolute CLI path (`PHP_BINDIR/php` by default or `AOWOW_PHP_CLI`), PHP
≥ 8.4 and actual required extensions/INI, shell-free process launch, local text-
protocol Memcached reads and write/fallback/refresh behavior if enabled. New
public assets/directories use 0644/0755 and existing permissions are preserved;
reconcile previously permissive modes and grant only intended generated targets.
The DB configurator writes credentials with at most 0640: provision the private
PHP/deployment group or equivalent read access. Precreate approved root files
such as robots.txt so the checkout parent can remain immutable. Keep PHP/config/
interpreter and other sites immutable/inaccessible to the site's FPM identity,
and prove read-only world grants. Verify actual reset/rebuild/template/tooltip
flows in restricted staging. A14 is covered by the separate real SQL/CLI suite
below; A15 quotas/retention, writable-browser-asset persistence and optional
profiling boundaries are not closed by these synthetic checks.


## SQL update and CLI failure accounting (A14)

Run from the checkout root with PHP ≥ 8.4, mbstring, mysqli, process functions and
Composer/Dibi installed. Use only an isolated disposable MySQL database named
`aowow_security_test_updates`, with an empty-password root fixture account. The
suite drops/recreates all tables in that exact database. It reads no runtime
configuration, world data, production accounts or credentials. Do not point it
at a shared database/server or run it with production credentials.

```sh
AOWOW_TEST_DATABASE=aowow_security_test_updates \
AOWOW_TEST_DB_HOST=127.0.0.1 AOWOW_TEST_DB_PORT=33062 \
php tests/security-updates.php
```

An isolated MySQL 8.4 fixture was exercised with PHP 8.5.10 and Dibi 5.1.1. Tests
use real SQL/DDL/transactions, triggers for statement/accounting/persistence
failure, independent CLI processes, server named locks and a hard-killed updater.
They parse all shipped top-level migrations without applying the game-data
corpus. Actual entrypoint/dispatcher/updater/sync code runs against synthetic
generators/configuration. Coverage includes zero-affected queries, delimiters,
EOF, exact filenames and part-zero markers, partial DDL persistence, journal
replay prevention, atomic version/journal completion and uncertain commit
acknowledgement, malformed metadata, maintenance verification, connection timer
retention, concurrent exclusion, pending work, help, initialization, verification
and follow-up statuses. Missing-extension checks deliberately stub extension
availability before the real kernel can read runtime configuration.

For a library-only test installation, `AOWOW_TEST_DIBI_DIR` may point to unpacked
Dibi source. `AOWOW_TEST_MYSQLI_EXTENSION` optionally passes an absolute mysqli
module path to child PHP processes when the fixture loads it with `php -d
extension=...`; ordinary PHP installations do not need either variable.

Revision 66 introduces `sql_update_journal` without a dated migration: it must
exist before any new migration SQL can execute. Fresh schema includes it; the
updater bootstraps a missing journal with deployment CREATE privilege. A schema
owner can pre-provision its exact definition from `setup/sql/01-db_structure.sql`
or `SqlUpdate::JOURNAL_DDL`; existing journals need no CLI CREATE. Journal and
version tables require InnoDB; exactly one version row is required. Deployment
needs journal SELECT/INSERT/UPDATE, version SELECT/UPDATE and the actual migration/
generator grants. FPM needs no new journal or DDL grants; keep deployment
credentials out of runtime config. Deploy all PHP/CLI changes together and test
actual upgrades, pending generators, maintenance and restoration in restricted
staging. MySQL fixture results do not establish MariaDB, full corpus, production
or host acceptance.

On a failed/interrupted migration, retain maintenance and inspect the running
journal, checksum, acknowledged progress and actual schema/data. Earlier SQL may
already be committed; restore a consistent backup or reconcile an audited recovery
on a disposable copy. Never clear a running record or advance a version simply
to retry. A migration completed before a follow-up-only failure is skipped on the
next update; pending generation remains. Successful retry preserves maintenance
if it was already enabled, so lift it only after site acceptance. See
[the review](../docs/aowow-security-review.md#a14--migration-failure-accounting)
for recovery and deployment boundaries.


## Outbound calls, contribution limits and retention (A15)

Run from the checkout root using PHP ≥ 8.4 with cURL, mbstring, OpenSSL and process
functions, and Node.js ≥ 18. The video test also needs the `openssl` executable
to create a disposable certificate. It maps only the fixed YouTube origin to
a loopback TLS fixture; it makes no public YouTube requests.

```sh
php tests/security-video.php
php tests/security-retention.php
node tests/security-community.mjs
php tests/security-community-pages.php
php tests/security-community-pages.php --fixtures | node tests/security-community.mjs --pages
php tests/security-private-uploads.php
php tests/security-guide-uploads.php
```

The existing image suites require GD/JPEG/PNG and exercise denied contribution
budgets before screenshot/avatar processing and both guide upload transports.
Video checks use real cURL/TLS for timeouts, certificate validation, redirect
rejection, bounded bodies/headers, malformed schemas and existing URL formats.
File checks create only synthetic temporary trees, covering preview, expiry,
fresh/published/pending/unknown files, links/hardlinks, protected roots, streaming
cursors past 1,000 entries, cache collisions/capacity/locking, staged videos and
the per-request diagnostic cap. The JS fixture executes the actual reply merge,
incremental loader and missing-reply highlight branch with synthetic UI/network
objects. Rendered paging links verify escaping and previous/next boundaries;
their actual inline scripts verify legacy anchors and redirect-loop avoidance.
These fixtures do not establish full browser acceptance.

For SQL checks use only an isolated disposable MySQL database named
`aowow_security_test_resources`, with an empty-password root fixture account.
The suite drops/recreates all tables in that exact database. It reads no runtime
configuration, world data, real accounts or credentials. Never use a shared
database/server or production credentials. Composer/Dibi and mysqli are required.

```sh
AOWOW_TEST_DATABASE=aowow_security_test_resources \
AOWOW_TEST_DB_HOST=127.0.0.1 AOWOW_TEST_DB_PORT=33063 \
php tests/security-contributions.php
```

Optional `AOWOW_TEST_DIBI_DIR` and `AOWOW_TEST_MYSQLI_EXTENSION` work as described
for A14. Real InnoDB transactions, triggers and independent workers check daily
and permanent account/global budgets, expired windows, failed SQL and uncertain
commit acknowledgements. Actual comment/reply joins check page boundaries,
visibility, tied timestamps, offsets and focused anchors. The actual migration
and CLI entrypoint/dispatcher/pruner run against synthetic configuration and a
temporary checkout, checking preview/apply status, private cursor state and
concurrent migration/cleanup exclusion. These MySQL 8.4 / PHP 8.5.10 / Dibi 5.1.1
checks do not establish MariaDB, live YouTube, production, scheduler, historical
storage, volume/database capacity, mail queue, FPM or complete UI acceptance.

Apply [1791000000_01.sql](../setup/sql/updates/1791000000_01.sql) and rebuild the
requested `globaljs` assets before accepting the PHP/template rollout. Runtime
needs budget DML without DDL; cleanup needs DELETE on `errors`,
`account_password_budget`, `screenshot_uploads` and expiring `contribution_budget`
rows. Keep the schema InnoDB. Run preview before scheduling repeated apply
batches, preserve permanent counters, and review existing storage separately.
See [A15](../docs/aowow-security-review.md#a15--outbound-calls-quotas-and-retention).


## Redirects and operator administration (A16)

Run from the checkout root using PHP ≥ 8.4 with mbstring, SimpleXML and process
functions, native `phpinfo` enabled for the allowed-operator compatibility
check, and Node.js ≥ 18:

```sh
php tests/security-redirects.php
php tests/security-redirects.php --fixtures | node tests/security-redirects.mjs
php tests/security-admin-boundary.php
php tests/security-csrf.php --http
```

The first suite uses synthetic URLs/peers and the actual return-target and
operator policy classes. It covers canonical/default/nondefault origins, root
and subdirectory paths, IPv6, fixed fallbacks, off-origin and malformed URLs,
control bytes, userinfo, slash/backslash/dot/encoding ambiguities, bounded lengths,
strict exact-IP policy validation and spoofed-header/environment rejection.
The Node fixture resolves actual PHP outputs with the WHATWG URL parser to
check browser origin/authority semantics. It makes no HTTP requests and does
not establish complete browser or site acceptance. XML checks parse the deny
policy without external entities and confirm it contains no access grants.

The HTTP suite starts independent loopback PHP workers with the actual
BaseResponse, CSRF gate and locale/announcement/diagnostic/configuration
handlers. Identity, configuration/DB operations, tabs and surrounding template
rendering are synthetic; the diagnostic handler calls native PHP `phpinfo`.
It checks missing/empty/wrong/malformed operator lists, all five protected routes,
GET/HEAD, no-store headers, zero privileged work on denial, allowed ADMIN/DEV
behavior, login/role enforcement, configuration writes with POST/CSRF, and
locale/announcement redirects with absent, off-origin and legitimate Referers.
The fixture substitutes a 403 for template authentication failures; production
may redirect anonymous users to signin or render a role-denial page. Synthetic
configuration write counters do not prove deployed DB grants or UI behavior.
No runtime private configuration, real account, database or public origin is used.

The existing CSRF HTTP fixture explicitly allows its synthetic loopback operator.
Use the [Apache fixture above](#private-screenshot-and-upload-preview-a10) for
actual GET/HEAD delivery of the XML on root/subdirectory/static layouts; it does
not execute a Flash/Acrobat policy client. On the deployed site serve the deny
master policy at `/crossdomain.xml` on every application/static origin, including
subdirectory installations, and purge the old wildcard response from caches.

Revision 68 requires no new SQL migration or JavaScript build. Deploy both new
components with the responder/endpoints and XML. Add exact operator IPs to the
existing private `config/security.php` only if browser diagnostics/configuration
are needed; preserve its cache key. The default denies those five routes.
Verify actual FPM `REMOTE_ADDR`, login/roles, unlisted staff and spoofed-header
denials, POST/CSRF, no-store and proxy cache behavior. Never authorize a shared
proxy IP. Native diagnostic output remains sensitive for allowed operators.
See [install step 11](../README.md#11-restrict-diagnosticsconfiguration-and-deploy-the-legacy-deny-policy)
and [A16](../docs/aowow-security-review.md#a16--redirects-and-legacydiagnostic-exposure).
