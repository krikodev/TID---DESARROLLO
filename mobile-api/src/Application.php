<?php
declare(strict_types=1);
namespace TidApi;

final class Application
{
    private StateStore $state;
    private \Closure $connect;
    public function __construct(private readonly array $config, ?callable $connect = null)
    {
        date_default_timezone_set($config['timezone'] ?? 'America/Lima');
        $this->state = new StateStore($config['state_path']);
        $this->connect = \Closure::fromCallable($connect ?? [Connection::class, 'mysql']);
    }
    public function handle(Request $request): Response
    {
        $requestId = bin2hex(random_bytes(8));
        $headers = ['X-Request-Id' => $requestId];
        try {
            $origin = $request->header('Origin');
            if ($origin !== '') {
                if (!in_array($origin, $this->config['allowed_origins'] ?? [], true)) { throw new ApiException(403, 'ORIGIN_NOT_ALLOWED', 'Origen web no permitido.'); }
                $headers['Access-Control-Allow-Origin'] = $origin;
                $headers['Vary'] = 'Origin';
            }
            $headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization, X-Company-Domain';
            $headers['Access-Control-Allow-Methods'] = 'GET, POST, OPTIONS';
            if ($request->method === 'OPTIONS') { return new Response(204, [], $headers); }
            if ($this->config['environment'] === 'production' && !$request->https) { throw new ApiException(426, 'HTTPS_REQUIRED', 'Esta API requiere HTTPS.'); }
            $domain = Config::domain($request->header('X-Company-Domain'));
            $tenant = $this->config['tenants'][$domain] ?? null;
            if ($tenant === null) { throw new ApiException(404, 'UNKNOWN_COMPANY', 'Empresa no registrada en esta API.'); }
            $this->state->throttle('request:' . $domain . ':' . $request->ip, 300, 60);
            $routes = [
                '/v1/health' => 'GET', '/v1/resumen' => 'GET', '/v1/auth/login' => 'POST', '/v1/auth/me' => 'GET', '/v1/auth/logout' => 'POST',
                '/v1/encomiendas/dni' => 'GET', '/v1/encomiendas/historial' => 'GET', '/v1/encomiendas/tracking' => 'GET',
                '/v1/encomiendas/comprobante' => 'GET', '/v1/encomiendas/imagenes' => 'GET',
            ];
            if (!isset($routes[$request->path])) { throw new ApiException(404, 'ROUTE_NOT_FOUND', 'Ruta no encontrada. La API es de solo consulta.'); }
            if ($request->method !== $routes[$request->path]) { $headers['Allow'] = $routes[$request->path] . ', OPTIONS'; throw new ApiException(405, 'METHOD_NOT_ALLOWED', 'Método no permitido.'); }
            if ($request->path === '/v1/auth/login') {
                $this->state->throttle('login-ip:' . $domain . ':' . $request->ip, (int)($this->config['login_limit'] ?? 10), (int)($this->config['login_window'] ?? 900));
                $email = $request->text('email', 254);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { throw new ApiException(422, 'VALIDATION_ERROR', 'Correo inválido.'); }
                $this->state->throttle('login-account:' . $domain . ':' . strtolower($email), (int)($this->config['login_limit'] ?? 10), (int)($this->config['login_window'] ?? 900));
                // No recortar la contraseña: los espacios pueden formar parte de ella.
                $password = $request->data['password'] ?? $request->data['pass'] ?? '';
                if (!is_string($password) || $password === '' || strlen($password) > 1024) { throw new ApiException(422, 'VALIDATION_ERROR', 'Contraseña requerida.'); }
            }
            $repo = new Repository(($this->connect)($tenant), $tenant);
            if ($request->path === '/v1/health') {
                $repo->health();
                $payload = ['company' => ['domain' => $domain, 'name' => $tenant['name']], 'api_version' => 'v1', 'read_only' => true];
            } elseif ($request->path === '/v1/auth/login') {
                $user = $repo->user($email);
                if (!$user || !Passwords::verify($password, (string)$user['contrasena'], $tenant)) { throw new ApiException(401, 'INVALID_CREDENTIALS', 'Credenciales inválidas o usuario sin acceso.'); }
                unset($user['contrasena']);
                $payload = $this->state->issue($domain, (int)$user['id_usuario'], (int)($this->config['session_ttl'] ?? 28800)) + ['user' => $user, 'company' => ['domain' => $domain, 'name' => $tenant['name']], 'read_only' => true];
            } else {
                if (!preg_match('/^Bearer ([a-f0-9]{64})$/D', $request->header('Authorization'), $match)) { throw new ApiException(401, 'UNAUTHENTICATED', 'Inicia sesión nuevamente.'); }
                $userId = $this->state->user($match[1], $domain);
                $user = $repo->user(null, $userId);
                if (!$user) { $this->state->revoke($match[1]); throw new ApiException(401, 'UNAUTHENTICATED', 'El usuario ya no tiene acceso.'); }
                unset($user['contrasena']);
                $page = $request->integer('page', 1, 100000);
                $limit = $request->integer('limit', 50, 100);
                switch ($request->path) {
                    case '/v1/resumen': $payload = $repo->summary(isset($request->data['fecha']) ? $request->date('fecha') : date('Y-m-d'), $user); break;
                    case '/v1/auth/me': $payload = ['user' => $user, 'read_only' => true]; break;
                    case '/v1/auth/logout': $this->state->revoke($match[1]); $payload = ['message' => 'Sesión cerrada.']; break;
                    case '/v1/encomiendas/dni':
                        $dni = $request->text('dni', 8);
                        if (!preg_match('/^\d{8}$/D', $dni)) { throw new ApiException(422, 'VALIDATION_ERROR', 'DNI debe contener 8 dígitos.'); }
                        $payload = $repo->pending($dni, $user, $page, $limit); break;
                    case '/v1/encomiendas/historial': $payload = $repo->history($request->date('fecha'), $request->text('filtro', 100, false), $user, $page, $limit); break;
                    case '/v1/encomiendas/tracking': $payload = $repo->tracking($request->text('codigo_tracking', 100), $user); break;
                    case '/v1/encomiendas/comprobante': $payload = $repo->receipt($request->text('serie', 20), $request->text('correlativo', 20), $request->date('fecha'), $user, $page, $limit); break;
                    case '/v1/encomiendas/imagenes':
                        $rows = $repo->tracking($request->text('codigo_tracking', 100), $user)['data'];
                        $urls = [];
                        foreach ($rows as $row) {
                            foreach (explode(',', (string)($row['fotos_evidencia'] ?? '')) as $url) {
                                $url = trim($url);
                                if (filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) { $urls[] = $url; }
                            }
                        }
                        $payload = ['imagenes' => array_map(static fn(string $url): array => ['url' => $url], array_values(array_unique($urls)))]; break;
                }
            }
            return new Response(200, ['success' => true] + $payload + ['request_id' => $requestId], $headers);
        } catch (ApiException $e) {
            if ($e->status === 429) { $headers['Retry-After'] = (string)($this->config['login_window'] ?? 900); }
            return new Response($e->status, ['success' => false, 'code' => $e->errorCode, 'message' => $e->getMessage(), 'request_id' => $requestId], $headers);
        } catch (\Throwable $e) {
            // El diagnóstico no incluye SQL, credenciales ni datos personales.
            error_log('TID API request=' . $requestId . ' exception=' . get_class($e) . ' code=' . $e->getCode());
            return new Response(503, ['success' => false, 'code' => 'SERVICE_UNAVAILABLE', 'message' => 'No se pudo consultar el sistema. Revisa conexión y esquema en el servidor.', 'request_id' => $requestId], $headers);
        }
    }
}
