"""HTTP real con fixtures: python tests/http_smoke.py --php php
Opcional: --frankenphp /ruta/frankenphp. Sin bases ni credenciales reales.
"""
import argparse
import json
import os
from pathlib import Path
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

parser = argparse.ArgumentParser()
parser.add_argument('--php', default='php')
parser.add_argument('--frankenphp')
args = parser.parse_args()
root = Path(__file__).resolve().parent.parent
with socket.socket() as sock:
    sock.bind(('127.0.0.1', 0))
    port = sock.getsockname()[1]
base = f'http://127.0.0.1:{port}'

def request(path, method='GET', data=None, domain='tid.net.pe', token=None, headers=None, form=False):
    headers = {'X-Company-Domain': domain, **(headers or {})}
    if token:
        headers['Authorization'] = 'Bearer ' + token
    body = None
    if data is not None:
        headers['Content-Type'] = 'application/x-www-form-urlencoded' if form else 'application/json'
        body = urllib.parse.urlencode(data).encode() if form else data if isinstance(data, bytes) else json.dumps(data).encode()
    try:
        response = urllib.request.urlopen(urllib.request.Request(base + path, data=body, headers=headers, method=method), timeout=10)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        raw = response.read()
        return response.status, dict(response.headers), json.loads(raw) if raw else {}

count = 0

def check(condition, name):
    global count
    assert condition, name
    count += 1
    print('PASS HTTP', name)

with tempfile.TemporaryDirectory(prefix='tid-http-') as directory:
    env = os.environ.copy()
    env['TID_API_TEST_STATE'] = str(Path(directory) / 'state.sqlite')
    if args.frankenphp:
        router = str(root / 'tests/http-router.php').replace('\\', '/').replace("'", "\\'")
        (Path(directory) / 'index.php').write_text("<?php require '" + router + "';\n")
        cmd = [args.frankenphp, 'php-server', '--listen', f'127.0.0.1:{port}', '--root', directory]
    else:
        cmd = [args.php, '-S', f'127.0.0.1:{port}', '-t', str(root / 'public'), str(root / 'tests/http-router.php')]
    server = subprocess.Popen(cmd, env=env, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    try:
        for attempt in range(100):
            try:
                response = request('/v1/health')
                break
            except urllib.error.URLError:
                if server.poll() is not None:
                    raise RuntimeError('No se pudo iniciar PHP')
                time.sleep(0.05)
        else:
            raise RuntimeError('PHP no respondió')
        check(response[0] == 200 and response[2]['api_version'] == 'v1', 'health y routing')
        response = request('/v1/auth/login', 'OPTIONS', domain='', headers={'Origin': 'http://localhost:5173'})
        check(response[0] == 204 and 'X-Company-Domain' in response[1]['Access-Control-Allow-Headers'], 'preflight sin token')
        response = request('/v1/auth/login', 'POST', {'email': 'operador@example.com', 'password': ' p4ss '})
        check(response[0] == 200, 'login JSON')
        token = response[2]['access_token']
        response = request('/v1/encomiendas/dni?dni=22222222', token=token)
        check(response[0] == 200 and len(response[2]['data']) == 2, 'GET autenticado')
        response = request('/v1/auth/me', domain='jrcargo.com.pe', token=token)
        check(response[0] == 401, 'token no cruza empresa')
        response = request('/v1/auth/login', 'POST', {'email': 'operador@example.com', 'pass': ' p4ss '}, form=True)
        check(response[0] == 200, 'login form compatible')
        response = request('/v1/auth/login', 'POST', b'{broken')
        check(response[0] == 400 and response[2]['code'] == 'INVALID_JSON', 'JSON malformado')
        response = request('/v1/auth/login', 'POST', b'[]')
        check(response[0] == 400, 'rechaza array JSON')
        response = request('/v1/auth/login', 'POST', b'{' + b' ' * 65536 + b'}')
        check(response[0] == 413, 'limita tamaño del body')
        response = request('/v1/auth/logout', 'POST', {}, token=token)
        check(response[0] == 200, 'logout')
        response = request('/v1/auth/me', token=token)
        check(response[0] == 401, 'token revocado')
        print(f'OK: {count} verificaciones HTTP reales con fixtures SQLite.')
    finally:
        server.terminate()
        try:
            server.wait(timeout=5)
        except subprocess.TimeoutExpired:
            server.kill()
            server.wait(timeout=5)
