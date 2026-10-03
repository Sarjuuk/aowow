#!/usr/bin/env bash
# Run explicit suite entrypoints; fixture helpers must never be executed as standalone tests.
set -euo pipefail
cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../.."

case "${1:-}" in
  lint)
    find includes endpoints localization template setup tests -type f -name '*.php' -print0 |
      xargs -0 -n 1 php -l
    php -l setup/security.php.example
    php -l index.php
    php -l aowow
    php -l prQueue
    while IFS= read -r -d '' script; do
      node --check "$script"
    done < <(find static tests -type f \( -name '*.js' -o -name '*.mjs' \) -print0)
    bash -n tests/ci/run.sh
    python3 - <<'PY'
import ast
from pathlib import Path
for path in Path('tests').rglob('*.py'):
    ast.parse(path.read_text(), filename=str(path))
PY
    ;;
  php)
    # Check before loading the cache fixture, which deliberately supplies a global Memcached stand-in.
    php -r '
      if (class_exists("Memcached", false)) {
          fwrite(STDERR, "Cache fixture requires the native memcached extension disabled; use an isolated test PHP configuration.\n");
          exit(1);
      }
    '
    for suite in json guide-editor tokens error-log client-ip private-uploads guide-uploads \
                 expressions cache builds video retention community-pages redirects admin-boundary; do
      php "tests/security-$suite.php"
    done
    php tests/security-csrf.php --http
    ;;
  javascript)
    php tests/security-json.php --fixtures | node tests/security-json.mjs
    node tests/security-private-uploads.mjs
    node tests/security-guide-uploads.mjs
    node tests/security-password-policy.mjs
    node tests/security-community.mjs
    php tests/security-community-pages.php --fixtures | node tests/security-community.mjs --pages
    php tests/security-redirects.php --fixtures | node tests/security-redirects.mjs
    ;;
  browser)
    # Standalone generated fixtures use the hosted runner's Chrome; PASS must come from executed DOM.
    browser="${AOWOW_TEST_CHROME:-google-chrome}"
    command -v "$browser" >/dev/null
    fixture_root="$(mktemp -d /tmp/aowow-security-browser-XXXXXX)"
    trap 'rm -rf -- "$fixture_root"' EXIT
    php tests/security-json.php --fixtures | node tests/security-json.mjs --browser > "$fixture_root/json.html"
    php tests/security-guide-editor.php --browser > "$fixture_root/guide-editor.html"
    php tests/security-csrf.php --browser > "$fixture_root/csrf.html"
    for fixture in json guide-editor csrf; do
      timeout 45s "$browser" --headless --no-sandbox --disable-gpu --disable-dev-shm-usage \
        --disable-background-networking \
        --dump-dom "file://$fixture_root/$fixture.html" > "$fixture_root/$fixture.dom"
      python3 tests/ci/check-browser.py "$fixture_root/$fixture.dom"
    done
    ;;
  sql)
    # These exact disposable databases are destructive fixtures; never load application credentials.
    export AOWOW_TEST_DB_HOST="${AOWOW_TEST_DB_HOST:-127.0.0.1}"
    export AOWOW_TEST_DB_PORT="${AOWOW_TEST_DB_PORT:-3306}"
    case "$AOWOW_TEST_DB_HOST" in
      127.0.0.1) ;;
      *) echo 'SQL CI requires an isolated MySQL fixture on 127.0.0.1.' >&2; exit 1 ;;
    esac
    # shellcheck disable=SC2016 # PHP variables must be passed literally.
    php -r '
      $db = new mysqli(getenv("AOWOW_TEST_DB_HOST"), "root", "", "", (int)getenv("AOWOW_TEST_DB_PORT"));
      foreach (["passwords", "screenshots", "updates", "resources"] as $suffix) {
          $db->query("CREATE DATABASE IF NOT EXISTS aowow_security_test_".$suffix." CHARACTER SET utf8mb4");
      }
    '
    # Recovery, activation and policy suites share tables and must remain sequential.
    for suite in password-recovery activation password-policy; do
      AOWOW_TEST_DATABASE=aowow_security_test_passwords php "tests/security-$suite.php"
    done
    AOWOW_TEST_DATABASE=aowow_security_test_screenshots php tests/security-screenshot-completion.php
    AOWOW_TEST_DATABASE=aowow_security_test_updates php tests/security-updates.php
    AOWOW_TEST_DATABASE=aowow_security_test_resources php tests/security-contributions.php
    ;;
  apache)
    fixture_root="$(mktemp -d /tmp/aowow-private-uploads-apache-XXXXXX)"
    container="aowow-security-apache-${fixture_root##*-}"
    cleanup() {
      docker rm --force "$container" >/dev/null 2>&1 || true
      rm -rf -- "$fixture_root"
    }
    trap cleanup EXIT
    python3 tests/security-private-uploads-apache.py --prepare "$fixture_root"
    docker run --detach --rm --name "$container" --publish 127.0.0.1::8080 \
      --read-only --cap-drop ALL --security-opt no-new-privileges --user 65534:65534 \
      --tmpfs /tmp:rw,nosuid,nodev --volume "$fixture_root:/fixture:ro" \
      --entrypoint httpd httpd:2.4 -f /fixture/httpd.conf -DFOREGROUND
    address="$(docker port "$container" 8080/tcp)"
    # Wait only for startup; assertion failures must not be hidden by retrying the test suite.
    ready=false
    for ((attempt=0; attempt<30; attempt++)); do
      if curl --fail --silent --output /dev/null "http://$address/crossdomain.xml"; then
        ready=true
        break
      fi
      sleep 1
    done
    if [[ "$ready" != true ]]; then
      docker logs "$container"
      echo 'Apache fixture did not become ready.' >&2
      exit 1
    fi
    python3 tests/security-private-uploads-apache.py --check "http://$address"
    ;;
  *)
    echo 'Usage: bash tests/ci/run.sh {lint|php|javascript|browser|sql|apache}' >&2
    exit 1
    ;;
esac
