#!/usr/bin/env python3
"""Prepare a synthetic Apache tree or verify its real HTTP upload denial rules."""
import argparse
import http.client
from pathlib import Path
import shutil
import tempfile
from urllib.parse import urlsplit

parser = argparse.ArgumentParser()
mode = parser.add_mutually_exclusive_group(required=True)
mode.add_argument('--prepare', type=Path)
mode.add_argument('--check')
args = parser.parse_args()
repo = Path(__file__).resolve().parent.parent

if args.prepare:
    root = args.prepare.resolve()
    if root.parent != Path(tempfile.gettempdir()).resolve() or not root.name.startswith('aowow-private-uploads-apache-'):
        parser.error('Use an empty aowow-private-uploads-apache-* directory in the temporary directory.')
    if root.exists() and any(root.iterdir()):
        parser.error('Fixture directory must be empty.')
    root.mkdir(exist_ok=True)
    root.chmod(0o755)
    files = {
        'uploads/screenshots/pending/7.jpg': 'PRIVATE-STAGING-SENTINEL',
        'uploads/screenshots/temp/owner-1-1-key.jpg': 'PRIVATE-STAGING-SENTINEL',
        'uploads/screenshots/temp/owner-1-1-key_original.jpg': 'PRIVATE-STAGING-SENTINEL',
        'uploads/temp/owner-1-1-video': 'PRIVATE-STAGING-SENTINEL',
        'uploads/temp/owner-avatar-12-key.jpg': 'PRIVATE-STAGING-SENTINEL',
        'uploads/temp/owner-avatar-12-key_original.jpg': 'PRIVATE-STAGING-SENTINEL',
        'uploads/temp/rejected.png': 'PRIVATE-STAGING-SENTINEL',
        'uploads/temp/deep/upload.php': 'PRIVATE-STAGING-SENTINEL',
        'uploads/screenshots/normal/7.jpg': 'PUBLIC-APPROVED-SENTINEL',
        'uploads/screenshots/thumb/7.jpg': 'PUBLIC-APPROVED-SENTINEL',
        'uploads/screenshots/resized/7.jpg': 'PUBLIC-APPROVED-SENTINEL',
        'uploads/avatars/12.jpg': 'PUBLIC-APPROVED-SENTINEL',
        'uploads/guide/images/1.png': 'PUBLIC-APPROVED-SENTINEL',
        'js/public.js': 'PUBLIC-APPROVED-SENTINEL',
    }
    for name, content in files.items():
        path = root / 'site/static' / name
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_text(content)
    shutil.copyfile(repo / '.htaccess', root / 'site/.htaccess')
    shutil.copyfile(repo / 'static/uploads/.htaccess', root / 'site/static/uploads/.htaccess')
    (root / 'site/index.php').write_text('ROOT-ROUTING-SENTINEL')
    shutil.copyfile(repo / 'crossdomain.xml', root / 'site/crossdomain.xml')
    shutil.copytree(root / 'site/static', root / 'assets')
    shutil.copyfile(repo / 'crossdomain.xml', root / 'assets/crossdomain.xml')
    (root / 'httpd.conf').write_text('''ServerRoot "/usr/local/apache2"
Listen 8080
ServerName app.example
PidFile /tmp/aowow-httpd.pid
LoadModule mpm_event_module modules/mod_mpm_event.so
LoadModule unixd_module modules/mod_unixd.so
LoadModule authz_core_module modules/mod_authz_core.so
LoadModule authz_host_module modules/mod_authz_host.so
LoadModule mime_module modules/mod_mime.so
LoadModule dir_module modules/mod_dir.so
LoadModule alias_module modules/mod_alias.so
LoadModule rewrite_module modules/mod_rewrite.so
User #65534
Group #65534
TypesConfig conf/mime.types
ErrorLog /proc/self/fd/2
LogLevel warn
DocumentRoot /fixture/site
<Directory /fixture>
    Require all granted
    AllowOverride All
    Options FollowSymLinks
</Directory>
<FilesMatch "^\\.ht">
    Require all denied
</FilesMatch>
<VirtualHost *:8080>
    ServerName app.example
    DocumentRoot /fixture/site
    Alias /nested /fixture/site
</VirtualHost>
<VirtualHost *:8080>
    ServerName static.example
    DocumentRoot /fixture/assets
</VirtualHost>
''')
    print('PASS: synthetic Apache fixture prepared')
    raise SystemExit

base = urlsplit(args.check)
if base.scheme != 'http' or base.hostname not in ('127.0.0.1', 'localhost', 'apache'):
    parser.error('Check only the isolated loopback/Apache fixture.')
checks = 0

def request(path, host, method='GET', headers=None):
    connection = http.client.HTTPConnection(base.hostname, base.port or 8080, timeout=5)
    connection.request(method, path, headers={'Host': host, **(headers or {})})
    response = connection.getresponse()
    result = response.status, response.read()
    connection.close()
    return result

def check(condition, message):
    global checks
    checks += 1
    if not condition:
        raise AssertionError(message)

private = [
    'uploads/screenshots/pending/7.jpg',
    'uploads/screenshots/temp/owner-1-1-key.jpg',
    'uploads/screenshots/temp/owner-1-1-key_original.jpg',
    'uploads/temp/owner-1-1-video',
    'uploads/temp/owner-avatar-12-key.jpg',
    'uploads/temp/owner-avatar-12-key_original.jpg',
    'uploads/temp/rejected.png', 'uploads/temp/deep/upload.php',
    'uploads/screenshots/pend%69ng/7.jpg',
    'uploads/%74emp/owner-1-1-video',
    'uploads/screenshots/normal/../pending/7.jpg',
    'uploads/screenshots/pending/7.jpg/extra',
    'uploads/screenshots/pending', 'uploads/screenshots/temp/', 'uploads/temp/',
    'uploads/temp/owner-1-1-video?download=1',
]
public = [
    'uploads/screenshots/normal/7.jpg', 'uploads/screenshots/thumb/7.jpg',
    'uploads/screenshots/resized/7.jpg', 'uploads/avatars/12.jpg',
    'uploads/guide/images/1.png', 'js/public.js',
]
for host, prefix in [('app.example', '/static/'), ('app.example', '/nested/static/'), ('static.example', '/')]:
    for path in private:
        for method in ['GET', 'HEAD']:
            status, body = request(prefix + path, host, method, {'Range': 'bytes=0-128'})
            check(status in (403, 404) and b'PRIVATE-STAGING-SENTINEL' not in body,
                  f'{host} {method} {prefix + path}: status {status}')
    for path in public:
        status, body = request(prefix + path, host)
        check(status == 200 and body == b'PUBLIC-APPROVED-SENTINEL', f'Public asset remains accessible: {host} {path}: {status}')
status, body = request('/?upload=preview&kind=pending&id=7', 'app.example')
check(status == 200 and body == b'ROOT-ROUTING-SENTINEL', 'Root PHP routing remains available (execution is covered separately by PHP HTTP checks)')
for host, path in [('app.example', '/crossdomain.xml'), ('app.example', '/nested/crossdomain.xml'), ('static.example', '/crossdomain.xml')]:
    status, body = request(path, host)
    check(status == 200 and b'permitted-cross-domain-policies="none"' in body and b'allow-access-from' not in body,
          f'Explicit deny policy remains accessible: {host} {path}')
    check(request(path, host, 'HEAD')[0] == 200, f'Deny policy HEAD works: {host} {path}')
print(f'PASS: {checks} Apache upload-denial/public-asset HTTP checks')
